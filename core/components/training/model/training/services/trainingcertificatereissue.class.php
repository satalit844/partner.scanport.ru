<?php

/**
 * Service for manager-side certificate generation and reissue.
 *
 * It preserves TrainingCertificateService behaviour, but writes a new
 * versioned PNG whenever a certificate is rendered.
 */
class TrainingCertificateReissueService extends TrainingCertificateService
{
    /**
     * Reissue existing certificates without requiring the course to remain
     * completed after its programme has changed.
     */
    public function reissueForUsers($courseId, array $userIds)
    {
        $courseId = $this->normalizeCourseId($courseId);
        if ($courseId <= 0) {
            return array();
        }

        $cleanIds = array();
        foreach ($userIds as $userId) {
            $userId = (int)$userId;
            if ($userId > 0 && !in_array($userId, $cleanIds, true)) {
                $cleanIds[] = $userId;
            }
        }

        $results = array();
        foreach ($cleanIds as $userId) {
            $certificate = $this->reissueUserCertificate($courseId, $userId);
            if ($certificate) {
                $results[] = $certificate;
            }
        }

        return $results;
    }

    /**
     * Rebuild an issued certificate using the current template and the text
     * saved in the historical certificate record.
     */
    public function reissueUserCertificate($courseId, $userId)
    {
        $courseId = $this->normalizeCourseId($courseId);
        $userId = (int)$userId;

        if ($courseId <= 0 || $userId <= 0) {
            return false;
        }

        $existing = $this->getUserCertificate($courseId, $userId);
        if (!$existing) {
            return false;
        }

        $template = $this->getTemplate($courseId);
        if (!$template || (int)$template['is_active'] !== 1) {
            return false;
        }

        if ($this->ensureTemplatePreview($template, false) === '') {
            return false;
        }

        $fullname = trim((string)$existing['fullname']);
        if ($fullname === '') {
            $fullname = $this->getUserDisplayName($userId);
        }

        $courseTitle = trim((string)$existing['course_title']);
        if ($courseTitle === '') {
            $courseTitle = $this->getCourseTitle($courseId);
        }

        $completedOn = trim((string)$existing['completedon']);
        if ($completedOn === '' || $completedOn === '0000-00-00 00:00:00') {
            $completedOn = trim((string)$existing['issuedon']);
        }

        $dateFormat = trim((string)$template['date_format']);
        if ($dateFormat === '') {
            $dateFormat = 'd.m.Y';
        }

        $generated = $this->generateCertificateImage($template, array(
            'course_id' => $courseId,
            'user_id' => $userId,
            'fullname' => $fullname,
            'course_title' => $courseTitle,
            'completed_date' => $this->formatDate($completedOn, $dateFormat),
            'completed_raw' => $completedOn,
        ), true);

        if (!$generated) {
            return false;
        }

        $params = array(
            ':course_id' => $courseId,
            ':template_id' => (int)$template['id'],
            ':user_id' => $userId,
            ':user_course_id' => (int)$existing['user_course_id'],
            ':status' => 'issued',
            ':fullname' => $fullname,
            ':course_title' => $courseTitle,
            ':completedon' => $completedOn,
            ':issuedon' => date('Y-m-d H:i:s'),
            ':file_path' => $generated['file_path'],
            ':preview_image' => $generated['preview_image'],
        );

        $sql = 'UPDATE `' . $this->tables['user_certificates'] . '` SET '
            . '`template_id` = :template_id, '
            . '`user_course_id` = :user_course_id, '
            . '`status` = :status, '
            . '`fullname` = :fullname, '
            . '`course_title` = :course_title, '
            . '`completedon` = :completedon, '
            . '`issuedon` = :issuedon, '
            . '`file_path` = :file_path, '
            . '`preview_image` = :preview_image, '
            . '`updatedon` = NOW() '
            . 'WHERE `course_id` = :course_id AND `user_id` = :user_id';

        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute($params)) {
            return false;
        }

        return $this->getUserCertificate($courseId, $userId);
    }

    /**
     * Every generated file receives a unique URL to prevent browser cache
     * reuse after template coordinates or text are changed.
     */
    protected function generateCertificateImage(array $template, array $data, $force = false)
    {
        $previewWeb = trim((string)$template['template_preview']);
        $previewFs = $this->webPathToFs($previewWeb);
        if ($previewWeb === '' || !is_file($previewFs)) {
            return false;
        }

        $outputDirWeb = $this->normalizeDirWeb(
            trim((string)$template['output_dir']) !== ''
                ? $template['output_dir']
                : ('/assets/training/certificates/course_' . (int)$data['course_id'] . '/')
        );
        $userDirWeb = $outputDirWeb . 'user_' . (int)$data['user_id'] . '/';
        $userDirFs = $this->ensureDirFs($this->webPathToFs($userDirWeb));
        if (!$userDirFs) {
            return false;
        }

        $certificateName = 'certificate-' . date('YmdHis') . '-'
            . substr(sha1(uniqid((string)$data['user_id'], true)), 0, 10) . '.png';
        $certificateFs = rtrim($userDirFs, '/\\') . '/' . $certificateName;
        $certificateWeb = $userDirWeb . $certificateName;

        $extension = strtolower(pathinfo($previewFs, PATHINFO_EXTENSION));
        if ($extension === 'jpg' || $extension === 'jpeg') {
            $image = @imagecreatefromjpeg($previewFs);
        } elseif ($extension === 'webp' && function_exists('imagecreatefromwebp')) {
            $image = @imagecreatefromwebp($previewFs);
        } else {
            $image = @imagecreatefrompng($previewFs);
        }

        if (!$image) {
            return false;
        }

        imagesavealpha($image, true);
        imagealphablending($image, true);

        $fontPath = $this->resolveFontPath();
        $this->drawField($image, $fontPath, trim((string)$data['fullname']), $template, 'fullname');
        $this->drawField($image, $fontPath, trim((string)$data['course_title']), $template, 'course_title');
        $this->drawField($image, $fontPath, trim((string)$data['completed_date']), $template, 'completed_date');

        @imagepng($image, $certificateFs);
        imagedestroy($image);

        if (!is_file($certificateFs)) {
            return false;
        }

        return array(
            'file_path' => $certificateWeb,
            'preview_image' => $certificateWeb,
        );
    }
}

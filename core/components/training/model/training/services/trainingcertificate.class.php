<?php

class TrainingCertificateService
{
    /** @var modX */
    protected $modx;
    /** @var Training */
    protected $training;
    /** @var array */
    protected $tables = array();

    public function __construct(modX $modx, Training $training = null)
    {
        $this->modx = $modx;
        $this->training = $training ?: new Training($modx);
        $prefix = (string)$this->modx->getOption('table_prefix', null, 'modx_');

        $this->tables = array(
            'templates' => $prefix . 'training_certificate_templates',
            'user_certificates' => $prefix . 'training_user_certificates',
            'courses' => $prefix . 'training_courses',
            'user_courses' => $prefix . 'training_user_courses',
            'manager_link' => $prefix . 'training_user_manager_link',
            'users' => trim((string)$this->modx->getTableName('modUser'), '`'),
            'profiles' => trim((string)$this->modx->getTableName('modUserProfile'), '`'),
            'resources' => trim((string)$this->modx->getTableName('modResource'), '`'),
        );
    }

    public function getTemplateTable()
    {
        return $this->tables['templates'];
    }

    public function getUserCertificatesTable()
    {
        return $this->tables['user_certificates'];
    }

    public function normalizeCourseId($courseId)
    {
        $courseId = (int)$courseId;
        if ($courseId <= 0) {
            return 0;
        }

        $stmt = $this->modx->prepare('SELECT `id` FROM `' . $this->tables['courses'] . '` WHERE `id` = :id LIMIT 1');
        if ($stmt && $stmt->execute(array(':id' => $courseId)) && (int)$stmt->fetchColumn() > 0) {
            return $courseId;
        }

        $stmt = $this->modx->prepare('SELECT `id` FROM `' . $this->tables['courses'] . '` WHERE `resource_id` = :resource_id LIMIT 1');
        if ($stmt && $stmt->execute(array(':resource_id' => $courseId))) {
            $resolved = (int)$stmt->fetchColumn();
            if ($resolved > 0) {
                return $resolved;
            }
        }

        return $courseId;
    }

    public function getTemplate($courseId)
    {
        $courseId = $this->normalizeCourseId($courseId);
        if ($courseId <= 0) {
            return null;
        }

        $stmt = $this->modx->prepare('SELECT * FROM `' . $this->tables['templates'] . '` WHERE `course_id` = :course_id LIMIT 1');
        if (!$stmt || !$stmt->execute(array(':course_id' => $courseId))) {
            return null;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function saveTemplate($courseId, array $data)
    {
        $courseId = $this->normalizeCourseId($courseId);
        if ($courseId <= 0) {
            return false;
        }

        $existing = $this->getTemplate($courseId);
        $row = array(
            'course_id' => $courseId,
            'template_pdf' => trim((string)(isset($data['template_pdf']) ? $data['template_pdf'] : '')),
            'template_preview' => trim((string)(isset($data['template_preview']) ? $data['template_preview'] : ($existing ? $existing['template_preview'] : ''))),
            'output_dir' => trim((string)(isset($data['output_dir']) ? $data['output_dir'] : '')),
            'page_no' => max(1, (int)(isset($data['page_no']) ? $data['page_no'] : 1)),
            'fullname_x' => $this->toDecimal(isset($data['fullname_x']) ? $data['fullname_x'] : 0),
            'fullname_y' => $this->toDecimal(isset($data['fullname_y']) ? $data['fullname_y'] : 0),
            'fullname_max_width' => $this->toDecimal(isset($data['fullname_max_width']) ? $data['fullname_max_width'] : 0),
            'fullname_font_size' => $this->toDecimal(isset($data['fullname_font_size']) ? $data['fullname_font_size'] : 28),
            'fullname_color' => $this->normalizeColor(isset($data['fullname_color']) ? $data['fullname_color'] : '#7B4F92'),
            'fullname_align' => $this->normalizeAlign(isset($data['fullname_align']) ? $data['fullname_align'] : 'left'),
            'course_title_x' => $this->toDecimal(isset($data['course_title_x']) ? $data['course_title_x'] : 0),
            'course_title_y' => $this->toDecimal(isset($data['course_title_y']) ? $data['course_title_y'] : 0),
            'course_title_max_width' => $this->toDecimal(isset($data['course_title_max_width']) ? $data['course_title_max_width'] : 0),
            'course_title_font_size' => $this->toDecimal(isset($data['course_title_font_size']) ? $data['course_title_font_size'] : 24),
            'course_title_color' => $this->normalizeColor(isset($data['course_title_color']) ? $data['course_title_color'] : '#7B4F92'),
            'course_title_align' => $this->normalizeAlign(isset($data['course_title_align']) ? $data['course_title_align'] : 'left'),
            'completed_date_x' => $this->toDecimal(isset($data['completed_date_x']) ? $data['completed_date_x'] : 0),
            'completed_date_y' => $this->toDecimal(isset($data['completed_date_y']) ? $data['completed_date_y'] : 0),
            'completed_date_max_width' => $this->toDecimal(isset($data['completed_date_max_width']) ? $data['completed_date_max_width'] : 0),
            'completed_date_font_size' => $this->toDecimal(isset($data['completed_date_font_size']) ? $data['completed_date_font_size'] : 20),
            'completed_date_color' => $this->normalizeColor(isset($data['completed_date_color']) ? $data['completed_date_color'] : '#7B4F92'),
            'completed_date_align' => $this->normalizeAlign(isset($data['completed_date_align']) ? $data['completed_date_align'] : 'left'),
            'date_format' => trim((string)(isset($data['date_format']) ? $data['date_format'] : 'd.m.Y')),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        );

        if ($row['output_dir'] === '') {
            $row['output_dir'] = '/assets/training/certificates/course_' . $courseId . '/';
        }

        if ($existing) {
            $sql = 'UPDATE `' . $this->tables['templates'] . '` SET '
                . '`template_pdf` = :template_pdf, '
                . '`template_preview` = :template_preview, '
                . '`output_dir` = :output_dir, '
                . '`page_no` = :page_no, '
                . '`fullname_x` = :fullname_x, '
                . '`fullname_y` = :fullname_y, '
                . '`fullname_max_width` = :fullname_max_width, '
                . '`fullname_font_size` = :fullname_font_size, '
                . '`fullname_color` = :fullname_color, '
                . '`fullname_align` = :fullname_align, '
                . '`course_title_x` = :course_title_x, '
                . '`course_title_y` = :course_title_y, '
                . '`course_title_max_width` = :course_title_max_width, '
                . '`course_title_font_size` = :course_title_font_size, '
                . '`course_title_color` = :course_title_color, '
                . '`course_title_align` = :course_title_align, '
                . '`completed_date_x` = :completed_date_x, '
                . '`completed_date_y` = :completed_date_y, '
                . '`completed_date_max_width` = :completed_date_max_width, '
                . '`completed_date_font_size` = :completed_date_font_size, '
                . '`completed_date_color` = :completed_date_color, '
                . '`completed_date_align` = :completed_date_align, '
                . '`date_format` = :date_format, '
                . '`is_active` = :is_active, '
                . '`updatedon` = NOW() '
                . 'WHERE `course_id` = :course_id';
        } else {
            $sql = 'INSERT INTO `' . $this->tables['templates'] . '` '
                . '(`course_id`,`template_pdf`,`template_preview`,`output_dir`,`page_no`,`fullname_x`,`fullname_y`,`fullname_max_width`,`fullname_font_size`,`fullname_color`,`fullname_align`,`course_title_x`,`course_title_y`,`course_title_max_width`,`course_title_font_size`,`course_title_color`,`course_title_align`,`completed_date_x`,`completed_date_y`,`completed_date_max_width`,`completed_date_font_size`,`completed_date_color`,`completed_date_align`,`date_format`,`is_active`,`createdon`,`updatedon`) '
                . 'VALUES '
                . '(:course_id,:template_pdf,:template_preview,:output_dir,:page_no,:fullname_x,:fullname_y,:fullname_max_width,:fullname_font_size,:fullname_color,:fullname_align,:course_title_x,:course_title_y,:course_title_max_width,:course_title_font_size,:course_title_color,:course_title_align,:completed_date_x,:completed_date_y,:completed_date_max_width,:completed_date_font_size,:completed_date_color,:completed_date_align,:date_format,:is_active,NOW(),NOW())';
        }

        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute($row)) {
            return false;
        }

        $template = $this->getTemplate($courseId);
        if ($template) {
            $preview = $this->ensureTemplatePreview($template, true);
            if ($preview && $preview !== $template['template_preview']) {
                $upd = $this->modx->prepare('UPDATE `' . $this->tables['templates'] . '` SET `template_preview` = :preview, `updatedon` = NOW() WHERE `course_id` = :course_id');
                if ($upd) {
                    $upd->execute(array(':preview' => $preview, ':course_id' => $courseId));
                }
            }
        }

        return $this->getTemplate($courseId);
    }

    public function ensureTemplatePreview(array $template, $force = false)
    {
        $current = trim((string)$template['template_preview']);
        if (!$force && $current !== '' && is_file($this->webPathToFs($current))) {
            return $current;
        }

        $pdfWeb = trim((string)$template['template_pdf']);
        $pdfFs = $this->webPathToFs($pdfWeb);
        if ($pdfWeb === '' || !is_file($pdfFs)) {
            return $current;
        }

        $pageNo = max(1, (int)$template['page_no']);
        $outputDirWeb = $this->normalizeDirWeb(trim((string)$template['output_dir']) !== '' ? $template['output_dir'] : ('/assets/training/certificates/course_' . (int)$template['course_id'] . '/'));
        $outputDirFs = $this->ensureDirFs($this->webPathToFs($outputDirWeb));
        if (!$outputDirFs) {
            return $current;
        }

        $prefixFs = rtrim($outputDirFs, '/\\') . '/template_preview';
        $pdftoppm = trim((string)$this->modx->getOption('training_pdftoppm_command', null, 'pdftoppm'));
        $cmd = escapeshellcmd($pdftoppm)
            . ' -f ' . (int)$pageNo
            . ' -l ' . (int)$pageNo
            . ' -png '
            . escapeshellarg($pdfFs) . ' '
            . escapeshellarg($prefixFs);

        @exec($cmd, $out, $code);
        $generatedFs = $prefixFs . '-1.png';
        if ($code !== 0 || !is_file($generatedFs)) {
            return $current;
        }

        return $this->fsPathToWeb($generatedFs);
    }

    public function getCourseTitle($courseId)
    {
        $courseId = $this->normalizeCourseId($courseId);
        $stmt = $this->modx->prepare('SELECT COALESCE(NULLIF(TRIM(r.`pagetitle`), ""), CONCAT("Курс #", c.`id`)) FROM `' . $this->tables['courses'] . '` c LEFT JOIN `' . $this->tables['resources'] . '` r ON r.`id` = c.`resource_id` WHERE c.`id` = :id LIMIT 1');
        if (!$stmt || !$stmt->execute(array(':id' => (int)$courseId))) {
            return '';
        }
        return (string)$stmt->fetchColumn();
    }

    public function getUserDisplayName($userId)
    {
        $stmt = $this->modx->prepare('SELECT u.`id`, u.`username`, COALESCE(NULLIF(TRIM(p.`fullname`), ""), NULLIF(TRIM(u.`username`), ""), CONCAT("Пользователь #", u.`id`)) AS `display_name` FROM `' . $this->tables['users'] . '` u LEFT JOIN `' . $this->tables['profiles'] . '` p ON p.`internalKey` = u.`id` WHERE u.`id` = :id LIMIT 1');
        if (!$stmt || !$stmt->execute(array(':id' => (int)$userId))) {
            return '';
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? trim((string)$row['display_name']) : '';
    }

    public function getCompletedUserCourse($courseId, $userId)
    {
        $courseId = $this->normalizeCourseId($courseId);
        $stmt = $this->modx->prepare('SELECT * FROM `' . $this->tables['user_courses'] . '` WHERE `course_id` = :course_id AND `user_id` = :user_id AND `status` = "completed" LIMIT 1');
        if (!$stmt || !$stmt->execute(array(':course_id' => (int)$courseId, ':user_id' => (int)$userId))) {
            return null;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getCompletedUsersForCourse($courseId)
    {
        $courseId = $this->normalizeCourseId($courseId);
        if ($courseId <= 0) {
            return array();
        }

        $stmt = $this->modx->prepare(
            'SELECT uc.* '
            . 'FROM `' . $this->tables['user_courses'] . '` uc '
            . 'WHERE uc.`course_id` = :course_id AND uc.`status` = "completed" '
            . 'ORDER BY uc.`completedon` DESC, uc.`id` DESC'
        );
        if (!$stmt || !$stmt->execute(array(':course_id' => (int)$courseId))) {
            $this->logSqlError($stmt, 'getCompletedUsersForCourse');
            return array();
        }

        $rows = (array)$stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = $this->getUsersInfoMap($this->extractUserIds($rows));

        foreach ($rows as &$row) {
            $userId = (int)(isset($row['user_id']) ? $row['user_id'] : 0);
            $info = isset($users[$userId]) ? $users[$userId] : array();
            $row['username'] = isset($info['username']) ? $info['username'] : '';
            $row['email'] = isset($info['email']) ? $info['email'] : '';
            $row['display_name'] = isset($info['display_name']) && trim((string)$info['display_name']) !== ''
                ? $info['display_name']
                : ($userId > 0 ? ('Пользователь #' . $userId) : '—');
        }
        unset($row);

        return $rows;
    }

    public function getUserCertificate($courseId, $userId)
    {
        $courseId = $this->normalizeCourseId($courseId);
        $stmt = $this->modx->prepare('SELECT * FROM `' . $this->tables['user_certificates'] . '` WHERE `course_id` = :course_id AND `user_id` = :user_id LIMIT 1');
        if (!$stmt || !$stmt->execute(array(':course_id' => (int)$courseId, ':user_id' => (int)$userId))) {
            return null;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function ensureCertificatesForUser($userId, $force = false)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return array();
        }

        $stmt = $this->modx->prepare(
            'SELECT uc.`course_id` '
            . 'FROM `' . $this->tables['user_courses'] . '` uc '
            . 'INNER JOIN `' . $this->tables['templates'] . '` t ON t.`course_id` = uc.`course_id` AND t.`is_active` = 1 '
            . 'WHERE uc.`user_id` = :user_id AND uc.`status` = "completed"'
        );
        if (!$stmt || !$stmt->execute(array(':user_id' => $userId))) {
            return array();
        }

        $results = array();
        foreach ((array)$stmt->fetchAll(PDO::FETCH_COLUMN) as $courseId) {
            $cert = $this->ensureUserCertificate((int)$courseId, $userId, $force);
            if ($cert) {
                $results[] = $cert;
            }
        }
        return $results;
    }

    public function ensureUserCertificate($courseId, $userId, $force = false)
    {
        $courseId = $this->normalizeCourseId($courseId);
        $userId = (int)$userId;
        if ($courseId <= 0 || $userId <= 0) {
            return false;
        }

        $template = $this->getTemplate($courseId);
        if (!$template || (int)$template['is_active'] !== 1) {
            return false;
        }

        $userCourse = $this->getCompletedUserCourse($courseId, $userId);
        if (!$userCourse) {
            return false;
        }

        $existing = $this->getUserCertificate($courseId, $userId);
        if (!$force && $existing && trim((string)$existing['file_path']) !== '' && is_file($this->webPathToFs($existing['file_path']))) {
            return $existing;
        }

        $preview = $this->ensureTemplatePreview($template, false);
        if ($preview === '') {
            return false;
        }

        $fullname = $this->getUserDisplayName($userId);
        $courseTitle = $this->getCourseTitle($courseId);
        $completedOn = trim((string)$userCourse['completedon']);
        if ($completedOn === '' || $completedOn === '0000-00-00 00:00:00') {
            $completedOn = trim((string)$userCourse['updatedon']);
        }
        $issuedOn = date('Y-m-d H:i:s');
        $dateText = $this->formatDate($completedOn, trim((string)$template['date_format']) !== '' ? $template['date_format'] : 'd.m.Y');

        $generated = $this->generateCertificateImage($template, array(
            'course_id' => $courseId,
            'user_id' => $userId,
            'fullname' => $fullname,
            'course_title' => $courseTitle,
            'completed_date' => $dateText,
            'completed_raw' => $completedOn,
        ), $force);

        if (!$generated) {
            return false;
        }

        $row = array(
            ':course_id' => $courseId,
            ':template_id' => (int)$template['id'],
            ':user_id' => $userId,
            ':user_course_id' => (int)$userCourse['id'],
            ':status' => 'issued',
            ':fullname' => $fullname,
            ':course_title' => $courseTitle,
            ':completedon' => $completedOn,
            ':issuedon' => $issuedOn,
            ':file_path' => $generated['file_path'],
            ':preview_image' => $generated['preview_image'],
        );

        if ($existing) {
            $sql = 'UPDATE `' . $this->tables['user_certificates'] . '` SET '
                . '`template_id` = :template_id, `user_course_id` = :user_course_id, `status` = :status, `fullname` = :fullname, `course_title` = :course_title, `completedon` = :completedon, `issuedon` = :issuedon, `file_path` = :file_path, `preview_image` = :preview_image, `updatedon` = NOW() '
                . 'WHERE `course_id` = :course_id AND `user_id` = :user_id';
        } else {
            $sql = 'INSERT INTO `' . $this->tables['user_certificates'] . '` '
                . '(`course_id`,`template_id`,`user_id`,`user_course_id`,`status`,`fullname`,`course_title`,`completedon`,`issuedon`,`file_path`,`preview_image`,`createdon`,`updatedon`) '
                . 'VALUES (:course_id,:template_id,:user_id,:user_course_id,:status,:fullname,:course_title,:completedon,:issuedon,:file_path,:preview_image,NOW(),NOW())';
        }

        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute($row)) {
            return false;
        }

        return $this->getUserCertificate($courseId, $userId);
    }

    public function generateAllForCourse($courseId, $force = false)
    {
        $courseId = $this->normalizeCourseId($courseId);
        $results = array();
        foreach ($this->getCompletedUsersForCourse($courseId) as $row) {
            $cert = $this->ensureUserCertificate((int)$courseId, (int)$row['user_id'], $force);
            if ($cert) {
                $results[] = $cert;
            }
        }
        return $results;
    }

    public function generateForUsers($courseId, array $userIds, $force = false)
    {
        $courseId = $this->normalizeCourseId($courseId);
        if ($courseId <= 0) {
            return array();
        }

        $cleanIds = array();
        foreach ($userIds as $id) {
            $id = (int)$id;
            if ($id > 0 && !in_array($id, $cleanIds, true)) {
                $cleanIds[] = $id;
            }
        }

        if (empty($cleanIds)) {
            return array();
        }

        $results = array();
        foreach ($cleanIds as $userId) {
            $cert = $this->ensureUserCertificate($courseId, $userId, $force);
            if ($cert) {
                $results[] = $cert;
            }
        }

        return $results;
    }

    public function listIssuedCertificatesForCourse($courseId)
    {
        $courseId = $this->normalizeCourseId($courseId);
        if ($courseId <= 0) {
            return array();
        }

        $map = array();

        $stmt = $this->modx->prepare(
            'SELECT uc.`id` AS `user_course_id`, uc.`course_id`, uc.`user_id`, uc.`completedon`, uc.`status` AS `course_status` '
            . 'FROM `' . $this->tables['user_courses'] . '` uc '
            . 'WHERE uc.`course_id` = :course_id AND uc.`status` = "completed" '
            . 'ORDER BY uc.`completedon` DESC, uc.`id` DESC'
        );
        if ($stmt && $stmt->execute(array(':course_id' => $courseId))) {
            foreach ((array)$stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $userId = (int)(isset($row['user_id']) ? $row['user_id'] : 0);
                if ($userId <= 0) {
                    continue;
                }
                $map[$userId] = array(
                    'user_course_id' => (int)(isset($row['user_course_id']) ? $row['user_course_id'] : 0),
                    'user_id' => $userId,
                    'completedon' => (string)(isset($row['completedon']) ? $row['completedon'] : ''),
                    'course_status' => (string)(isset($row['course_status']) ? $row['course_status'] : 'completed'),
                    'certificate_id' => 0,
                    'issuedon' => '',
                    'file_path' => '',
                    'preview_image' => '',
                    'certificate_status' => '',
                );
            }
        } else {
            $this->logSqlError($stmt, 'listIssuedCertificatesForCourse:user_courses');
        }

        $stmt = $this->modx->prepare(
            'SELECT cert.`id` AS `certificate_id`, cert.`user_id`, cert.`user_course_id`, cert.`completedon`, cert.`issuedon`, cert.`file_path`, cert.`preview_image`, cert.`status` AS `certificate_status` '
            . 'FROM `' . $this->tables['user_certificates'] . '` cert '
            . 'WHERE cert.`course_id` = :course_id '
            . 'ORDER BY cert.`issuedon` DESC, cert.`id` DESC'
        );
        if ($stmt && $stmt->execute(array(':course_id' => $courseId))) {
            foreach ((array)$stmt->fetchAll(PDO::FETCH_ASSOC) as $cert) {
                $userId = (int)(isset($cert['user_id']) ? $cert['user_id'] : 0);
                if ($userId <= 0) {
                    continue;
                }
                if (!isset($map[$userId])) {
                    $map[$userId] = array(
                        'user_course_id' => (int)(isset($cert['user_course_id']) ? $cert['user_course_id'] : 0),
                        'user_id' => $userId,
                        'completedon' => (string)(isset($cert['completedon']) ? $cert['completedon'] : ''),
                        'course_status' => 'completed',
                        'certificate_id' => 0,
                        'issuedon' => '',
                        'file_path' => '',
                        'preview_image' => '',
                        'certificate_status' => '',
                    );
                }
                $map[$userId]['certificate_id'] = (int)(isset($cert['certificate_id']) ? $cert['certificate_id'] : 0);
                $map[$userId]['issuedon'] = (string)(isset($cert['issuedon']) ? $cert['issuedon'] : '');
                $map[$userId]['file_path'] = (string)(isset($cert['file_path']) ? $cert['file_path'] : '');
                $map[$userId]['preview_image'] = (string)(isset($cert['preview_image']) ? $cert['preview_image'] : '');
                $map[$userId]['certificate_status'] = (string)(isset($cert['certificate_status']) ? $cert['certificate_status'] : '');
                if (trim((string)$map[$userId]['completedon']) === '') {
                    $map[$userId]['completedon'] = (string)(isset($cert['completedon']) ? $cert['completedon'] : '');
                }
                if ((int)$map[$userId]['user_course_id'] <= 0) {
                    $map[$userId]['user_course_id'] = (int)(isset($cert['user_course_id']) ? $cert['user_course_id'] : 0);
                }
            }
        } else {
            $this->logSqlError($stmt, 'listIssuedCertificatesForCourse:certificates');
        }

        if (empty($map)) {
            return array();
        }

        $users = $this->getUsersInfoMap(array_keys($map));
        foreach ($map as $userId => &$row) {
            $info = isset($users[$userId]) ? $users[$userId] : array();
            $row['username'] = isset($info['username']) ? $info['username'] : '';
            $row['email'] = isset($info['email']) ? $info['email'] : '';
            $row['display_name'] = isset($info['display_name']) && trim((string)$info['display_name']) !== ''
                ? $info['display_name']
                : ('Пользователь #' . (int)$userId);
        }
        unset($row);

        $rows = array_values($map);
        usort($rows, array($this, 'sortCertificateRows'));

        return $rows;
    }

    protected function sortCertificateRows(array $a, array $b)
    {
        $ad = strtotime((string)(isset($a['completedon']) ? $a['completedon'] : '')) ?: 0;
        $bd = strtotime((string)(isset($b['completedon']) ? $b['completedon'] : '')) ?: 0;
        if ($ad === $bd) {
            return ((int)$b['user_id']) - ((int)$a['user_id']);
        }
        return $bd - $ad;
    }

    protected function extractUserIds(array $rows)
    {
        $ids = array();
        foreach ($rows as $row) {
            $id = (int)(isset($row['user_id']) ? $row['user_id'] : 0);
            if ($id > 0 && !in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    protected function getUsersInfoMap(array $ids)
    {
        $clean = array();
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id > 0 && !in_array($id, $clean, true)) {
                $clean[] = $id;
            }
        }
        if (empty($clean)) {
            return array();
        }

        $result = array();
        foreach ($clean as $id) {
            $result[$id] = array(
                'id' => $id,
                'username' => '',
                'email' => '',
                'display_name' => 'Пользователь #' . $id,
            );
        }

        $placeholders = implode(',', array_fill(0, count($clean), '?'));
        $sql = 'SELECT u.`id`, u.`username`, p.`email`, p.`fullname`, p.`surname`, p.`patronymic` '
            . 'FROM `' . $this->tables['users'] . '` u '
            . 'LEFT JOIN `' . $this->tables['profiles'] . '` p ON p.`internalKey` = u.`id` '
            . 'WHERE u.`id` IN (' . $placeholders . ')';
        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute($clean)) {
            $this->logSqlError($stmt, 'getUsersInfoMap');
            return $result;
        }

        foreach ((array)$stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $id = (int)(isset($row['id']) ? $row['id'] : 0);
            if ($id <= 0) {
                continue;
            }
            $fullname = trim((string)(isset($row['fullname']) ? $row['fullname'] : ''));
            if ($fullname === '') {
                $parts = array();
                foreach (array('surname', 'patronymic') as $key) {
                    $part = trim((string)(isset($row[$key]) ? $row[$key] : ''));
                    if ($part !== '') {
                        $parts[] = $part;
                    }
                }
                $fullname = trim(implode(' ', $parts));
            }
            if ($fullname === '') {
                $fullname = trim((string)(isset($row['username']) ? $row['username'] : ''));
            }
            if ($fullname === '') {
                $fullname = 'Пользователь #' . $id;
            }
            $result[$id] = array(
                'id' => $id,
                'username' => (string)(isset($row['username']) ? $row['username'] : ''),
                'email' => (string)(isset($row['email']) ? $row['email'] : ''),
                'display_name' => $fullname,
            );
        }

        return $result;
    }

    protected function logSqlError($stmt, $context)
    {
        if ($stmt && method_exists($stmt, 'errorInfo')) {
            $info = $stmt->errorInfo();
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[TrainingCertificateService] ' . $context . ': ' . print_r($info, true));
        }
    }

    public function getManageableUsers($actorUserId, $includeSelf = true)
    {
        $actorUserId = (int)$actorUserId;
        if ($actorUserId <= 0) {
            return array();
        }

        $ids = array();
        if ($includeSelf) {
            $ids[] = $actorUserId;
        }
        $stmt = $this->modx->prepare('SELECT DISTINCT `employee_user_id` FROM `' . $this->tables['manager_link'] . '` WHERE `manager_user_id` = :user_id AND `is_active` = 1 ORDER BY `employee_user_id` ASC');
        if ($stmt && $stmt->execute(array(':user_id' => $actorUserId))) {
            foreach ((array)$stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
                $id = (int)$id;
                if ($id > 0 && !in_array($id, $ids, true)) {
                    $ids[] = $id;
                }
            }
        }

        if (empty($ids)) {
            return array();
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = 'SELECT u.`id`, u.`username`, p.`email`, COALESCE(NULLIF(TRIM(p.`fullname`), ""), NULLIF(TRIM(u.`username`), ""), CONCAT("Пользователь #", u.`id`)) AS `display_name` '
            . 'FROM `' . $this->tables['users'] . '` u '
            . 'LEFT JOIN `' . $this->tables['profiles'] . '` p ON p.`internalKey` = u.`id` '
            . 'WHERE u.`id` IN (' . $placeholders . ')';
        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute($ids)) {
            return array();
        }

        $rows = (array)$stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = array();
        foreach ($rows as $row) {
            $map[(int)$row['id']] = $row;
        }

        $result = array();
        foreach ($ids as $id) {
            if (isset($map[$id])) {
                $result[] = $map[$id];
            }
        }
        return $result;
    }

    public function canManageUser($actorUserId, $targetUserId)
    {
        $actorUserId = (int)$actorUserId;
        $targetUserId = (int)$targetUserId;
        if ($actorUserId <= 0 || $targetUserId <= 0) {
            return false;
        }
        if ($actorUserId === $targetUserId) {
            return true;
        }
        $stmt = $this->modx->prepare('SELECT COUNT(*) FROM `' . $this->tables['manager_link'] . '` WHERE `manager_user_id` = :actor AND `employee_user_id` = :target AND `is_active` = 1');
        if (!$stmt || !$stmt->execute(array(':actor' => $actorUserId, ':target' => $targetUserId))) {
            return false;
        }
        return ((int)$stmt->fetchColumn()) > 0;
    }

    public function listUserCertificates($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return array();
        }

        $sql = 'SELECT cert.*, t.`template_pdf`, t.`template_preview`, r.`pagetitle` AS `course_pagetitle` '
            . 'FROM `' . $this->tables['user_certificates'] . '` cert '
            . 'LEFT JOIN `' . $this->tables['templates'] . '` t ON t.`id` = cert.`template_id` '
            . 'LEFT JOIN `' . $this->tables['courses'] . '` c ON c.`id` = cert.`course_id` '
            . 'LEFT JOIN `' . $this->tables['resources'] . '` r ON r.`id` = c.`resource_id` '
            . 'WHERE cert.`user_id` = :user_id '
            . 'ORDER BY cert.`issuedon` DESC, cert.`id` DESC';
        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute(array(':user_id' => $userId))) {
            return array();
        }
        return (array)$stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function generateCertificateImage(array $template, array $data, $force = false)
    {
        $previewWeb = trim((string)$template['template_preview']);
        $previewFs = $this->webPathToFs($previewWeb);
        if ($previewWeb === '' || !is_file($previewFs)) {
            return false;
        }

        $outputDirWeb = $this->normalizeDirWeb(trim((string)$template['output_dir']) !== '' ? $template['output_dir'] : ('/assets/training/certificates/course_' . (int)$data['course_id'] . '/'));
        $userDirWeb = $outputDirWeb . 'user_' . (int)$data['user_id'] . '/';
        $userDirFs = $this->ensureDirFs($this->webPathToFs($userDirWeb));
        if (!$userDirFs) {
            return false;
        }

        $certificateFs = rtrim($userDirFs, '/\\') . '/certificate.png';
        $certificateWeb = $userDirWeb . 'certificate.png';

        if (!$force && is_file($certificateFs)) {
            return array('file_path' => $certificateWeb, 'preview_image' => $certificateWeb);
        }

        $ext = strtolower(pathinfo($previewFs, PATHINFO_EXTENSION));
        if ($ext === 'jpg' || $ext === 'jpeg') {
            $image = @imagecreatefromjpeg($previewFs);
        } elseif ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
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

        return array('file_path' => $certificateWeb, 'preview_image' => $certificateWeb);
    }

    protected function drawField($image, $fontPath, $text, array $template, $prefix)
    {
        $text = trim((string)$text);
        if ($text === '') {
            return;
        }

        $x = (float)$template[$prefix . '_x'];
        $y = (float)$template[$prefix . '_y'];

        // На текущем шаблоне дата визуально была выше и левее нужного места.
        // Делаем мягкий сдвиг только для даты; при необходимости его можно переопределить системными настройками.
        if ($prefix === 'completed_date') {
            $x += (float)$this->modx->getOption('training_certificate_completed_date_offset_x', null, 40);
            $y += (float)$this->modx->getOption('training_certificate_completed_date_offset_y', null, 35);
        }

        $maxWidth = (float)$template[$prefix . '_max_width'];
        $fontSize = max(8, (float)$template[$prefix . '_font_size']);
        $color = $this->allocateColor($image, (string)$template[$prefix . '_color']);
        $align = $this->normalizeAlign((string)$template[$prefix . '_align']);

        if ($fontPath && function_exists('imagettfbbox') && function_exists('imagettftext')) {
            $font = $fontSize;
            if ($maxWidth > 0) {
                while ($font > 8) {
                    $box = imagettfbbox($font, 0, $fontPath, $text);
                    $width = abs($box[2] - $box[0]);
                    if ($width <= $maxWidth) {
                        break;
                    }
                    $font -= 1;
                }
            }
            $box = imagettfbbox($font, 0, $fontPath, $text);
            $width = abs($box[2] - $box[0]);
            if ($align === 'center' && $maxWidth > 0) {
                $x += max(0, ($maxWidth - $width) / 2);
            } elseif ($align === 'right' && $maxWidth > 0) {
                $x += max(0, ($maxWidth - $width));
            }
            imagettftext($image, $font, 0, (int)round($x), (int)round($y), $color, $fontPath, $text);
            return;
        }

        $font = 5;
        $width = imagefontwidth($font) * strlen($text);
        if ($align === 'center' && $maxWidth > 0) {
            $x += max(0, ($maxWidth - $width) / 2);
        } elseif ($align === 'right' && $maxWidth > 0) {
            $x += max(0, ($maxWidth - $width));
        }
        imagestring($image, $font, (int)round($x), max(0, (int)round($y) - 15), $text, $color);
    }

    protected function allocateColor($image, $color)
    {
        $color = $this->normalizeColor($color);
        $hex = ltrim($color, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return imagecolorallocate($image, $r, $g, $b);
    }

    protected function resolveFontPath()
    {
        $candidates = array(
            trim((string)$this->modx->getOption('training_certificate_font_path', null, '')),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation2/LiberationSans-Regular.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSans.ttf',
        );
        foreach ($candidates as $path) {
            if ($path !== '' && is_file($path)) {
                return $path;
            }
        }
        return '';
    }

    protected function toDecimal($value)
    {
        return number_format((float)$value, 2, '.', '');
    }

    protected function normalizeColor($value)
    {
        $value = trim((string)$value);
        if (!preg_match('/^#[0-9a-fA-F]{3,6}$/', $value)) {
            return '#7B4F92';
        }
        return strtoupper($value);
    }

    protected function normalizeAlign($value)
    {
        $value = strtolower(trim((string)$value));
        return in_array($value, array('left', 'center', 'right'), true) ? $value : 'left';
    }

    protected function formatDate($dateTime, $format)
    {
        $timestamp = strtotime((string)$dateTime);
        if (!$timestamp) {
            $timestamp = time();
        }
        return date($format, $timestamp);
    }

    protected function normalizeDirWeb($path)
    {
        $path = str_replace('\\', '/', trim((string)$path));
        if ($path === '') {
            return '/assets/training/certificates/';
        }
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        return rtrim($path, '/') . '/';
    }

    protected function webPathToFs($web)
    {
        $web = str_replace('\\', '/', trim((string)$web));
        if ($web === '') {
            return '';
        }
        $basePath = rtrim((string)$this->modx->getOption('base_path'), '/\\');
        return $basePath . '/' . ltrim($web, '/');
    }

    protected function fsPathToWeb($fs)
    {
        $fs = str_replace('\\', '/', trim((string)$fs));
        $basePath = str_replace('\\', '/', rtrim((string)$this->modx->getOption('base_path'), '/\\'));
        if (strpos($fs, $basePath) === 0) {
            return '/' . ltrim(substr($fs, strlen($basePath)), '/');
        }
        return $fs;
    }

    protected function ensureDirFs($fs)
    {
        $fs = str_replace('\\', '/', trim((string)$fs));
        if ($fs === '') {
            return '';
        }
        if (!is_dir($fs)) {
            @mkdir($fs, 0775, true);
        }
        return is_dir($fs) ? $fs : '';
    }
}

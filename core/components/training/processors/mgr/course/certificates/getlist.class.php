<?php

$corePath = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/';
require_once $corePath . 'model/training/training.class.php';
require_once $corePath . 'model/training/services/trainingcertificate.class.php';

class TrainingCourseCertificatesGetListProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $service = new TrainingCertificateService($this->modx, new Training($this->modx));
        $courseId = $service->normalizeCourseId((int)$this->getProperty('course_id', 0));

        if ($courseId <= 0) {
            return $this->output(array(), 0);
        }

        $start = max(0, (int)$this->getProperty('start', 0));
        $limit = max(0, (int)$this->getProperty('limit', 20));

        $rows = $service->listIssuedCertificatesForCourse($courseId);
        $total = count($rows);

        if ($limit > 0) {
            $rows = array_slice($rows, $start, $limit);
        }

        $results = array();
        foreach ((array)$rows as $row) {
            $userId = (int)(isset($row['user_id']) ? $row['user_id'] : 0);
            $certificateId = (int)(isset($row['certificate_id']) ? $row['certificate_id'] : 0);
            $filePath = trim((string)(isset($row['file_path']) ? $row['file_path'] : ''));
            $displayName = trim((string)(isset($row['display_name']) ? $row['display_name'] : ''));

            if ($displayName === '') {
                $displayName = $userId > 0 ? ('Пользователь #' . $userId) : '—';
            }

            $results[] = array(
                'id' => $userId,
                'user_id' => $userId,
                'user_course_id' => (int)(isset($row['user_course_id']) ? $row['user_course_id'] : 0),
                'display_name' => $displayName,
                'email' => (string)(isset($row['email']) ? $row['email'] : ''),
                'completedon' => (string)(isset($row['completedon']) ? $row['completedon'] : ''),
                'completedon_formatted' => $this->formatDate(isset($row['completedon']) ? $row['completedon'] : ''),
                'certificate_id' => $certificateId,
                'certificate_generated' => $certificateId > 0 ? 1 : 0,
                'certificate_generated_label' => $certificateId > 0 ? 'Да' : 'Нет',
                'certificate_status' => (string)(isset($row['certificate_status']) ? $row['certificate_status'] : ''),
                'issuedon' => (string)(isset($row['issuedon']) ? $row['issuedon'] : ''),
                'issuedon_formatted' => $this->formatDate(isset($row['issuedon']) ? $row['issuedon'] : ''),
                'file_path' => $filePath,
                'preview_image' => (string)(isset($row['preview_image']) ? $row['preview_image'] : ''),
            );
        }

        return $this->output($results, $total);
    }

    protected function formatDate($value)
    {
        $value = trim((string)$value);
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return '—';
        }
        $ts = strtotime($value);
        return $ts ? date('d.m.Y H:i', $ts) : $value;
    }

    protected function output(array $rows, $total)
    {
        $payload = array(
            'success' => true,
            'total' => (int)$total,
            'results' => array_values($rows),
            'object' => array(
                'total' => (int)$total,
                'results' => array_values($rows),
            ),
        );

        return $this->modx->toJSON($payload);
    }
}
return 'TrainingCourseCertificatesGetListProcessor';

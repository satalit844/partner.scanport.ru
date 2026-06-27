<?php
$corePath = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/';
require_once $corePath . 'model/training/training.class.php';
require_once $corePath . 'model/training/services/trainingcertificate.class.php';

class TrainingCourseCertificateReissueProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $courseId = (int)$this->getProperty('course_id', 0);
        if ($courseId <= 0) {
            return $this->failure('Не указан курс');
        }

        $userIdsRaw = trim((string)$this->getProperty('user_ids', ''));
        $userIds = array();

        foreach (preg_split('/[^0-9]+/', $userIdsRaw) as $id) {
            $id = (int)$id;
            if ($id > 0 && !in_array($id, $userIds, true)) {
                $userIds[] = $id;
            }
        }

        if (empty($userIds)) {
            return $this->failure('Выберите пользователей');
        }

        $service = new TrainingCertificateService($this->modx, new Training($this->modx));
        $certificates = $service->reissueForUsers($courseId, $userIds);

        return $this->success('', array(
            'reissued_count' => count($certificates),
            'total_selected' => count($userIds),
            'skipped_count' => count($userIds) - count($certificates),
            'mode' => 'selected_reissue',
        ));
    }
}

return 'TrainingCourseCertificateReissueProcessor';
<?php

$corePath = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/';
require_once $corePath . 'model/training/training.class.php';
require_once $corePath . 'model/training/services/trainingcertificate.class.php';

class TrainingCourseCertificateGenerateProcessor extends modProcessor
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

        $force = (int)$this->getProperty('force', 0) === 1;
        $userIdsRaw = trim((string)$this->getProperty('user_ids', ''));
        $userIds = array();

        if ($userIdsRaw !== '') {
            foreach (preg_split('/[^0-9]+/', $userIdsRaw) as $id) {
                $id = (int)$id;
                if ($id > 0 && !in_array($id, $userIds, true)) {
                    $userIds[] = $id;
                }
            }
        }

        $service = new TrainingCertificateService($this->modx, new Training($this->modx));

        if (!empty($userIds)) {
            $certificates = $service->generateForUsers($courseId, $userIds, $force);
            return $this->success('', array(
                'generated_count' => count($certificates),
                'total_selected' => count($userIds),
                'mode' => 'selected',
            ));
        }

        $users = $service->getCompletedUsersForCourse($courseId);
        $certificates = $service->generateAllForCourse($courseId, $force);

        return $this->success('', array(
            'generated_count' => count($certificates),
            'total_completed' => count($users),
            'mode' => 'all',
        ));
    }
}
return 'TrainingCourseCertificateGenerateProcessor';

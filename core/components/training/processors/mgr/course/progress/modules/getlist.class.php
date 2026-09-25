<?php

require_once dirname(__DIR__) . '/_helpers.php';

class TrainingCourseProgressModulesGetListProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $courseId = trainingProgressCmpCourseId($this);
        $userId = (int)$this->getProperty('user_id', 0);
        $excludeCompleted = (int)$this->getProperty('exclude_completed', 0) === 1;

        if ($courseId <= 0) {
            return trainingProgressCmpListResponse($this, array(), 0);
        }

        try {
            $service = trainingProgressCmpGetService($this->modx);

            if ($userId > 0) {
                $details = $service->getUserProgressDetails($courseId, $userId);
                $rows = isset($details['modules']) ? (array)$details['modules'] : array();
            } else {
                $rows = $service->getModules($courseId);
            }

            $rows = array_values(array_filter($rows, function ($row) use ($excludeCompleted) {
                if ((int)$row['is_active'] !== 1) {
                    return false;
                }

                if ($excludeCompleted && !empty($row['completed'])) {
                    return false;
                }

                return true;
            }));

            return trainingProgressCmpListResponse($this, $rows, count($rows));
        } catch (Throwable $e) {
            return $this->failure($e->getMessage());
        }
    }
}

return 'TrainingCourseProgressModulesGetListProcessor';

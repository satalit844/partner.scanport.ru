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
        if ($courseId <= 0) {
            return trainingProgressCmpListResponse($this, array(), 0);
        }

        try {
            $rows = trainingProgressCmpGetService($this->modx)->getModules($courseId);
            $rows = array_values(array_filter($rows, function ($row) {
                return (int)$row['is_active'] === 1;
            }));

            return trainingProgressCmpListResponse($this, $rows, count($rows));
        } catch (Throwable $e) {
            return $this->failure($e->getMessage());
        }
    }
}

return 'TrainingCourseProgressModulesGetListProcessor';

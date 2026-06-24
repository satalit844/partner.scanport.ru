<?php

require_once __DIR__ . '/_helpers.php';

class TrainingCourseProgressSummaryProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $courseId = trainingProgressCmpCourseId($this);
        $userId = (int)$this->getProperty('user_id', 0);
        $detail = (int)$this->getProperty('detail', 0) === 1;

        if ($courseId <= 0 || $userId <= 0) {
            return $this->failure('Выберите пользователя');
        }

        try {
            $service = trainingProgressCmpGetService($this->modx);
            $summary = $detail
                ? $service->getUserProgressDetails($courseId, $userId)
                : $service->getUserSummary($courseId, $userId);

            return $this->success('', $summary);
        } catch (Throwable $e) {
            return $this->failure($e->getMessage());
        }
    }
}

return 'TrainingCourseProgressSummaryProcessor';

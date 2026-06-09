<?php

require_once dirname(__FILE__) . '/_helpers.php';

class TrainingCourseAccessSyncUsersProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $courseId = (int)$this->getProperty('course_id');
        if ($courseId <= 0) {
            return $this->failure('Не указан курс');
        }

        $service = TrainingCourseAccessHelper::getProgressService($this->modx);
        $result = $service->syncUserCourses($courseId);

        return $this->success('Пользователи курса синхронизированы', $result);
    }
}

return 'TrainingCourseAccessSyncUsersProcessor';

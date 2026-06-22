<?php

require_once __DIR__ . '/_helpers.php';

class TrainingCourseProgressPlanProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $courseId = trainingProgressCmpCourseId($this);
        $userId = (int)$this->getProperty('user_id', 0);
        $moduleId = (int)$this->getProperty('module_id', 0);
        $lessonId = (int)$this->getProperty('lesson_id', 0);
        $mode = $this->getProperty('mode', 'lesson') === 'module' ? 'module' : 'lesson';

        if ($courseId <= 0 || $userId <= 0 || $moduleId <= 0) {
            return $this->failure('Выберите пользователя и модуль');
        }

        try {
            $service = trainingProgressCmpGetService($this->modx);
            $plan = $service->buildPlan($courseId, $userId, $mode, $moduleId, $lessonId);

            trainingProgressCmpLog($this->modx, 'plan', array(
                'course_id' => $courseId,
                'user_id' => $userId,
                'mode' => $mode,
                'module_id' => $moduleId,
                'lesson_id' => $lessonId,
                'complete_module_ids' => $plan['complete_module_ids'],
                'complete_lesson_ids' => $plan['complete_lesson_ids'],
            ));

            return $this->success('', array(
                'plan_html' => $service->getPlanHtml($plan),
                'mode' => $mode,
                'complete_modules' => count($plan['complete_module_ids']),
                'complete_lessons' => count($plan['complete_lesson_ids']),
                'completed_videos' => (int)$plan['completed_videos_count'],
                'activities_count' => count($plan['required_activities']),
            ));
        } catch (Throwable $e) {
            return $this->failure($e->getMessage());
        }
    }
}

return 'TrainingCourseProgressPlanProcessor';

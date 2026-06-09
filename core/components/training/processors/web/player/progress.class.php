<?php

require_once dirname(dirname(__FILE__)) . '/_helpers.php';

class TrainingWebPlayerProgressProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        if ($failure = TrainingWebHelper::requireAuth($this)) {
            return $failure;
        }

        $service = TrainingWebHelper::getProgressService($this->modx);
        $userId = (int)$this->modx->user->get('id');
        $moduleResourceId = (int)$this->getProperty('module', 0);
        $lessonId = (int)$this->getProperty('lesson', 0);
        $lessonVideoId = (int)$this->getProperty('video', 0);
        $currentTime = max(0, (int)$this->getProperty('current_time', 0));
        $durationSeconds = max(0, (int)$this->getProperty('duration_seconds', 0));
        $completed = (int)$this->getProperty('completed', 0) === 1;

        if ($moduleResourceId <= 0 || $lessonId <= 0) {
            return $this->failure('Не указан урок', array('code' => 400));
        }

        $resolved = $service->resolvePlayerContext($moduleResourceId, $lessonId, $userId);
        if (empty($resolved['success'])) {
            return $this->failure(isset($resolved['message']) ? $resolved['message'] : 'Недоступный урок', $resolved);
        }

        if ((int)$resolved['resolved_module_resource_id'] !== $moduleResourceId || (int)$resolved['resolved_lesson_id'] !== $lessonId) {
            return $this->failure('Урок недоступен для сохранения прогресса', array('code' => 403));
        }

        /** @var TrainingModule $module */
        $module = $resolved['module'];
        $courseId = (int)$resolved['course_id'];
        $moduleId = (int)$module->get('id');

        $videoProgress = null;
        if ($lessonVideoId > 0 && TrainingWebHelper::hasUserVideoProgressTable($this->modx)) {
            $videoProgress = TrainingWebHelper::saveUserVideoProgress($this->modx, $service, $courseId, $moduleId, $lessonId, $lessonVideoId, $userId, array(
                'current_time' => $currentTime,
                'max_time' => $currentTime,
                'duration_seconds' => $durationSeconds,
                'completed' => $completed ? 1 : 0,
            ));
        }

        if ($videoProgress) {
            $lessonProgress = TrainingWebHelper::syncLegacyLessonProgressFromVideos($this->modx, $service, $courseId, $moduleId, $lessonId, $userId);
        } else {
            $lessonProgress = $service->saveLessonProgress($courseId, $moduleId, $lessonId, $userId, array(
                'current_time' => $currentTime,
                'max_time' => $currentTime,
                'duration_seconds' => $durationSeconds,
                'completed' => $completed ? 1 : 0,
            ));
        }

        if (!$lessonProgress) {
            return $this->failure('Не удалось сохранить прогресс', array('code' => 500));
        }

        $moduleProgress = $service->recalculateModuleProgressFromLessons($courseId, $moduleId, $userId);
        $userCourse = $service->recalculateUserCourse($courseId, $userId);
        $nextLessonId = $service->getNextLessonId($moduleId, $lessonId, true);
        $nextAccessible = $nextLessonId > 0 ? $service->canAccessLesson($courseId, $moduleId, $nextLessonId, $userId) : false;
        $nextVideoId = $nextLessonId > 0 ? TrainingWebHelper::getPreferredLessonVideoId($this->modx, $courseId, $nextLessonId, $userId, 0) : 0;

        return $this->success('', array(
            'video' => array(
                'id' => $lessonVideoId,
                'progress_percent' => $videoProgress ? round((float)$videoProgress['progress_percent']) : 0,
                'completed' => $videoProgress ? (!empty($videoProgress['completed']) ? 1 : 0) : 0,
                'current_time' => $videoProgress ? (int)$videoProgress['current_time'] : $currentTime,
                'max_time' => $videoProgress ? (int)$videoProgress['max_time'] : $currentTime,
            ),
            'lesson' => array(
                'id' => $lessonId,
                'progress_percent' => round((float)$lessonProgress->get('progress_percent')),
                'completed' => (int)$lessonProgress->get('completed') === 1 ? 1 : 0,
                'current_time' => (int)$lessonProgress->get('current_time'),
                'max_time' => (int)$lessonProgress->get('max_time'),
            ),
            'module' => array(
                'id' => $moduleId,
                'progress_percent' => $moduleProgress ? round((float)$moduleProgress->get('progress_percent')) : 0,
                'completed' => $moduleProgress ? ((int)$moduleProgress->get('completed') === 1 ? 1 : 0) : 0,
            ),
            'course' => array(
                'progress_percent' => $userCourse ? round((float)$userCourse->get('progress_percent')) : 0,
                'completed_modules' => $userCourse ? (int)$userCourse->get('completed_modules') : 0,
                'total_modules' => $userCourse ? (int)$userCourse->get('total_modules') : 0,
                'status' => $userCourse ? (string)$userCourse->get('status') : 'assigned',
            ),
            'nav' => array(
                'next_lesson_id' => $nextLessonId,
                'next_accessible' => $nextAccessible ? 1 : 0,
                'next_url' => $nextAccessible ? TrainingWebHelper::makePlayerUrl($this->modx, $moduleResourceId, $nextLessonId, $nextVideoId) : '',
            ),
        ));
    }
}

return 'TrainingWebPlayerProgressProcessor';

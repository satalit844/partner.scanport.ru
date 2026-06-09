<?php

require_once dirname(dirname(__FILE__)) . '/_helpers.php';

class TrainingWebCourseUnassignProcessor extends modProcessor
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

        $actorUserId = (int)$this->modx->user->get('id');
        $courseId = TrainingWebHelper::resolveCourseId(
            $this->modx,
            (int)$this->getProperty('course_id', 0),
            (int)$this->getProperty('resource_id', 0)
        );
        $targetUserId = (int)$this->getProperty('user_id', 0);

        if ($courseId <= 0) {
            return $this->failure('Не указан курс');
        }
        if ($targetUserId <= 0) {
            return $this->failure('Не указан пользователь');
        }

        $service = TrainingWebHelper::getProgressService($this->modx);
        $result = $service->revokeCourseAccessForUser($courseId, $targetUserId, $actorUserId);

        if (empty($result['success'])) {
            return $this->failure(!empty($result['message']) ? $result['message'] : 'Не удалось снять доступ');
        }

        /** @var TrainingUserCourse $userCourse */
        $userCourse = !empty($result['user_course']) ? $result['user_course'] : null;

        return $this->success('Доступ обновлен', [
            'course_id' => $courseId,
            'user_id' => $targetUserId,
            'removed' => !empty($result['removed']) ? 1 : 0,
            'has_access_now' => !empty($result['has_access_now']) ? 1 : 0,
            'status' => $userCourse ? (string)$userCourse->get('status') : '',
        ]);
    }
}

return 'TrainingWebCourseUnassignProcessor';
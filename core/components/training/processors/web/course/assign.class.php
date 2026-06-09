<?php

require_once dirname(dirname(__FILE__)) . '/_helpers.php';

class TrainingWebCourseAssignProcessor extends modProcessor
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
        $accessRole = trim((string)$this->getProperty('access_role', 'employee'));
        $activeFrom = $this->getProperty('active_from');
        $activeTo = $this->getProperty('active_to');
        $isActive = (int)((string)$this->getProperty('is_active', '1') === '0' ? 0 : 1);

        if ($courseId <= 0) {
            return $this->failure('Не указан курс');
        }
        if ($targetUserId <= 0) {
            return $this->failure('Не указан пользователь');
        }

        $service = TrainingWebHelper::getProgressService($this->modx);
        $result = $service->assignCourseAccessToUser($courseId, $targetUserId, $actorUserId, [
            'access_role' => $accessRole,
            'active_from' => $activeFrom,
            'active_to' => $activeTo,
            'is_active' => $isActive,
        ]);

        if (empty($result['success'])) {
            return $this->failure(!empty($result['message']) ? $result['message'] : 'Не удалось назначить курс');
        }

        /** @var TrainingCourseAccess $access */
        $access = !empty($result['access']) ? $result['access'] : null;
        /** @var TrainingUserCourse $userCourse */
        $userCourse = !empty($result['user_course']) ? $result['user_course'] : null;

        return $this->success('Курс назначен', [
            'course_id' => $courseId,
            'user_id' => $targetUserId,
            'access_id' => $access ? (int)$access->get('id') : 0,
            'access_role' => $access ? (string)$access->get('access_role') : '',
            'created' => !empty($result['created']) ? 1 : 0,
            'status' => $userCourse ? (string)$userCourse->get('status') : '',
            'progress_percent' => $userCourse ? (float)$userCourse->get('progress_percent') : 0,
        ]);
    }
}

return 'TrainingWebCourseAssignProcessor';
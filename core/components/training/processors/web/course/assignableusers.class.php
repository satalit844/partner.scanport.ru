<?php

require_once dirname(dirname(__FILE__)) . '/_helpers.php';

class TrainingWebCourseAssignableUsersProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    protected function formatDateValue($value)
    {
        if ($value === null || $value === '' || $value === '0000-00-00 00:00:00' || $value === 0 || $value === '0') {
            return '—';
        }

        if (is_numeric($value)) {
            $ts = (int)$value;
        } else {
            $ts = strtotime((string)$value);
        }

        if (!$ts) {
            return '—';
        }

        return date('d.m.Y H:i', $ts);
    }

    protected function decodeExtended($value)
    {
        if (is_array($value)) {
            return $value;
        }

        $value = trim((string)$value);
        if ($value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    protected function extractOrganization($extended)
    {
        $extended = $this->decodeExtended($extended);

        $keys = [
            'company',
            'organization',
            'organisation',
            'org',
            'company_name',
            'organisation_name',
            'employer',
            'workplace',
        ];

        foreach ($keys as $key) {
            if (!empty($extended[$key])) {
                return trim((string)$extended[$key]);
            }
        }

        return '';
    }




    protected function extractOrganizationFromRow(array $row)
    {
        $keys = [
            'field_list_company',
            'field_company',
            'company',
            'organization',
        ];

        foreach ($keys as $key) {
            if (isset($row[$key])) {
                $value = trim((string)$row[$key]);
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return $this->extractOrganization(isset($row['extended']) ? $row['extended'] : '');
    }

    /**
     * Быстрый расчёт для таблицы «Управление курсами».
     *
     * Использует сохранённый итог TrainingUserCourse и не пересчитывает
     * видео, тесты и практики каждого сотрудника на каждый AJAX-запрос.
     */
    protected function getCourseTrackProgress(TrainingProgressService $service, $courseId, $userId, array $userCourseRow = [])
    {
        $progressPercent = isset($userCourseRow['progress_percent'])
            ? (float)$userCourseRow['progress_percent']
            : 0;

        $status = isset($userCourseRow['status'])
            ? (string)$userCourseRow['status']
            : '';

        $totalModules = isset($userCourseRow['total_modules'])
            ? max(0, (int)$userCourseRow['total_modules'])
            : 0;

        $completedModules = isset($userCourseRow['completed_modules'])
            ? max(0, (int)$userCourseRow['completed_modules'])
            : 0;

        $progressPercent = max(0, min(100, (int)round($progressPercent)));

        if ($progressPercent <= 0 && $totalModules > 0 && $completedModules > 0) {
            $progressPercent = max(0, min(100, (int)round(($completedModules / $totalModules) * 100)));
        }

        if ($progressPercent >= 100) {
            $status = 'completed';
        } elseif (
            $progressPercent > 0
            && ($status === '' || $status === 'assigned' || $status === 'not_started')
        ) {
            $status = 'in_progress';
        }

        return [
            'progress_percent' => $progressPercent,
            'status' => $status,
            'total_items' => $totalModules,
            'completed_items' => min($completedModules, $totalModules),
            'videos_total' => 0,
            'videos_completed' => 0,
            'tests_total' => 0,
            'tests_passed' => 0,
            'practices_total' => 0,
            'practices_completed' => 0,
        ];
    }

    protected function buildState($hasAccess, array $userCourseRow = [], $directAccessId = 0)
    {
        $progressPercent = isset($userCourseRow['progress_percent']) ? (float)$userCourseRow['progress_percent'] : 0;
        $status = isset($userCourseRow['status']) ? (string)$userCourseRow['status'] : '';

        if (!$hasAccess) {
            return [
                'state' => 'new',
                'status_text' => 'Не начат',
                'status_class' => 'label-chip--blue',
                'button_text' => '',
                'button_class' => '',
                'show_assign_button' => 0,
                'show_status_chip' => 1,
                'can_unassign' => 0,
            ];
        }

        if ($status === 'completed' || $progressPercent >= 100) {
            return [
                'state' => 'done',
                'status_text' => 'Завершено',
                'status_class' => 'label-chip--green',
                'button_text' => '',
                'button_class' => '',
                'show_assign_button' => 0,
                'show_status_chip' => 1,
                'can_unassign' => $directAccessId > 0 ? 1 : 0,
            ];
        }

        if ($status === 'in_progress' || $progressPercent > 0) {
            return [
                'state' => 'progress',
                'status_text' => 'В процессе - ' . round($progressPercent) . '%',
                'status_class' => 'label-chip--purple',
                'button_text' => '',
                'button_class' => '',
                'show_assign_button' => 0,
                'show_status_chip' => 1,
                'can_unassign' => $directAccessId > 0 ? 1 : 0,
            ];
        }

        return [
            'state' => 'new',
            'status_text' => 'Не начат',
            'status_class' => 'label-chip--blue',
            'button_text' => '',
            'button_class' => '',
            'show_assign_button' => 0,
            'show_status_chip' => 1,
            'can_unassign' => $directAccessId > 0 ? 1 : 0,
        ];
    }




    protected function actorHasDirectorManagementAccess($courseId, $actorUserId)
    {
        $courseId = (int)$courseId;
        $actorUserId = (int)$actorUserId;
        if ($courseId <= 0 || $actorUserId <= 0) {
            return false;
        }

        /** @var TrainingCourseAccess $access */
        $access = $this->modx->getObject('TrainingCourseAccess', [
            'course_id' => $courseId,
            'principal_type' => 'user',
            'principal_id' => $actorUserId,
            'access_role' => 'director',
        ]);
        if ($access) {
            return true;
        }

        /** @var TrainingUserCourse $userCourse */
        $userCourse = $this->modx->getObject('TrainingUserCourse', [
            'course_id' => $courseId,
            'user_id' => $actorUserId,
            'access_role' => 'director',
        ]);

        return $userCourse && (string)$userCourse->get('status') !== 'revoked';
    }



    protected function isDirectAccessActiveNow($access)
    {
        if (!$access) {
            return false;
        }

        if ((int)$access->get('is_active') !== 1) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $activeFrom = (string)$access->get('active_from');
        $activeTo = (string)$access->get('active_to');

        if ($activeFrom !== '' && $activeFrom !== '0000-00-00 00:00:00' && $now < $activeFrom) {
            return false;
        }

        if ($activeTo !== '' && $activeTo !== '0000-00-00 00:00:00' && $now > $activeTo) {
            return false;
        }

        return true;
    }

    protected function actorHasActiveCoursePassingAccess($courseId, $actorUserId)
    {
        $courseId = (int)$courseId;
        $actorUserId = (int)$actorUserId;
        if ($courseId <= 0 || $actorUserId <= 0) {
            return false;
        }

        /** @var TrainingCourseAccess|null $access */
        $access = $this->modx->getObject('TrainingCourseAccess', [
            'course_id' => $courseId,
            'principal_type' => 'user',
            'principal_id' => $actorUserId,
        ]);

        if (!$access) {
            return false;
        }

        return $this->isDirectAccessActiveNow($access);
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
        $includeSelf = (int)$this->getProperty('include_self', 0) === 1;
        $query = trim((string)$this->getProperty('query', ''));

        $service = TrainingWebHelper::getProgressService($this->modx);

        $managedIds = $service->getManagedUserIds($actorUserId, true);
        $managedIds = array_values(array_unique(array_map('intval', (array)$managedIds)));
        $actorCanManageCourse = $courseId > 0 && (
            $this->actorHasDirectorManagementAccess($courseId, $actorUserId)
            || $service->canManageCourse($courseId, $actorUserId)
        );

        /*
         * training-director-scope-v1
         *
         * Если у пользователя есть активные прямые подчинённые, это директор
         * команды. Его список всегда ограничиваем своей командой, даже когда
         * isAdminUser() по сессии/правам возвращает true.
         *
         * Настоящий администратор без связей директор → сотрудник по-прежнему
         * получает полный список пользователей.
         */
        if (!empty($managedIds)) {
            $ids = $managedIds;

            if ($actorCanManageCourse && $this->actorHasActiveCoursePassingAccess($courseId, $actorUserId)) {
                $ids[] = $actorUserId;
            }
        } elseif ($service->isAdminUser($actorUserId)) {
            $ids = $service->getAssignableUserIds($actorUserId, $courseId, true);
        } else {
            $ids = [];

            if ($actorCanManageCourse && $this->actorHasActiveCoursePassingAccess($courseId, $actorUserId)) {
                $ids[] = $actorUserId;
            } elseif (empty($ids)) {
                $ids = $service->getAssignableUserIds($actorUserId, $courseId, $includeSelf);
            }
        }

        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$includeSelf && !$actorCanManageCourse && !$service->isAdminUser($actorUserId)) {
            $ids = array_values(array_diff($ids, [$actorUserId]));
        }

        if (empty($ids)) {
            return $this->success('', [
                'course_id' => $courseId,
                'total' => 0,
                'results' => [],
            ]);
        }

        $accessibleMap = [];
        $userCourseMap = [];
        $directAccessMap = [];

        if ($courseId > 0) {
            $accessibleMap = $service->collectAccessibleUserMap($courseId);

            $userCourses = $this->modx->getIterator('TrainingUserCourse', [
                'course_id' => $courseId,
                'user_id:IN' => $ids,
            ]);
            /** @var TrainingUserCourse $userCourse */
            foreach ($userCourses as $userCourse) {
                $userCourseMap[(int)$userCourse->get('user_id')] = $userCourse;
            }

            $directAccesses = $this->modx->getIterator('TrainingCourseAccess', [
                'course_id' => $courseId,
                'principal_type' => 'user',
                'principal_id:IN' => $ids,
            ]);
            /** @var TrainingCourseAccess $directAccess */
            foreach ($directAccesses as $directAccess) {
                $directAccessMap[(int)$directAccess->get('principal_id')] = $directAccess;
            }
        }

        $users = $this->modx->getIterator('modUser', [
            'id:IN' => $ids,
            'active' => 1,
        ]);

        $results = [];
        /** @var modUser $user */
        foreach ($users as $user) {
            $userId = (int)$user->get('id');
            $profile = $user->getOne('Profile');
            $userCourse = isset($userCourseMap[$userId]) ? $userCourseMap[$userId] : null;
            $directAccess = isset($directAccessMap[$userId]) ? $directAccessMap[$userId] : null;

            $hasAccess = isset($accessibleMap[$userId]);
            $userCourseRow = $userCourse ? $userCourse->toArray() : [];
            $directAccessId = $directAccess ? (int)$directAccess->get('id') : 0;
            $state = $this->buildState($hasAccess, $userCourseRow, $directAccessId);
            $track = $this->getCourseTrackProgress($service, $courseId, $userId, $userCourseRow);
            $state = $this->buildState($hasAccess, array_merge($userCourseRow, [
                'progress_percent' => $track['progress_percent'],
                'status' => $track['status'],
            ]), $directAccessId);

            $fullname = $profile ? trim((string)$profile->get('fullname')) : '';
            $email = $profile ? trim((string)$profile->get('email')) : '';
            $extended = $profile ? $profile->get('extended') : '';
            $organization = $this->extractOrganizationFromRow($userCourseRow);
            if ($organization === '') {
                $organization = $this->extractOrganization($extended);
            }

            $results[] = [
                'id' => $userId,
                'username' => (string)$user->get('username'),
                'fullname' => $fullname,
                'email' => $email,
                'organization' => $organization,
                'has_access' => $hasAccess ? 1 : 0,
                'progress_percent' => $track['progress_percent'],
                'status' => $track['status'],
                'resolved_access_role' => $courseId > 0
                    ? $service->resolveUserAccessRoleForCourse($courseId, $userId, 'employee')
                    : 'employee',
                'direct_access_id' => $directAccessId,
                'access_role' => $userCourse ? (string)$userCourse->get('access_role') : '',
                'access_status' => $userCourse ? (string)$userCourse->get('status') : '',
                'last_activity' => $userCourse ? $this->formatDateValue($userCourse->get('last_activity')) : '—',
                'state' => $state['state'],
                'status_text' => $state['status_text'],
                'status_class' => $state['status_class'],
                'button_text' => $state['button_text'],
                'button_class' => $state['button_class'],
                'show_assign_button' => $state['show_assign_button'],
                'show_status_chip' => $state['show_status_chip'],
                'can_unassign' => $state['can_unassign'],
                'can_assign' => ($courseId > 0 && !$hasAccess && (
                    $service->canAssignCourseToUser($courseId, $actorUserId, $userId)
                    || ($actorCanManageCourse && $userId !== $actorUserId && in_array($userId, $managedIds, true))
                )) ? 1 : 0,
            ];
        }

        usort($results, function ($a, $b) {
            $aName = mb_strtolower((string)($a['fullname'] ?: $a['username']), 'UTF-8');
            $bName = mb_strtolower((string)($b['fullname'] ?: $b['username']), 'UTF-8');
            return strcmp($aName, $bName);
        });

        if ($query !== '') {
            $needle = mb_strtolower($query, 'UTF-8');
            $results = array_values(array_filter($results, function ($row) use ($needle) {
                $haystack = mb_strtolower(implode(' ', [
                    isset($row['fullname']) ? $row['fullname'] : '',
                    isset($row['username']) ? $row['username'] : '',
                    isset($row['email']) ? $row['email'] : '',
                    isset($row['organization']) ? $row['organization'] : '',
                ]), 'UTF-8');
                return mb_strpos($haystack, $needle, 0, 'UTF-8') !== false;
            }));
        }

        return $this->success('', [
            'course_id' => $courseId,
            'total' => count($results),
            'results' => $results,
        ]);
    }
}

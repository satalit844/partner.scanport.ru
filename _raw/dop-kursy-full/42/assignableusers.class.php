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

        // ВАЖНО:
        // если у пользователя есть явные связи director -> employee,
        // на фронте управления курсами показываем ТОЛЬКО их.
        // Это отключает ветку isAdminUser()/sudo, из-за которой раньше вытягивались все пользователи.
        $managedIds = $service->getManagedUserIds($actorUserId, true);
        $managedIds = array_values(array_unique(array_map('intval', (array)$managedIds)));

        if (!empty($managedIds)) {
            $ids = $managedIds;
            if ($includeSelf) {
                $ids[] = $actorUserId;
            }
        } else {
            $ids = $service->getAssignableUserIds($actorUserId, $courseId, $includeSelf);
        }

        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$includeSelf) {
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
            /** @var TrainingCourseAccess $access */
            foreach ($directAccesses as $access) {
                $directAccessMap[(int)$access->get('principal_id')] = $access;
            }
        }

        $c = $this->modx->newQuery('modUser');
        $c->leftJoin('modUserProfile', 'Profile', 'Profile.internalKey = modUser.id');
        $c->where([
            'modUser.id:IN' => $ids,
        ]);

        if ($query !== '') {
            $c->where([
                'modUser.username:LIKE' => '%' . $query . '%',
                'OR:Profile.fullname:LIKE' => '%' . $query . '%',
                'OR:Profile.email:LIKE' => '%' . $query . '%',
            ]);
        }

        $c->select([
            'modUser.id AS id',
            'modUser.username AS username',
            'Profile.fullname AS fullname',
            'Profile.email AS email',
            'Profile.extended AS extended',
            'Profile.lastlogin AS lastlogin',
        ]);
        $c->sortby('Profile.fullname', 'ASC');
        $c->sortby('modUser.username', 'ASC');

        $rows = [];

        if ($c->prepare() && $c->stmt->execute()) {
            while ($row = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                $userId = (int)$row['id'];
                $fullname = trim((string)$row['fullname']);
                $email = trim((string)$row['email']);
                $username = trim((string)$row['username']);
                $organization = $this->extractOrganization($row['extended']);

                $displayName = $fullname !== '' ? $fullname : $username;
                $label = '#' . $userId . ' ' . $username;
                if ($fullname !== '') {
                    $label .= ' (' . $fullname . ')';
                }
                if ($email !== '') {
                    $label .= ' [' . $email . ']';
                }

                /** @var TrainingUserCourse|null $userCourse */
                $userCourse = isset($userCourseMap[$userId]) ? $userCourseMap[$userId] : null;
                /** @var TrainingCourseAccess|null $directAccess */
                $directAccess = isset($directAccessMap[$userId]) ? $directAccessMap[$userId] : null;

                $hasAccess = $courseId > 0 ? isset($accessibleMap[$userId]) : 0;
                $directAccessId = $directAccess ? (int)$directAccess->get('id') : 0;

                $userCourseRow = $userCourse ? $userCourse->toArray() : [];
                $state = $this->buildState($hasAccess, $userCourseRow, $directAccessId);

                $progressPercent = $userCourse ? round((float)$userCourse->get('progress_percent')) : 0;
                $startedOn = $userCourse ? $userCourse->get('startedon') : null;

                $rows[] = [
                    'id' => $userId,
                    'name' => $label,
                    'display' => $label,
                    'display_name' => $displayName,
                    'username' => $username,
                    'fullname' => $fullname,
                    'email' => $email,
                    'organization' => $organization !== '' ? $organization : '—',
                    'scope' => $userId === $actorUserId ? 'self' : 'managed',

                    'course_id' => $courseId,
                    'has_access' => $hasAccess ? 1 : 0,
                    'direct_access_id' => $directAccessId,
                    'direct_access_role' => $directAccess ? (string)$directAccess->get('access_role') : '',
                    'resolved_access_role' => $courseId > 0
                        ? $service->resolveUserAccessRoleForCourse($courseId, $userId, 'employee')
                        : 'employee',

                    'user_course_status' => $userCourse ? (string)$userCourse->get('status') : '',
                    'progress_percent' => $progressPercent,
                    'startedon' => $startedOn,
                    'startedon_formatted' => $this->formatDateValue($startedOn),
                    'last_login' => $row['lastlogin'],
                    'last_login_formatted' => $this->formatDateValue($row['lastlogin']),

                    'state' => $state['state'],
                    'status_text' => $state['status_text'],
                    'status_class' => $state['status_class'],
                    'show_assign_button' => $state['show_assign_button'],
                    'show_status_chip' => $state['show_status_chip'],
                    'button_text' => $state['button_text'],
                    'button_class' => $state['button_class'],

                    'can_assign' => ($courseId > 0 && !$hasAccess && $service->canAssignCourseToUser($courseId, $actorUserId, $userId)) ? 1 : 0,
                    'can_unassign' => $state['can_unassign'],
                ];
            }
        }

        return $this->success('', [
            'course_id' => $courseId,
            'total' => count($rows),
            'results' => $rows,
        ]);
    }
}

return 'TrainingWebCourseAssignableUsersProcessor';
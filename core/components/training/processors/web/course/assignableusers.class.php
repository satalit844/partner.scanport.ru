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




    /*
     * training-license-ui-v1
     *
     * Интерфейс директора получает состояние только его собственного пула:
     * course_id + director_user_id. Доступы старого типа не расходуют
     * лицензии и отмечаются отдельно как legacy.
     */
    protected function getLicenseAssignmentsTable()
    {
        $courseAccessTable = trim((string)$this->modx->getTableName('TrainingCourseAccess'), '`');
        $suffix = '_course_access';

        if (
            $courseAccessTable === ''
            || substr($courseAccessTable, -strlen($suffix)) !== $suffix
        ) {
            return '';
        }

        $table = substr($courseAccessTable, 0, -strlen($suffix)) . '_license_assignments';
        $stmt = $this->modx->prepare('SHOW TABLES LIKE :table_name');

        if (!$stmt) {
            return '';
        }

        $stmt->bindValue(':table_name', $table, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchColumn() ? $table : '';
    }

    protected function getDirectorLicenseSummary($courseId, $directorUserId)
    {
        $summary = [
            'enabled' => 0,
            'director_access_id' => 0,
            'total' => 0,
            'reserved' => 0,
            'consumed' => 0,
            'free' => 0,
        ];

        $courseId = (int)$courseId;
        $directorUserId = (int)$directorUserId;

        if ($courseId <= 0 || $directorUserId <= 0) {
            return $summary;
        }

        /** @var TrainingCourseAccess|null $directorAccess */
        $directorAccess = $this->modx->getObject('TrainingCourseAccess', [
            'course_id' => $courseId,
            'principal_type' => 'user',
            'principal_id' => $directorUserId,
            'access_role' => 'director',
        ]);

        if (!$directorAccess || (int)$directorAccess->get('licenses_enabled') !== 1) {
            return $summary;
        }

        $summary['director_access_id'] = (int)$directorAccess->get('id');
        $summary['total'] = max(0, (int)$directorAccess->get('licenses_total'));

        if ($summary['total'] <= 0) {
            return $summary;
        }

        $assignmentsTable = $this->getLicenseAssignmentsTable();
        if ($assignmentsTable === '') {
            return $summary;
        }

        $safeTable = str_replace('`', '``', $assignmentsTable);
        $stmt = $this->modx->prepare(
            'SELECT `state`, COUNT(*) AS `total` '
            . 'FROM `' . $safeTable . '` '
            . 'WHERE `course_id` = :course_id '
            . 'AND `director_user_id` = :director_user_id '
            . 'AND `state` IN ("reserved", "consumed") '
            . 'GROUP BY `state`'
        );

        if ($stmt) {
            $stmt->bindValue(':course_id', $courseId, PDO::PARAM_INT);
            $stmt->bindValue(':director_user_id', $directorUserId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $state = (string)$row['state'];
                    if ($state === 'reserved') {
                        $summary['reserved'] = max(0, (int)$row['total']);
                    } elseif ($state === 'consumed') {
                        $summary['consumed'] = max(0, (int)$row['total']);
                    }
                }
            }
        }

        $summary['enabled'] = 1;
        $summary['free'] = max(0, $summary['total'] - $summary['reserved'] - $summary['consumed']);

        return $summary;
    }

    protected function getDirectorLicenseAssignments($courseId, $directorUserId, array $employeeIds)
    {
        $result = [];
        $courseId = (int)$courseId;
        $directorUserId = (int)$directorUserId;

        $ids = array_values(array_unique(array_filter(array_map('intval', $employeeIds))));
        if ($courseId <= 0 || $directorUserId <= 0 || empty($ids)) {
            return $result;
        }

        $assignmentsTable = $this->getLicenseAssignmentsTable();
        if ($assignmentsTable === '') {
            return $result;
        }

        $safeTable = str_replace('`', '``', $assignmentsTable);
        $safeIds = implode(',', $ids);

        $stmt = $this->modx->prepare(
            'SELECT `id`, `employee_user_id`, `state`, `progress_percent`, '
            . '`reservedon`, `threshold_reachedon`, `consumedon`, `releasedon`, `access_closedon` '
            . 'FROM `' . $safeTable . '` '
            . 'WHERE `course_id` = :course_id '
            . 'AND `director_user_id` = :director_user_id '
            . 'AND `employee_user_id` IN (' . $safeIds . ') '
            . 'ORDER BY `id` DESC'
        );

        if (!$stmt) {
            return $result;
        }

        $stmt->bindValue(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->bindValue(':director_user_id', $directorUserId, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            return $result;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $employeeUserId = (int)$row['employee_user_id'];

            /*
             * Для одного сотрудника нужен только последний жизненный цикл
             * лицензии. Старые released-записи остаются историей.
             */
            if ($employeeUserId > 0 && !isset($result[$employeeUserId])) {
                $result[$employeeUserId] = $row;
            }
        }

        return $result;
    }

    protected function applyLicenseUiState(array $state, $hasAccess, $directAccessId, array $licenseRow, array $licenseSummary)
    {
        /*
         * training-license-status-restore-v1
         *
         * Таблица сотрудников всегда показывает привычный статус обучения:
         * «Не начат», «В процессе — N%», «Завершено».
         *
         * Состояние лицензии хранится только в служебных полях строки:
         * license_state / license_action / license_note.
         * По ним фронт выбирает кнопку и текст в подтверждающей модалке,
         * но не заменяет прогресс обучения.
         */
        $state['license_state'] = '';
        $state['license_action'] = '';
        $state['license_note'] = '';

        $hasAccess = (bool)$hasAccess;
        $directAccessId = (int)$directAccessId;
        $licenseEnabled = !empty($licenseSummary['enabled']);

        if (!$licenseEnabled) {
            return $state;
        }

        if (!$hasAccess) {
            $state['license_action'] = 'assign';
            return $state;
        }

        /*
         * Групповой/унаследованный доступ на этой странице не закрываем:
         * он не принадлежит личному пулу директора.
         */
        if ($directAccessId <= 0) {
            return $state;
        }

        $licenseState = isset($licenseRow['state'])
            ? (string)$licenseRow['state']
            : '';

        if ($licenseState === 'reserved') {
            $state['license_state'] = 'reserved';
            $state['license_action'] = 'unassign_return';
            $state['license_note'] = 'При закрытии доступа лицензия вернётся в пул.';
            return $state;
        }

        if ($licenseState === 'consumed') {
            $state['license_state'] = 'consumed';
            $state['license_action'] = 'unassign_close';
            $state['license_note'] = 'При закрытии доступа лицензия не возвращается.';
            return $state;
        }

        /*
         * Доступ, созданный до системы лицензий или администратором.
         * Он продолжает отображать нормальный прогресс обучения.
         */
        $state['license_state'] = 'legacy';
        $state['license_action'] = 'unassign_legacy';
        $state['license_note'] = 'Лицензии директора не затрагиваются.';

        return $state;
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
            /** @var TrainingCourseAccess $access */
            foreach ($directAccesses as $access) {
                $directAccessMap[(int)$access->get('principal_id')] = $access;
            }
        }

        $licenseSummary = $this->getDirectorLicenseSummary($courseId, $actorUserId);
        $licenseAssignmentsMap = !empty($licenseSummary['enabled'])
            ? $this->getDirectorLicenseAssignments($courseId, $actorUserId, $ids)
            : [];

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
            'Profile.field_company AS field_company',
            'Profile.field_list_company AS field_list_company',
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
                $organization = $this->extractOrganizationFromRow($row);

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
                $trackProgress = $this->getCourseTrackProgress($service, $courseId, $userId, $userCourseRow);
                $userCourseRow['progress_percent'] = $trackProgress['progress_percent'];
                if ($trackProgress['status'] !== '') {
                    $userCourseRow['status'] = $trackProgress['status'];
                }

                $state = $this->buildState($hasAccess, $userCourseRow, $directAccessId);
                $licenseRow = isset($licenseAssignmentsMap[$userId])
                    ? (array)$licenseAssignmentsMap[$userId]
                    : [];
                $state = $this->applyLicenseUiState(
                    $state,
                    $hasAccess,
                    $directAccessId,
                    $licenseRow,
                    $licenseSummary
                );

                $progressPercent = (int)$trackProgress['progress_percent'];
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
                    'license_enabled' => !empty($licenseSummary['enabled']) ? 1 : 0,
                    'license_state' => isset($state['license_state']) ? (string)$state['license_state'] : '',
                    'license_action' => isset($state['license_action']) ? (string)$state['license_action'] : '',
                    'license_note' => isset($state['license_note']) ? (string)$state['license_note'] : '',
                    'license_progress_percent' => isset($licenseRow['progress_percent']) ? (float)$licenseRow['progress_percent'] : 0,
                    'resolved_access_role' => $courseId > 0
                        ? $service->resolveUserAccessRoleForCourse($courseId, $userId, 'employee')
                        : 'employee',

                    'user_course_status' => $userCourse ? (string)$userCourse->get('status') : '',
                    'progress_percent' => $progressPercent,
                    'track_total_items' => (int)$trackProgress['total_items'],
                    'track_completed_items' => (int)$trackProgress['completed_items'],
                    'videos_total' => (int)$trackProgress['videos_total'],
                    'videos_completed' => (int)$trackProgress['videos_completed'],
                    'tests_total' => (int)$trackProgress['tests_total'],
                    'tests_passed' => (int)$trackProgress['tests_passed'],
                    'practices_total' => (int)$trackProgress['practices_total'],
                    'practices_completed' => (int)$trackProgress['practices_completed'],
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

                    'can_assign' => ($courseId > 0 && !$hasAccess && (
                        $service->canAssignCourseToUser($courseId, $actorUserId, $userId)
                        || ($actorCanManageCourse && $userId !== $actorUserId && in_array($userId, $managedIds, true))
                    )) ? 1 : 0,
                    'can_unassign' => $state['can_unassign'],
                ];
            }
        }

        return $this->success('', [
            'course_id' => $courseId,
            'license_summary' => $licenseSummary,
            'total' => count($rows),
            'results' => $rows,
        ]);
    }
}

return 'TrainingWebCourseAssignableUsersProcessor';
<?php

class TrainingProgressService
{
    /** @var modX */
    protected $modx;

    /** @var Training */
    protected $training;

    public function __construct(modX $modx, Training $training)
    {
        $this->modx = $modx;
        $this->training = $training;
    }

    protected function getNow()
    {
        return date('Y-m-d H:i:s');
    }

    protected function normalizeRole($role)
    {
        $role = trim((string)$role);
        return $role === 'director' ? 'director' : 'employee';
    }

    protected function mergeRole($currentRole, $newRole)
    {
        $currentRole = $this->normalizeRole($currentRole);
        $newRole = $this->normalizeRole($newRole);
        return $currentRole === 'director' || $newRole === 'director' ? 'director' : 'employee';
    }

    public function normalizeDateTimeValue($value)
    {
        $value = trim((string)$value);
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        $value = str_replace('T', ' ', $value);
        if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
            return $value . ' 00:00:00';
        }

        if (preg_match('#^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}$#', $value)) {
            return $value . ':00';
        }

        return $value;
    }

    public function isAccessCurrentlyActive($access, $now = null)
    {
        $now = $now ?: $this->getNow();
        $activeFrom = null;
        $activeTo = null;

        if ($access instanceof TrainingCourseAccess) {
            if (!(int)$access->get('is_active')) {
                return false;
            }
            $activeFrom = $access->get('active_from');
            $activeTo = $access->get('active_to');
        } elseif (is_array($access)) {
            if (isset($access['is_active']) && !(int)$access['is_active']) {
                return false;
            }
            $activeFrom = isset($access['active_from']) ? $access['active_from'] : null;
            $activeTo = isset($access['active_to']) ? $access['active_to'] : null;
        }

        if (!empty($activeFrom) && $now < $activeFrom) {
            return false;
        }
        if (!empty($activeTo) && $now > $activeTo) {
            return false;
        }

        return true;
    }

    public function getUserGroupIds($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return [];
        }

        $ids = [];
        $members = $this->modx->getIterator('modUserGroupMember', [
            'member' => $userId,
        ]);

        /** @var modUserGroupMember $member */
        foreach ($members as $member) {
            $groupId = (int)$member->get('user_group');
            if ($groupId > 0) {
                $ids[$groupId] = $groupId;
            }
        }

        return array_values($ids);
    }

    public function hasCourseAccess($courseId, $userId)
    {
        $courseId = (int)$courseId;
        $userId = (int)$userId;
        if ($courseId <= 0 || $userId <= 0) {
            return false;
        }

        $groupIds = $this->getUserGroupIds($userId);
        $items = $this->modx->getIterator('TrainingCourseAccess', [
            'course_id' => $courseId,
            'is_active' => 1,
        ]);

        $now = $this->getNow();
        /** @var TrainingCourseAccess $item */
        foreach ($items as $item) {
            if (!$this->isAccessCurrentlyActive($item, $now)) {
                continue;
            }

            $principalType = (string)$item->get('principal_type');
            $principalId = (int)$item->get('principal_id');
            if ($principalType === 'user' && $principalId === $userId) {
                return true;
            }
            if ($principalType === 'group' && in_array($principalId, $groupIds, true)) {
                return true;
            }
        }

        return false;
    }

    public function collectAccessibleUserMap($courseId)
    {
        $courseId = (int)$courseId;
        if ($courseId <= 0) {
            return [];
        }

        $result = [];
        $now = $this->getNow();
        $items = $this->modx->getIterator('TrainingCourseAccess', [
            'course_id' => $courseId,
            'is_active' => 1,
        ]);

        /** @var TrainingCourseAccess $item */
        foreach ($items as $item) {
            if (!$this->isAccessCurrentlyActive($item, $now)) {
                continue;
            }

            $principalType = (string)$item->get('principal_type');
            $principalId = (int)$item->get('principal_id');
            $accessRole = $this->normalizeRole($item->get('access_role'));
            if ($principalId <= 0) {
                continue;
            }

            if ($principalType === 'user') {
                if (!isset($result[$principalId])) {
                    $result[$principalId] = [
                        'user_id' => $principalId,
                        'access_role' => $accessRole,
                    ];
                } else {
                    $result[$principalId]['access_role'] = $this->mergeRole($result[$principalId]['access_role'], $accessRole);
                }
                continue;
            }

            if ($principalType === 'group') {
                $members = $this->modx->getIterator('modUserGroupMember', [
                    'user_group' => $principalId,
                ]);
                /** @var modUserGroupMember $member */
                foreach ($members as $member) {
                    $memberId = (int)$member->get('member');
                    if ($memberId <= 0) {
                        continue;
                    }
                    if (!isset($result[$memberId])) {
                        $result[$memberId] = [
                            'user_id' => $memberId,
                            'access_role' => $accessRole,
                        ];
                    } else {
                        $result[$memberId]['access_role'] = $this->mergeRole($result[$memberId]['access_role'], $accessRole);
                    }
                }
            }
        }

        ksort($result);
        return $result;
    }

    public function collectAccessibleUserIds($courseId)
    {
        return array_keys($this->collectAccessibleUserMap($courseId));
    }

    public function resolveUserAccessRoleForCourse($courseId, $userId, $default = 'employee')
    {
        $courseId = (int)$courseId;
        $userId = (int)$userId;
        if ($courseId <= 0 || $userId <= 0) {
            return $this->normalizeRole($default);
        }

        $map = $this->collectAccessibleUserMap($courseId);
        if (isset($map[$userId]['access_role'])) {
            return $this->normalizeRole($map[$userId]['access_role']);
        }

        /** @var TrainingUserCourse $userCourse */
        $userCourse = $this->modx->getObject('TrainingUserCourse', [
            'course_id' => $courseId,
            'user_id' => $userId,
        ]);
        if ($userCourse) {
            return $this->normalizeRole($userCourse->get('access_role'));
        }

        return $this->normalizeRole($default);
    }

    public function ensureUserCourse($courseId, $userId, $accessRole = 'employee')
    {
        $courseId = (int)$courseId;
        $userId = (int)$userId;
        $accessRole = $this->normalizeRole($accessRole);

        /** @var TrainingUserCourse $userCourse */
        $userCourse = $this->modx->getObject('TrainingUserCourse', [
            'course_id' => $courseId,
            'user_id' => $userId,
        ]);

        if (!$userCourse) {
            $userCourse = $this->modx->newObject('TrainingUserCourse');
            $userCourse->fromArray([
                'course_id' => $courseId,
                'user_id' => $userId,
                'access_role' => $accessRole,
                'status' => 'assigned',
                'current_module_id' => 0,
                'progress_percent' => 0,
                'completed_modules' => 0,
                'total_modules' => 0,
                'startedon' => null,
                'completedon' => null,
                'last_activity' => $this->getNow(),
            ], '', true, true);
            $userCourse->save();
        }

        return $userCourse;
    }

    public function syncUserCourseForUser($courseId, $userId)
    {
        $courseId = (int)$courseId;
        $userId = (int)$userId;
        if ($courseId <= 0 || $userId <= 0) {
            return false;
        }

        $hasAccess = $this->hasCourseAccess($courseId, $userId);
        $now = $this->getNow();

        /** @var TrainingUserCourse $userCourse */
        $userCourse = $this->modx->getObject('TrainingUserCourse', [
            'course_id' => $courseId,
            'user_id' => $userId,
        ]);

        if ($hasAccess) {
            $role = $this->resolveUserAccessRoleForCourse($courseId, $userId, 'employee');
            if (!$userCourse) {
                $userCourse = $this->ensureUserCourse($courseId, $userId, $role);
            }
            $userCourse->set('access_role', $role);
            if ((string)$userCourse->get('status') === 'revoked') {
                $userCourse->set('status', 'assigned');
            }
            $userCourse->set('last_activity', $now);
            $userCourse->save();
            return $this->recalculateUserCourse($courseId, $userId);
        }

        if ($userCourse) {
            if ((string)$userCourse->get('status') !== 'completed') {
                $userCourse->set('status', 'revoked');
            }
            $userCourse->set('last_activity', $now);
            $userCourse->save();
        }

        return $userCourse;
    }

    public function syncUserCourses($courseId)
    {
        $courseId = (int)$courseId;
        if ($courseId <= 0) {
            return [
                'created' => 0,
                'updated' => 0,
                'deactivated' => 0,
                'users' => [],
            ];
        }

        $accessibleUserMap = $this->collectAccessibleUserMap($courseId);
        $accessibleUserIds = array_keys($accessibleUserMap);
        $existing = $this->modx->getIterator('TrainingUserCourse', [
            'course_id' => $courseId,
        ]);

        $existingMap = [];
        /** @var TrainingUserCourse $userCourse */
        foreach ($existing as $userCourse) {
            $existingMap[(int)$userCourse->get('user_id')] = $userCourse;
        }

        $created = 0;
        $updated = 0;
        $deactivated = 0;
        $activeMap = array_fill_keys($accessibleUserIds, true);
        $now = $this->getNow();

        foreach ($accessibleUserMap as $userId => $row) {
            $userId = (int)$userId;
            $accessRole = $this->normalizeRole($row['access_role']);
            $userCourse = isset($existingMap[$userId]) ? $existingMap[$userId] : null;
            if (!$userCourse) {
                $userCourse = $this->ensureUserCourse($courseId, $userId, $accessRole);
                $created++;
            } else {
                if ((string)$userCourse->get('status') === 'revoked') {
                    $userCourse->set('status', 'assigned');
                }
                $userCourse->set('access_role', $accessRole);
                $userCourse->set('last_activity', $now);
                $userCourse->save();
                $updated++;
            }

            $this->recalculateUserCourse($courseId, $userId);
        }

        foreach ($existingMap as $userId => $userCourse) {
            if (isset($activeMap[$userId])) {
                continue;
            }
            if ((string)$userCourse->get('status') !== 'completed') {
                $userCourse->set('status', 'revoked');
            }
            $userCourse->set('last_activity', $now);
            $userCourse->save();
            $deactivated++;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'deactivated' => $deactivated,
            'users' => $accessibleUserIds,
        ];
    }

    public function getCourseModules($courseId, $onlyActive = true, $requiredOnly = false)
    {
        $courseId = (int)$courseId;
        $c = $this->modx->newQuery('TrainingModule');
        $c->where(['course_id' => $courseId]);
        if ($onlyActive) {
            $c->where(['is_active' => 1]);
        }
        if ($requiredOnly) {
            $c->where(['is_required' => 1]);
        }
        $c->leftJoin('modResource', 'Resource', 'Resource.id = TrainingModule.resource_id');
        $c->sortby('Resource.menuindex', 'ASC');
        $c->sortby('TrainingModule.id', 'ASC');
        return $this->modx->getCollection('TrainingModule', $c);
    }


    protected function ensureUserTestPackage()
    {
        static $loaded = null;
        if ($loaded !== null) {
            return $loaded;
        }

        $corePath = $this->modx->getOption(
            'usertest_core_path',
            null,
            $this->modx->getOption('core_path') . 'components/usertest/'
        );
        $modelPath = rtrim($corePath, '/\\') . '/model/';

        if (!is_dir($modelPath)) {
            $loaded = false;
            return false;
        }

        $this->modx->addPackage('usertest', $modelPath);
        $loaded = true;

        return true;
    }

    public function normalizeActivityLinkType($value)
    {
        $value = trim((string)$value);
        return $value === 'practice' ? 'practice' : 'test';
    }

    public function getActivityLinkTypeLabel($value)
    {
        return $this->normalizeActivityLinkType($value) === 'practice' ? 'Практическая работа' : 'Тест';
    }

    public function getModuleActivityLinks($moduleId, $courseId = 0)
    {
        $moduleId = (int)$moduleId;
        $courseId = (int)$courseId;
        if ($moduleId <= 0) {
            return [];
        }

        $c = $this->modx->newQuery('TrainingTestLink');
        $c->where(['module_id' => $moduleId]);
        if ($courseId > 0) {
            $c->where(['course_id' => $courseId]);
        }
        $c->sortby('sort_order', 'ASC');
        $c->sortby('id', 'ASC');

        return $this->modx->getCollection('TrainingTestLink', $c);
    }

    protected function getUserTestStatusObject($courseId, $moduleId, $testId, $userId)
    {
        $courseId = (int)$courseId;
        $moduleId = (int)$moduleId;
        $testId = (int)$testId;
        $userId = (int)$userId;
        if ($courseId <= 0 || $moduleId <= 0 || $testId <= 0 || $userId <= 0) {
            return null;
        }

        /** @var TrainingUserTestStatus $status */
        $status = $this->modx->getObject('TrainingUserTestStatus', [
            'course_id' => $courseId,
            'module_id' => $moduleId,
            'usertest_test_id' => $testId,
            'user_id' => $userId,
        ]);

        if (!$status) {
            $status = $this->modx->newObject('TrainingUserTestStatus');
            $status->fromArray([
                'course_id' => $courseId,
                'module_id' => $moduleId,
                'usertest_test_id' => $testId,
                'user_id' => $userId,
                'last_result_id' => 0,
                'attempts' => 0,
                'passed' => 0,
                'status' => 'not_started',
                'last_score' => 0,
                'last_passedon' => null,
                'updatedon' => null,
            ], '', true, true);
        }

        return $status;
    }

    public function syncUserTestStatus($courseId, $moduleId, $testId, $userId)
    {
        $courseId = (int)$courseId;
        $moduleId = (int)$moduleId;
        $testId = (int)$testId;
        $userId = (int)$userId;
        if ($courseId <= 0 || $moduleId <= 0 || $testId <= 0 || $userId <= 0) {
            return null;
        }

        $status = $this->getUserTestStatusObject($courseId, $moduleId, $testId, $userId);
        if (!$status) {
            return null;
        }

        /** @var TrainingTestLink|null $link */
        $link = $this->modx->getObject('TrainingTestLink', [
            'course_id' => $courseId,
            'module_id' => $moduleId,
            'usertest_test_id' => $testId,
        ]);
        $linkType = $link ? $this->normalizeActivityLinkType($link->get('link_type')) : 'test';
        $minPassPercent = $link ? max(0, min(100, (float)$link->get('min_pass_percent'))) : 0;

        if ($linkType === 'practice') {
            $attemptTable = $this->modx->getOption('table_prefix') . 'partnerstraining_practice_attempts';
            $stmtExists = $this->modx->prepare('SHOW TABLES LIKE ?');
            if ($stmtExists) {
                $stmtExists->execute([$attemptTable]);
                $attemptTableExists = (bool)$stmtExists->fetchColumn();
                if ($attemptTableExists) {
                    $stmtCount = $this->modx->prepare('SELECT COUNT(*) FROM `' . $attemptTable . '` WHERE `course_id` = :course_id AND `module_id` = :module_id AND `user_id` = :user_id AND `test_link_id` = :test_link_id');
                    $stmtCount->execute([
                        ':course_id' => $courseId,
                        ':module_id' => $moduleId,
                        ':user_id' => $userId,
                        ':test_link_id' => (int)$link->get('id'),
                    ]);
                    $attempts = (int)$stmtCount->fetchColumn();

                    $stmtLatest = $this->modx->prepare('SELECT * FROM `' . $attemptTable . '` WHERE `course_id` = :course_id AND `module_id` = :module_id AND `user_id` = :user_id AND `test_link_id` = :test_link_id ORDER BY `attempt_no` DESC, `id` DESC LIMIT 1');
                    $stmtLatest->execute([
                        ':course_id' => $courseId,
                        ':module_id' => $moduleId,
                        ':user_id' => $userId,
                        ':test_link_id' => (int)$link->get('id'),
                    ]);
                    $latestAttempt = $stmtLatest->fetch(PDO::FETCH_ASSOC);

                    if ($latestAttempt) {
                        $state = 'not_started';
                        $passed = 0;
                        $lastScore = isset($latestAttempt['score']) ? (float)$latestAttempt['score'] : 0;
                        $lastPassedOn = null;
                        $attemptStatus = trim((string)$latestAttempt['status']);

                        switch ($attemptStatus) {
                            case 'draft':
                                $state = 'in_progress';
                                break;
                            case 'submitted':
                            case 'pending_review':
                                $state = 'pending_review';
                                break;
                            case 'revision_requested':
                            case 'rejected':
                                $state = 'rejected';
                                break;
                            case 'accepted':
                                $state = 'accepted';
                                $passed = 1;
                                $lastPassedOn = !empty($latestAttempt['reviewedon']) ? $latestAttempt['reviewedon'] : $this->getNow();
                                break;
                            default:
                                $state = $attemptStatus !== '' ? $attemptStatus : 'not_started';
                                break;
                        }

                        $status->set('course_id', $courseId);
                        $status->set('module_id', $moduleId);
                        $status->set('usertest_test_id', $testId);
                        $status->set('user_id', $userId);
                        $status->set('last_result_id', (int)$latestAttempt['id']);
                        $status->set('attempts', $attempts);
                        $status->set('passed', $passed);
                        $status->set('status', $state);
                        $status->set('last_score', $lastScore);
                        $status->set('last_passedon', $lastPassedOn);
                        $status->set('updatedon', $this->getNow());
                        $status->save();

                        return $status;
                    }

                    if (!$status->get('id')) {
                        $status->save();
                    }
                    return $status;
                }
            }
        }

        if (!$this->ensureUserTestPackage()) {
            if (!$status->get('id')) {
                $status->save();
            }
            return $status;
        }

        $attempts = (int)$this->modx->getCount('UserTestResults', [
            'test_id' => $testId,
            'user_id' => $userId,
        ]);

        $q = $this->modx->newQuery('UserTestResults');
        $q->where([
            'test_id' => $testId,
            'user_id' => $userId,
        ]);
        $q->sortby('id', 'DESC');
        $q->limit(1);

        /** @var xPDOObject|UserTestResults|null $latest */
        $latest = $this->modx->getObject('UserTestResults', $q);
        if (!$latest) {
            if (!$status->get('id')) {
                $status->save();
            }
            return $status;
        }

        $latestId = (int)$latest->get('id');
        $statusId = (int)$latest->get('status_id');
        $variantId = (int)$latest->get('variant_id');
        $testPoint = (float)$latest->get('test_point');
        $maxPoint = (float)$latest->get('max_point');

        $score = 0;
        if ($maxPoint > 0) {
            $score = round(min(100, max(0, ($testPoint / $maxPoint) * 100)), 2);
        } elseif ($testPoint > 0) {
            $score = round($testPoint, 2);
        }

        $variantPassed = 0;
        if ($variantId > 0) {
            $variant = $this->modx->getObject('UserTestVariants', ['id' => $variantId]);
            if ($variant && (int)$variant->get('passed') === 1) {
                $variantPassed = 1;
            }
        }

        $passedByScore = false;
        if ($minPassPercent > 0) {
            $passedByScore = $score >= $minPassPercent;
        }

        $passed = 0;
        $state = 'not_started';

        if ($statusId === 1) {
            $state = 'in_progress';
        } elseif ($statusId === 3) {
            $state = $linkType === 'practice' ? 'pending_review' : 'checking';
        } elseif ($statusId === 2) {
            $resolvedPassed = false;
            if ($variantId > 0) {
                $resolvedPassed = $variantPassed === 1 || $passedByScore;
            } elseif ($minPassPercent > 0) {
                $resolvedPassed = $passedByScore;
            } else {
                $resolvedPassed = $testPoint > 0;
            }

            if ($linkType === 'practice') {
                $passed = $resolvedPassed ? 1 : 0;
                $state = $resolvedPassed ? 'accepted' : 'rejected';
            } else {
                $passed = $resolvedPassed ? 1 : 0;
                $state = $resolvedPassed ? 'passed' : 'failed';
            }
        }

        $passedOn = null;
        if ($passed) {
            $passedOn = $latest->get('date');
            if (!$passedOn || $passedOn === '0000-00-00 00:00:00') {
                $passedOn = $this->getNow();
            }
        }

        $status->set('course_id', $courseId);
        $status->set('module_id', $moduleId);
        $status->set('usertest_test_id', $testId);
        $status->set('user_id', $userId);
        $status->set('last_result_id', $latestId);
        $status->set('attempts', $attempts);
        $status->set('passed', $passed);
        $status->set('status', $state);
        $status->set('last_score', $score);
        $status->set('last_passedon', $passedOn);
        $status->set('updatedon', $this->getNow());
        $status->save();

        return $status;
    }


    public function syncModuleUserTestStatuses($courseId, $moduleId, $userId)
    {
        $rows = $this->getModuleActivityLinks($moduleId, $courseId);
        /** @var TrainingTestLink $row */
        foreach ($rows as $row) {
            $this->syncUserTestStatus($courseId, $moduleId, (int)$row->get('usertest_test_id'), $userId);
        }
    }

    public function getModuleActivityStatusMap($courseId, $moduleId, $userId)
    {
        $courseId = (int)$courseId;
        $moduleId = (int)$moduleId;
        $userId = (int)$userId;
        if ($courseId <= 0 || $moduleId <= 0 || $userId <= 0) {
            return [];
        }

        $this->syncModuleUserTestStatuses($courseId, $moduleId, $userId);

        $map = [];
        $items = $this->modx->getCollection('TrainingUserTestStatus', [
            'course_id' => $courseId,
            'module_id' => $moduleId,
            'user_id' => $userId,
        ]);

        /** @var TrainingUserTestStatus $item */
        foreach ($items as $item) {
            $map[(int)$item->get('usertest_test_id')] = $item;
        }

        return $map;
    }

    public function getModuleActivityRows($courseId, $moduleId, $userId)
    {
        $courseId = (int)$courseId;
        $moduleId = (int)$moduleId;
        $userId = (int)$userId;
        $rows = [];

        $links = $this->getModuleActivityLinks($moduleId, $courseId);
        $statusMap = $this->getModuleActivityStatusMap($courseId, $moduleId, $userId);
        $hasUserTest = $this->ensureUserTestPackage();

        /** @var TrainingTestLink $link */
        foreach ($links as $link) {
            $testId = (int)$link->get('usertest_test_id');
            $linkType = $this->normalizeActivityLinkType($link->get('link_type'));
            $status = isset($statusMap[$testId]) ? $statusMap[$testId] : null;
            $statusKey = $status ? trim((string)$status->get('status')) : 'not_started';
            if ($statusKey === '') {
                $statusKey = 'not_started';
            }

            $passed = $status ? ((int)$status->get('passed') === 1) : false;
            $started = $passed;
            if ($status) {
                $started = $started
                    || (int)$status->get('attempts') > 0
                    || (float)$status->get('last_score') > 0
                    || (string)$status->get('status') !== 'not_started';
            }

            $title = 'Активность #' . $testId;
            $description = '';
            $testType = 0;
            if ($hasUserTest) {
                $test = $this->modx->getObject('UserTestTests', ['id' => $testId]);
                if ($test) {
                    $testTitle = trim((string)$test->get('name'));
                    if ($testTitle !== '') {
                        $title = $testTitle;
                    }
                    $description = trim((string)$test->get('description'));
                    $testType = (int)$test->get('type');
                }
            }

            $rows[] = [
                'id' => (int)$link->get('id'),
                'course_id' => (int)$link->get('course_id'),
                'module_id' => (int)$link->get('module_id'),
                'usertest_test_id' => $testId,
                'title' => $title,
                'description' => $description,
                'link_type' => $linkType,
                'link_type_label' => $this->getActivityLinkTypeLabel($linkType),
                'sort_order' => (int)$link->get('sort_order'),
                'is_required' => (int)$link->get('is_required') === 1 ? 1 : 0,
                'max_attempts' => (int)$link->get('max_attempts'),
                'min_pass_percent' => (float)$link->get('min_pass_percent'),
                'block_next_module_until_passed' => (int)$link->get('block_next_module_until_passed') === 1 ? 1 : 0,
                'status_key' => $statusKey,
                'completed' => $passed ? 1 : 0,
                'passed' => $passed ? 1 : 0,
                'started' => $started ? 1 : 0,
                'attempts' => $status ? (int)$status->get('attempts') : 0,
                'last_score' => $status ? (float)$status->get('last_score') : 0,
                'test_type' => $testType,
            ];
        }

        return $rows;
    }

    public function getModuleActivityStats($courseId, $moduleId, $userId)
    {
        $stats = [
            'tests_total' => 0,
            'tests_passed' => 0,
            'tests_started' => 0,
            'practices_total' => 0,
            'practices_completed' => 0,
            'practices_started' => 0,
            'required_total' => 0,
            'required_completed' => 0,
            'required_started' => 0,
        ];

        $rows = $this->getModuleActivityRows($courseId, $moduleId, $userId);
        foreach ($rows as $row) {
            $isPractice = $row['link_type'] === 'practice';
            $completed = !empty($row['completed']);
            $started = !empty($row['started']);
            $required = !empty($row['is_required']);

            if ($isPractice) {
                $stats['practices_total']++;
                if ($completed) {
                    $stats['practices_completed']++;
                }
                if ($started) {
                    $stats['practices_started']++;
                }
            } else {
                $stats['tests_total']++;
                if ($completed) {
                    $stats['tests_passed']++;
                }
                if ($started) {
                    $stats['tests_started']++;
                }
            }

            if ($required) {
                $stats['required_total']++;
                if ($completed) {
                    $stats['required_completed']++;
                }
                if ($started) {
                    $stats['required_started']++;
                }
            }
        }

        return $stats;
    }

    public function getCourseActivityStats($courseId, $userId)
    {
        $stats = [
            'tests_total' => 0,
            'tests_passed' => 0,
            'tests_started' => 0,
            'practices_total' => 0,
            'practices_completed' => 0,
            'practices_started' => 0,
            'required_total' => 0,
            'required_completed' => 0,
            'required_started' => 0,
        ];

        $modules = $this->getCourseModules((int)$courseId, true, false);
        /** @var TrainingModule $module */
        foreach ($modules as $module) {
            $moduleStats = $this->getModuleActivityStats($courseId, (int)$module->get('id'), $userId);
            foreach ($stats as $key => $value) {
                $stats[$key] += (int)$moduleStats[$key];
            }
        }

        return $stats;
    }


    public function saveModuleProgress($courseId, $moduleId, $userId, array $data = [])
    {
        $courseId = (int)$courseId;
        $moduleId = (int)$moduleId;
        $userId = (int)$userId;

        if ($courseId <= 0 || $moduleId <= 0 || $userId <= 0) {
            return false;
        }

        /** @var TrainingModule $module */
        $module = $this->modx->getObject('TrainingModule', [
            'id' => $moduleId,
            'course_id' => $courseId,
        ]);
        if (!$module) {
            return false;
        }

        /** @var TrainingUserModuleProgress $progress */
        $progress = $this->modx->getObject('TrainingUserModuleProgress', [
            'module_id' => $moduleId,
            'user_id' => $userId,
        ]);

        if (!$progress) {
            $progress = $this->modx->newObject('TrainingUserModuleProgress');
            $progress->fromArray([
                'course_id' => $courseId,
                'module_id' => $moduleId,
                'user_id' => $userId,
                'status' => 'not_started',
                'current_time' => 0,
                'max_time' => 0,
                'watched_seconds' => 0,
                'duration_seconds' => max(0, (int)$module->get('duration_seconds')),
                'progress_percent' => 0,
                'completed' => 0,
                'completedon' => null,
                'last_watch' => null,
            ], '', true, true);
        }

        $duration = max(0, (int)$module->get('duration_seconds'));
        $currentTime = isset($data['current_time']) ? max(0, (int)$data['current_time']) : (int)$progress->get('current_time');
        if ($duration > 0 && $currentTime > $duration) {
            $currentTime = $duration;
        }

        $maxTime = max((int)$progress->get('max_time'), $currentTime, isset($data['max_time']) ? (int)$data['max_time'] : 0);
        if ($duration > 0 && $maxTime > $duration) {
            $maxTime = $duration;
        }

        $completed = !empty($data['completed']) ? 1 : 0;
        $progressPercent = 0;
        if ($duration > 0) {
            $progressPercent = round(($maxTime / $duration) * 100, 2);
            if ($progressPercent >= 90) {
                $completed = 1;
            }
        } elseif ($maxTime > 0) {
            $progressPercent = 100;
            $completed = 1;
        }

        $now = $this->getNow();
        $status = $completed ? 'completed' : ($maxTime > 0 ? 'in_progress' : 'not_started');

        $progress->set('course_id', $courseId);
        $progress->set('module_id', $moduleId);
        $progress->set('user_id', $userId);
        $progress->set('status', $status);
        $progress->set('current_time', $currentTime);
        $progress->set('max_time', $maxTime);
        $progress->set('watched_seconds', max((int)$progress->get('watched_seconds'), $maxTime));
        $progress->set('duration_seconds', $duration);
        $progress->set('progress_percent', $progressPercent);
        $progress->set('completed', $completed);
        $progress->set('completedon', $completed ? ($progress->get('completedon') ?: $now) : null);
        $progress->set('last_watch', $now);
        $progress->save();

        $this->recalculateUserCourse($courseId, $userId);
        return $progress;
    }

    public function recalculateUserCourse($courseId, $userId)
    {
        $courseId = (int)$courseId;
        $userId = (int)$userId;
        if ($courseId <= 0 || $userId <= 0) {
            return false;
        }

        /** @var TrainingUserCourse $userCourse */
        $userCourse = $this->ensureUserCourse($courseId, $userId, $this->resolveUserAccessRoleForCourse($courseId, $userId, 'employee'));
        if (!$userCourse) {
            return false;
        }

        $requiredModules = $this->getCourseModules($courseId, true, true);
        $requiredIds = [];
        /** @var TrainingModule $module */
        foreach ($requiredModules as $module) {
            $requiredIds[] = (int)$module->get('id');
        }

        if (empty($requiredIds)) {
            $allActiveModules = $this->getCourseModules($courseId, true, false);
            foreach ($allActiveModules as $module) {
                $requiredIds[] = (int)$module->get('id');
            }
        }

        $totalModules = count($requiredIds);
        $completedModules = 0;
        $currentModuleId = 0;
        $started = false;

        if ($totalModules > 0) {
            $progressItems = $this->modx->getCollection('TrainingUserModuleProgress', [
                'course_id' => $courseId,
                'user_id' => $userId,
            ]);

            $progressMap = [];
            /** @var TrainingUserModuleProgress $item */
            foreach ($progressItems as $item) {
                $progressMap[(int)$item->get('module_id')] = $item;
            }

            foreach ($requiredIds as $moduleId) {
                /** @var TrainingUserModuleProgress|null $item */
                $item = isset($progressMap[$moduleId]) ? $progressMap[$moduleId] : null;
                if ($item && (int)$item->get('completed') === 1) {
                    $completedModules++;
                }
                if ($item && ((int)$item->get('max_time') > 0 || (string)$item->get('status') !== 'not_started')) {
                    $started = true;
                }
                if ($currentModuleId <= 0 && (!$item || (int)$item->get('completed') !== 1)) {
                    $currentModuleId = $moduleId;
                }
            }
        }

        $progressPercent = $totalModules > 0 ? round(($completedModules / $totalModules) * 100, 2) : 0;
        $status = 'assigned';
        $completedOn = null;
        $startedOn = $userCourse->get('startedon');

        if ($completedModules > 0 || $started) {
            $status = 'in_progress';
            if (!$startedOn) {
                $startedOn = $this->getNow();
            }
        }

        if ($totalModules > 0 && $completedModules >= $totalModules) {
            $status = 'completed';
            $completedOn = $userCourse->get('completedon') ?: $this->getNow();
            $currentModuleId = 0;
        }

        $userCourse->set('access_role', $this->resolveUserAccessRoleForCourse($courseId, $userId, $userCourse->get('access_role')));
        $userCourse->set('status', $status);
        $userCourse->set('current_module_id', $currentModuleId);
        $userCourse->set('progress_percent', $progressPercent);
        $userCourse->set('completed_modules', $completedModules);
        $userCourse->set('total_modules', $totalModules);
        $userCourse->set('startedon', $startedOn ?: null);
        $userCourse->set('completedon', $completedOn);
        $userCourse->set('last_activity', $this->getNow());
        $userCourse->save();

        return $userCourse;
    }

    public function getUserObject($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return null;
        }

        if ($this->modx->user && (int)$this->modx->user->get('id') === $userId) {
            return $this->modx->user;
        }

        return $this->modx->getObject('modUser', ['id' => $userId]);
    }

    public function getUserLabel($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return '—';
        }

        $user = $this->getUserObject($userId);
        if (!$user) {
            return '—';
        }

        $profile = $user->getOne('Profile');
        $fullname = $profile ? trim((string)$profile->get('fullname')) : '';
        $email = $profile ? trim((string)$profile->get('email')) : '';
        $username = trim((string)$user->get('username'));

        $label = '#' . $userId . ' ' . $username;
        if ($fullname !== '') {
            $label .= ' (' . $fullname . ')';
        }
        if ($email !== '') {
            $label .= ' [' . $email . ']';
        }

        return $label;
    }

    public function isAdminUser($userId = 0)
    {
        $userId = (int)$userId;
        if ($userId <= 0 && $this->modx->user) {
            $userId = (int)$this->modx->user->get('id');
        }
        if ($userId <= 0) {
            return false;
        }

        if ($this->modx->user && (int)$this->modx->user->get('id') === $userId) {
            if ($this->modx->hasPermission('sudo') || $this->modx->hasPermission('settings')) {
                return true;
            }
        }

        $user = $this->getUserObject($userId);
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'isMember')) {
            if ($user->isMember('Administrator') || $user->isMember('Администратор')) {
                return true;
            }
        }

        return false;
    }

    public function getManagedUserIds($managerUserId, $onlyActive = true)
    {
        $managerUserId = (int)$managerUserId;
        if ($managerUserId <= 0) {
            return [];
        }

        $criteria = [
            'manager_user_id' => $managerUserId,
        ];
        if ($onlyActive) {
            $criteria['is_active'] = 1;
        }

        $ids = [];
        $items = $this->modx->getIterator('TrainingUserManagerLink', $criteria);
        foreach ($items as $item) {
            $employeeId = (int)$item->get('employee_user_id');
            if ($employeeId > 0) {
                $ids[$employeeId] = $employeeId;
            }
        }

        ksort($ids);
        return array_values($ids);
    }

    public function isManagerOfUser($managerUserId, $employeeUserId, $onlyActive = true)
    {
        $managerUserId = (int)$managerUserId;
        $employeeUserId = (int)$employeeUserId;
        if ($managerUserId <= 0 || $employeeUserId <= 0 || $managerUserId === $employeeUserId) {
            return false;
        }
    
        $criteria = [
            'manager_user_id' => $managerUserId,
            'employee_user_id' => $employeeUserId,
        ];
        if ($onlyActive) {
            $criteria['is_active'] = 1;
        }
    
        return $this->modx->getCount('TrainingUserManagerLink', $criteria) > 0;
    }
    
    /**
     * Может ли пользователь управлять назначениями по конкретному курсу
     * admin -> любой курс
     * director -> только курс, где у него есть доступ с ролью director
     * employee -> false
     */
    public function canManageCourse($courseId, $actorUserId)
    {
        $courseId = (int)$courseId;
        $actorUserId = (int)$actorUserId;
    
        if ($courseId <= 0 || $actorUserId <= 0) {
            return false;
        }
    
        if ($this->isAdminUser($actorUserId)) {
            return true;
        }
    
        if (!$this->hasCourseAccess($courseId, $actorUserId)) {
            return false;
        }
    
        return $this->resolveUserAccessRoleForCourse($courseId, $actorUserId, 'employee') === 'director';
    }
    /**
     * Список курсов, которыми текущий пользователь может управлять.
     * Понадобится для фронта страницы "Управление курсами".
     */
    public function getManageableCourseIds($actorUserId)
    {
        $actorUserId = (int)$actorUserId;
        if ($actorUserId <= 0) {
            return [];
        }
    
        $ids = [];
    
        if ($this->isAdminUser($actorUserId)) {
            $c = $this->modx->newQuery('TrainingCourse');
            $c->where(['is_active' => 1]);
            $c->select(['id']);
            if ($c->prepare() && $c->stmt->execute()) {
                while ($row = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                    $courseId = (int)$row['id'];
                    if ($courseId > 0) {
                        $ids[$courseId] = $courseId;
                    }
                }
            }
    
            ksort($ids);
            return array_values($ids);
        }
    
        $c = $this->modx->newQuery('TrainingUserCourse');
        $c->leftJoin('TrainingCourse', 'Course', 'Course.id = TrainingUserCourse.course_id');
        $c->where([
            'TrainingUserCourse.user_id' => $actorUserId,
            'TrainingUserCourse.access_role' => 'director',
            'TrainingUserCourse.status:!=' => 'revoked',
            'Course.is_active' => 1,
        ]);
        $c->select(['TrainingUserCourse.course_id']);
    
        if ($c->prepare() && $c->stmt->execute()) {
            while ($row = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                $courseId = (int)$row['course_id'];
                if ($courseId > 0 && $this->canManageCourse($courseId, $actorUserId)) {
                    $ids[$courseId] = $courseId;
                }
            }
        }
    
        ksort($ids);
        return array_values($ids);
    }
    /**
     * Можно ли назначить конкретный курс конкретному пользователю
     */
    public function canAssignCourseToUser($courseId, $actorUserId, $targetUserId)
    {
        $courseId = (int)$courseId;
        $actorUserId = (int)$actorUserId;
        $targetUserId = (int)$targetUserId;
    
        if ($courseId <= 0 || $actorUserId <= 0 || $targetUserId <= 0) {
            return false;
        }
    
        if ($this->isAdminUser($actorUserId)) {
            return true;
        }
    
        if (!$this->canManageCourse($courseId, $actorUserId)) {
            return false;
        }
    
        if ($actorUserId === $targetUserId) {
            return true;
        }
    
        return $this->isManagerOfUser($actorUserId, $targetUserId, true);
    }

    /**
     * Пользователи, которых можно назначать по конкретному курсу
     */
    public function getAssignableUserIds($actorUserId, $courseId = 0, $includeSelf = true)
    {
        $actorUserId = (int)$actorUserId;
        $courseId = (int)$courseId;
    
        if ($actorUserId <= 0) {
            return [];
        }
    
        if ($this->isAdminUser($actorUserId)) {
            $ids = [];
            $users = $this->modx->getIterator('modUser');
            foreach ($users as $user) {
                $uid = (int)$user->get('id');
                if ($uid > 0) {
                    $ids[$uid] = $uid;
                }
            }
            ksort($ids);
            return array_values($ids);
        }
    
        if ($courseId > 0 && !$this->canManageCourse($courseId, $actorUserId)) {
            return [];
        }
    
        $ids = array_fill_keys($this->getManagedUserIds($actorUserId, true), true);
    
        if ($includeSelf) {
            $ids[$actorUserId] = true;
        }
    
        $result = array_keys($ids);
        sort($result, SORT_NUMERIC);
    
        return $result;
    }

    public function getDirectUserAccess($courseId, $userId)
    {
        $courseId = (int)$courseId;
        $userId = (int)$userId;
        if ($courseId <= 0 || $userId <= 0) {
            return null;
        }
    
        return $this->modx->getObject('TrainingCourseAccess', [
            'course_id' => $courseId,
            'principal_type' => 'user',
            'principal_id' => $userId,
        ]);
    }

    public function assignCourseAccessToUser($courseId, $targetUserId, $actorUserId, array $options = [])
    {
        $courseId = (int)$courseId;
        $targetUserId = (int)$targetUserId;
        $actorUserId = (int)$actorUserId;
    
        if ($courseId <= 0 || $targetUserId <= 0 || $actorUserId <= 0) {
            return [
                'success' => false,
                'message' => 'Некорректные параметры назначения',
            ];
        }
    
        if (!$this->canAssignCourseToUser($courseId, $actorUserId, $targetUserId)) {
            return [
                'success' => false,
                'message' => 'Недостаточно прав для назначения этого курса этому пользователю',
            ];
        }
    
        $accessRole = isset($options['access_role'])
            ? $this->normalizeRole($options['access_role'])
            : 'employee';
    
        // Не-админ не может назначать director другим людям
        if (!$this->isAdminUser($actorUserId) && $actorUserId !== $targetUserId) {
            $accessRole = 'employee';
        }
    
        $activeFrom = isset($options['active_from']) ? $this->normalizeDateTimeValue($options['active_from']) : null;
        $activeTo = isset($options['active_to']) ? $this->normalizeDateTimeValue($options['active_to']) : null;
        $isActive = isset($options['is_active']) ? (int)((string)$options['is_active'] === '0' ? 0 : 1) : 1;
    
        /** @var TrainingCourseAccess $access */
        $access = $this->getDirectUserAccess($courseId, $targetUserId);
        $created = false;
    
        if (!$access) {
            $access = $this->modx->newObject('TrainingCourseAccess');
            $created = true;
        }
    
        $access->fromArray([
            'course_id' => $courseId,
            'principal_type' => 'user',
            'principal_id' => $targetUserId,
            'access_role' => $accessRole,
            'is_active' => $isActive,
            'active_from' => $activeFrom,
            'active_to' => $activeTo,
            'assigned_by' => $actorUserId,
        ], '', true, true);
    
        if ($created && !$access->get('createdon')) {
            $access->set('createdon', $this->getNow());
        }
    
        if (!$access->save()) {
            return [
                'success' => false,
                'message' => 'Не удалось сохранить назначение курса',
            ];
        }
    
        $this->syncUserCourseForUser($courseId, $targetUserId);
    
        return [
            'success' => true,
            'created' => $created,
            'access' => $access,
            'user_course' => $this->modx->getObject('TrainingUserCourse', [
                'course_id' => $courseId,
                'user_id' => $targetUserId,
            ]),
        ];
    }

    public function revokeCourseAccessForUser($courseId, $targetUserId, $actorUserId)
    {
        $courseId = (int)$courseId;
        $targetUserId = (int)$targetUserId;
        $actorUserId = (int)$actorUserId;
    
        if ($courseId <= 0 || $targetUserId <= 0 || $actorUserId <= 0) {
            return [
                'success' => false,
                'message' => 'Некорректные параметры снятия доступа',
            ];
        }
    
        if (!$this->canAssignCourseToUser($courseId, $actorUserId, $targetUserId)) {
            return [
                'success' => false,
                'message' => 'Недостаточно прав для снятия доступа по этому курсу',
            ];
        }
    
        /** @var TrainingCourseAccess $access */
        $access = $this->getDirectUserAccess($courseId, $targetUserId);
        if ($access) {
            $access->remove();
        }
    
        $userCourse = $this->syncUserCourseForUser($courseId, $targetUserId);
    
        return [
            'success' => true,
            'removed' => (bool)$access,
            'has_access_now' => $this->hasCourseAccess($courseId, $targetUserId),
            'user_course' => $userCourse,
        ];
    }

    public function getMyCourses($userId, array $options = [])
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return [];
        }

        $includeRevoked = !empty($options['include_revoked']);
        $recalculate = !empty($options['recalculate']);
        $limit = isset($options['limit']) ? max(0, (int)$options['limit']) : 0;
        $start = isset($options['start']) ? max(0, (int)$options['start']) : 0;

        $c = $this->modx->newQuery('TrainingUserCourse');
        $c->where([
            'TrainingUserCourse.user_id' => $userId,
        ]);
        if (!$includeRevoked) {
            $c->where([
                'TrainingUserCourse.status:!=' => 'revoked',
            ]);
        }

        $c->leftJoin('TrainingCourse', 'Course', 'Course.id = TrainingUserCourse.course_id');
        $c->leftJoin('modResource', 'Resource', 'Resource.id = Course.resource_id');
        $c->select([
            $this->modx->getSelectColumns('TrainingUserCourse', 'TrainingUserCourse'),
            'Course.resource_id AS resource_id',
            'Course.is_active AS course_is_active',
            'Course.is_sequential AS is_sequential',
            'Resource.pagetitle AS pagetitle',
            'Resource.longtitle AS longtitle',
            'Resource.description AS description',
            'Resource.uri AS uri',
            'Resource.published AS published',
            'Resource.menuindex AS menuindex',
        ]);
        $c->sortby('Resource.menuindex', 'ASC');
        $c->sortby('TrainingUserCourse.course_id', 'ASC');
        if ($limit > 0) {
            $c->limit($limit, $start);
        }

        $rows = [];
        if ($c->prepare() && $c->stmt->execute()) {
            while ($row = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                $courseId = (int)$row['course_id'];
                if ($recalculate && $courseId > 0) {
                    $this->recalculateUserCourse($courseId, $userId);
                    $userCourse = $this->modx->getObject('TrainingUserCourse', [
                        'course_id' => $courseId,
                        'user_id' => $userId,
                    ]);
                    if ($userCourse) {
                        $row = array_merge($row, $userCourse->toArray());
                    }
                }

                $rows[] = [
                    'course_id' => $courseId,
                    'resource_id' => (int)$row['resource_id'],
                    'pagetitle' => (string)$row['pagetitle'],
                    'longtitle' => (string)$row['longtitle'],
                    'description' => (string)$row['description'],
                    'uri' => (string)$row['uri'],
                    'published' => (int)$row['published'],
                    'course_is_active' => (int)$row['course_is_active'],
                    'is_sequential' => (int)$row['is_sequential'],
                    'access_role' => $this->normalizeRole($row['access_role']),
                    'status' => (string)$row['status'],
                    'current_module_id' => (int)$row['current_module_id'],
                    'progress_percent' => (float)$row['progress_percent'],
                    'completed_modules' => (int)$row['completed_modules'],
                    'total_modules' => (int)$row['total_modules'],
                    'startedon' => $row['startedon'],
                    'completedon' => $row['completedon'],
                    'last_activity' => $row['last_activity'],
                ];
            }
        }

        return $rows;
    }

    public function getLessonCompletionThresholdPercent()
    {
        return 90;
    }

    public function getModuleByResourceId($resourceId)
    {
        $resourceId = (int)$resourceId;
        if ($resourceId <= 0) {
            return null;
        }

        return $this->modx->getObject('TrainingModule', array(
            'resource_id' => $resourceId,
        ));
    }

    public function lessonHasActiveVideo($lessonId)
    {
        $lessonId = (int)$lessonId;
        if ($lessonId <= 0) {
            return false;
        }

        return $this->modx->getCount('TrainingModuleVideo', array(
            'lesson_id' => $lessonId,
            'is_active' => 1,
        )) > 0;
    }

    public function getModuleLessons($moduleId, $onlyActive = true, $onlyWithVideo = false)
    {
        $moduleId = (int)$moduleId;
        if ($moduleId <= 0) {
            return array();
        }

        $c = $this->modx->newQuery('TrainingModuleLesson');
        $c->where(array(
            'module_id' => $moduleId,
        ));
        if ($onlyActive) {
            $c->where(array(
                'is_active' => 1,
            ));
        }
        $c->sortby('sort_order', 'ASC');
        $c->sortby('id', 'ASC');

        $items = $this->modx->getCollection('TrainingModuleLesson', $c);
        if (!$onlyWithVideo) {
            return $items;
        }

        $result = array();
        /** @var TrainingModuleLesson $item */
        foreach ($items as $item) {
            if ($this->lessonHasActiveVideo((int)$item->get('id'))) {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function getLessonVideoItems($lessonId)
    {
        $lessonId = (int)$lessonId;
        if ($lessonId <= 0) {
            return array();
        }

        $c = $this->modx->newQuery('TrainingModuleVideo');
        $c->where(array(
            'lesson_id' => $lessonId,
            'is_active' => 1,
        ));
        $c->sortby('is_default', 'DESC');
        $c->sortby('height', 'DESC');
        $c->sortby('width', 'DESC');
        $c->sortby('bitrate', 'DESC');
        $c->sortby('id', 'ASC');

        return $this->modx->getCollection('TrainingModuleVideo', $c);
    }

    public function getLessonDefaultVideo($lessonId)
    {
        $items = $this->getLessonVideoItems($lessonId);
        if (empty($items)) {
            return null;
        }

        foreach ($items as $item) {
            if ((int)$item->get('is_default') === 1) {
                return $item;
            }
        }

        return reset($items);
    }

    public function getLessonSlides($lessonId)
    {
        $lessonId = (int)$lessonId;
        if ($lessonId <= 0) {
            return array();
        }

        $c = $this->modx->newQuery('TrainingModuleSlide');
        $c->where(array(
            'lesson_id' => $lessonId,
            'is_active' => 1,
        ));
        $c->sortby('slide_no', 'ASC');
        $c->sortby('timecode_ms', 'ASC');
        $c->sortby('id', 'ASC');

        return $this->modx->getCollection('TrainingModuleSlide', $c);
    }

    public function getLessonProgress($courseId, $lessonId, $userId)
    {
        $courseId = (int)$courseId;
        $lessonId = (int)$lessonId;
        $userId = (int)$userId;
        if ($courseId <= 0 || $lessonId <= 0 || $userId <= 0) {
            return null;
        }

        return $this->modx->getObject('TrainingUserLessonProgress', array(
            'course_id' => $courseId,
            'lesson_id' => $lessonId,
            'user_id' => $userId,
        ));
    }

    public function ensureLessonProgress($courseId, $lessonId, $userId, $durationSeconds = 0)
    {
        $courseId = (int)$courseId;
        $lessonId = (int)$lessonId;
        $userId = (int)$userId;
        $durationSeconds = max(0, (int)$durationSeconds);

        if ($courseId <= 0 || $lessonId <= 0 || $userId <= 0) {
            return null;
        }

        /** @var TrainingUserLessonProgress $progress */
        $progress = $this->getLessonProgress($courseId, $lessonId, $userId);
        if (!$progress) {
            $progress = $this->modx->newObject('TrainingUserLessonProgress');
            $progress->fromArray(array(
                'course_id' => $courseId,
                'lesson_id' => $lessonId,
                'user_id' => $userId,
                'status' => 'not_started',
                'current_time' => 0,
                'max_time' => 0,
                'watched_seconds' => 0,
                'duration_seconds' => $durationSeconds,
                'progress_percent' => 0,
                'completed' => 0,
                'completedon' => null,
                'last_watch' => null,
            ), '', true, true);
            $progress->save();
        } elseif ($durationSeconds > 0 && (int)$progress->get('duration_seconds') <= 0) {
            $progress->set('duration_seconds', $durationSeconds);
            $progress->save();
        }

        return $progress;
    }

    public function getLessonSequence($moduleId, $onlyWithVideo = true)
    {
        $items = $this->getModuleLessons($moduleId, true, $onlyWithVideo);
        $result = array();
        foreach ($items as $item) {
            $result[] = (int)$item->get('id');
        }
        return $result;
    }

    public function getPreviousLessonId($moduleId, $lessonId, $onlyWithVideo = true)
    {
        $moduleId = (int)$moduleId;
        $lessonId = (int)$lessonId;
        $sequence = $this->getLessonSequence($moduleId, $onlyWithVideo);
        $count = count($sequence);
        for ($i = 0; $i < $count; $i++) {
            if ((int)$sequence[$i] === $lessonId) {
                return $i > 0 ? (int)$sequence[$i - 1] : 0;
            }
        }
        return 0;
    }

    public function getNextLessonId($moduleId, $lessonId, $onlyWithVideo = true)
    {
        $moduleId = (int)$moduleId;
        $lessonId = (int)$lessonId;
        $sequence = $this->getLessonSequence($moduleId, $onlyWithVideo);
        $count = count($sequence);
        for ($i = 0; $i < $count; $i++) {
            if ((int)$sequence[$i] === $lessonId) {
                return $i < ($count - 1) ? (int)$sequence[$i + 1] : 0;
            }
        }
        return 0;
    }

    public function canAccessModule($courseId, $moduleId, $userId)
    {
        $courseId = (int)$courseId;
        $moduleId = (int)$moduleId;
        $userId = (int)$userId;

        if ($courseId <= 0 || $moduleId <= 0 || $userId <= 0) {
            return false;
        }

        if (!$this->hasCourseAccess($courseId, $userId)) {
            return false;
        }

        /** @var TrainingCourse $course */
        $course = $this->modx->getObject('TrainingCourse', array(
            'id' => $courseId,
            'is_active' => 1,
        ));
        if (!$course) {
            return false;
        }

        if (!(int)$course->get('is_sequential')) {
            return true;
        }

        $modules = $this->getCourseModules($courseId, true, false);
        /** @var TrainingModule $module */
        foreach ($modules as $module) {
            $currentId = (int)$module->get('id');
            if ($currentId === $moduleId) {
                return true;
            }

            $lessons = $this->getModuleLessons($currentId, true, true);
            if (empty($lessons)) {
                continue;
            }

            /** @var TrainingUserModuleProgress $progress */
            $progress = $this->modx->getObject('TrainingUserModuleProgress', array(
                'course_id' => $courseId,
                'module_id' => $currentId,
                'user_id' => $userId,
            ));
            if (!$progress || (int)$progress->get('completed') !== 1) {
                return false;
            }
        }

        return false;
    }

    public function canAccessLesson($courseId, $moduleId, $lessonId, $userId)
    {
        $courseId = (int)$courseId;
        $moduleId = (int)$moduleId;
        $lessonId = (int)$lessonId;
        $userId = (int)$userId;

        if ($courseId <= 0 || $moduleId <= 0 || $lessonId <= 0 || $userId <= 0) {
            return false;
        }

        if (!$this->canAccessModule($courseId, $moduleId, $userId)) {
            return false;
        }

        /** @var TrainingModuleLesson $lesson */
        $lesson = $this->modx->getObject('TrainingModuleLesson', array(
            'id' => $lessonId,
            'module_id' => $moduleId,
            'is_active' => 1,
        ));
        if (!$lesson || !$this->lessonHasActiveVideo($lessonId)) {
            return false;
        }

        $sequence = $this->getLessonSequence($moduleId, true);
        $exists = false;
        foreach ($sequence as $sequenceLessonId) {
            $sequenceLessonId = (int)$sequenceLessonId;
            if ($sequenceLessonId === $lessonId) {
                $exists = true;
                break;
            }

            /** @var TrainingUserLessonProgress $progress */
            $progress = $this->getLessonProgress($courseId, $sequenceLessonId, $userId);
            if (!$progress || (int)$progress->get('completed') !== 1) {
                return false;
            }
        }

        return $exists;
    }

    public function getRecommendedLessonId($courseId, $moduleId, $userId)
    {
        $courseId = (int)$courseId;
        $moduleId = (int)$moduleId;
        $userId = (int)$userId;

        $lessons = $this->getModuleLessons($moduleId, true, true);
        if (empty($lessons)) {
            return 0;
        }

        /** @var TrainingModuleLesson $lesson */
        foreach ($lessons as $lesson) {
            $lessonId = (int)$lesson->get('id');
            if (!$this->canAccessLesson($courseId, $moduleId, $lessonId, $userId)) {
                continue;
            }

            $progress = $this->getLessonProgress($courseId, $lessonId, $userId);
            if (!$progress || (int)$progress->get('completed') !== 1) {
                return $lessonId;
            }
        }

        $lastAvailable = 0;
        foreach ($lessons as $lesson) {
            $lessonId = (int)$lesson->get('id');
            if ($this->canAccessLesson($courseId, $moduleId, $lessonId, $userId)) {
                $lastAvailable = $lessonId;
            }
        }

        return $lastAvailable;
    }

    public function getFirstAccessibleModuleId($courseId, $userId)
    {
        $courseId = (int)$courseId;
        $userId = (int)$userId;
        if ($courseId <= 0 || $userId <= 0) {
            return 0;
        }

        $firstPlayable = 0;
        $modules = $this->getCourseModules($courseId, true, false);
        /** @var TrainingModule $module */
        foreach ($modules as $module) {
            $moduleId = (int)$module->get('id');
            if (!$this->canAccessModule($courseId, $moduleId, $userId)) {
                continue;
            }

            $lessons = $this->getModuleLessons($moduleId, true, true);
            if (!empty($lessons)) {
                return $moduleId;
            }

            if ($firstPlayable <= 0) {
                $firstPlayable = $moduleId;
            }
        }

        return $firstPlayable;
    }

    public function resolvePlayerContext($moduleResourceId, $lessonId, $userId)
    {
        $moduleResourceId = (int)$moduleResourceId;
        $lessonId = (int)$lessonId;
        $userId = (int)$userId;

        $module = $this->getModuleByResourceId($moduleResourceId);
        if (!$module) {
            return array(
                'success' => false,
                'message' => 'Модуль не найден',
            );
        }

        $courseId = (int)$module->get('course_id');
        if (!$this->hasCourseAccess($courseId, $userId)) {
            return array(
                'success' => false,
                'message' => 'Нет доступа к курсу',
                'code' => 403,
            );
        }

        $resolvedModule = $module;
        $resolvedModuleId = (int)$resolvedModule->get('id');
        $redirected = false;

        if (!$this->canAccessModule($courseId, $resolvedModuleId, $userId)) {
            $fallbackModuleId = $this->getFirstAccessibleModuleId($courseId, $userId);
            if ($fallbackModuleId > 0) {
                $resolvedModule = $this->modx->getObject('TrainingModule', array('id' => $fallbackModuleId));
                $resolvedModuleId = $resolvedModule ? (int)$resolvedModule->get('id') : 0;
                $redirected = true;
            }
        }

        if (!$resolvedModule || $resolvedModuleId <= 0) {
            return array(
                'success' => false,
                'message' => 'Нет доступных модулей',
            );
        }

        $resolvedLessonId = $lessonId;
        $lesson = null;
        if ($resolvedLessonId > 0) {
            $lesson = $this->modx->getObject('TrainingModuleLesson', array(
                'id' => $resolvedLessonId,
                'module_id' => $resolvedModuleId,
                'is_active' => 1,
            ));
        }

        if (!$lesson || !$this->lessonHasActiveVideo($resolvedLessonId) || !$this->canAccessLesson($courseId, $resolvedModuleId, $resolvedLessonId, $userId)) {
            $resolvedLessonId = $this->getRecommendedLessonId($courseId, $resolvedModuleId, $userId);
            $lesson = $resolvedLessonId > 0 ? $this->modx->getObject('TrainingModuleLesson', array(
                'id' => $resolvedLessonId,
                'module_id' => $resolvedModuleId,
                'is_active' => 1,
            )) : null;
            $redirected = true;
        }

        if (!$lesson) {
            return array(
                'success' => false,
                'message' => 'Нет доступных уроков',
            );
        }

        return array(
            'success' => true,
            'course_id' => $courseId,
            'module' => $resolvedModule,
            'lesson' => $lesson,
            'requested_module_resource_id' => $moduleResourceId,
            'requested_lesson_id' => $lessonId,
            'resolved_module_resource_id' => (int)$resolvedModule->get('resource_id'),
            'resolved_lesson_id' => (int)$lesson->get('id'),
            'redirected' => $redirected ? 1 : 0,
        );
    }

    public function recalculateModuleProgressFromLessons($courseId, $moduleId, $userId)
    {
        $courseId = (int)$courseId;
        $moduleId = (int)$moduleId;
        $userId = (int)$userId;

        if ($courseId <= 0 || $moduleId <= 0 || $userId <= 0) {
            return false;
        }

        /** @var TrainingModule $module */
        $module = $this->modx->getObject('TrainingModule', array(
            'id' => $moduleId,
            'course_id' => $courseId,
        ));
        if (!$module) {
            return false;
        }

        /** @var TrainingUserModuleProgress $progress */
        $progress = $this->modx->getObject('TrainingUserModuleProgress', array(
            'course_id' => $courseId,
            'module_id' => $moduleId,
            'user_id' => $userId,
        ));
        if (!$progress) {
            $progress = $this->modx->newObject('TrainingUserModuleProgress');
            $progress->fromArray(array(
                'course_id' => $courseId,
                'module_id' => $moduleId,
                'user_id' => $userId,
                'status' => 'not_started',
                'current_time' => 0,
                'max_time' => 0,
                'watched_seconds' => 0,
                'duration_seconds' => 0,
                'progress_percent' => 0,
                'completed' => 0,
                'completedon' => null,
                'last_watch' => null,
            ), '', true, true);
        }

        $lessons = $this->getModuleLessons($moduleId, true, true);
        $lessonIds = array();
        $durationSeconds = 0;
        foreach ($lessons as $lesson) {
            $lessonIds[] = (int)$lesson->get('id');
            $durationSeconds += max(0, (int)$lesson->get('duration_seconds'));
        }

        $progressMap = array();
        if (!empty($lessonIds)) {
            $items = $this->modx->getCollection('TrainingUserLessonProgress', array(
                'course_id' => $courseId,
                'user_id' => $userId,
                'lesson_id:IN' => $lessonIds,
            ));
            /** @var TrainingUserLessonProgress $item */
            foreach ($items as $item) {
                $progressMap[(int)$item->get('lesson_id')] = $item;
            }
        }

        $completedLessons = 0;
        $started = false;
        $watchedSeconds = 0;
        $sumPercent = 0;
        $lessonProgressUnits = 0;
        $currentTime = 0;
        $maxTime = 0;

        foreach ($lessons as $lesson) {
            $lessonId = (int)$lesson->get('id');
            $lessonProgress = isset($progressMap[$lessonId]) ? $progressMap[$lessonId] : null;
            if ($lessonProgress) {
                $lessonPercent = max(0, min(100, (float)$lessonProgress->get('progress_percent')));
                $watchedSeconds += max(0, (int)$lessonProgress->get('max_time'));
                $sumPercent += $lessonPercent;
                $lessonProgressUnits += $lessonPercent / 100;
                if ((int)$lessonProgress->get('completed') === 1) {
                    $completedLessons++;
                }
                if ((int)$lessonProgress->get('max_time') > 0 || (string)$lessonProgress->get('status') !== 'not_started') {
                    $started = true;
                }
                if ($currentTime <= 0 && (int)$lessonProgress->get('completed') !== 1) {
                    $currentTime = max(0, (int)$lessonProgress->get('current_time'));
                    $maxTime = max(0, (int)$lessonProgress->get('max_time'));
                }
            }
        }

        $lessonCount = count($lessons);
        $activityStats = $this->getModuleActivityStats($courseId, $moduleId, $userId);
        $activityTotal = (int)$activityStats['tests_total'] + (int)$activityStats['practices_total'];
        $activityCompleted = (int)$activityStats['tests_passed'] + (int)$activityStats['practices_completed'];
        $activityStarted = (int)$activityStats['tests_started'] + (int)$activityStats['practices_started'];

        $trackItemsTotal = $lessonCount + $activityTotal;
        if ($trackItemsTotal > 0) {
            $progressPercent = round(min(100, (($lessonProgressUnits + $activityCompleted) / $trackItemsTotal) * 100), 2);
        } elseif ($durationSeconds > 0) {
            $progressPercent = round(min(100, ($watchedSeconds / $durationSeconds) * 100), 2);
        } elseif ($lessonCount > 0) {
            $progressPercent = round($sumPercent / $lessonCount, 2);
        } else {
            $progressPercent = 0;
        }

        // Временно не блокируем следующий модуль обязательными тестами/практиками.
        // Модуль считается завершённым, как только пользователь завершил все уроки с видео.
        $requiredItemsTotal = $lessonCount;
        $requiredItemsCompleted = $completedLessons;

        if ($requiredItemsTotal > 0) {
            $completed = ($requiredItemsCompleted >= $requiredItemsTotal) ? 1 : 0;
        } else {
            $fallbackRequiredTotal = (int)$activityStats['required_total'];
            $fallbackRequiredCompleted = (int)$activityStats['required_completed'];
            $completed = ($fallbackRequiredTotal > 0 && $fallbackRequiredCompleted >= $fallbackRequiredTotal) ? 1 : 0;
        }

        if ($completed) {
            $progressPercent = 100;
        }

        $status = 'not_started';
        if ($completed) {
            $status = 'completed';
        } elseif ($started || $progressPercent > 0 || $activityStarted > 0) {
            $status = 'in_progress';
        }

        $now = $this->getNow();
        $progress->set('course_id', $courseId);
        $progress->set('module_id', $moduleId);
        $progress->set('user_id', $userId);
        $progress->set('status', $status);
        $progress->set('current_time', $currentTime);
        $progress->set('max_time', $maxTime);
        $progress->set('watched_seconds', $watchedSeconds);
        $progress->set('duration_seconds', $durationSeconds);
        $progress->set('progress_percent', $progressPercent);
        $progress->set('completed', $completed);
        $progress->set('completedon', $completed ? ($progress->get('completedon') ?: $now) : null);
        $progress->set('last_watch', ($started || $completed) ? $now : null);
        $progress->save();

        $this->recalculateUserCourse($courseId, $userId);
        return $progress;
    }

    public function saveLessonProgress($courseId, $moduleId, $lessonId, $userId, array $data = array())
    {
        $courseId = (int)$courseId;
        $moduleId = (int)$moduleId;
        $lessonId = (int)$lessonId;
        $userId = (int)$userId;

        if ($courseId <= 0 || $moduleId <= 0 || $lessonId <= 0 || $userId <= 0) {
            return false;
        }

        /** @var TrainingModuleLesson $lesson */
        $lesson = $this->modx->getObject('TrainingModuleLesson', array(
            'id' => $lessonId,
            'module_id' => $moduleId,
            'is_active' => 1,
        ));
        if (!$lesson || !$this->lessonHasActiveVideo($lessonId)) {
            return false;
        }

        $duration = max(0, isset($data['duration_seconds']) ? (int)$data['duration_seconds'] : (int)$lesson->get('duration_seconds'));
        /** @var TrainingUserLessonProgress $progress */
        $progress = $this->ensureLessonProgress($courseId, $lessonId, $userId, $duration);
        if (!$progress) {
            return false;
        }

        $currentTime = isset($data['current_time']) ? max(0, (int)$data['current_time']) : (int)$progress->get('current_time');
        $inputMaxTime = isset($data['max_time']) ? max(0, (int)$data['max_time']) : 0;
        $maxTime = max((int)$progress->get('max_time'), $inputMaxTime, $currentTime);

        if ($duration > 0) {
            if ($currentTime > $duration) {
                $currentTime = $duration;
            }
            if ($maxTime > $duration) {
                $maxTime = $duration;
            }
        }

        $completed = !empty($data['completed']) ? 1 : 0;
        $progressPercent = 0;
        if ($duration > 0) {
            $progressPercent = round(min(100, ($maxTime / $duration) * 100), 2);
            if ($progressPercent >= $this->getLessonCompletionThresholdPercent()) {
                $completed = 1;
            }
        } elseif ($maxTime > 0) {
            $progressPercent = 100;
            $completed = 1;
        }

        $status = 'not_started';
        if ($completed) {
            $status = 'completed';
            $progressPercent = 100;
        } elseif ($maxTime > 0 || $currentTime > 0) {
            $status = 'in_progress';
        }

        $now = $this->getNow();
        $progress->set('course_id', $courseId);
        $progress->set('lesson_id', $lessonId);
        $progress->set('user_id', $userId);
        $progress->set('status', $status);
        $progress->set('current_time', $currentTime);
        $progress->set('max_time', $maxTime);
        $progress->set('watched_seconds', max((int)$progress->get('watched_seconds'), $maxTime));
        $progress->set('duration_seconds', $duration);
        $progress->set('progress_percent', $progressPercent);
        $progress->set('completed', $completed);
        $progress->set('completedon', $completed ? ($progress->get('completedon') ?: $now) : null);
        $progress->set('last_watch', $now);
        $progress->save();

        $this->recalculateModuleProgressFromLessons($courseId, $moduleId, $userId);
        return $progress;
    }

}

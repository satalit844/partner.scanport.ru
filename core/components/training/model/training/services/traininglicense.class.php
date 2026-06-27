<?php

/**
 * training-license-core-v1
 *
 * Лицензионный учёт для курсов:
 * reserved — место занято;
 * released — доступ закрыт до порога, место возвращено;
 * consumed — достигнуты 80% либо выдан сертификат.
 */
class TrainingLicenseService
{
    /** @var modX */
    protected $modx;

    /** @var TrainingProgressService */
    protected $progressService;

    /** @var array */
    protected $tables = array();

    /** @var array */
    protected static $instances = array();

    public static function getInstance(modX $modx, $progressService = null)
    {
        $key = spl_object_hash($modx);

        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($modx, $progressService);
        } elseif ($progressService && !self::$instances[$key]->progressService) {
            self::$instances[$key]->progressService = $progressService;
        }

        return self::$instances[$key];
    }

    public function __construct(modX $modx, $progressService = null)
    {
        /*
         * training-license-constructor-syntax-v3
         *
         * TrainingProgressService здесь не создаём автоматически:
         * в данном проекте его конструктор требует два аргумента.
         * При вызове из сервиса прогресса он передаётся вторым аргументом.
         */
        $this->modx = $modx;
        $this->progressService = $progressService;

        $this->tables = array(
            'course_access' => $this->resolveTable('TrainingCourseAccess', 'partnerstraining_course_access'),
            'assignments' => '',
            'manager_link' => $this->resolveTable('TrainingUserManagerLink', 'partnerstraining_user_manager_link'),
            'certificates' => '',
        );

        $courseAccessTable = (string)$this->tables['course_access'];
        $suffix = '_course_access';

        if (
            $courseAccessTable !== ''
            && substr($courseAccessTable, -strlen($suffix)) === $suffix
        ) {
            $prefix = substr($courseAccessTable, 0, -strlen($suffix));
            $this->tables['assignments'] = $prefix . '_license_assignments';
            $this->tables['certificates'] = $prefix . '_user_certificates';
        }
    }

    protected function resolveTable($className, $suffix)
    {
        $tables = array();
        $stmt = $this->modx->query('SHOW TABLES');

        if ($stmt) {
            foreach ((array)$stmt->fetchAll(PDO::FETCH_NUM) as $row) {
                if (!empty($row[0])) {
                    $tables[] = (string)$row[0];
                }
            }
        }

        try {
            $candidate = trim((string)$this->modx->getTableName($className), '`');
            if ($candidate !== '' && in_array($candidate, $tables, true)) {
                return $candidate;
            }
        } catch (Exception $e) {
        }

        foreach ($tables as $table) {
            if (preg_match('/(?:^|_)' . preg_quote($suffix, '/') . '$/', $table)) {
                return $table;
            }
        }

        return '';
    }

    protected function tableExists($table)
    {
        if ($table === '') {
            return false;
        }

        $stmt = $this->modx->prepare('SHOW TABLES LIKE :table_name');

        if (!$stmt) {
            return false;
        }

        $stmt->bindValue(':table_name', $table, PDO::PARAM_STR);
        $stmt->execute();

        return (bool)$stmt->fetchColumn();
    }

    protected function now()
    {
        return date('Y-m-d H:i:s');
    }

    protected function safeTable($table)
    {
        return str_replace('`', '``', (string)$table);
    }

    public function begin()
    {
        /* training-license-transaction-fix-v1 */
        return $this->modx->exec('START TRANSACTION') !== false;
    }

    public function commit()
    {
        /* training-license-transaction-fix-v1 */
        return $this->modx->exec('COMMIT') !== false;
    }

    public function rollback()
    {
        /* training-license-transaction-fix-v1 */
        return $this->modx->exec('ROLLBACK') !== false;
    }

    protected function getDirectorAccessRow($courseId, $directorUserId, $forUpdate = false)
    {
        $courseId = (int)$courseId;
        $directorUserId = (int)$directorUserId;

        if (
            $courseId <= 0 ||
            $directorUserId <= 0 ||
            !$this->tableExists($this->tables['course_access'])
        ) {
            return null;
        }

        $table = $this->safeTable($this->tables['course_access']);
        $sql = 'SELECT `id`,`course_id`,`principal_id`,`access_role`,`licenses_total`,`licenses_enabled`,`is_active` '
            . 'FROM `' . $table . '` '
            . 'WHERE `course_id` = :course_id '
            . 'AND `principal_type` = "user" '
            . 'AND `principal_id` = :director_user_id '
            . 'AND `access_role` = "director" '
            . 'ORDER BY `id` ASC LIMIT 1';

        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }

        $stmt = $this->modx->prepare($sql);

        if (!$stmt || !$stmt->execute(array(
            ':course_id' => $courseId,
            ':director_user_id' => $directorUserId,
        ))) {
            return null;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $row : null;
    }

    protected function getEmployeeDirectorIds($employeeUserId)
    {
        $employeeUserId = (int)$employeeUserId;
        $result = array();

        if (
            $employeeUserId <= 0 ||
            !$this->tableExists($this->tables['manager_link'])
        ) {
            return $result;
        }

        $table = $this->safeTable($this->tables['manager_link']);
        $stmt = $this->modx->prepare(
            'SELECT DISTINCT `manager_user_id` '
            . 'FROM `' . $table . '` '
            . 'WHERE `employee_user_id` = :employee_user_id '
            . 'AND `is_active` = 1 '
            . 'ORDER BY `manager_user_id` ASC'
        );

        if ($stmt && $stmt->execute(array(':employee_user_id' => $employeeUserId))) {
            foreach ((array)$stmt->fetchAll(PDO::FETCH_COLUMN) as $userId) {
                $userId = (int)$userId;
                if ($userId > 0) {
                    $result[] = $userId;
                }
            }
        }

        return array_values(array_unique($result));
    }

    public function resolveOwnerForAssignment($courseId, $employeeUserId, $actorUserId, array $options = array())
    {
        $courseId = (int)$courseId;
        $employeeUserId = (int)$employeeUserId;
        $actorUserId = (int)$actorUserId;
        $explicitOwnerId = isset($options['license_director_user_id'])
            ? (int)$options['license_director_user_id']
            : 0;

        if ($courseId <= 0 || $employeeUserId <= 0 || $actorUserId <= 0) {
            return array(
                'success' => false,
                'message' => 'Некорректные параметры лицензии',
            );
        }

        $isAdmin = $this->progressService
            ? (bool)$this->progressService->isAdminUser($actorUserId)
            : false;

        if ($explicitOwnerId > 0) {
            if (!$isAdmin && $explicitOwnerId !== $actorUserId) {
                return array(
                    'success' => false,
                    'message' => 'Нельзя использовать лицензию другого директора',
                );
            }

            $row = $this->getDirectorAccessRow($courseId, $explicitOwnerId);

            if (!$row) {
                return array(
                    'success' => false,
                    'message' => 'У выбранного директора нет доступа к этому курсу',
                );
            }

            return array(
                'success' => true,
                'owner' => $row,
                'mode' => 'explicit',
            );
        }

        $actorDirector = $this->getDirectorAccessRow($courseId, $actorUserId);

        if ($actorDirector) {
            return array(
                'success' => true,
                'owner' => $actorDirector,
                'mode' => 'actor_director',
            );
        }

        $candidates = array();
        foreach ($this->getEmployeeDirectorIds($employeeUserId) as $directorUserId) {
            $row = $this->getDirectorAccessRow($courseId, $directorUserId);
            if ($row) {
                $candidates[] = $row;
            }
        }

        if (count($candidates) === 1) {
            return array(
                'success' => true,
                'owner' => $candidates[0],
                'mode' => 'employee_director',
            );
        }

        if (count($candidates) > 1) {
            return array(
                'success' => false,
                'message' => 'У сотрудника несколько директоров на этом курсе. Выберите владельца лицензии в админке курса.',
                'needs_owner_selection' => true,
            );
        }

        /*
         * Нет директора — это старый/административный доступ без лицензии.
         * Он не влияет на остатки пулов.
         */
        return array(
            'success' => true,
            'owner' => null,
            'mode' => 'legacy_admin_free',
        );
    }

    public function isLicenseEnabledForOwner($owner)
    {
        return is_array($owner)
            && (int)(isset($owner['licenses_enabled']) ? $owner['licenses_enabled'] : 0) === 1
            && (int)(isset($owner['licenses_total']) ? $owner['licenses_total'] : 0) > 0;
    }

    protected function getTrackStats($courseId, $userId)
    {
        $courseId = (int)$courseId;
        $userId = (int)$userId;

        $videos = array(
            'total_videos' => 0,
            'completed_videos' => 0,
        );

        $helpersPath = rtrim((string)$this->modx->getOption(
            'training.core_path',
            null,
            $this->modx->getOption('core_path') . 'components/training/'
        ), '/\\') . '/processors/web/_helpers.php';

        if (!class_exists('TrainingWebHelper') && is_file($helpersPath)) {
            require_once $helpersPath;
        }

        if (class_exists('TrainingWebHelper') && $this->progressService) {
            try {
                $videoStats = TrainingWebHelper::getCourseVideoStats(
                    $this->modx,
                    $this->progressService,
                    $courseId,
                    $userId
                );

                if (is_array($videoStats)) {
                    $videos['total_videos'] = (int)(isset($videoStats['total_videos']) ? $videoStats['total_videos'] : 0);
                    $videos['completed_videos'] = (int)(isset($videoStats['completed_videos']) ? $videoStats['completed_videos'] : 0);
                }
            } catch (Exception $e) {
            }
        }

        $activity = $this->progressService
            ? $this->progressService->getCourseActivityStats($courseId, $userId)
            : array();

        $testsTotal = (int)(isset($activity['tests_total']) ? $activity['tests_total'] : 0);
        $testsPassed = (int)(isset($activity['tests_passed']) ? $activity['tests_passed'] : 0);
        $practicesTotal = (int)(isset($activity['practices_total']) ? $activity['practices_total'] : 0);
        $practicesCompleted = (int)(isset($activity['practices_completed']) ? $activity['practices_completed'] : 0);

        $total = max(0, $videos['total_videos']) + max(0, $testsTotal) + max(0, $practicesTotal);
        $completed = min(max(0, $videos['completed_videos']), max(0, $videos['total_videos']))
            + min(max(0, $testsPassed), max(0, $testsTotal))
            + min(max(0, $practicesCompleted), max(0, $practicesTotal));

        $percent = $total > 0 ? round(($completed / $total) * 100, 2) : 0.00;

        return array(
            'total_items' => $total,
            'completed_items' => $completed,
            'progress_percent' => max(0, min(100, $percent)),
            'videos_total' => (int)$videos['total_videos'],
            'videos_completed' => (int)$videos['completed_videos'],
            'tests_total' => $testsTotal,
            'tests_passed' => $testsPassed,
            'practices_total' => $practicesTotal,
            'practices_completed' => $practicesCompleted,
        );
    }

    protected function getCertificateId($courseId, $userId)
    {
        if (
            !$this->tableExists($this->tables['certificates']) ||
            (int)$courseId <= 0 ||
            (int)$userId <= 0
        ) {
            return 0;
        }

        $table = $this->safeTable($this->tables['certificates']);
        $stmt = $this->modx->prepare(
            'SELECT `id` FROM `' . $table . '` '
            . 'WHERE `course_id` = :course_id AND `user_id` = :user_id '
            . 'AND (`status` = "issued" OR `status` = "" OR `status` IS NULL) '
            . 'ORDER BY `id` DESC LIMIT 1'
        );

        if ($stmt && $stmt->execute(array(
            ':course_id' => (int)$courseId,
            ':user_id' => (int)$userId,
        ))) {
            return (int)$stmt->fetchColumn();
        }

        return 0;
    }

    protected function getActiveAllocation($courseId, $employeeUserId, $directorUserId = 0, $forUpdate = false)
    {
        if (
            !$this->tableExists($this->tables['assignments']) ||
            (int)$courseId <= 0 ||
            (int)$employeeUserId <= 0
        ) {
            return null;
        }

        $table = $this->safeTable($this->tables['assignments']);
        $sql = 'SELECT * FROM `' . $table . '` '
            . 'WHERE `course_id` = :course_id '
            . 'AND `employee_user_id` = :employee_user_id '
            . 'AND `state` IN ("reserved","consumed") '
            . 'AND `access_closedon` IS NULL ';

        $params = array(
            ':course_id' => (int)$courseId,
            ':employee_user_id' => (int)$employeeUserId,
        );

        if ((int)$directorUserId > 0) {
            $sql .= 'AND `director_user_id` = :director_user_id ';
            $params[':director_user_id'] = (int)$directorUserId;
        }

        $sql .= 'ORDER BY CASE WHEN `state` = "reserved" THEN 0 ELSE 1 END, `id` DESC LIMIT 1';

        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }

        $stmt = $this->modx->prepare($sql);

        if (!$stmt || !$stmt->execute($params)) {
            return null;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $row : null;
    }

    protected function getAnyConsumedAllocation($courseId, $employeeUserId, $directorUserId, $forUpdate = false)
    {
        if (
            !$this->tableExists($this->tables['assignments']) ||
            (int)$courseId <= 0 ||
            (int)$employeeUserId <= 0 ||
            (int)$directorUserId <= 0
        ) {
            return null;
        }

        $table = $this->safeTable($this->tables['assignments']);
        $sql = 'SELECT * FROM `' . $table . '` '
            . 'WHERE `course_id` = :course_id '
            . 'AND `employee_user_id` = :employee_user_id '
            . 'AND `director_user_id` = :director_user_id '
            . 'AND `state` = "consumed" '
            . 'ORDER BY `id` DESC LIMIT 1';

        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }

        $stmt = $this->modx->prepare($sql);

        if (!$stmt || !$stmt->execute(array(
            ':course_id' => (int)$courseId,
            ':employee_user_id' => (int)$employeeUserId,
            ':director_user_id' => (int)$directorUserId,
        ))) {
            return null;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $row : null;
    }

    protected function getPoolCounts($directorAccessId)
    {
        $result = array(
            'reserved' => 0,
            'consumed' => 0,
        );

        if (
            !$this->tableExists($this->tables['assignments']) ||
            (int)$directorAccessId <= 0
        ) {
            return $result;
        }

        $table = $this->safeTable($this->tables['assignments']);
        $stmt = $this->modx->prepare(
            'SELECT '
            . 'SUM(CASE WHEN `state` = "reserved" THEN 1 ELSE 0 END) AS `reserved_count`, '
            . 'SUM(CASE WHEN `state` = "consumed" THEN 1 ELSE 0 END) AS `consumed_count` '
            . 'FROM `' . $table . '` '
            . 'WHERE `director_access_id` = :director_access_id'
        );

        if ($stmt && $stmt->execute(array(':director_access_id' => (int)$directorAccessId))) {
            $row = (array)$stmt->fetch(PDO::FETCH_ASSOC);
            $result['reserved'] = (int)(isset($row['reserved_count']) ? $row['reserved_count'] : 0);
            $result['consumed'] = (int)(isset($row['consumed_count']) ? $row['consumed_count'] : 0);
        }

        return $result;
    }

    public function getDirectorSummary($courseId, $directorUserId)
    {
        $owner = $this->getDirectorAccessRow($courseId, $directorUserId);

        if (!$owner) {
            return array(
                'found' => false,
                'total' => 0,
                'reserved' => 0,
                'consumed' => 0,
                'free' => 0,
                'enabled' => false,
                'director_access_id' => 0,
            );
        }

        $counts = $this->getPoolCounts((int)$owner['id']);
        $total = max(0, (int)$owner['licenses_total']);
        $enabled = $this->isLicenseEnabledForOwner($owner);

        return array(
            'found' => true,
            'total' => $total,
            'reserved' => $counts['reserved'],
            'consumed' => $counts['consumed'],
            'free' => $enabled ? max(0, $total - $counts['reserved'] - $counts['consumed']) : 0,
            'enabled' => $enabled,
            'director_access_id' => (int)$owner['id'],
            'director_user_id' => (int)$owner['principal_id'],
        );
    }

    public function canDirectorPassCourse($courseId, $directorUserId, $directorAccessId)
    {
        $courseId = (int)$courseId;
        $directorUserId = (int)$directorUserId;
        $directorAccessId = (int)$directorAccessId;

        if ($courseId <= 0 || $directorUserId <= 0 || $directorAccessId <= 0) {
            return false;
        }

        $owner = $this->getDirectorAccessRow($courseId, $directorUserId);

        if (!$owner || (int)$owner['id'] !== $directorAccessId) {
            return false;
        }

        /*
         * Лицензионный учёт выключен: не меняем старое поведение директора.
         */
        if (!$this->isLicenseEnabledForOwner($owner)) {
            return true;
        }

        return (bool)$this->getActiveAllocation($courseId, $directorUserId, $directorUserId);
    }

    public function reserveForAccess($courseId, $employeeUserId, $actorUserId, $employeeAccessId, $userCourseId, array $owner)
    {
        $courseId = (int)$courseId;
        $employeeUserId = (int)$employeeUserId;
        $actorUserId = (int)$actorUserId;
        $employeeAccessId = (int)$employeeAccessId;
        $userCourseId = (int)$userCourseId;

        if (!$this->isLicenseEnabledForOwner($owner)) {
            return array(
                'success' => true,
                'licensed' => false,
                'state' => 'legacy',
                'message' => '',
            );
        }

        $directorAccessId = (int)$owner['id'];
        $directorUserId = (int)$owner['principal_id'];

        $lockedOwner = $this->getDirectorAccessRow($courseId, $directorUserId, true);

        if (!$lockedOwner) {
            return array(
                'success' => false,
                'message' => 'Не найден доступ директора для лицензии',
            );
        }

        if (!$this->isLicenseEnabledForOwner($lockedOwner)) {
            return array(
                'success' => false,
                'message' => 'У директора не настроен лимит лицензий по этому курсу',
            );
        }

        $active = $this->getActiveAllocation(
            $courseId,
            $employeeUserId,
            $directorUserId,
            true
        );

        if ($active) {
            $table = $this->safeTable($this->tables['assignments']);
            $stmt = $this->modx->prepare(
                'UPDATE `' . $table . '` SET '
                . '`employee_access_id` = :employee_access_id, '
                . '`user_course_id` = :user_course_id, '
                . '`assigned_by` = :assigned_by, '
                . '`updatedon` = :updatedon '
                . 'WHERE `id` = :id'
            );

            if ($stmt) {
                $stmt->execute(array(
                    ':employee_access_id' => $employeeAccessId,
                    ':user_course_id' => $userCourseId,
                    ':assigned_by' => $actorUserId,
                    ':updatedon' => $this->now(),
                    ':id' => (int)$active['id'],
                ));
            }

            return array(
                'success' => true,
                'licensed' => true,
                'state' => (string)$active['state'],
                'assignment_id' => (int)$active['id'],
                'reused' => true,
            );
        }

        /*
         * Закрытый consumed — это тот же уже оплаченный сотрудник.
         * При повторной активации доступ возвращается без второго списания.
         */
        $consumed = $this->getAnyConsumedAllocation(
            $courseId,
            $employeeUserId,
            $directorUserId,
            true
        );

        if ($consumed) {
            $table = $this->safeTable($this->tables['assignments']);
            $stmt = $this->modx->prepare(
                'UPDATE `' . $table . '` SET '
                . '`employee_access_id` = :employee_access_id, '
                . '`user_course_id` = :user_course_id, '
                . '`assigned_by` = :assigned_by, '
                . '`access_closedon` = NULL, '
                . '`updatedon` = :updatedon '
                . 'WHERE `id` = :id'
            );

            if (!$stmt || !$stmt->execute(array(
                ':employee_access_id' => $employeeAccessId,
                ':user_course_id' => $userCourseId,
                ':assigned_by' => $actorUserId,
                ':updatedon' => $this->now(),
                ':id' => (int)$consumed['id'],
            ))) {
                return array(
                    'success' => false,
                    'message' => 'Не удалось восстановить ранее списанную лицензию',
                );
            }

            return array(
                'success' => true,
                'licensed' => true,
                'state' => 'consumed',
                'assignment_id' => (int)$consumed['id'],
                'reused' => true,
            );
        }

        $counts = $this->getPoolCounts($directorAccessId);
        $free = max(
            0,
            (int)$lockedOwner['licenses_total']
            - (int)$counts['reserved']
            - (int)$counts['consumed']
        );

        if ($free <= 0) {
            return array(
                'success' => false,
                'message' => 'Нет свободных лицензий для этого курса',
                'summary' => array(
                    'total' => (int)$lockedOwner['licenses_total'],
                    'reserved' => (int)$counts['reserved'],
                    'consumed' => (int)$counts['consumed'],
                    'free' => 0,
                ),
            );
        }

        $table = $this->safeTable($this->tables['assignments']);
        $now = $this->now();
        $source = $directorUserId === $actorUserId ? 'director' : 'admin_director';

        $stmt = $this->modx->prepare(
            'INSERT INTO `' . $table . '` ('
            . '`director_access_id`,`course_id`,`director_user_id`,`employee_user_id`,'
            . '`employee_access_id`,`user_course_id`,`state`,`source`,`assigned_by`,'
            . '`reservedon`,`progress_percent`,`createdon`,`updatedon`'
            . ') VALUES ('
            . ':director_access_id,:course_id,:director_user_id,:employee_user_id,'
            . ':employee_access_id,:user_course_id,"reserved",:source,:assigned_by,'
            . ':reservedon,0,:createdon,:updatedon'
            . ')'
        );

        if (!$stmt || !$stmt->execute(array(
            ':director_access_id' => $directorAccessId,
            ':course_id' => $courseId,
            ':director_user_id' => $directorUserId,
            ':employee_user_id' => $employeeUserId,
            ':employee_access_id' => $employeeAccessId,
            ':user_course_id' => $userCourseId,
            ':source' => $source,
            ':assigned_by' => $actorUserId,
            ':reservedon' => $now,
            ':createdon' => $now,
            ':updatedon' => $now,
        ))) {
            return array(
                'success' => false,
                'message' => 'Не удалось зарезервировать лицензию',
            );
        }

        return array(
            'success' => true,
            'licensed' => true,
            'state' => 'reserved',
            'assignment_id' => (int)$this->modx->lastInsertId(),
            'reused' => false,
        );
    }

    public function syncForEmployee($courseId, $employeeUserId, $certificateId = 0)
    {
        $courseId = (int)$courseId;
        $employeeUserId = (int)$employeeUserId;
        $certificateId = (int)$certificateId;

        if (
            $courseId <= 0 ||
            $employeeUserId <= 0 ||
            !$this->tableExists($this->tables['assignments'])
        ) {
            return array(
                'updated' => 0,
                'state' => '',
                'track' => array(),
            );
        }

        if ($certificateId <= 0) {
            $certificateId = $this->getCertificateId($courseId, $employeeUserId);
        }

        $track = $this->getTrackStats($courseId, $employeeUserId);
        $shouldConsume = $certificateId > 0
            || (
                (int)$track['total_items'] > 0
                && (float)$track['progress_percent'] >= 80
            );

        if (!$shouldConsume) {
            return array(
                'updated' => 0,
                'state' => '',
                'track' => $track,
            );
        }

        $table = $this->safeTable($this->tables['assignments']);
        $now = $this->now();

        $stmt = $this->modx->prepare(
            'UPDATE `' . $table . '` SET '
            . '`state` = "consumed", '
            . '`threshold_reachedon` = CASE '
            . 'WHEN :progress_percent >= 80 AND (`threshold_reachedon` IS NULL OR `threshold_reachedon` = "0000-00-00 00:00:00") '
            . 'THEN :now_value ELSE `threshold_reachedon` END, '
            . '`consumedon` = CASE '
            . 'WHEN `consumedon` IS NULL OR `consumedon` = "0000-00-00 00:00:00" '
            . 'THEN :now_value ELSE `consumedon` END, '
            . '`certificate_id` = CASE WHEN :certificate_id > 0 THEN :certificate_id ELSE `certificate_id` END, '
            . '`progress_percent` = :progress_percent, '
            . '`updatedon` = :now_value '
            . 'WHERE `course_id` = :course_id '
            . 'AND `employee_user_id` = :employee_user_id '
            . 'AND `state` = "reserved"'
        );

        $updated = 0;

        if ($stmt && $stmt->execute(array(
            ':progress_percent' => (float)$track['progress_percent'],
            ':now_value' => $now,
            ':certificate_id' => $certificateId,
            ':course_id' => $courseId,
            ':employee_user_id' => $employeeUserId,
        ))) {
            $updated = (int)$stmt->rowCount();
        }

        return array(
            'updated' => $updated,
            'state' => $updated > 0 ? 'consumed' : '',
            'track' => $track,
            'certificate_id' => $certificateId,
        );
    }

    public function closeForRevocation($courseId, $employeeUserId, $actorUserId, $directorUserId = 0)
    {
        $courseId = (int)$courseId;
        $employeeUserId = (int)$employeeUserId;
        $actorUserId = (int)$actorUserId;
        $directorUserId = (int)$directorUserId;

        $this->syncForEmployee($courseId, $employeeUserId);

        $ownerId = $directorUserId;

        if ($ownerId <= 0) {
            $actorOwner = $this->getDirectorAccessRow($courseId, $actorUserId);
            if ($actorOwner) {
                $ownerId = (int)$actorOwner['principal_id'];
            }
        }

        $allocation = $this->getActiveAllocation($courseId, $employeeUserId, $ownerId);

        if (!$allocation && $ownerId <= 0) {
            $allocation = $this->getActiveAllocation($courseId, $employeeUserId);
        }

        if (!$allocation) {
            return array(
                'success' => true,
                'licensed' => false,
                'license_state' => 'legacy',
                'return_license' => false,
            );
        }

        $table = $this->safeTable($this->tables['assignments']);
        $now = $this->now();
        $track = $this->getTrackStats($courseId, $employeeUserId);

        if ((string)$allocation['state'] === 'reserved') {
            $stmt = $this->modx->prepare(
                'UPDATE `' . $table . '` SET '
                . '`state` = "released", '
                . '`releasedon` = :releasedon, '
                . '`release_reason` = "access_revoked_before_80", '
                . '`progress_percent` = :progress_percent, '
                . '`access_closedon` = :access_closedon, '
                . '`updatedon` = :updatedon '
                . 'WHERE `id` = :id AND `state` = "reserved"'
            );

            if (!$stmt || !$stmt->execute(array(
                ':releasedon' => $now,
                ':progress_percent' => (float)$track['progress_percent'],
                ':access_closedon' => $now,
                ':updatedon' => $now,
                ':id' => (int)$allocation['id'],
            ))) {
                return array(
                    'success' => false,
                    'message' => 'Не удалось вернуть лицензию',
                );
            }

            return array(
                'success' => true,
                'licensed' => true,
                'license_state' => 'released',
                'return_license' => true,
                'assignment_id' => (int)$allocation['id'],
                'track' => $track,
            );
        }

        $stmt = $this->modx->prepare(
            'UPDATE `' . $table . '` SET '
            . '`access_closedon` = :access_closedon, '
            . '`progress_percent` = :progress_percent, '
            . '`updatedon` = :updatedon '
            . 'WHERE `id` = :id AND `state` = "consumed"'
        );

        if (!$stmt || !$stmt->execute(array(
            ':access_closedon' => $now,
            ':progress_percent' => (float)$track['progress_percent'],
            ':updatedon' => $now,
            ':id' => (int)$allocation['id'],
        ))) {
            return array(
                'success' => false,
                'message' => 'Не удалось закрыть лицензированный доступ',
            );
        }

        return array(
            'success' => true,
            'licensed' => true,
            'license_state' => 'consumed',
            'return_license' => false,
            'assignment_id' => (int)$allocation['id'],
            'track' => $track,
        );
    }

    public function getAllocationState($courseId, $employeeUserId, $directorUserId = 0)
    {
        $allocation = $this->getActiveAllocation($courseId, $employeeUserId, $directorUserId);

        if (!$allocation) {
            return array(
                'state' => 'legacy',
                'licensed' => false,
                'assignment_id' => 0,
            );
        }

        return array(
            'state' => (string)$allocation['state'],
            'licensed' => true,
            'assignment_id' => (int)$allocation['id'],
            'director_user_id' => (int)$allocation['director_user_id'],
            'access_closedon' => (string)$allocation['access_closedon'],
        );
    }
}
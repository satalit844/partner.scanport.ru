<?php

/**
 * Сервис ручного назначения прогресса из CMP курса.
 *
 * Не предназначен для фронтенда. Используется только processors/mgr/course/progress/.
 * Все изменения выполняются только методом applyPlan() внутри транзакции.
 */
class TrainingProgressAssignmentService
{
    /** @var modX */
    protected $modx;

    /** @var Training */
    protected $training;

    /** @var TrainingProgressService */
    protected $progressService;

    /** @var string */
    protected $corePath;

    /** @var array */
    protected $tableCache = array();

    /** @var array */
    protected $columnsCache = array();

    public function __construct(modX $modx)
    {
        $this->modx = $modx;
        $this->corePath = rtrim(
            $modx->getOption(
                'training.core_path',
                null,
                $modx->getOption('core_path') . 'components/training/'
            ),
            '/\\'
        ) . '/';

        $this->training = new Training($modx);
        $this->progressService = new TrainingProgressService($modx, $this->training);

        $webHelper = $this->corePath . 'processors/web/_helpers.php';
        if (is_file($webHelper)) {
            require_once $webHelper;
        }
    }

    public function getCourseUsers($courseId)
    {
        $courseId = (int)$courseId;
        if ($courseId <= 0) {
            return array();
        }

        $userCourseTable = $this->cleanTable($this->modx->getTableName('TrainingUserCourse'));
        $userTable = $this->cleanTable($this->modx->getTableName('modUser'));
        $profileTable = $this->cleanTable($this->modx->getTableName('modUserProfile'));
        $moduleTable = $this->cleanTable($this->modx->getTableName('TrainingModule'));
        $resourceTable = $this->cleanTable($this->modx->getTableName('modResource'));

        $sql = 'SELECT uc.*, '
            . 'u.`username`, p.`fullname`, p.`email`, '
            . 'r.`pagetitle` AS `current_module_title` '
            . 'FROM `' . $userCourseTable . '` uc '
            . 'LEFT JOIN `' . $userTable . '` u ON u.`id` = uc.`user_id` '
            . 'LEFT JOIN `' . $profileTable . '` p ON p.`internalKey` = uc.`user_id` '
            . 'LEFT JOIN `' . $moduleTable . '` m ON m.`id` = uc.`current_module_id` '
            . 'LEFT JOIN `' . $resourceTable . '` r ON r.`id` = m.`resource_id` '
            . 'WHERE uc.`course_id` = :course_id '
            . 'AND COALESCE(NULLIF(TRIM(uc.`status`), ""), "assigned") <> "revoked" '
            . 'ORDER BY COALESCE(NULLIF(p.`fullname`, ""), u.`username`) ASC, uc.`user_id` ASC';

        $rows = $this->fetchAll($sql, array(':course_id' => $courseId));

        foreach ($rows as &$row) {
            $row['user_id'] = (int)$row['user_id'];
            $row['display_name'] = trim((string)$row['fullname']);
            if ($row['display_name'] === '') {
                $row['display_name'] = trim((string)$row['username']);
            }
            if ($row['display_name'] === '') {
                $row['display_name'] = 'Пользователь #' . $row['user_id'];
            }

            $row['current_module_title'] = trim((string)$row['current_module_title']);
            $row['current_module_label'] = $row['current_module_title'] !== ''
                ? $row['current_module_title']
                : '—';

            $row['current_lesson_label'] = $this->resolveCurrentLessonLabel(
                $courseId,
                (int)$row['user_id'],
                (int)$row['current_module_id']
            );
        }
        unset($row);

        return $rows;
    }

    public function getModules($courseId)
    {
        $courseId = (int)$courseId;
        if ($courseId <= 0) {
            return array();
        }

        $moduleTable = $this->cleanTable($this->modx->getTableName('TrainingModule'));
        $resourceTable = $this->cleanTable($this->modx->getTableName('modResource'));

        $sql = 'SELECT m.`id`, m.`course_id`, m.`resource_id`, m.`is_active`, m.`is_required`, '
            . 'r.`pagetitle`, r.`menuindex` '
            . 'FROM `' . $moduleTable . '` m '
            . 'LEFT JOIN `' . $resourceTable . '` r ON r.`id` = m.`resource_id` '
            . 'WHERE m.`course_id` = :course_id '
            . 'ORDER BY r.`menuindex` ASC, m.`id` ASC';

        $rows = $this->fetchAll($sql, array(':course_id' => $courseId));
        foreach ($rows as &$row) {
            $row['id'] = (int)$row['id'];
            $row['menuindex'] = (int)$row['menuindex'];
            $row['is_active'] = (int)$row['is_active'];
            $row['is_required'] = (int)$row['is_required'];
            $row['display_name'] = trim((string)$row['pagetitle']);
            if ($row['display_name'] === '') {
                $row['display_name'] = 'Модуль #' . $row['id'];
            }
        }
        unset($row);

        return $rows;
    }

    public function getLessons($courseId, $moduleId)
    {
        $courseId = (int)$courseId;
        $moduleId = (int)$moduleId;

        if ($courseId <= 0 || $moduleId <= 0) {
            return array();
        }

        /*
         * В текущем компоненте уроки принадлежат модулю. Используем штатный
         * TrainingProgressService, а не прямой SQL с предположением о course_id
         * в таблице уроков.
         */
        $moduleExists = false;
        foreach ($this->getModules($courseId) as $module) {
            if ((int)$module['id'] === $moduleId) {
                $moduleExists = true;
                break;
            }
        }

        if (!$moduleExists) {
            return array();
        }

        $items = $this->progressService->getModuleLessons($moduleId, false, false);
        $rows = array();

        /** @var TrainingModuleLesson $item */
        foreach ($items as $item) {
            $title = trim((string)$item->get('title'));

            $rows[] = array(
                'id' => (int)$item->get('id'),
                'module_id' => (int)$item->get('module_id'),
                'title' => $title,
                'sort_order' => (int)$item->get('sort_order'),
                'is_active' => (int)$item->get('is_active'),
                'duration_seconds' => (int)$item->get('duration_seconds'),
                'display_name' => $title !== '' ? $title : ('Урок #' . (int)$item->get('id')),
            );
        }

        return $rows;
    }

    public function getUserSummary($courseId, $userId)
    {
        $courseId = (int)$courseId;
        $userId = (int)$userId;
        $user = $this->getCourseUser($courseId, $userId);
        if (!$user) {
            throw new RuntimeException('Пользователь не назначен на этот курс.');
        }

        $modules = $this->getModules($courseId);
        $moduleProgressTable = $this->cleanTable($this->modx->getTableName('TrainingUserModuleProgress'));
        $lessonProgressTable = $this->cleanTable($this->modx->getTableName('TrainingUserLessonProgress'));

        $moduleRows = $this->fetchAll(
            'SELECT * FROM `' . $moduleProgressTable . '` '
            . 'WHERE `course_id` = :course_id AND `user_id` = :user_id',
            array(':course_id' => $courseId, ':user_id' => $userId)
        );

        $moduleMap = array();
        foreach ($moduleRows as $row) {
            $moduleMap[(int)$row['module_id']] = $row;
        }

        $currentModuleId = (int)$user['current_module_id'];
        if ($currentModuleId <= 0) {
            foreach ($modules as $module) {
                $moduleId = (int)$module['id'];
                if (!isset($moduleMap[$moduleId]) || (int)$moduleMap[$moduleId]['completed'] !== 1) {
                    $currentModuleId = $moduleId;
                    break;
                }
            }
        }

        $currentLesson = $this->resolveCurrentLesson($courseId, $userId, $currentModuleId, $lessonProgressTable);

        $completedModules = array();
        foreach ($modules as $module) {
            $moduleId = (int)$module['id'];
            if (isset($moduleMap[$moduleId]) && (int)$moduleMap[$moduleId]['completed'] === 1) {
                $completedModules[] = $moduleId;
            }
        }

        return array(
            'user_id' => $userId,
            'display_name' => $user['display_name'],
            'email' => $user['email'],
            'course_status' => (string)$user['status'],
            'progress_percent' => (float)$user['progress_percent'],
            'completed_modules' => (int)$user['completed_modules'],
            'total_modules' => (int)$user['total_modules'],
            'current_module_id' => $currentModuleId,
            'current_module_label' => $this->moduleLabelFromList($modules, $currentModuleId),
            'current_lesson_id' => $currentLesson ? (int)$currentLesson['id'] : 0,
            'current_lesson_label' => $currentLesson ? (string)$currentLesson['display_name'] : '—',
            'completed_module_ids' => $completedModules,
        );
    }

    /**
     * Возвращает подробный снимок прогресса пользователя по курсу.
     *
     * Используется только в CMP: для режима просмотра и безопасного
     * исключения уже завершённых модулей/уроков из списков назначения.
     * Метод ничего не меняет в БД.
     */
    public function getUserProgressDetails($courseId, $userId)
    {
        $courseId = (int)$courseId;
        $userId = (int)$userId;

        if ($courseId <= 0 || $userId <= 0) {
            throw new RuntimeException('Не заполнены обязательные параметры прогресса.');
        }

        $summary = $this->getUserSummary($courseId, $userId);
        $modules = $this->getModules($courseId);

        $moduleProgressTable = $this->cleanTable($this->modx->getTableName('TrainingUserModuleProgress'));
        $lessonProgressTable = $this->cleanTable($this->modx->getTableName('TrainingUserLessonProgress'));

        $moduleRows = $this->fetchAll(
            'SELECT * FROM `' . $moduleProgressTable . '` '
            . 'WHERE `course_id` = :course_id AND `user_id` = :user_id',
            array(':course_id' => $courseId, ':user_id' => $userId)
        );

        $lessonRows = $this->fetchAll(
            'SELECT * FROM `' . $lessonProgressTable . '` '
            . 'WHERE `course_id` = :course_id AND `user_id` = :user_id',
            array(':course_id' => $courseId, ':user_id' => $userId)
        );

        $moduleMap = array();
        foreach ($moduleRows as $row) {
            $moduleMap[(int)$row['module_id']] = $row;
        }

        $lessonMap = array();
        foreach ($lessonRows as $row) {
            $lessonMap[(int)$row['lesson_id']] = $row;
        }

        $detailModules = array();
        $currentModuleId = (int)$summary['current_module_id'];
        $currentLessonId = (int)$summary['current_lesson_id'];

        foreach ($modules as $module) {
            $moduleId = (int)$module['id'];
            $moduleProgress = isset($moduleMap[$moduleId]) ? $moduleMap[$moduleId] : array();

            $moduleCompleted = !empty($moduleProgress) && (int)$moduleProgress['completed'] === 1;
            $moduleStatus = !empty($moduleProgress['status'])
                ? (string)$moduleProgress['status']
                : 'not_started';
            $modulePercent = isset($moduleProgress['progress_percent'])
                ? max(0, min(100, (float)$moduleProgress['progress_percent']))
                : 0;

            if ($moduleCompleted) {
                $moduleStatus = 'completed';
                $modulePercent = 100;
            }

            $lessonDetails = array();
            $lessonsTotal = 0;
            $lessonsCompleted = 0;

            foreach ($this->getLessons($courseId, $moduleId) as $lesson) {
                if ((int)$lesson['is_active'] !== 1) {
                    continue;
                }

                $lessonId = (int)$lesson['id'];
                $lessonsTotal++;

                $lessonProgress = isset($lessonMap[$lessonId]) ? $lessonMap[$lessonId] : array();
                $lessonCompleted = !empty($lessonProgress) && (int)$lessonProgress['completed'] === 1;
                $lessonStatus = !empty($lessonProgress['status'])
                    ? (string)$lessonProgress['status']
                    : 'not_started';
                $lessonPercent = isset($lessonProgress['progress_percent'])
                    ? max(0, min(100, (float)$lessonProgress['progress_percent']))
                    : 0;

                if ($lessonCompleted) {
                    $lessonStatus = 'completed';
                    $lessonPercent = 100;
                    $lessonsCompleted++;
                }

                $lessonDetails[] = array(
                    'id' => $lessonId,
                    'module_id' => $moduleId,
                    'display_name' => (string)$lesson['display_name'],
                    'sort_order' => (int)$lesson['sort_order'],
                    'is_active' => 1,
                    'duration_seconds' => isset($lesson['duration_seconds']) ? (int)$lesson['duration_seconds'] : 0,
                    'status' => $lessonStatus,
                    'progress_percent' => $lessonPercent,
                    'completed' => $lessonCompleted ? 1 : 0,
                    'is_current' => ($lessonId === $currentLessonId) ? 1 : 0,
                );
            }

            $detailModules[] = array(
                'id' => $moduleId,
                'display_name' => (string)$module['display_name'],
                'menuindex' => (int)$module['menuindex'],
                'is_active' => (int)$module['is_active'],
                'is_required' => (int)$module['is_required'],
                'status' => $moduleStatus,
                'progress_percent' => $modulePercent,
                'completed' => $moduleCompleted ? 1 : 0,
                'lessons_total' => $lessonsTotal,
                'lessons_completed' => $lessonsCompleted,
                'is_current' => ($moduleId === $currentModuleId) ? 1 : 0,
                'lessons' => $lessonDetails,
            );
        }

        $summary['modules'] = $detailModules;

        return $summary;
    }

    public function buildPlan($courseId, $userId, $mode, $moduleId, $lessonId = 0)
    {
        $courseId = (int)$courseId;
        $userId = (int)$userId;
        $moduleId = (int)$moduleId;
        $lessonId = (int)$lessonId;
        $mode = trim((string)$mode);
        if (!in_array($mode, array('lesson', 'module'), true)) {
            throw new RuntimeException('Этот режим предназначен только для просмотра и не изменяет прогресс.');
        }

        if ($courseId <= 0 || $userId <= 0 || $moduleId <= 0) {
            throw new RuntimeException('Не заполнены обязательные параметры прогресса.');
        }

        $progressDetails = $this->getUserProgressDetails($courseId, $userId);
        $userSummary = $progressDetails;
        $modules = isset($progressDetails['modules']) ? (array)$progressDetails['modules'] : array();
        $moduleIndexes = array();
        $targetModule = null;
        $targetModuleProgress = null;

        foreach ($modules as $index => $module) {
            $moduleIndexes[(int)$module['id']] = $index;
            if ((int)$module['id'] === $moduleId) {
                $targetModule = $module;
                $targetModuleProgress = $module;
            }
        }

        if (!$targetModule) {
            throw new RuntimeException('Выбранный модуль не относится к этому курсу.');
        }

        if (!empty($targetModuleProgress['completed'])) {
            throw new RuntimeException('Выбранный модуль уже завершён. Выберите текущий или следующий незавершённый модуль.');
        }

        $currentModuleId = (int)$userSummary['current_module_id'];
        if ($currentModuleId > 0
            && isset($moduleIndexes[$currentModuleId])
            && $moduleIndexes[$moduleId] < $moduleIndexes[$currentModuleId]
        ) {
            throw new RuntimeException(
                'Перенос назад пока запрещён. Выбранный модуль находится раньше текущего. '
                . 'Для понижения прогресса нужен отдельный безопасный сценарий сброса.'
            );
        }

        $targetLessons = isset($targetModuleProgress['lessons'])
            ? (array)$targetModuleProgress['lessons']
            : array();
        $targetLesson = null;
        $targetLessonProgressMap = array();

        foreach ($targetLessons as $targetLessonRow) {
            $targetLessonProgressMap[(int)$targetLessonRow['id']] = $targetLessonRow;
        }

        if ($mode === 'lesson') {
            if ($lessonId <= 0) {
                throw new RuntimeException('Выберите урок, на котором пользователь должен остановиться.');
            }

            foreach ($targetLessons as $lesson) {
                if ((int)$lesson['id'] === $lessonId && (int)$lesson['is_active'] === 1) {
                    $targetLesson = $lesson;
                    break;
                }
            }

            if (!$targetLesson) {
                throw new RuntimeException('Выбранный урок не относится к модулю или выключен.');
            }

            if (!empty($targetLessonProgressMap[$lessonId]['completed'])) {
                throw new RuntimeException('Выбранный урок уже завершён. Выберите первый незавершённый урок этого модуля.');
            }
        }

        $completeModuleIds = array();
        foreach ($modules as $module) {
            if ((int)$module['is_active'] !== 1) {
                continue;
            }

            $moduleIndex = $moduleIndexes[(int)$module['id']];
            $targetIndex = $moduleIndexes[$moduleId];

            if ($mode === 'lesson' && $moduleIndex < $targetIndex) {
                $completeModuleIds[] = (int)$module['id'];
            }

            if ($mode === 'module' && $moduleIndex <= $targetIndex) {
                $completeModuleIds[] = (int)$module['id'];
            }
        }

        $completeLessonIds = array();
        $completeLessons = array();

        foreach ($completeModuleIds as $completeModuleId) {
            foreach ($this->getLessons($courseId, $completeModuleId) as $lesson) {
                if ((int)$lesson['is_active'] !== 1) {
                    continue;
                }

                $completeLessonIds[] = (int)$lesson['id'];
                $completeLessons[] = $lesson;
            }
        }

        if ($mode === 'lesson') {
            foreach ($targetLessons as $lesson) {
                if ((int)$lesson['is_active'] === 1 && (int)$lesson['sort_order'] < (int)$targetLesson['sort_order']) {
                    $completeLessonIds[] = (int)$lesson['id'];
                    $completeLessons[] = $lesson;
                }
            }
        }

        $completeLessonIds = array_values(array_unique($completeLessonIds));

        $activityModules = $completeModuleIds;
        $activities = $this->getRequiredActivities($courseId, $activityModules);

        $completedVideoCount = 0;
        foreach ($completeLessons as $lesson) {
            $completedVideoCount += count($this->getLessonVideos((int)$lesson['id']));
        }

        $currentVideo = null;
        if ($targetLesson) {
            $videos = $this->getLessonVideos((int)$targetLesson['id']);
            if (empty($videos)) {
                throw new RuntimeException('У выбранного урока нет активного видео. Его нельзя назначить как текущую точку.');
            }
            $currentVideo = $videos[0];
        }

        return array(
            'course_id' => $courseId,
            'user_id' => $userId,
            'mode' => $mode,
            'target_module' => $targetModule,
            'target_lesson' => $targetLesson,
            'complete_module_ids' => $completeModuleIds,
            'complete_lesson_ids' => $completeLessonIds,
            'required_activities' => $activities,
            'completed_videos_count' => $completedVideoCount,
            'current_video' => $currentVideo,
            'summary_before' => $userSummary,
        );
    }

    public function applyPlan(array $plan, $actorId = 0)
    {
        $courseId = (int)$plan['course_id'];
        $userId = (int)$plan['user_id'];
        $actorId = (int)$actorId;
        $now = date('Y-m-d H:i:s');

        $videoProgressTable = $this->getPlainTableByNeedle('user_lesson_video_progress');
        $videoProgressColumns = $videoProgressTable !== '' ? $this->getColumnsMeta($videoProgressTable) : array();

        $practiceTable = $this->getPlainTableByNeedle('practice_attempts');
        if ($practiceTable === '') {
            throw new RuntimeException('Не найдена таблица practice_attempts.');
        }
        $practiceColumns = $this->getColumnsMeta($practiceTable);

        $testResultsTable = $this->getUserTestResultsTable();
        $testResultsColumns = $this->getColumnsMeta($testResultsTable);

        $this->assertInsertReady(
            $testResultsTable,
            $testResultsColumns,
            $this->buildTestResultData($testResultsColumns, 1, 1, $now)
        );
        $this->assertInsertReady(
            $practiceTable,
            $practiceColumns,
            $this->buildPracticeAttemptData($practiceColumns, $courseId, 1, 1, 1, 1, $actorId, $now, 1)
        );

        $summary = array(
            'lessons_completed' => 0,
            'videos_completed' => 0,
            'videos_started' => 0,
            'tests_created' => 0,
            'tests_existing' => 0,
            'practices_created' => 0,
            'practices_updated' => 0,
            'practices_existing' => 0,
            'modules_recalculated' => array(),
        );

        $this->modx->query('START TRANSACTION');

        try {
            $lessonTable = $this->cleanTable($this->modx->getTableName('TrainingModuleLesson'));

            foreach ((array)$plan['complete_lesson_ids'] as $lessonId) {
                $lessonId = (int)$lessonId;
                $lessonRows = $this->fetchAll(
                    'SELECT * FROM `' . $lessonTable . '` WHERE `id` = :lesson_id LIMIT 1',
                    array(':lesson_id' => $lessonId)
                );
                if (empty($lessonRows)) {
                    throw new RuntimeException('Не найден урок #' . $lessonId);
                }

                $lesson = $lessonRows[0];
                $moduleId = (int)$lesson['module_id'];
                $videos = $this->getLessonVideos($lessonId);
                $duration = max(0, (int)$lesson['duration_seconds']);
                foreach ($videos as $video) {
                    $duration += max(0, (int)$video['duration_seconds']);
                }
                $duration = max(1, $duration);

                $this->progressService->saveLessonProgress($courseId, $moduleId, $lessonId, $userId, array(
                    'duration_seconds' => $duration,
                    'current_time' => $duration,
                    'max_time' => $duration,
                    'completed' => 1,
                ));

                foreach ($videos as $video) {
                    $result = $this->syncVideoProgress(
                        $videoProgressTable,
                        $videoProgressColumns,
                        $courseId,
                        $userId,
                        $lessonId,
                        $video,
                        true,
                        max(1, (int)$video['duration_seconds']),
                        $now
                    );
                    if (empty($result['skipped'])) {
                        $summary['videos_completed']++;
                    }
                }

                $summary['lessons_completed']++;
            }

            foreach ((array)$plan['required_activities'] as $activity) {
                if ($activity['link_type'] === 'practice') {
                    $result = $this->ensurePracticeApproved(
                        $practiceTable,
                        $practiceColumns,
                        $courseId,
                        $userId,
                        (int)$activity['module_id'],
                        (int)$activity['link_id'],
                        (int)$activity['ref_id'],
                        $actorId,
                        $now
                    );

                    if ($result === 'created') {
                        $summary['practices_created']++;
                    } elseif ($result === 'updated') {
                        $summary['practices_updated']++;
                    } else {
                        $summary['practices_existing']++;
                    }
                } else {
                    $result = $this->ensureTestPassed(
                        $testResultsTable,
                        $testResultsColumns,
                        $userId,
                        (int)$activity['ref_id'],
                        (float)$activity['min_pass_percent'],
                        $now
                    );

                    if ($result === 'created') {
                        $summary['tests_created']++;
                    } else {
                        $summary['tests_existing']++;
                    }
                }
            }

            if ($plan['mode'] === 'lesson' && !empty($plan['target_lesson'])) {
                $lesson = $plan['target_lesson'];
                $lessonId = (int)$lesson['id'];
                $moduleId = (int)$lesson['module_id'];
                $videos = $this->getLessonVideos($lessonId);
                $duration = max(0, (int)$lesson['duration_seconds']);
                foreach ($videos as $video) {
                    $duration += max(0, (int)$video['duration_seconds']);
                }
                $duration = max(2, $duration);

                $this->progressService->saveLessonProgress($courseId, $moduleId, $lessonId, $userId, array(
                    'duration_seconds' => $duration,
                    'current_time' => 1,
                    'max_time' => 1,
                    'completed' => 0,
                ));

                if (!empty($videos)) {
                    $result = $this->syncVideoProgress(
                        $videoProgressTable,
                        $videoProgressColumns,
                        $courseId,
                        $userId,
                        $lessonId,
                        $videos[0],
                        false,
                        1,
                        $now
                    );
                    if (empty($result['skipped'])) {
                        $summary['videos_started']++;
                    }
                }
            }

            $recalculateModuleIds = array_values(array_unique(array_merge(
                (array)$plan['complete_module_ids'],
                array(!empty($plan['target_module']['id']) ? (int)$plan['target_module']['id'] : 0)
            )));

            foreach ($recalculateModuleIds as $moduleId) {
                $moduleId = (int)$moduleId;
                if ($moduleId <= 0) {
                    continue;
                }

                $this->progressService->syncModuleUserTestStatuses($courseId, $moduleId, $userId);
                $this->progressService->recalculateModuleProgressFromLessons($courseId, $moduleId, $userId);
                $summary['modules_recalculated'][] = $moduleId;
            }

            $this->progressService->recalculateUserCourse($courseId, $userId);

            $this->modx->query('COMMIT');

            return $summary;
        } catch (Throwable $e) {
            try {
                $this->modx->query('ROLLBACK');
            } catch (Throwable $rollbackError) {
                // Основную ошибку пробрасываем ниже.
            }

            throw $e;
        }
    }

    public function getPlanHtml(array $plan)
    {
        $targetModule = isset($plan['target_module']['display_name']) ? $plan['target_module']['display_name'] : '—';
        $targetLesson = !empty($plan['target_lesson']['display_name']) ? $plan['target_lesson']['display_name'] : '';
        $modeLabel = $plan['mode'] === 'module'
            ? 'Завершить модуль'
            : 'Остановить на уроке';

        $tests = 0;
        $practices = 0;
        foreach ((array)$plan['required_activities'] as $activity) {
            if ($activity['link_type'] === 'practice') {
                $practices++;
            } else {
                $tests++;
            }
        }

        $html = '<div class="training-progress-plan">';
        $html .= '<h3 style="margin:0 0 10px;">План изменений</h3>';
        $html .= '<p style="margin:0 0 12px;"><b>Режим:</b> ' . $this->esc($modeLabel) . '</p>';
        $html .= '<p style="margin:0 0 12px;"><b>Целевой модуль:</b> ' . $this->esc($targetModule) . '</p>';

        if ($targetLesson !== '') {
            $html .= '<p style="margin:0 0 12px;"><b>Точка остановки:</b> ' . $this->esc($targetLesson) . '</p>';
        }

        $html .= '<ul style="margin:0 0 12px 18px;">';
        $html .= '<li>Завершить модулей: <b>' . count((array)$plan['complete_module_ids']) . '</b></li>';
        $html .= '<li>Завершить уроков: <b>' . count((array)$plan['complete_lesson_ids']) . '</b></li>';
        $html .= '<li>Отметить реальных видео как просмотренные: <b>' . (int)$plan['completed_videos_count'] . '</b></li>';
        $html .= '<li>Закрыть обязательных тестов: <b>' . $tests . '</b></li>';
        $html .= '<li>Проверить обязательных практик: <b>' . $practices . '</b></li>';
        $html .= '</ul>';

        if ($plan['mode'] === 'lesson') {
            $html .= '<div style="padding:10px;border:1px solid #d8c7f3;background:#faf7ff;">'
                . 'Выбранный урок не завершается: он будет отмечен как <b>«В процессе»</b>.'
                . '</div>';
        }

        $html .= '<div style="margin-top:12px;color:#777;">'
            . 'Понижение прогресса и очистка следующих модулей в этой версии не выполняются.'
            . '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function getCourseUser($courseId, $userId)
    {
        $courseId = (int)$courseId;
        $userId = (int)$userId;
        $rows = $this->getCourseUsers($courseId);

        foreach ($rows as $row) {
            if ((int)$row['user_id'] === $userId) {
                return $row;
            }
        }

        return null;
    }

    protected function resolveCurrentLessonLabel($courseId, $userId, $moduleId)
    {
        $lesson = $this->resolveCurrentLesson($courseId, $userId, $moduleId);
        return $lesson ? (string)$lesson['display_name'] : '—';
    }

    protected function resolveCurrentLesson($courseId, $userId, $moduleId, $lessonProgressTable = '')
    {
        $courseId = (int)$courseId;
        $userId = (int)$userId;
        $moduleId = (int)$moduleId;
        if ($moduleId <= 0) {
            return null;
        }

        $lessons = $this->getLessons($courseId, $moduleId);
        if (empty($lessons)) {
            return null;
        }

        if ($lessonProgressTable === '') {
            $lessonProgressTable = $this->cleanTable($this->modx->getTableName('TrainingUserLessonProgress'));
        }

        $rows = $this->fetchAll(
            'SELECT * FROM `' . $lessonProgressTable . '` '
            . 'WHERE `course_id` = :course_id AND `user_id` = :user_id',
            array(':course_id' => $courseId, ':user_id' => $userId)
        );

        $progressMap = array();
        foreach ($rows as $row) {
            $progressMap[(int)$row['lesson_id']] = $row;
        }

        foreach ($lessons as $lesson) {
            $lessonId = (int)$lesson['id'];
            if ((int)$lesson['is_active'] !== 1) {
                continue;
            }

            if (!isset($progressMap[$lessonId]) || (int)$progressMap[$lessonId]['completed'] !== 1) {
                return $lesson;
            }
        }

        return null;
    }

    protected function moduleLabelFromList(array $modules, $moduleId)
    {
        foreach ($modules as $module) {
            if ((int)$module['id'] === (int)$moduleId) {
                return (string)$module['display_name'];
            }
        }

        return '—';
    }

    protected function getRequiredActivities($courseId, array $moduleIds)
    {
        if (empty($moduleIds)) {
            return array();
        }

        $table = $this->cleanTable($this->modx->getTableName('TrainingTestLink'));
        $params = array(':course_id' => (int)$courseId);
        $holders = $this->makeIn($moduleIds, 'module', $params);
        $rows = $this->fetchAll(
            'SELECT * FROM `' . $table . '` '
            . 'WHERE `course_id` = :course_id '
            . 'AND `module_id` IN (' . implode(',', $holders) . ') '
            . 'AND (`is_required` = 1 OR `block_next_module_until_passed` = 1) '
            . 'ORDER BY `module_id` ASC, `sort_order` ASC, `id` ASC',
            $params
        );

        $result = array();
        foreach ($rows as $row) {
            $linkType = trim((string)$row['link_type']) === 'practice' ? 'practice' : 'test';
            $refId = (int)$row['usertest_test_id'];

            if ($refId <= 0) {
                throw new RuntimeException('У обязательной активности #' . (int)$row['id'] . ' нет ID теста/практики.');
            }

            $result[] = array(
                'link_id' => (int)$row['id'],
                'module_id' => (int)$row['module_id'],
                'link_type' => $linkType,
                'ref_id' => $refId,
                'min_pass_percent' => (float)$row['min_pass_percent'],
                'title' => isset($row['title']) ? trim((string)$row['title']) : '',
            );
        }

        return $result;
    }

    protected function getLessonVideos($lessonId)
    {
        $lessonId = (int)$lessonId;
        if ($lessonId <= 0 || !class_exists('TrainingWebHelper')) {
            return array();
        }

        return (array)TrainingWebHelper::fetchLessonVideos($this->modx, $lessonId, true);
    }

    protected function ensureUserTestPackage()
    {
        $corePath = $this->modx->getOption(
            'usertest_core_path',
            null,
            $this->modx->getOption('core_path') . 'components/usertest/'
        );
        $modelPath = rtrim($corePath, '/\\') . '/model/';

        if (!is_dir($modelPath)) {
            return false;
        }

        $this->modx->addPackage('usertest', $modelPath);
        return true;
    }

    protected function getUserTestResultsTable()
    {
        if (!$this->ensureUserTestPackage()) {
            throw new RuntimeException('Не удалось подключить модель UserTest.');
        }

        $table = $this->cleanTable($this->modx->getTableName('UserTestResults'));
        if ($table === '') {
            throw new RuntimeException('Не определена таблица UserTestResults.');
        }

        return $table;
    }

    protected function ensureTestPassed($table, array $meta, $userId, $testId, $minPassPercent, $now)
    {
        $existing = $this->getBestPassedTestResult($table, $userId, $testId, $minPassPercent);
        if ($existing) {
            return 'existing';
        }

        $data = $this->buildTestResultData($meta, $testId, $userId, $now);
        $this->assertInsertReady($table, $meta, $data);
        $this->insertRow($table, $meta, $data);

        return 'created';
    }

    protected function getBestPassedTestResult($table, $userId, $testId, $minPassPercent)
    {
        $rows = $this->fetchAll(
            'SELECT * FROM `' . $table . '` '
            . 'WHERE `test_id` = :test_id AND `user_id` = :user_id AND `status_id` IN (2, 3) '
            . 'ORDER BY `id` DESC',
            array(':test_id' => (int)$testId, ':user_id' => (int)$userId)
        );

        foreach ($rows as $row) {
            $maxPoint = isset($row['max_point']) ? (float)$row['max_point'] : 0;
            $testPoint = isset($row['test_point']) ? (float)$row['test_point'] : 0;
            $score = $maxPoint > 0 ? round(($testPoint / $maxPoint) * 100, 2) : $testPoint;

            if ($score >= (float)$minPassPercent || ((float)$minPassPercent <= 0 && $testPoint > 0)) {
                return $row;
            }
        }

        return null;
    }

    protected function buildTestResultData(array $meta, $testId, $userId, $now)
    {
        $data = array();

        $this->setIfColumn($data, $meta, array('test_id'), (int)$testId);
        $this->setIfColumn($data, $meta, array('user_id'), (int)$userId);
        $this->setIfColumn($data, $meta, array('variant_id'), 0);
        $this->setIfColumn($data, $meta, array('status_id'), 2);
        $this->setIfColumn($data, $meta, array('test_point'), 100);
        $this->setIfColumn($data, $meta, array('max_point'), 100);
        $this->setIfColumn($data, $meta, array('date', 'completedon', 'completed_at', 'finishedon', 'finished_at', 'end_date'), $now);
        $this->setIfColumn($data, $meta, array('startedon', 'started_at', 'start_date'), $now);
        $this->setIfColumn($data, $meta, array('createdon', 'created_at'), $now);
        $this->setIfColumn($data, $meta, array('updatedon', 'updated_at'), $now);
        $this->setIfColumn($data, $meta, array('properties', 'data', 'answers', 'response'), '{}');
        $this->setIfColumn($data, $meta, array('ip', 'user_agent', 'session', 'session_id', 'token', 'hash'), '');
        $this->setIfColumn($data, $meta, array('time', 'time_spent', 'duration', 'duration_seconds'), 0);

        return $data;
    }

    protected function ensurePracticeApproved(
        $table,
        array $meta,
        $courseId,
        $userId,
        $moduleId,
        $linkId,
        $practiceId,
        $actorId,
        $now
    ) {
        $rows = $this->fetchAll(
            'SELECT * FROM `' . $table . '` '
            . 'WHERE `course_id` = :course_id AND `module_id` = :module_id AND `user_id` = :user_id '
            . 'AND (`test_link_id` = :link_id OR `practice_id` = :practice_id) '
            . 'ORDER BY `attempt_no` DESC, `attempt_num` DESC, `id` DESC LIMIT 1',
            array(
                ':course_id' => (int)$courseId,
                ':module_id' => (int)$moduleId,
                ':user_id' => (int)$userId,
                ':link_id' => (int)$linkId,
                ':practice_id' => (int)$practiceId,
            )
        );

        if (!empty($rows)
            && in_array(trim((string)$rows[0]['status']), array('approved', 'accepted'), true)
        ) {
            return 'existing';
        }

        $attemptNo = 1;
        if (!empty($rows)) {
            $attemptNo = max(
                isset($rows[0]['attempt_no']) ? (int)$rows[0]['attempt_no'] : 0,
                isset($rows[0]['attempt_num']) ? (int)$rows[0]['attempt_num'] : 0,
                1
            );
        }

        $data = $this->buildPracticeAttemptData(
            $meta,
            $courseId,
            $moduleId,
            $userId,
            $practiceId,
            $linkId,
            $actorId,
            $now,
            $attemptNo
        );

        if (!empty($rows)) {
            $this->updateById($table, $meta, (int)$rows[0]['id'], $data);
            return 'updated';
        }

        $this->assertInsertReady($table, $meta, $data);
        $this->insertRow($table, $meta, $data);

        return 'created';
    }

    protected function buildPracticeAttemptData(
        array $meta,
        $courseId,
        $moduleId,
        $userId,
        $practiceId,
        $linkId,
        $actorId,
        $now,
        $attemptNo
    ) {
        $data = array();

        $this->setIfColumn($data, $meta, array('course_id'), (int)$courseId);
        $this->setIfColumn($data, $meta, array('module_id'), (int)$moduleId);
        $this->setIfColumn($data, $meta, array('user_id'), (int)$userId);
        $this->setIfColumn($data, $meta, array('practice_id'), (int)$practiceId);
        $this->setIfColumn($data, $meta, array('test_link_id'), (int)$linkId);
        $this->setIfColumn($data, $meta, array('attempt_no', 'attempt_num'), (int)$attemptNo);
        $this->setIfColumn($data, $meta, array('is_latest'), 1);
        $this->setIfColumn($data, $meta, array('status'), 'approved');
        $this->setIfColumn($data, $meta, array('score'), 100);
        $this->setIfColumn($data, $meta, array('max_score'), 100);
        $this->setIfColumn($data, $meta, array('submittedon', 'submitted_at'), $now);
        $this->setIfColumn($data, $meta, array('reviewedon', 'reviewed_at'), $now);
        $this->setIfColumn($data, $meta, array('reviewedby', 'reviewer_user_id'), (int)$actorId);
        $this->setIfColumn($data, $meta, array('createdon', 'created_at'), $now);
        $this->setIfColumn($data, $meta, array('updatedon', 'updated_at'), $now);
        $this->setIfColumn($data, $meta, array('comment', 'review_comment', 'manager_comment'), 'Отмечено как проверенное через CMP «Прогресс».');
        $this->setIfColumn($data, $meta, array('answer', 'answer_text', 'content'), '');
        $this->setIfColumn($data, $meta, array('files', 'attachments', 'properties'), '{}');

        return $data;
    }

    protected function syncVideoProgress(
        $table,
        array $meta,
        $courseId,
        $userId,
        $lessonId,
        array $video,
        $completed,
        $currentSeconds,
        $now
    ) {
        if ($table === '' || empty($meta)) {
            return array('skipped' => true);
        }

        $videoIdColumn = $this->firstExistingColumn($meta, array(
            'lesson_video_id',
            'video_id',
            'module_lesson_video_id',
        ));

        if ($videoIdColumn === '') {
            return array('skipped' => true);
        }

        $duration = max(0, isset($video['duration_seconds']) ? (int)$video['duration_seconds'] : 0);
        $maxTime = $completed
            ? max(1, $duration)
            : max(1, min((int)$currentSeconds, max(1, $duration)));
        $percent = $completed ? 100 : ($duration > 0 ? round(($maxTime / $duration) * 100, 2) : 0);

        $whereData = array(
            'course_id' => (int)$courseId,
            'user_id' => (int)$userId,
            'lesson_id' => (int)$lessonId,
            $videoIdColumn => (int)$video['id'],
        );

        $data = array();
        $this->setIfColumn($data, $meta, array('status'), $completed ? 'completed' : 'in_progress');
        $this->setIfColumn($data, $meta, array('current_time'), $maxTime);
        $this->setIfColumn($data, $meta, array('max_time'), $maxTime);
        $this->setIfColumn($data, $meta, array('watched_seconds'), $maxTime);
        $this->setIfColumn($data, $meta, array('duration_seconds'), $duration);
        $this->setIfColumn($data, $meta, array('progress_percent'), $percent);
        $this->setIfColumn($data, $meta, array('completed', 'is_completed'), $completed ? 1 : 0);
        $this->setIfColumn($data, $meta, array('completedon', 'completed_at'), $completed ? $now : null);
        $this->setIfColumn($data, $meta, array('last_watch', 'last_viewed', 'updatedon', 'updated_at'), $now);
        $this->setIfColumn($data, $meta, array('createdon', 'created_at'), $now);

        return $this->upsertByWhere($table, $meta, $whereData, $data);
    }

    protected function getPlainTableByNeedle($needle)
    {
        $needle = strtolower((string)$needle);
        if ($needle === '') {
            return '';
        }

        $cacheKey = 'needle:' . $needle;
        if (array_key_exists($cacheKey, $this->tableCache)) {
            return $this->tableCache[$cacheKey];
        }

        $rows = $this->fetchAll('SHOW TABLES');
        foreach ($rows as $row) {
            $values = array_values($row);
            if (!empty($values[0]) && strpos(strtolower((string)$values[0]), $needle) !== false) {
                $this->tableCache[$cacheKey] = (string)$values[0];
                return $this->tableCache[$cacheKey];
            }
        }

        $this->tableCache[$cacheKey] = '';
        return '';
    }

    protected function getColumnsMeta($table)
    {
        if ($table === '') {
            return array();
        }

        if (isset($this->columnsCache[$table])) {
            return $this->columnsCache[$table];
        }

        $rows = $this->fetchAll('SHOW COLUMNS FROM `' . $table . '`');
        $meta = array();

        foreach ($rows as $row) {
            $meta[(string)$row['Field']] = array(
                'type' => isset($row['Type']) ? (string)$row['Type'] : '',
                'null' => isset($row['Null']) ? (string)$row['Null'] : 'YES',
                'default' => array_key_exists('Default', $row) ? $row['Default'] : null,
                'extra' => isset($row['Extra']) ? (string)$row['Extra'] : '',
            );
        }

        $this->columnsCache[$table] = $meta;
        return $meta;
    }

    protected function firstExistingColumn(array $meta, array $candidates)
    {
        foreach ($candidates as $candidate) {
            if (array_key_exists($candidate, $meta)) {
                return $candidate;
            }
        }

        return '';
    }

    protected function setIfColumn(array &$data, array $meta, array $candidates, $value)
    {
        $column = $this->firstExistingColumn($meta, $candidates);
        if ($column !== '') {
            $data[$column] = $value;
        }

        return $column;
    }

    protected function assertInsertReady($table, array $meta, array $data)
    {
        $missing = array();

        foreach ($meta as $column => $info) {
            $isAuto = stripos($info['extra'], 'auto_increment') !== false;
            $required = $info['null'] === 'NO' && $info['default'] === null && !$isAuto;

            if ($required && !array_key_exists($column, $data)) {
                $missing[] = $column . ' (' . $info['type'] . ')';
            }
        }

        if (!empty($missing)) {
            throw new RuntimeException(
                'Не хватает обязательных полей для INSERT в ' . $table . ': ' . implode(', ', $missing)
            );
        }
    }

    protected function insertRow($table, array $meta, array $data)
    {
        $columns = array();
        $holders = array();
        $params = array();

        foreach ($data as $column => $value) {
            if (!array_key_exists($column, $meta)) {
                continue;
            }

            $key = ':i_' . $column;
            $columns[] = '`' . $column . '`';
            $holders[] = $key;
            $params[$key] = $value;
        }

        if (empty($columns)) {
            throw new RuntimeException('Не удалось собрать INSERT в ' . $table);
        }

        $this->execute(
            'INSERT INTO `' . $table . '` (' . implode(',', $columns) . ') VALUES (' . implode(',', $holders) . ')',
            $params
        );

        return array('action' => 'insert');
    }

    protected function updateById($table, array $meta, $id, array $data)
    {
        if (!array_key_exists('id', $meta)) {
            throw new RuntimeException('В таблице ' . $table . ' нет id для UPDATE.');
        }

        $sets = array();
        $params = array(':id' => (int)$id);

        foreach ($data as $column => $value) {
            if (!array_key_exists($column, $meta) || in_array($column, array('id', 'createdon', 'created_at'), true)) {
                continue;
            }

            $key = ':u_' . $column;
            $sets[] = '`' . $column . '` = ' . $key;
            $params[$key] = $value;
        }

        if (empty($sets)) {
            return array('action' => 'none');
        }

        $this->execute(
            'UPDATE `' . $table . '` SET ' . implode(',', $sets) . ' WHERE `id` = :id',
            $params
        );

        return array('action' => 'update');
    }

    protected function upsertByWhere($table, array $meta, array $whereData, array $data)
    {
        $where = array();
        $params = array();

        foreach ($whereData as $column => $value) {
            if (!array_key_exists($column, $meta)) {
                throw new RuntimeException('В таблице ' . $table . ' нет колонки ' . $column);
            }

            $key = ':w_' . $column;
            $where[] = '`' . $column . '` = ' . $key;
            $params[$key] = $value;
        }

        $rows = $this->fetchAll(
            'SELECT * FROM `' . $table . '` WHERE ' . implode(' AND ', $where) . ' LIMIT 1',
            $params
        );

        if (!empty($rows)) {
            $result = $this->updateById($table, $meta, (int)$rows[0]['id'], $data);
            return array('action' => $result['action']);
        }

        $insertData = array_merge($whereData, $data);
        $this->assertInsertReady($table, $meta, $insertData);
        return $this->insertRow($table, $meta, $insertData);
    }

    protected function makeIn(array $ids, $prefix, array &$params)
    {
        $holders = array();
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        foreach ($ids as $index => $id) {
            $key = ':' . $prefix . '_' . $index;
            $holders[] = $key;
            $params[$key] = $id;
        }

        return $holders;
    }

    protected function fetchAll($sql, array $params = array())
    {
        return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function execute($sql, array $params = array())
    {
        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute($params)) {
            $error = $stmt && method_exists($stmt, 'errorInfo')
                ? implode(' | ', $stmt->errorInfo())
                : 'prepare/execute failed';

            throw new RuntimeException($error . "\nSQL: " . $sql);
        }

        return $stmt;
    }

    protected function cleanTable($table)
    {
        return str_replace('`', '', trim((string)$table));
    }

    protected function esc($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

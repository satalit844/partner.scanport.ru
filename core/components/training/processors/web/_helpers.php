<?php

require_once dirname(dirname(__DIR__)) . '/model/training/training.class.php';
require_once dirname(dirname(__DIR__)) . '/model/training/services/trainingprogress.class.php';

class TrainingWebHelper
{
    protected static $resolvedTables = array();

    public static function getTraining(modX $modx)
    {
        return new Training($modx);
    }

    public static function getProgressService(modX $modx)
    {
        return new TrainingProgressService($modx, self::getTraining($modx));
    }

    public static function requireAuth(modProcessor $processor, $context = '')
    {
        $context = trim((string)$context);
        if ($context === '') {
            $context = $processor->modx->context ? $processor->modx->context->get('key') : 'web';
        }

        if (
            !$processor->modx->user
            || !(int)$processor->modx->user->get('id')
            || !$processor->modx->user->isAuthenticated($context)
        ) {
            return $processor->failure('Нужно авторизоваться', array('code' => 401, 'context' => $context));
        }

        return null;
    }

    public static function resolveCourseId(modX $modx, $courseId = 0, $resourceId = 0)
    {
        $courseId = (int)$courseId;
        $resourceId = (int)$resourceId;

        if ($courseId > 0) {
            return $courseId;
        }

        if ($resourceId <= 0) {
            return 0;
        }

        /** @var TrainingCourse $course */
        $course = $modx->getObject('TrainingCourse', array(
            'resource_id' => $resourceId,
        ));

        return $course ? (int)$course->get('id') : 0;
    }

    public static function esc($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    public static function normalizePath($path)
    {
        return rtrim(str_replace('\\', '/', (string)$path), '/');
    }

    public static function formatDuration($seconds)
    {
        $seconds = (int)$seconds;
        if ($seconds <= 0) {
            return '0 сек';
        }

        $hours = (int)floor($seconds / 3600);
        $minutes = (int)floor(($seconds % 3600) / 60);
        $secs = (int)($seconds % 60);

        if ($hours > 0) {
            return $hours . ' ч ' . $minutes . ' мин';
        }
        if ($minutes > 0) {
            return $minutes . ' мин ' . $secs . ' сек';
        }
        return $secs . ' сек';
    }

    protected static function tableExists(modX $modx, $table)
    {
        $table = trim((string)$table);
        if ($table === '') {
            return false;
        }
        $stmt = $modx->prepare('SHOW TABLES LIKE :table');
        if (!$stmt) {
            return false;
        }
        if (!$stmt->execute(array(':table' => $table))) {
            return false;
        }
        return (bool)$stmt->fetchColumn();
    }

    protected static function resolveTable(modX $modx, $name)
    {
        $name = ltrim((string)$name, '_');
        if ($name === '') {
            return '';
        }
        if (isset(self::$resolvedTables[$name])) {
            return self::$resolvedTables[$name];
        }

        $prefix = (string)$modx->getOption('table_prefix', null, 'modx_');
        $map = array(
            'lesson_videos' => array($prefix . 'partnerstraining_lesson_videos', 'modx_partnerstraining_lesson_videos'),
            'module_videos' => array($prefix . 'partnerstraining_module_videos', 'modx_partnerstraining_module_videos'),
            'module_slides' => array($prefix . 'partnerstraining_module_slides', 'modx_partnerstraining_module_slides'),
            'user_video_progress' => array($prefix . 'partnerstraining_user_lesson_video_progress', 'modx_partnerstraining_user_lesson_video_progress'),
        );

        $candidates = isset($map[$name]) ? $map[$name] : array($prefix . 'partnerstraining_' . $name);
        foreach (array_values(array_unique(array_filter($candidates))) as $candidate) {
            if (self::tableExists($modx, $candidate)) {
                self::$resolvedTables[$name] = $candidate;
                return $candidate;
            }
        }

        self::$resolvedTables[$name] = reset($candidates);
        return self::$resolvedTables[$name];
    }

    public static function lessonVideosTable(modX $modx)
    {
        return self::resolveTable($modx, 'lesson_videos');
    }

    public static function qualitiesTable(modX $modx)
    {
        return self::resolveTable($modx, 'module_videos');
    }

    public static function slidesTable(modX $modx)
    {
        return self::resolveTable($modx, 'module_slides');
    }

    public static function userVideoProgressTable(modX $modx)
    {
        return self::resolveTable($modx, 'user_video_progress');
    }

    public static function hasUserVideoProgressTable(modX $modx)
    {
        return self::tableExists($modx, self::userVideoProgressTable($modx));
    }

    public static function makePlayerUrl(modX $modx, $moduleResourceId, $lessonId, $videoId = 0)
    {
        $moduleResourceId = (int)$moduleResourceId;
        $lessonId = (int)$lessonId;
        $videoId = (int)$videoId;
        $viewerResourceId = (int)$modx->getOption('training.training_view_resource_id', null, 174);
        if ($viewerResourceId <= 0 || $moduleResourceId <= 0 || $lessonId <= 0) {
            return '';
        }

        $baseUrl = $modx->makeUrl($viewerResourceId);
        if (!$baseUrl) {
            return '';
        }

        $query = array(
            'module' => $moduleResourceId,
            'lesson' => $lessonId,
        );
        if ($videoId > 0) {
            $query['video'] = $videoId;
        }

        return $baseUrl . (strpos($baseUrl, '?') === false ? '?' : '&') . http_build_query($query);
    }

    public static function fetchLessonVideos(modX $modx, $lessonId, $onlyActive = true)
    {
        $lessonId = (int)$lessonId;
        if ($lessonId <= 0) {
            return array();
        }

        $sql = 'SELECT * FROM `' . self::lessonVideosTable($modx) . '` WHERE `lesson_id` = :lesson_id AND TRIM(COALESCE(`source_video`, "")) <> ""';
        if ($onlyActive) {
            $sql .= ' AND `is_active` = 1';
        }
        $sql .= ' ORDER BY `sort_order` ASC, `id` ASC';

        $stmt = $modx->prepare($sql);
        if (!$stmt || !$stmt->execute(array(':lesson_id' => $lessonId))) {
            return array();
        }

        return (array)$stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function fetchLessonVideo(modX $modx, $lessonVideoId, $lessonId = 0, $onlyActive = true)
    {
        $lessonVideoId = (int)$lessonVideoId;
        $lessonId = (int)$lessonId;
        if ($lessonVideoId <= 0) {
            return null;
        }

        $sql = 'SELECT * FROM `' . self::lessonVideosTable($modx) . '` WHERE `id` = :id AND TRIM(COALESCE(`source_video`, "")) <> ""';
        $params = array(':id' => $lessonVideoId);
        if ($lessonId > 0) {
            $sql .= ' AND `lesson_id` = :lesson_id';
            $params[':lesson_id'] = $lessonId;
        }
        if ($onlyActive) {
            $sql .= ' AND `is_active` = 1';
        }
        $sql .= ' LIMIT 1';

        $stmt = $modx->prepare($sql);
        if (!$stmt || !$stmt->execute($params)) {
            return null;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function fetchLessonVideoQualities(modX $modx, $lessonVideoId, $onlyActive = true)
    {
        $lessonVideoId = (int)$lessonVideoId;
        if ($lessonVideoId <= 0) {
            return array();
        }

        $sql = 'SELECT * FROM `' . self::qualitiesTable($modx) . '` WHERE `lesson_video_id` = :lesson_video_id';
        if ($onlyActive) {
            $sql .= ' AND `is_active` = 1';
        }
        $sql .= ' ORDER BY `is_default` DESC, `height` DESC, `width` DESC, `bitrate` DESC, `id` ASC';

        $stmt = $modx->prepare($sql);
        if (!$stmt || !$stmt->execute(array(':lesson_video_id' => $lessonVideoId))) {
            return array();
        }

        return (array)$stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function fetchDefaultVideoQuality(modX $modx, $lessonVideoId)
    {
        $items = self::fetchLessonVideoQualities($modx, $lessonVideoId, true);
        if (empty($items)) {
            return null;
        }
        foreach ($items as $item) {
            if (!empty($item['is_default'])) {
                return $item;
            }
        }
        return reset($items);
    }

    public static function fetchAllLessonSlides(modX $modx, $lessonId)
    {
        $lessonId = (int)$lessonId;
        if ($lessonId <= 0) {
            return array();
        }

        $sql = 'SELECT s.*, COALESCE(v.`title`, \'\') AS `video_title`, COALESCE(v.`sort_order`, 0) AS `video_sort_order`, COALESCE(v.`preview_image`, \'\') AS `video_preview_image`, COALESCE(v.`duration_seconds`, 0) AS `video_duration_seconds` '
            . 'FROM `' . self::slidesTable($modx) . '` s '
            . 'LEFT JOIN `' . self::lessonVideosTable($modx) . '` v ON v.`id` = s.`lesson_video_id` '
            . 'WHERE s.`lesson_id` = :lesson_id AND s.`is_active` = 1 '
            . 'ORDER BY s.`slide_no` ASC, s.`timecode_ms` ASC, s.`id` ASC';

        $stmt = $modx->prepare($sql);
        if (!$stmt || !$stmt->execute(array(':lesson_id' => $lessonId))) {
            return array();
        }

        return (array)$stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function fetchTimelineSlides(modX $modx, $lessonId, $lessonVideoId)
    {
        $lessonId = (int)$lessonId;
        $lessonVideoId = (int)$lessonVideoId;
        if ($lessonId <= 0) {
            return array();
        }

        $sql = 'SELECT * FROM `' . self::slidesTable($modx) . '` WHERE `lesson_id` = :lesson_id AND `is_active` = 1';
        $params = array(':lesson_id' => $lessonId);
        if ($lessonVideoId > 0) {
            $sql .= ' AND (`lesson_video_id` = :lesson_video_id OR `lesson_video_id` = 0)';
            $params[':lesson_video_id'] = $lessonVideoId;
        }
        $sql .= ' ORDER BY `slide_no` ASC, `timecode_ms` ASC, `id` ASC';

        $stmt = $modx->prepare($sql);
        if (!$stmt || !$stmt->execute($params)) {
            return array();
        }

        return (array)$stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPreferredLessonVideoId(modX $modx, $courseId, $lessonId, $userId, $requestedVideoId = 0)
    {
        $courseId = (int)$courseId;
        $lessonId = (int)$lessonId;
        $userId = (int)$userId;
        $requestedVideoId = (int)$requestedVideoId;

        if ($requestedVideoId > 0) {
            $row = self::fetchLessonVideo($modx, $requestedVideoId, $lessonId, true);
            if ($row) {
                return (int)$row['id'];
            }
        }

        if (self::hasUserVideoProgressTable($modx)) {
            $sql = 'SELECT p.`lesson_video_id` FROM `' . self::userVideoProgressTable($modx) . '` p '
                . 'INNER JOIN `' . self::lessonVideosTable($modx) . '` lv ON lv.`id` = p.`lesson_video_id` AND lv.`is_active` = 1 '
                . 'WHERE p.`course_id` = :course_id AND p.`lesson_id` = :lesson_id AND p.`user_id` = :user_id '
                . 'ORDER BY COALESCE(p.`last_watch`, \'0000-00-00 00:00:00\') DESC, p.`max_time` DESC, p.`current_time` DESC, p.`id` DESC LIMIT 1';
            $stmt = $modx->prepare($sql);
            if ($stmt && $stmt->execute(array(':course_id' => $courseId, ':lesson_id' => $lessonId, ':user_id' => $userId))) {
                $value = (int)$stmt->fetchColumn();
                if ($value > 0) {
                    return $value;
                }
            }
        }

        $rows = self::fetchLessonVideos($modx, $lessonId, true);
        foreach ($rows as $row) {
            if (!empty($row['is_default'])) {
                return (int)$row['id'];
            }
        }
        if (!empty($rows)) {
            $first = reset($rows);
            return (int)$first['id'];
        }

        return 0;
    }

    public static function getLessonVideoPosition(array $videoRows, $lessonVideoId)
    {
        $lessonVideoId = (int)$lessonVideoId;
        $total = count($videoRows);
        $position = 0;
        foreach ($videoRows as $index => $row) {
            if ((int)$row['id'] === $lessonVideoId) {
                $position = $index + 1;
                break;
            }
        }
        return array($position, $total);
    }

    public static function getUserVideoProgress(modX $modx, $courseId, $lessonId, $lessonVideoId, $userId)
    {
        if (!self::hasUserVideoProgressTable($modx)) {
            return null;
        }

        $sql = 'SELECT * FROM `' . self::userVideoProgressTable($modx) . '` WHERE `course_id` = :course_id AND `lesson_id` = :lesson_id AND `lesson_video_id` = :lesson_video_id AND `user_id` = :user_id LIMIT 1';
        $stmt = $modx->prepare($sql);
        if (!$stmt || !$stmt->execute(array(
            ':course_id' => (int)$courseId,
            ':lesson_id' => (int)$lessonId,
            ':lesson_video_id' => (int)$lessonVideoId,
            ':user_id' => (int)$userId,
        ))) {
            return null;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function getLessonVideoProgressMap(modX $modx, $courseId, $lessonId, $userId)
    {
        $result = array();
        if (!self::hasUserVideoProgressTable($modx)) {
            return $result;
        }

        $sql = 'SELECT * FROM `' . self::userVideoProgressTable($modx) . '` WHERE `course_id` = :course_id AND `lesson_id` = :lesson_id AND `user_id` = :user_id';
        $stmt = $modx->prepare($sql);
        if (!$stmt || !$stmt->execute(array(
            ':course_id' => (int)$courseId,
            ':lesson_id' => (int)$lessonId,
            ':user_id' => (int)$userId,
        ))) {
            return $result;
        }

        foreach ((array)$stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[(int)$row['lesson_video_id']] = $row;
        }

        return $result;
    }

    public static function saveUserVideoProgress(modX $modx, TrainingProgressService $service, $courseId, $moduleId, $lessonId, $lessonVideoId, $userId, array $data = array())
    {
        if (!self::hasUserVideoProgressTable($modx)) {
            return null;
        }

        $courseId = (int)$courseId;
        $moduleId = (int)$moduleId;
        $lessonId = (int)$lessonId;
        $lessonVideoId = (int)$lessonVideoId;
        $userId = (int)$userId;

        if ($courseId <= 0 || $moduleId <= 0 || $lessonId <= 0 || $lessonVideoId <= 0 || $userId <= 0) {
            return null;
        }

        $videoRow = self::fetchLessonVideo($modx, $lessonVideoId, $lessonId, true);
        if (!$videoRow) {
            return null;
        }

        $existing = self::getUserVideoProgress($modx, $courseId, $lessonId, $lessonVideoId, $userId);
        $durationSeconds = isset($data['duration_seconds']) ? max(0, (int)$data['duration_seconds']) : max(0, (int)$videoRow['duration_seconds']);
        if ($durationSeconds <= 0) {
            $durationSeconds = max(0, (int)$videoRow['duration_seconds']);
        }

        $currentTime = isset($data['current_time']) ? max(0, (int)$data['current_time']) : ($existing ? (int)$existing['current_time'] : 0);
        $inputMaxTime = isset($data['max_time']) ? max(0, (int)$data['max_time']) : 0;
        $maxTime = max($currentTime, $inputMaxTime, $existing ? (int)$existing['max_time'] : 0);
        if ($durationSeconds > 0) {
            if ($currentTime > $durationSeconds) {
                $currentTime = $durationSeconds;
            }
            if ($maxTime > $durationSeconds) {
                $maxTime = $durationSeconds;
            }
        }

        $threshold = method_exists($service, 'getLessonCompletionThresholdPercent')
            ? (int)$service->getLessonCompletionThresholdPercent()
            : 90;

        $completed = !empty($data['completed']) ? 1 : 0;
        $progressPercent = 0;
        if ($durationSeconds > 0) {
            $progressPercent = round(min(100, ($maxTime / $durationSeconds) * 100), 2);
            if ($progressPercent >= $threshold) {
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

        $now = date('Y-m-d H:i:s');
        $table = self::userVideoProgressTable($modx);

        if ($existing) {
            $sql = 'UPDATE `' . $table . '` SET `module_id` = :module_id, `status` = :status, `current_time` = :current_time, `max_time` = :max_time, `watched_seconds` = :watched_seconds, `duration_seconds` = :duration_seconds, `progress_percent` = :progress_percent, `completed` = :completed, `completedon` = :completedon, `last_watch` = :last_watch, `updatedon` = :updatedon WHERE `id` = :id';
            $params = array(
                ':module_id' => $moduleId,
                ':status' => $status,
                ':current_time' => $currentTime,
                ':max_time' => $maxTime,
                ':watched_seconds' => $maxTime,
                ':duration_seconds' => $durationSeconds,
                ':progress_percent' => $progressPercent,
                ':completed' => $completed,
                ':completedon' => $completed ? (!empty($existing['completedon']) ? $existing['completedon'] : $now) : null,
                ':last_watch' => $now,
                ':updatedon' => $now,
                ':id' => (int)$existing['id'],
            );
        } else {
            $sql = 'INSERT INTO `' . $table . '` (`course_id`,`module_id`,`lesson_id`,`lesson_video_id`,`user_id`,`status`,`current_time`,`max_time`,`watched_seconds`,`duration_seconds`,`progress_percent`,`completed`,`completedon`,`last_watch`,`createdon`,`updatedon`) VALUES (:course_id,:module_id,:lesson_id,:lesson_video_id,:user_id,:status,:current_time,:max_time,:watched_seconds,:duration_seconds,:progress_percent,:completed,:completedon,:last_watch,:createdon,:updatedon)';
            $params = array(
                ':course_id' => $courseId,
                ':module_id' => $moduleId,
                ':lesson_id' => $lessonId,
                ':lesson_video_id' => $lessonVideoId,
                ':user_id' => $userId,
                ':status' => $status,
                ':current_time' => $currentTime,
                ':max_time' => $maxTime,
                ':watched_seconds' => $maxTime,
                ':duration_seconds' => $durationSeconds,
                ':progress_percent' => $progressPercent,
                ':completed' => $completed,
                ':completedon' => $completed ? $now : null,
                ':last_watch' => $now,
                ':createdon' => $now,
                ':updatedon' => $now,
            );
        }

        $stmt = $modx->prepare($sql);
        if (!$stmt || !$stmt->execute($params)) {
            return null;
        }

        return self::getUserVideoProgress($modx, $courseId, $lessonId, $lessonVideoId, $userId);
    }

    public static function syncLegacyLessonProgressFromVideos(modX $modx, TrainingProgressService $service, $courseId, $moduleId, $lessonId, $userId)
    {
        $courseId = (int)$courseId;
        $moduleId = (int)$moduleId;
        $lessonId = (int)$lessonId;
        $userId = (int)$userId;

        $videoRows = self::fetchLessonVideos($modx, $lessonId, true);
        if (empty($videoRows)) {
            return $service->ensureLessonProgress($courseId, $lessonId, $userId, 0);
        }

        if (!self::hasUserVideoProgressTable($modx)) {
            return $service->ensureLessonProgress($courseId, $lessonId, $userId, 0);
        }

        $progressMap = self::getLessonVideoProgressMap($modx, $courseId, $lessonId, $userId);
        $totalDuration = 0;
        $watchedSeconds = 0;
        $completedVideos = 0;
        $hasStarted = false;

        foreach ($videoRows as $videoRow) {
            $videoId = (int)$videoRow['id'];
            $duration = max(0, (int)$videoRow['duration_seconds']);
            $progressRow = isset($progressMap[$videoId]) ? $progressMap[$videoId] : null;
            if ($progressRow) {
                $duration = max($duration, (int)$progressRow['duration_seconds']);
            }
            $totalDuration += $duration;
            if ($progressRow) {
                $watchedSeconds += min($duration > 0 ? $duration : (int)$progressRow['max_time'], max((int)$progressRow['max_time'], (int)$progressRow['current_time']));
                if (!empty($progressRow['completed'])) {
                    $completedVideos++;
                }
                if (!empty($progressRow['completed']) || (int)$progressRow['max_time'] > 0 || (int)$progressRow['current_time'] > 0 || (float)$progressRow['progress_percent'] > 0) {
                    $hasStarted = true;
                }
            }
        }

        $preferredVideoId = self::getPreferredLessonVideoId($modx, $courseId, $lessonId, $userId, 0);
        $preferredProgress = $preferredVideoId > 0 && isset($progressMap[$preferredVideoId]) ? $progressMap[$preferredVideoId] : null;
        $currentTime = $preferredProgress ? (int)$preferredProgress['current_time'] : 0;
        $maxTime = $preferredProgress ? (int)$preferredProgress['max_time'] : 0;
        $completed = count($videoRows) > 0 && $completedVideos >= count($videoRows) ? 1 : 0;
        $progressPercent = 0;
        if ($totalDuration > 0) {
            $progressPercent = round(min(100, ($watchedSeconds / $totalDuration) * 100), 2);
        } elseif ($completed) {
            $progressPercent = 100;
        }
        if ($completed) {
            $progressPercent = 100;
        }

        $status = 'not_started';
        if ($completed) {
            $status = 'completed';
        } elseif ($hasStarted || $watchedSeconds > 0 || $currentTime > 0 || $maxTime > 0) {
            $status = 'in_progress';
        }

        /** @var TrainingUserLessonProgress $lessonProgress */
        $lessonProgress = $service->ensureLessonProgress($courseId, $lessonId, $userId, $totalDuration);
        if (!$lessonProgress) {
            return null;
        }

        $now = date('Y-m-d H:i:s');
        $lessonProgress->set('status', $status);
        $lessonProgress->set('current_time', $currentTime);
        $lessonProgress->set('max_time', $maxTime);
        $lessonProgress->set('watched_seconds', $watchedSeconds);
        $lessonProgress->set('duration_seconds', $totalDuration);
        $lessonProgress->set('progress_percent', $progressPercent);
        $lessonProgress->set('completed', $completed);
        $lessonProgress->set('completedon', $completed ? ($lessonProgress->get('completedon') ?: $now) : null);
        $lessonProgress->set('last_watch', $hasStarted ? $now : $lessonProgress->get('last_watch'));
        $lessonProgress->save();

        return $lessonProgress;
    }

    public static function getLessonVideoStats(modX $modx, TrainingProgressService $service, $courseId, $lessonId, $userId)
    {
        $courseId = (int)$courseId;
        $lessonId = (int)$lessonId;
        $userId = (int)$userId;

        /*
         * training-get-request-memo-v1:lesson-video-stats
         *
         * The same video stats are rendered in several course-page blocks.
         * They are immutable while one ordinary GET is being rendered.
         * POST progress updates deliberately bypass this request memo.
         */
        static $requestMemo = array();
        $memoEnabled = isset($_SERVER['REQUEST_METHOD'])
            && strtoupper((string)$_SERVER['REQUEST_METHOD']) === 'GET';
        $memoKey = $courseId . '|' . $lessonId . '|' . $userId;

        if ($memoEnabled && array_key_exists($memoKey, $requestMemo)) {
            return $requestMemo[$memoKey];
        }

        $rows = self::fetchLessonVideos($modx, $lessonId, true);
        $stats = array(
            'total_videos' => count($rows),
            'completed_videos' => 0,
            'started_videos' => 0,
        );

        if (empty($rows)) {
            if ($memoEnabled) {
                $requestMemo[$memoKey] = $stats;
            }
            return $stats;
        }

        if (self::hasUserVideoProgressTable($modx)) {
            $progressMap = self::getLessonVideoProgressMap($modx, $courseId, $lessonId, $userId);
            foreach ($rows as $row) {
                $videoId = (int)$row['id'];
                if (empty($progressMap[$videoId])) {
                    continue;
                }
                $progress = $progressMap[$videoId];
                if (!empty($progress['completed'])) {
                    $stats['completed_videos']++;
                }
                if (!empty($progress['completed']) || (int)$progress['max_time'] > 0 || (int)$progress['current_time'] > 0 || (float)$progress['progress_percent'] > 0 || (string)$progress['status'] !== 'not_started') {
                    $stats['started_videos']++;
                }
            }

            if ($memoEnabled) {
                $requestMemo[$memoKey] = $stats;
            }
            return $stats;
        }

        $lessonProgress = $service->getLessonProgress($courseId, $lessonId, $userId);
        if ($lessonProgress && (int)$lessonProgress->get('completed') === 1) {
            $stats['completed_videos'] = $stats['total_videos'];
            $stats['started_videos'] = $stats['total_videos'];
        } elseif ($lessonProgress && (
            (int)$lessonProgress->get('max_time') > 0 ||
            (int)$lessonProgress->get('current_time') > 0 ||
            (float)$lessonProgress->get('progress_percent') > 0 ||
            (string)$lessonProgress->get('status') !== 'not_started'
        )) {
            $stats['started_videos'] = min(1, $stats['total_videos']);
        }

        if ($memoEnabled) {
            $requestMemo[$memoKey] = $stats;
        }

        return $stats;
    }

    public static function getCourseVideoStats(modX $modx, TrainingProgressService $service, $courseId, $userId)
    {
        $result = array(
            'total_videos' => 0,
            'completed_videos' => 0,
            'started_videos' => 0,
        );

        $modules = $service->getCourseModules((int)$courseId, true, false);
        foreach ($modules as $module) {
            $lessons = $service->getModuleLessons((int)$module->get('id'), true, false);
            foreach ($lessons as $lesson) {
                $stats = self::getLessonVideoStats($modx, $service, $courseId, (int)$lesson->get('id'), $userId);
                $result['total_videos'] += (int)$stats['total_videos'];
                $result['completed_videos'] += (int)$stats['completed_videos'];
                $result['started_videos'] += (int)$stats['started_videos'];
            }
        }

        return $result;
    }
}

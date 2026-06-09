<?php

require_once dirname(__DIR__) . '/_media_helper.php';

class TrainingLessonVideoHelper
{
    protected static $resolvedTables = [];

    protected static function tableExists(modX $modx, $table)
    {
        $table = trim((string)$table);
        if ($table === '') { return false; }
        $stmt = $modx->prepare('SHOW TABLES LIKE :table');
        if (!$stmt) { return false; }
        if (!$stmt->execute([':table' => $table])) { return false; }
        return (bool)$stmt->fetchColumn();
    }

    public static function table(modX $modx, $name)
    {
        $name = ltrim((string)$name, '_');
        if ($name === '') { return ''; }
        if (isset(self::$resolvedTables[$name])) { return self::$resolvedTables[$name]; }

        $prefix = (string)$modx->getOption('table_prefix', null, 'modx_');
        $candidates = [
            $modx->getTableName('TrainingModuleLesson'),
            $modx->getTableName('TrainingModuleVideo'),
            $modx->getTableName('TrainingModuleSlide'),
        ];
        $map = [
            'lesson_videos' => [$prefix . 'partnerstraining_lesson_videos', 'modx_partnerstraining_lesson_videos'],
            'module_videos' => [$prefix . 'partnerstraining_module_videos', 'modx_partnerstraining_module_videos'],
            'module_slides' => [$prefix . 'partnerstraining_module_slides', 'modx_partnerstraining_module_slides'],
        ];
        $candidates = isset($map[$name]) ? array_merge($map[$name], $candidates) : $candidates;
        foreach (array_values(array_unique(array_filter($candidates))) as $candidate) {
            if (self::tableExists($modx, $candidate)) {
                self::$resolvedTables[$name] = $candidate;
                return $candidate;
            }
        }
        self::$resolvedTables[$name] = $prefix . 'partnerstraining_' . $name;
        return self::$resolvedTables[$name];
    }

    public static function lessonVideosTable(modX $modx){ return self::table($modx, 'lesson_videos'); }
    public static function qualitiesTable(modX $modx){ return self::table($modx, 'module_videos'); }
    public static function slidesTable(modX $modx){ return self::table($modx, 'module_slides'); }

    public static function fetchVideo(modX $modx, $id)
    {
        $id = (int)$id;
        if ($id <= 0) { return null; }
        $sql = 'SELECT * FROM `' . self::lessonVideosTable($modx) . '` WHERE `id` = :id LIMIT 1';
        $stmt = $modx->prepare($sql);
        if (!$stmt || !$stmt->execute([':id' => $id])) { return null; }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function getLesson(modX $modx, $lessonId)
    {
        $lessonId = (int)$lessonId;
        if ($lessonId <= 0) { return null; }
        return $modx->getObject('TrainingModuleLesson', ['id' => $lessonId]);
    }

    public static function countLessonVideos(modX $modx, $lessonId)
    {
        $sql = "SELECT COUNT(*) FROM `" . self::lessonVideosTable($modx) . "` WHERE `lesson_id` = :lesson_id AND TRIM(COALESCE(`source_video`, '')) != ''";
        $stmt = $modx->prepare($sql);
        if (!$stmt || !$stmt->execute([':lesson_id' => (int)$lessonId])) { return 0; }
        return (int)$stmt->fetchColumn();
    }

    public static function countLessonSlides(modX $modx, $lessonId)
    {
        $sql = 'SELECT COUNT(*) FROM `' . self::slidesTable($modx) . '` WHERE `lesson_id` = :lesson_id AND `lesson_video_id` > 0 AND `is_active` = 1';
        $stmt = $modx->prepare($sql);
        if (!$stmt || !$stmt->execute([':lesson_id' => (int)$lessonId])) { return 0; }
        return (int)$stmt->fetchColumn();
    }

    public static function countLessonQualities(modX $modx, $lessonVideoId)
    {
        $stmt = $modx->prepare('SELECT COUNT(*) FROM `' . self::qualitiesTable($modx) . '` WHERE `lesson_video_id` = :lesson_video_id');
        if (!$stmt || !$stmt->execute([':lesson_video_id' => (int)$lessonVideoId])) { return 0; }
        return (int)$stmt->fetchColumn();
    }

    public static function countVideoSlides(modX $modx, $lessonVideoId)
    {
        $stmt = $modx->prepare('SELECT COUNT(*) FROM `' . self::slidesTable($modx) . '` WHERE `lesson_video_id` = :lesson_video_id');
        if (!$stmt || !$stmt->execute([':lesson_video_id' => (int)$lessonVideoId])) { return 0; }
        return (int)$stmt->fetchColumn();
    }

    public static function getCourseIdByLesson(modX $modx, TrainingModuleLesson $lesson)
    {
        $module = $lesson->getOne('Module');
        return $module ? (int)$module->get('course_id') : 0;
    }

    public static function resolveVideoDirs(modX $modx, TrainingModuleLesson $lesson, $videoId)
    {
        $courseId = self::getCourseIdByLesson($modx, $lesson);
        $moduleId = (int)$lesson->get('module_id');
        $lessonId = (int)$lesson->get('id');
        $videoId = (int)$videoId;
        $baseAbsolute = rtrim($modx->getOption('base_path'), '/\\') . '/assets/training/courses/' . $courseId . '/modules/' . $moduleId . '/lessons/' . $lessonId . '/videos/' . $videoId . '/';
        return [
            'base_absolute' => TrainingMediaHelper::normalizeFsPath($baseAbsolute, false),
            'base_web' => TrainingMediaHelper::fsPathToWeb($modx, $baseAbsolute),
            'source_absolute' => TrainingMediaHelper::normalizeFsPath($baseAbsolute . 'source/', false),
            'qualities_absolute' => TrainingMediaHelper::normalizeFsPath($baseAbsolute . 'qualities/', false),
        ];
    }

    public static function buildSourceFilename(modX $modx, TrainingModuleLesson $lesson, $videoId, $extension)
    {
        $courseId = self::getCourseIdByLesson($modx, $lesson);
        $moduleId = (int)$lesson->get('module_id');
        $lessonId = (int)$lesson->get('id');
        $extension = TrainingMediaHelper::sanitizeExtension($extension, 'mp4');
        return 'course_' . $courseId . '_module_' . $moduleId . '_lesson_' . $lessonId . '_video_' . (int)$videoId . '_source.' . $extension;
    }

    public static function buildQualityFilename(modX $modx, TrainingModuleLesson $lesson, $videoId, $quality, $extension)
    {
        $courseId = self::getCourseIdByLesson($modx, $lesson);
        $moduleId = (int)$lesson->get('module_id');
        $lessonId = (int)$lesson->get('id');
        $extension = TrainingMediaHelper::sanitizeExtension($extension, 'mp4');
        $quality = TrainingMediaHelper::sanitizeQuality($quality, 'video');
        return 'course_' . $courseId . '_module_' . $moduleId . '_lesson_' . $lessonId . '_video_' . (int)$videoId . '_' . $quality . '.' . $extension;
    }

    public static function ensureVideoDirs(modX $modx, TrainingModuleLesson $lesson, $videoId)
    {
        $dirs = self::resolveVideoDirs($modx, $lesson, $videoId);
        return TrainingMediaHelper::ensureDir($dirs['base_absolute']) && TrainingMediaHelper::ensureDir($dirs['source_absolute']) && TrainingMediaHelper::ensureDir($dirs['qualities_absolute']);
    }

    public static function clearDefaultLessonVideo(modX $modx, $lessonId, $keepId)
    {
        $stmt = $modx->prepare('UPDATE `' . self::lessonVideosTable($modx) . '` SET `is_default` = 0 WHERE `lesson_id` = :lesson_id AND `id` != :id');
        if ($stmt) { $stmt->execute([':lesson_id' => (int)$lessonId, ':id' => (int)$keepId]); }
    }

    public static function clearDefaultQuality(modX $modx, $lessonVideoId, $keepId)
    {
        $stmt = $modx->prepare('UPDATE `' . self::qualitiesTable($modx) . '` SET `is_default` = 0 WHERE `lesson_video_id` = :lesson_video_id AND `id` != :id');
        if ($stmt) { $stmt->execute([':lesson_video_id' => (int)$lessonVideoId, ':id' => (int)$keepId]); }
    }

    public static function nextSortOrder(modX $modx, $lessonId)
    {
        $stmt = $modx->prepare('SELECT MAX(`sort_order`) FROM `' . self::lessonVideosTable($modx) . '` WHERE `lesson_id` = :lesson_id');
        if (!$stmt || !$stmt->execute([':lesson_id' => (int)$lessonId])) { return 1; }
        return max(1, (int)$stmt->fetchColumn() + 1);
    }

    public static function reorderRows(modX $modx, $table, $foreignField, $foreignId, $orderField, $movedId, $targetId, $position = 'after')
    {
        $foreignId = (int)$foreignId; $movedId = (int)$movedId; $targetId = (int)$targetId;
        $position = strtolower((string)$position) === 'before' ? 'before' : 'after';
        if ($foreignId <= 0 || $movedId <= 0 || $targetId <= 0 || $movedId === $targetId) { return false; }
        $stmt = $modx->prepare('SELECT `id` FROM `' . $table . '` WHERE `' . $foreignField . '` = :foreign_id ORDER BY `' . $orderField . '` ASC, `id` ASC');
        if (!$stmt || !$stmt->execute([':foreign_id' => $foreignId])) { return false; }
        $ids = [];
        foreach ((array)$stmt->fetchAll(PDO::FETCH_ASSOC) as $row) { $ids[] = (int)$row['id']; }
        if (!$ids || !in_array($movedId, $ids, true) || !in_array($targetId, $ids, true)) { return false; }
        $ids = array_values(array_filter($ids, function($id) use ($movedId){ return (int)$id !== (int)$movedId; }));
        $targetIndex = array_search($targetId, $ids, true);
        if ($targetIndex === false) { return false; }
        if ($position === 'after') { $targetIndex++; }
        array_splice($ids, $targetIndex, 0, [$movedId]);
        $modx->beginTransaction();
        try {
            $update = $modx->prepare('UPDATE `' . $table . '` SET `' . $orderField . '` = :sort_value WHERE `id` = :id');
            if (!$update) { throw new RuntimeException('Не удалось подготовить reorder-запрос'); }
            $sortValue = 1;
            foreach ($ids as $id) {
                if (!$update->execute([':sort_value' => $sortValue, ':id' => (int)$id])) {
                    $error = $update->errorInfo();
                    throw new RuntimeException(!empty($error[2]) ? $error[2] : 'Ошибка обновления порядка');
                }
                $sortValue++;
            }
            $modx->commit();
            return true;
        } catch (Exception $e) {
            $modx->rollBack();
            $modx->log(modX::LOG_LEVEL_ERROR, '[Training] reorderRows error: ' . $e->getMessage());
            return false;
        }
    }

    public static function recalcLesson(modX $modx, TrainingModuleLesson $lesson)
    {
        $lessonId = (int)$lesson->get('id');
        $stmt = $modx->prepare('SELECT COALESCE(SUM(`duration_seconds`),0) FROM `' . self::lessonVideosTable($modx) . '` WHERE `lesson_id` = :lesson_id AND `is_active` = 1');
        $duration = 0;
        if ($stmt && $stmt->execute([':lesson_id' => $lessonId])) { $duration = (int)$stmt->fetchColumn(); }
        $stmt = $modx->prepare('SELECT COUNT(*) FROM `' . self::qualitiesTable($modx) . '` WHERE `lesson_id` = :lesson_id AND `is_active` = 1');
        $qualities = 0;
        if ($stmt && $stmt->execute([':lesson_id' => $lessonId])) { $qualities = (int)$stmt->fetchColumn(); }
        $stmt = $modx->prepare('SELECT COUNT(*) FROM `' . self::slidesTable($modx) . '` WHERE `lesson_id` = :lesson_id AND `is_active` = 1');
        $slides = 0;
        if ($stmt && $stmt->execute([':lesson_id' => $lessonId])) { $slides = (int)$stmt->fetchColumn(); }
        $videoStatus = 'none';
        if ($qualities > 0) {
            $videoStatus = 'ready';
        } else {
            $stmt = $modx->prepare("SELECT COUNT(*) FROM `" . self::lessonVideosTable($modx) . "` WHERE `lesson_id` = :lesson_id AND TRIM(COALESCE(`source_video`, '')) != ''");
            $hasSource = 0;
            if ($stmt && $stmt->execute([':lesson_id' => $lessonId])) { $hasSource = (int)$stmt->fetchColumn(); }
            if ($hasSource > 0) { $videoStatus = 'available'; }
        }
        $presentationStatus = 'none';
        if ($slides > 0) { $presentationStatus = 'ready'; }
        elseif (trim((string)$lesson->get('source_presentation')) !== '') { $presentationStatus = 'available'; }
        $lesson->set('duration_seconds', $duration);
        $lesson->set('video_status', $videoStatus);
        $lesson->set('presentation_status', $presentationStatus);
        $lesson->set('updatedon', date('Y-m-d H:i:s'));
        $lesson->save();
    }

    public static function unlinkWebPath(modX $modx, $path)
    {
        $absolute = TrainingMediaHelper::resolveLocalPath($modx, $path);
        if ($absolute !== '' && is_file($absolute)) { @unlink($absolute); }
    }

    public static function applyEvenSlideTimecodes(modX $modx, $lessonVideoId, $durationMs)
    {
        $lessonVideoId = (int)$lessonVideoId;
        $durationMs = max(0, (int)$durationMs);
        if ($lessonVideoId <= 0 || $durationMs <= 0) { return 0; }
        $stmt = $modx->prepare('SELECT `id` FROM `' . self::slidesTable($modx) . '` WHERE `lesson_video_id` = :lesson_video_id AND `is_active` = 1 ORDER BY `slide_no` ASC, `id` ASC');
        if (!$stmt || !$stmt->execute([':lesson_video_id' => $lessonVideoId])) { return 0; }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) { return 0; }
        $count = count($rows);
        $step = $count > 0 ? (int)floor($durationMs / $count) : 0;
        $updated = 0;
        $updateStmt = $modx->prepare('UPDATE `' . self::slidesTable($modx) . '` SET `timecode_ms` = :timecode_ms WHERE `id` = :id');
        foreach ($rows as $index => $row) {
            if ($updateStmt && $updateStmt->execute([':timecode_ms' => ($step * $index), ':id' => (int)$row['id']])) { $updated++; }
        }
        return $updated;
    }
}

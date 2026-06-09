<?php

class TrainingMediaHelper
{
    public static function normalizeFsPath($path, $allowFile = true)
    {
        $path = str_replace('\\', '/', (string)$path);
        $path = preg_replace('#/+#', '/', $path);

        if ($path === '') {
            return '';
        }

        if ($allowFile && preg_match('#\.[a-z0-9]{2,6}$#i', $path)) {
            return $path;
        }

        return rtrim($path, '/') . '/';
    }

    public static function fsPathToWeb(modX $modx, $absolutePath)
    {
        $absolutePath = self::normalizeFsPath($absolutePath);
        $basePath = self::normalizeFsPath($modx->getOption('base_path'));

        if ($absolutePath !== '' && strpos($absolutePath, $basePath) === 0) {
            return '/' . ltrim(substr($absolutePath, strlen($basePath)), '/');
        }

        return $absolutePath;
    }

    public static function webPathToFs(modX $modx, $path)
    {
        $path = trim((string)$path);
        if ($path === '') {
            return '';
        }

        if (preg_match('#^(?:[a-z]:)?/#i', $path) && is_file($path)) {
            return $path;
        }

        if (preg_match('#^(https?:)?//#i', $path)) {
            return '';
        }

        return rtrim($modx->getOption('base_path'), '/\\') . '/' . ltrim($path, '/');
    }

    public static function resolveLocalPath(modX $modx, $path)
    {
        $path = trim((string)$path);
        if ($path === '') {
            return '';
        }

        if (preg_match('#^(?:[a-z]:)?/#i', $path) && is_file($path)) {
            return self::normalizeFsPath($path, true);
        }

        $absolute = self::webPathToFs($modx, $path);
        if ($absolute !== '' && is_file($absolute)) {
            return self::normalizeFsPath($absolute, true);
        }

        return '';
    }

    public static function ensureDir($path)
    {
        $path = self::normalizeFsPath($path, false);
        if ($path === '') {
            return false;
        }

        if (is_dir($path)) {
            return true;
        }

        return @mkdir($path, 0775, true) || is_dir($path);
    }

    public static function copyInto($sourcePath, $targetPath)
    {
        $sourcePath = self::normalizeFsPath($sourcePath, true);
        $targetPath = self::normalizeFsPath($targetPath, true);

        if ($sourcePath === '' || $targetPath === '' || !is_file($sourcePath)) {
            return false;
        }

        if (!self::ensureDir(dirname($targetPath))) {
            return false;
        }

        if (realpath($sourcePath) === realpath($targetPath)) {
            return true;
        }

        return @copy($sourcePath, $targetPath);
    }

    public static function getCommand(modX $modx, $key, $default)
    {
        return trim((string)$modx->getOption($key, null, $default, true));
    }

    public static function runCommand(modX $modx, $command, array &$output = [], &$exitCode = 0)
    {
        $output = [];
        $exitCode = 1;

        if (!function_exists('exec')) {
            $output[] = 'exec() disabled';
            return false;
        }

        $modx->log(modX::LOG_LEVEL_INFO, '[training] ' . $command);
        exec($command . ' 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            $modx->log(modX::LOG_LEVEL_ERROR, '[training] command failed (' . $exitCode . '): ' . implode("\n", $output));
            return false;
        }

        return true;
    }


    public static function sanitizeExtension($extension, $default = '')
    {
        $extension = strtolower(trim((string)$extension));
        $extension = preg_replace('/[^a-z0-9]+/', '', $extension);
        if ($extension === '') {
            $extension = strtolower(trim((string)$default));
            $extension = preg_replace('/[^a-z0-9]+/', '', $extension);
        }
        return $extension;
    }

    public static function sanitizeQuality($quality, $default = 'video')
    {
        $quality = strtolower(trim((string)$quality));
        $quality = preg_replace('/[^a-z0-9]+/', '_', $quality);
        $quality = trim($quality, '_');
        return $quality !== '' ? $quality : $default;
    }

    public static function extractLastNumber($value)
    {
        if (preg_match_all('/(\d+)/', (string)$value, $matches) && !empty($matches[1])) {
            return (int)end($matches[1]);
        }

        return 0;
    }

    public static function buildCoursePresentationSourceFilename(TrainingCourse $course, $extension)
    {
        $extension = self::sanitizeExtension($extension, 'pdf');
        return 'course_' . (int)$course->get('id') . '_presentation_source.' . $extension;
    }

    public static function buildCoursePresentationPdfFilename(TrainingCourse $course)
    {
        return 'course_' . (int)$course->get('id') . '_presentation.pdf';
    }

    public static function buildCourseSlideFilename(TrainingCourse $course, $index, $extension = 'jpg')
    {
        $extension = self::sanitizeExtension($extension, 'jpg');
        return 'course_' . (int)$course->get('id') . '_slide_' . sprintf('%03d', max(1, (int)$index)) . '.' . $extension;
    }

    public static function buildModulePresentationSourceFilename(TrainingModule $module, $extension)
    {
        $extension = self::sanitizeExtension($extension, 'pdf');
        return 'course_' . (int)$module->get('course_id') . '_module_' . (int)$module->get('id') . '_presentation_source.' . $extension;
    }

    public static function buildModulePresentationPdfFilename(TrainingModule $module)
    {
        return 'course_' . (int)$module->get('course_id') . '_module_' . (int)$module->get('id') . '_presentation.pdf';
    }

    public static function buildModuleSlideFilename(TrainingModule $module, $index, $extension = 'jpg')
    {
        $extension = self::sanitizeExtension($extension, 'jpg');
        return 'course_' . (int)$module->get('course_id') . '_module_' . (int)$module->get('id') . '_slide_' . sprintf('%03d', max(1, (int)$index)) . '.' . $extension;
    }

    public static function buildLessonPresentationPdfFilename(TrainingModuleLesson $lesson, $extension = 'pdf')
    {
        $extension = self::sanitizeExtension($extension, 'pdf');
        $moduleId = (int)$lesson->get('module_id');
        $courseId = 0;
        $module = $lesson->getOne('Module');
        if ($module) {
            $courseId = (int)$module->get('course_id');
        }

        return 'course_' . $courseId . '_module_' . $moduleId . '_lesson_' . (int)$lesson->get('id') . '_presentation.' . $extension;
    }

    public static function buildLessonVideoSourceFilename(TrainingModuleLesson $lesson, $extension)
    {
        $extension = self::sanitizeExtension($extension, 'mp4');
        $moduleId = (int)$lesson->get('module_id');
        $courseId = 0;
        $module = $lesson->getOne('Module');
        if ($module) {
            $courseId = (int)$module->get('course_id');
        }

        return 'course_' . $courseId . '_module_' . $moduleId . '_lesson_' . (int)$lesson->get('id') . '_source.' . $extension;
    }

    public static function buildLessonQualityVideoFilename(TrainingModuleLesson $lesson, $quality, $extension = 'mp4')
    {
        $extension = self::sanitizeExtension($extension, 'mp4');
        $quality = self::sanitizeQuality($quality, 'video');
        $moduleId = (int)$lesson->get('module_id');
        $courseId = 0;
        $module = $lesson->getOne('Module');
        if ($module) {
            $courseId = (int)$module->get('course_id');
        }

        return 'course_' . $courseId . '_module_' . $moduleId . '_lesson_' . (int)$lesson->get('id') . '_' . $quality . '.' . $extension;
    }

    public static function buildLessonPreviewFilename(TrainingModuleLesson $lesson, $extension = 'jpg')
    {
        $extension = self::sanitizeExtension($extension, 'jpg');
        $moduleId = (int)$lesson->get('module_id');
        $courseId = 0;
        $module = $lesson->getOne('Module');
        if ($module) {
            $courseId = (int)$module->get('course_id');
        }

        return 'course_' . $courseId . '_module_' . $moduleId . '_lesson_' . (int)$lesson->get('id') . '_preview.' . $extension;
    }

    public static function resolveCoursePresentationDirs(modX $modx, TrainingCourse $course)
    {
        $base = rtrim($modx->getOption('base_path'), '/\\') . '/assets/training/courses/' . (int)$course->get('id') . '/presentation/';
        $pdfName = self::buildCoursePresentationPdfFilename($course);

        return [
            'base_absolute' => self::normalizeFsPath($base),
            'base_web' => self::fsPathToWeb($modx, $base),
            'slides_absolute' => self::normalizeFsPath($base . 'slides/'),
            'slides_web' => self::fsPathToWeb($modx, $base . 'slides/'),
            'pdf_absolute' => self::normalizeFsPath($base . $pdfName, true),
            'pdf_web' => self::fsPathToWeb($modx, $base . $pdfName),
        ];
    }

    public static function resolveModulePresentationDirs(modX $modx, TrainingModule $module)
    {
        $courseId = (int)$module->get('course_id');
        $moduleId = (int)$module->get('id');
        $base = rtrim($modx->getOption('base_path'), '/\\') . '/assets/training/courses/' . $courseId . '/modules/' . $moduleId . '/presentation/';
        $pdfName = self::buildModulePresentationPdfFilename($module);

        return [
            'base_absolute' => self::normalizeFsPath($base),
            'base_web' => self::fsPathToWeb($modx, $base),
            'slides_absolute' => self::normalizeFsPath($base . 'slides/'),
            'slides_web' => self::fsPathToWeb($modx, $base . 'slides/'),
            'pdf_absolute' => self::normalizeFsPath($base . $pdfName, true),
            'pdf_web' => self::fsPathToWeb($modx, $base . $pdfName),
        ];
    }

    public static function resolveModuleVideoDirs(modX $modx, TrainingModule $module)
    {
        $courseId = (int)$module->get('course_id');
        $base = rtrim($modx->getOption('base_path'), '/\\') . '/assets/training/courses/' . $courseId . '/lessons/' . (int)$module->get('id') . '/';

        return [
            'base_absolute' => self::normalizeFsPath($base),
            'base_web' => self::fsPathToWeb($modx, $base),
            'source_absolute' => self::normalizeFsPath($base . 'source/'),
            'source_web' => self::fsPathToWeb($modx, $base . 'source/'),
            'video_absolute' => self::normalizeFsPath($base . 'video/'),
            'video_web' => self::fsPathToWeb($modx, $base . 'video/'),
            'preview_absolute' => self::normalizeFsPath($base . 'preview/'),
            'preview_web' => self::fsPathToWeb($modx, $base . 'preview/'),
        ];
    }

    public static function detectVideoMeta(modX $modx, $path)
    {
        $ffprobe = self::getCommand($modx, 'training_ffprobe_command', 'ffprobe');
        $path = self::normalizeFsPath($path, true);
        $output = [];
        $code = 0;
        $command = escapeshellcmd($ffprobe) . ' -v quiet -print_format json -show_streams -show_format ' . escapeshellarg($path);

        if (!self::runCommand($modx, $command, $output, $code)) {
            return [
                'width' => 0,
                'height' => 0,
                'duration' => 0,
                'bitrate' => 0,
                'filesize' => is_file($path) ? (int)filesize($path) : 0,
            ];
        }

        $json = json_decode(implode("\n", $output), true);
        $width = 0;
        $height = 0;
        $duration = 0;
        $bitrate = 0;

        if (!empty($json['streams']) && is_array($json['streams'])) {
            foreach ($json['streams'] as $stream) {
                if (!empty($stream['codec_type']) && $stream['codec_type'] === 'video') {
                    $width = (int)($stream['width'] ?? 0);
                    $height = (int)($stream['height'] ?? 0);
                    if (!empty($stream['duration'])) {
                        $duration = (float)$stream['duration'];
                    }
                    if (!empty($stream['bit_rate'])) {
                        $bitrate = (int)round(((int)$stream['bit_rate']) / 1000);
                    }
                    break;
                }
            }
        }

        if ($duration <= 0 && !empty($json['format']['duration'])) {
            $duration = (float)$json['format']['duration'];
        }
        if ($bitrate <= 0 && !empty($json['format']['bit_rate'])) {
            $bitrate = (int)round(((int)$json['format']['bit_rate']) / 1000);
        }

        return [
            'width' => $width,
            'height' => $height,
            'duration' => $duration,
            'bitrate' => $bitrate,
            'filesize' => is_file($path) ? (int)filesize($path) : 0,
        ];
    }

    public static function upsertModuleVideo(modX $modx, $moduleId, array $data)
    {
        $quality = trim((string)($data['quality'] ?? ''));
        if ($moduleId <= 0 || $quality === '') {
            return false;
        }

        /** @var TrainingModuleVideo $video */
        $video = $modx->getObject('TrainingModuleVideo', [
            'module_id' => (int)$moduleId,
            'quality' => $quality,
        ]);

        if (!$video) {
            $video = $modx->newObject('TrainingModuleVideo');
            $video->set('module_id', (int)$moduleId);
            $video->set('quality', $quality);
        }

        foreach (['mime', 'file_path', 'width', 'height', 'bitrate', 'filesize', 'is_default', 'is_active'] as $field) {
            if (array_key_exists($field, $data)) {
                $video->set($field, $data[$field]);
            }
        }

        return $video->save();
    }

    public static function clearOtherDefaultVideos(modX $modx, $moduleId, $keepQuality)
    {
        $c = $modx->newQuery('TrainingModuleVideo');
        $c->where([
            'module_id' => (int)$moduleId,
            'quality:!=' => (string)$keepQuality,
        ]);
        $items = $modx->getCollection('TrainingModuleVideo', $c);
        foreach ($items as $item) {
            if ((int)$item->get('is_default') === 1) {
                $item->set('is_default', 0);
                $item->save();
            }
        }
    }

    
public static function resolveLessonVideoDirs(modX $modx, TrainingModuleLesson $lesson)
    {
        /** @var TrainingModule $module */
        $module = $lesson->getOne('Module');
        if (!$module) {
            $module = $modx->getObject('TrainingModule', ['id' => (int)$lesson->get('module_id')]);
        }
        if (!$module) {
            return [];
        }

        $courseId = (int)$module->get('course_id');
        $lessonId = (int)$lesson->get('id');
        $moduleId = (int)$module->get('id');
        $basePath = rtrim($modx->getOption('base_path'), '/\\');

        $canonicalBase = $basePath . '/assets/training/courses/' . $courseId . '/modules/' . $moduleId . '/lessons/' . $lessonId . '/';
        $legacyBase = $basePath . '/assets/training/courses/' . $courseId . '/lessons/' . $moduleId . '/lesson_' . $lessonId . '/';

        $base = self::normalizeFsPath($canonicalBase, false);

        $dbSource = trim((string)$lesson->get('source_video'));
        if ($dbSource !== '') {
            $dbAbs = self::resolveLocalPath($modx, $dbSource);
            if ($dbAbs !== '') {
                $sourceDir = self::normalizeFsPath(dirname($dbAbs), false);
                if (preg_match('#/(?:source|video(?:/[^/]+)?|preview)/$#i', $sourceDir)) {
                    $sourceDir = preg_replace('#/(?:source|video(?:/[^/]+)?|preview)/$#i', '/', $sourceDir);
                }
                if ($sourceDir !== '' && (strpos($sourceDir, '/courses/' . $courseId . '/modules/' . $moduleId . '/lessons/' . $lessonId . '/') !== false || strpos($sourceDir, '/courses/' . $courseId . '/lessons/' . $moduleId . '/lesson_' . $lessonId . '/') !== false)) {
                    $base = $sourceDir;
                }
            }
        }

        return [
            'base_absolute' => $base,
            'base_web' => self::fsPathToWeb($modx, $base),
            'source_absolute' => self::normalizeFsPath($base . 'source/'),
            'source_web' => self::fsPathToWeb($modx, $base . 'source/'),
            'video_absolute' => self::normalizeFsPath($base . 'video/'),
            'video_web' => self::fsPathToWeb($modx, $base . 'video/'),
            'preview_absolute' => self::normalizeFsPath($base . 'preview/'),
            'preview_web' => self::fsPathToWeb($modx, $base . 'preview/'),
            'legacy_base_absolute' => self::normalizeFsPath($legacyBase, false),
        ];
    }

    public static function resolveLessonPresentationDirs(modX $modx, TrainingModuleLesson $lesson)
    {
        /** @var TrainingModule $module */
        $module = $lesson->getOne('Module');
        if (!$module) {
            $module = $modx->getObject('TrainingModule', ['id' => (int)$lesson->get('module_id')]);
        }
        if (!$module) {
            return [];
        }

        $courseId = (int)$module->get('course_id');
        $lessonId = (int)$lesson->get('id');
        $moduleId = (int)$module->get('id');
        $basePath = rtrim($modx->getOption('base_path'), '/\\');

        $canonicalBase = $basePath . '/assets/training/courses/' . $courseId . '/modules/' . $moduleId . '/lessons/' . $lessonId . '/presentation/';
        $legacyBase = $basePath . '/assets/training/courses/' . $courseId . '/lessons/' . $moduleId . '/lesson_' . $lessonId . '/presentation/';

        $base = self::normalizeFsPath($canonicalBase, false);

        $dbSlidesDir = trim((string)$lesson->get('slides_dir'));
        if ($dbSlidesDir !== '') {
            $dbAbs = self::webPathToFs($modx, $dbSlidesDir);
            if ($dbAbs !== '') {
                $dbAbs = self::normalizeFsPath($dbAbs, false);
                if ($dbAbs !== '') {
                    if (preg_match('#/slides/$#i', $dbAbs)) {
                        $dbAbs = preg_replace('#/slides/$#i', '/', $dbAbs);
                    }
                    if (strpos($dbAbs, '/courses/' . $courseId . '/modules/' . $moduleId . '/lessons/' . $lessonId . '/presentation/') !== false || strpos($dbAbs, '/courses/' . $courseId . '/lessons/' . $moduleId . '/lesson_' . $lessonId . '/presentation/') !== false) {
                        $base = $dbAbs;
                    }
                }
            }
        }

        return [
            'base_absolute' => $base,
            'base_web' => self::fsPathToWeb($modx, $base),
            'slides_absolute' => self::normalizeFsPath($base . 'slides/'),
            'slides_web' => self::fsPathToWeb($modx, $base . 'slides/'),
            'pdf_absolute' => self::normalizeFsPath($base . self::buildLessonPresentationPdfFilename($lesson), true),
            'pdf_web' => self::fsPathToWeb($modx, $base . self::buildLessonPresentationPdfFilename($lesson)),
        ];
    }

    public static function upsertLessonVideo(modX $modx, $lessonId, array $data)
    {
        $lessonId = (int)$lessonId;
        $quality = trim((string)($data['quality'] ?? ''));
        if ($lessonId <= 0 || $quality === '') {
            return false;
        }

        /** @var TrainingModuleLesson $lesson */
        $lesson = $modx->getObject('TrainingModuleLesson', ['id' => $lessonId]);
        if (!$lesson) {
            return false;
        }

        /** @var TrainingModuleVideo $video */
        $video = $modx->getObject('TrainingModuleVideo', [
            'lesson_id' => $lessonId,
            'quality' => $quality,
        ]);

        if (!$video) {
            $video = $modx->newObject('TrainingModuleVideo');
            $video->set('module_id', (int)$lesson->get('module_id'));
            $video->set('lesson_id', $lessonId);
            $video->set('quality', $quality);
        }

        foreach (['mime', 'file_path', 'width', 'height', 'bitrate', 'filesize', 'is_default', 'is_active'] as $field) {
            if (array_key_exists($field, $data)) {
                $video->set($field, $data[$field]);
            }
        }

        return $video->save();
    }

    public static function clearOtherDefaultLessonVideos(modX $modx, $lessonId, $keepQuality)
    {
        $lessonId = (int)$lessonId;
        if ($lessonId <= 0) {
            return;
        }
        $c = $modx->newQuery('TrainingModuleVideo');
        $c->where([
            'lesson_id' => $lessonId,
            'quality:!=' => (string)$keepQuality,
        ]);
        $items = $modx->getCollection('TrainingModuleVideo', $c);
        foreach ($items as $item) {
            if ((int)$item->get('is_default') === 1) {
                $item->set('is_default', 0);
                $item->save();
            }
        }
    }

    public static function applyEvenLessonSlideTimecodes(modX $modx, $lessonId, $durationMs)
    {
        $lessonId = (int)$lessonId;
        $durationMs = (int)$durationMs;
        if ($lessonId <= 0 || $durationMs <= 0) {
            return 0;
        }

        $c = $modx->newQuery('TrainingModuleSlide');
        $c->where(['lesson_id' => $lessonId]);
        $c->sortby('slide_no', 'ASC');
        $slides = $modx->getCollection('TrainingModuleSlide', $c);
        $total = count($slides);
        if ($total <= 0) {
            return 0;
        }

        $step = (int)floor($durationMs / max(1, $total));
        $index = 0;
        foreach ($slides as $slide) {
            $slide->set('timecode_ms', $index * $step);
            $slide->save();
            $index++;
        }

        return $total;
    }

    public static function applyEvenSlideTimecodes(modX $modx, $moduleId, $durationMs)
    {
        $moduleId = (int)$moduleId;
        $durationMs = (int)$durationMs;
        if ($moduleId <= 0 || $durationMs <= 0) {
            return 0;
        }

        $c = $modx->newQuery('TrainingModuleSlide');
        $c->where(['module_id' => $moduleId]);
        $c->sortby('slide_no', 'ASC');
        $c->sortby('id', 'ASC');
        $items = $modx->getCollection('TrainingModuleSlide', $c);

        $count = count($items);
        if ($count === 0) {
            return 0;
        }

        $index = 0;
        foreach ($items as $item) {
            $timecode = $count > 1 ? (int)floor(($index * $durationMs) / $count) : 0;
            $item->set('timecode_ms', $timecode);
            $item->save();
            $index++;
        }

        return $count;
    }


    public static function isPathWithin($path, $base)
    {
        $path = self::normalizeFsPath($path);
        $base = self::normalizeFsPath($base, false);
        return $path !== '' && $base !== '' && strpos($path, $base) === 0;
    }

    public static function deleteLocalFile(modX $modx, $path)
    {
        $path = trim((string)$path);
        if ($path === '') {
            return false;
        }

        $absolute = self::resolveLocalPath($modx, $path);
        if ($absolute === '') {
            $absolute = self::normalizeFsPath($path, true);
        }

        if ($absolute === '' || !is_file($absolute)) {
            return false;
        }

        return @unlink($absolute);
    }

    public static function deleteDirTree($path)
    {
        $path = self::normalizeFsPath($path, false);
        if ($path === '' || !file_exists($path)) {
            return true;
        }

        if (is_file($path)) {
            return @unlink($path);
        }

        $items = scandir($path);
        if (!is_array($items)) {
            return false;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $target = $path . $item;
            if (is_dir($target)) {
                if (!self::deleteDirTree($target . '/')) {
                    return false;
                }
                continue;
            }

            if (file_exists($target) && !@unlink($target)) {
                return false;
            }
        }

        return @rmdir(rtrim($path, '/'));
    }

    public static function removeEmptyParentDirs($path, $stopAt = '')
    {
        $path = self::normalizeFsPath($path, false);
        $stopAt = self::normalizeFsPath($stopAt, false);

        while ($path !== '' && is_dir($path)) {
            if ($stopAt !== '' && strpos($path, $stopAt) !== 0) {
                break;
            }

            $items = scandir($path);
            if (!is_array($items)) {
                break;
            }

            $items = array_diff($items, ['.', '..']);
            if (!empty($items)) {
                break;
            }

            @rmdir(rtrim($path, '/'));
            $parent = dirname(rtrim($path, '/'));
            if ($parent === '.' || $parent === '/' || $parent === '' || $parent === rtrim($path, '/')) {
                break;
            }
            $path = self::normalizeFsPath($parent, false);
        }
    }

    public static function reassignDefaultLessonVideo(modX $modx, $lessonId)
    {
        $lessonId = (int)$lessonId;
        if ($lessonId <= 0) {
            return null;
        }

        $c = $modx->newQuery('TrainingModuleVideo');
        $c->where(['lesson_id' => $lessonId]);
        $c->sortby('is_default', 'DESC');
        $c->sortby('is_active', 'DESC');
        $c->sortby('height', 'DESC');
        $c->sortby('width', 'DESC');
        $c->sortby('id', 'ASC');
        $videos = $modx->getCollection('TrainingModuleVideo', $c);

        if (empty($videos)) {
            return null;
        }

        $default = null;
        foreach ($videos as $video) {
            if ($default === null) {
                $default = $video;
                if ((int)$video->get('is_default') !== 1) {
                    $video->set('is_default', 1);
                    $video->save();
                }
                continue;
            }

            if ((int)$video->get('is_default') === 1) {
                $video->set('is_default', 0);
                $video->save();
            }
        }

        return $default;
    }

    public static function refreshLessonVideoState(modX $modx, TrainingModuleLesson $lesson)
    {
        $lessonId = (int)$lesson->get('id');
        $defaultVideo = self::reassignDefaultLessonVideo($modx, $lessonId);

        if (!$defaultVideo) {
            $previewImage = trim((string)$lesson->get('preview_image'));
            if ($previewImage !== '') {
                $dirs = self::resolveLessonVideoDirs($modx, $lesson);
                $previewAbs = self::resolveLocalPath($modx, $previewImage);
                if ($previewAbs !== '' && !empty($dirs['preview_absolute']) && self::isPathWithin($previewAbs, $dirs['preview_absolute'])) {
                    @unlink($previewAbs);
                    self::removeEmptyParentDirs(dirname($previewAbs), $dirs['preview_absolute']);
                }
            }

            $lesson->set('video_status', 'none');
            $lesson->set('duration_seconds', 0);
            $lesson->set('preview_image', '');
            $lesson->set('updatedon', date('Y-m-d H:i:s'));
            $lesson->save();
            return [
                'has_videos' => false,
                'default_video_id' => 0,
            ];
        }

        $hasActive = (int)$modx->getCount('TrainingModuleVideo', [
            'lesson_id' => $lessonId,
            'is_active' => 1,
        ]) > 0;

        $lesson->set('video_status', $hasActive ? 'ready' : 'none');
        if ((int)$lesson->get('duration_seconds') < 0) {
            $lesson->set('duration_seconds', 0);
        }
        $lesson->set('updatedon', date('Y-m-d H:i:s'));
        $lesson->save();

        return [
            'has_videos' => true,
            'default_video_id' => (int)$defaultVideo->get('id'),
            'default_quality' => (string)$defaultVideo->get('quality'),
        ];
    }

    public static function refreshLessonPresentationState(modX $modx, TrainingModuleLesson $lesson)
    {
        $lessonId = (int)$lesson->get('id');
        $slidesCount = (int)$modx->getCount('TrainingModuleSlide', ['lesson_id' => $lessonId]);
        $videoStatus = trim((string)$lesson->get('video_status'));

        if ($slidesCount <= 0) {
            $lesson->set('presentation_status', 'none');
        } elseif ($videoStatus === 'ready') {
            $lesson->set('presentation_status', 'ready');
        } else {
            $lesson->set('presentation_status', 'available');
        }

        $lesson->set('updatedon', date('Y-m-d H:i:s'));
        $lesson->save();

        return $slidesCount;
    }

    public static function deleteLessonVideoRecord(modX $modx, $video, $deleteFile = true)
    {
        if (!$video || !($video instanceof xPDOObject)) {
            $video = $modx->getObject('TrainingModuleVideo', ['id' => (int)$video]);
        }
        if (!$video) {
            return false;
        }

        $lessonId = (int)$video->get('lesson_id');
        $filePath = trim((string)$video->get('file_path'));
        $fileAbsolute = '';
        if ($deleteFile && $filePath !== '') {
            $fileAbsolute = self::resolveLocalPath($modx, $filePath);
            if ($fileAbsolute !== '') {
                @unlink($fileAbsolute);
            }
        }

        $removed = (bool)$video->remove();
        if ($removed && $fileAbsolute !== '') {
            $lesson = $lessonId > 0 ? $modx->getObject('TrainingModuleLesson', ['id' => $lessonId]) : null;
            if ($lesson) {
                $dirs = self::resolveLessonVideoDirs($modx, $lesson);
                $qualityDir = self::normalizeFsPath(dirname($fileAbsolute), false);
                if (!empty($dirs['video_absolute']) && self::isPathWithin($qualityDir, $dirs['video_absolute'])) {
                    self::removeEmptyParentDirs($qualityDir, $dirs['video_absolute']);
                }
            }
        }

        return $removed;
    }

    public static function deleteLessonSlideRecord(modX $modx, $slide)
    {
        if (!$slide || !($slide instanceof xPDOObject)) {
            $slide = $modx->getObject('TrainingModuleSlide', ['id' => (int)$slide]);
        }
        if (!$slide) {
            return false;
        }

        return (bool)$slide->remove();
    }

    public static function ensureModuleDefaultLesson(modX $modx, $moduleId, $excludeLessonId = 0)
    {
        $moduleId = (int)$moduleId;
        $excludeLessonId = (int)$excludeLessonId;
        if ($moduleId <= 0) {
            return 0;
        }

        $c = $modx->newQuery('TrainingModuleLesson');
        $c->where(['module_id' => $moduleId]);
        if ($excludeLessonId > 0) {
            $c->where(['id:!=' => $excludeLessonId]);
        }
        $c->sortby('is_default', 'DESC');
        $c->sortby('sort_order', 'ASC');
        $c->sortby('id', 'ASC');
        $lessons = $modx->getCollection('TrainingModuleLesson', $c);

        if (empty($lessons)) {
            return 0;
        }

        $defaultId = 0;
        foreach ($lessons as $lesson) {
            if ($defaultId === 0) {
                $defaultId = (int)$lesson->get('id');
                if ((int)$lesson->get('is_default') !== 1) {
                    $lesson->set('is_default', 1);
                    $lesson->save();
                }
                continue;
            }

            if ((int)$lesson->get('is_default') === 1) {
                $lesson->set('is_default', 0);
                $lesson->save();
            }
        }

        return $defaultId;
    }

    public static function cleanupLessonMedia(modX $modx, TrainingModuleLesson $lesson)
    {
        $videoDirs = self::resolveLessonVideoDirs($modx, $lesson);
        $presentationDirs = self::resolveLessonPresentationDirs($modx, $lesson);

        $paths = [];
        foreach (['preview_image', 'source_video', 'source_presentation', 'presentation_pdf'] as $field) {
            $value = trim((string)$lesson->get($field));
            if ($value !== '') {
                $paths[] = $value;
            }
        }

        foreach ($paths as $path) {
            self::deleteLocalFile($modx, $path);
        }

        if (!empty($videoDirs['base_absolute'])) {
            self::deleteDirTree($videoDirs['base_absolute']);
            $legacyBase = !empty($videoDirs['legacy_base_absolute']) ? $videoDirs['legacy_base_absolute'] : '';
            if ($legacyBase !== '' && $legacyBase !== $videoDirs['base_absolute']) {
                self::deleteDirTree($legacyBase);
            }
        }

        if (!empty($presentationDirs['base_absolute'])) {
            self::deleteDirTree($presentationDirs['base_absolute']);
        }
    }

    public static function removeLessonCascade(modX $modx, TrainingModuleLesson $lesson)
    {
        $lessonId = (int)$lesson->get('id');
        $moduleId = (int)$lesson->get('module_id');
        $wasDefault = (int)$lesson->get('is_default') === 1;

        foreach ($modx->getCollection('TrainingModuleVideo', ['lesson_id' => $lessonId]) as $video) {
            self::deleteLessonVideoRecord($modx, $video, true);
        }

        foreach ($modx->getCollection('TrainingModuleSlide', ['lesson_id' => $lessonId]) as $slide) {
            self::deleteLessonSlideRecord($modx, $slide);
        }

        self::cleanupLessonMedia($modx, $lesson);
        $removed = (bool)$lesson->remove();

        if ($removed && $moduleId > 0) {
            self::ensureModuleDefaultLesson($modx, $moduleId, $lessonId);
        }

        return [
            'removed' => $removed,
            'module_id' => $moduleId,
            'was_default' => $wasDefault,
        ];
    }


    public static function gatherFiles(modX $modx, array $dirs, array $extensions, $maxDepth = 4, $limit = 300)
    {
        $results = [];
        $seen = [];
        $extensions = array_map('strtolower', $extensions);

        foreach ($dirs as $dir) {
            $dir = self::normalizeFsPath($dir, false);
            if ($dir === '' || !is_dir($dir)) {
                continue;
            }
            self::scanDirRecursive($modx, $dir, $extensions, (int)$maxDepth, $results, $seen, (int)$limit);
            if (count($results) >= $limit) {
                break;
            }
        }

        usort($results, function ($a, $b) {
            return strnatcasecmp($a['path'], $b['path']);
        });

        return $results;
    }

    protected static function scanDirRecursive(modX $modx, $dir, array $extensions, $maxDepth, array &$results, array &$seen, $limit, $depth = 0)
    {
        if ($depth > $maxDepth || count($results) >= $limit || !is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if (!is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = self::normalizeFsPath($dir, false) . $item;
            if (is_dir($path)) {
                self::scanDirRecursive($modx, $path, $extensions, $maxDepth, $results, $seen, $limit, $depth + 1);
                if (count($results) >= $limit) {
                    return;
                }
                continue;
            }

            if (!is_file($path)) {
                continue;
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($ext, $extensions, true)) {
                continue;
            }

            $webPath = self::fsPathToWeb($modx, $path);
            if (isset($seen[$webPath])) {
                continue;
            }
            $seen[$webPath] = true;

            $results[] = [
                'path' => $webPath,
                'name' => basename($path),
                'dir' => self::fsPathToWeb($modx, dirname($path) . '/'),
                'ext' => $ext,
                'filesize' => (int)filesize($path),
            ];

            if (count($results) >= $limit) {
                return;
            }
        }
    }



    public static function normalizeWebPath($path)
    {
        $path = trim((string)$path);
        if ($path === '') {
            return '';
        }

        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);

        if (preg_match('#^(https?:)?//#i', $path)) {
            return '';
        }

        if ($path[0] !== '/') {
            $path = '/' . ltrim($path, '/');
        }

        return $path;
    }

    public static function addReferencedMediaPath(modX $modx, array &$results, $path)
    {
        $path = trim((string)$path);
        if ($path === '') {
            return;
        }

        if (preg_match('#^(https?:)?//#i', $path)) {
            return;
        }

        $normalized = '';
        if (preg_match('#^(?:[a-z]:)?/#i', $path) && strpos($path, rtrim($modx->getOption('base_path'), '/\\')) === 0) {
            $normalized = self::fsPathToWeb($modx, $path);
        } else {
            $normalized = self::normalizeWebPath($path);
        }

        if ($normalized === '') {
            return;
        }

        $results[$normalized] = true;
    }

    public static function addReferencedDirFiles(modX $modx, array &$results, $dirPath, array $extensions = ['jpg','jpeg','png','webp','gif','pdf'])
    {
        $dirPath = trim((string)$dirPath);
        if ($dirPath === '') {
            return;
        }

        $absolute = self::webPathToFs($modx, $dirPath);
        if ($absolute === '') {
            $absolute = $dirPath;
        }
        $absolute = self::normalizeFsPath($absolute, false);

        if ($absolute === '' || !is_dir($absolute)) {
            return;
        }

        $files = self::gatherFiles($modx, [$absolute], $extensions, 3, 5000);
        foreach ($files as $file) {
            if (!empty($file['path'])) {
                $results[self::normalizeWebPath($file['path'])] = true;
            }
        }
    }

    public static function collectReferencedMediaFiles(modX $modx)
    {
        $results = [];

        $recordFields = [
            'TrainingCourse' => ['source_presentation', 'presentation_pdf'],
            'TrainingModule' => ['source_video', 'source_presentation', 'presentation_pdf'],
            'TrainingModuleLesson' => ['source_video', 'source_presentation', 'presentation_pdf', 'preview_image'],
        ];

        foreach ($recordFields as $class => $fields) {
            foreach ($modx->getIterator($class) as $object) {
                foreach ($fields as $field) {
                    self::addReferencedMediaPath($modx, $results, $object->get($field));
                }

                $slidesDir = trim((string)$object->get('slides_dir'));
                if ($slidesDir !== '') {
                    self::addReferencedDirFiles($modx, $results, $slidesDir, ['jpg','jpeg','png','webp','gif']);
                }
            }
        }

        foreach ($modx->getIterator('TrainingModuleVideo') as $video) {
            self::addReferencedMediaPath($modx, $results, $video->get('file_path'));
        }

        foreach ($modx->getIterator('TrainingModuleSlide') as $slide) {
            self::addReferencedMediaPath($modx, $results, $slide->get('image'));
        }

        return $results;
    }

    public static function getTrainingMediaBaseDirs(modX $modx)
    {
        $basePath = rtrim($modx->getOption('base_path'), '/\\');
        return [
            self::normalizeFsPath($basePath . '/assets/training/courses/', false),
        ];
    }
    public static function formatSeconds($seconds)
    {
        $seconds = (int)$seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}

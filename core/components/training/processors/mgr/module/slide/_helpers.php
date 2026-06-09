<?php

require_once dirname(dirname(__DIR__)) . '/_media_helper.php';

class TrainingModuleSlideHelper
{
    public static function getLessonContext(modX $modx, $lessonId)
    {
        $lessonId = (int)$lessonId;
        if ($lessonId <= 0) {
            return [null, null, null, null];
        }

        /** @var TrainingModuleLesson $lesson */
        $lesson = $modx->getObject('TrainingModuleLesson', ['id' => $lessonId]);
        if (!$lesson) {
            return [null, null, null, null];
        }

        /** @var TrainingModule $module */
        $module = $modx->getObject('TrainingModule', ['id' => (int)$lesson->get('module_id')]);
        if (!$module) {
            return [$lesson, null, null, null];
        }

        /** @var TrainingCourse $course */
        $course = $modx->getObject('TrainingCourse', ['id' => (int)$module->get('course_id')]);
        $courseResource = null;
        if ($course) {
            $courseResource = $modx->getObject('modResource', ['id' => (int)$course->get('resource_id')]);
        }

        return [$lesson, $module, $course, $courseResource];
    }

    public static function resolveLessonSlidesDir(modX $modx, $lessonId)
    {
        list($lesson, $module, $course, $courseResource) = self::getLessonContext($modx, $lessonId);
        if (!$lesson || !$module) {
            return [
                'absolute' => '',
                'web' => '',
                'exists' => false,
                'checked' => [],
            ];
        }

        $checked = [];
        $candidates = [];

        $lessonDirs = TrainingMediaHelper::resolveLessonPresentationDirs($modx, $lesson);
        if (!empty($lessonDirs['slides_absolute'])) {
            $candidates[] = $lessonDirs['slides_absolute'];
        }

        $dbSlidesDir = trim((string)$lesson->get('slides_dir'));
        if ($dbSlidesDir !== '') {
            $candidates[] = self::webOrFsToFs($modx, $dbSlidesDir);
        }

        if ($module) {
            $moduleDirs = TrainingMediaHelper::resolveModulePresentationDirs($modx, $module);
            if (!empty($moduleDirs['slides_absolute'])) {
                $candidates[] = $moduleDirs['slides_absolute'];
            }
            $moduleDbSlidesDir = trim((string)$module->get('slides_dir'));
            if ($moduleDbSlidesDir !== '') {
                $candidates[] = self::webOrFsToFs($modx, $moduleDbSlidesDir);
            }
        }

        // fallback на старые/общие папки курса, чтобы старые данные не потерялись
        $basePath = rtrim($modx->getOption('base_path'), '/\\') . '/';
        $assetsTrainingBase = $basePath . 'assets/training/';
        if ($course) {
            $courseDbId = (int)$course->get('id');
            $courseResourceId = (int)$course->get('resource_id');
            $courseAlias = $courseResource ? trim((string)$courseResource->get('alias')) : '';
            $courseDbSlidesDir = trim((string)$course->get('slides_dir'));
            if ($courseDbSlidesDir !== '') {
                $candidates[] = self::webOrFsToFs($modx, $courseDbSlidesDir);
            }
            $candidates[] = $assetsTrainingBase . 'courses/' . $courseDbId . '/presentation/slides/';
            $candidates[] = $assetsTrainingBase . 'courses/' . $courseResourceId . '/presentation/slides/';
            if ($courseAlias !== '') {
                $candidates[] = $assetsTrainingBase . 'courses/' . $courseAlias . '/presentation/slides/';
            }
        }

        foreach ($candidates as $candidate) {
            $candidate = self::normalizeFsPath($candidate);
            if ($candidate === '') {
                continue;
            }
            if (in_array($candidate, $checked, true)) {
                continue;
            }
            $checked[] = $candidate;
            if (is_dir($candidate)) {
                return [
                    'absolute' => $candidate,
                    'web' => self::fsPathToWeb($modx, $candidate),
                    'exists' => true,
                    'checked' => $checked,
                ];
            }
        }

        $fallback = !empty($checked) ? $checked[0] : '';

        return [
            'absolute' => $fallback,
            'web' => self::fsPathToWeb($modx, $fallback),
            'exists' => false,
            'checked' => $checked,
        ];
    }

    public static function scanLessonSlides(modX $modx, $lessonId)
    {
        $dirInfo = self::resolveLessonSlidesDir($modx, $lessonId);
        $results = [];

        if (empty($dirInfo['exists']) || empty($dirInfo['absolute']) || !is_dir($dirInfo['absolute'])) {
            return [
                'dir' => $dirInfo,
                'slides' => $results,
            ];
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $items = scandir($dirInfo['absolute']);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $filePath = $dirInfo['absolute'] . $item;
            if (!is_file($filePath)) {
                continue;
            }
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExt, true)) {
                continue;
            }
            $results[] = [
                'filename' => $item,
                'path' => self::fsPathToWeb($modx, $filePath),
                'absolute_path' => self::normalizeFsPath($filePath),
                'slide_no' => self::extractSlideNumber($item),
            ];
        }

        usort($results, function ($a, $b) {
            return strnatcasecmp($a['filename'], $b['filename']);
        });

        return [
            'dir' => $dirInfo,
            'slides' => $results,
        ];
    }

    public static function normalizeWebPath(modX $modx, $path)
    {
        $path = trim((string)$path);
        if ($path === '') {
            return '';
        }

        $path = str_replace('\\', '/', $path);

        if (preg_match('#^(https?:)?//#i', $path)) {
            return $path;
        }

        $basePath = self::normalizeFsPath($modx->getOption('base_path'));
        if ($basePath !== '' && strpos(self::normalizeFsPath($path), $basePath) === 0) {
            return self::fsPathToWeb($modx, $path);
        }

        $path = '/' . ltrim($path, '/');
        return preg_replace('#/+#', '/', $path);
    }

    public static function detectFileSize(modX $modx, $path)
    {
        $path = trim((string)$path);
        if ($path === '') {
            return 0;
        }

        $path = str_replace('\\', '/', $path);
        if (preg_match('#^(https?:)?//#i', $path)) {
            return 0;
        }

        if (preg_match('#^(?:[a-z]:)?/#i', $path) && is_file($path)) {
            return (int)filesize($path);
        }

        $path = ltrim($path, '/');
        $absolute = rtrim($modx->getOption('base_path'), '/\\') . '/' . $path;
        if (is_file($absolute)) {
            return (int)filesize($absolute);
        }

        return 0;
    }

    public static function extractSlideNumber($filename)
    {
        return TrainingMediaHelper::extractLastNumber($filename);
    }

    public static function fsPathToWeb(modX $modx, $absolutePath)
    {
        $absolutePath = self::normalizeFsPath($absolutePath);
        $basePath = self::normalizeFsPath($modx->getOption('base_path'));

        if ($absolutePath !== '' && $basePath !== '' && strpos($absolutePath, $basePath) === 0) {
            $relative = ltrim(substr($absolutePath, strlen($basePath)), '/');
            return '/' . $relative;
        }

        return $absolutePath;
    }

    public static function normalizeFsPath($path)
    {
        $path = str_replace('\\', '/', (string)$path);
        $path = preg_replace('#/+#', '/', $path);

        if ($path !== '' && substr($path, -1) !== '/') {
            $path .= '/';
            if (preg_match('#\.[a-z0-9]+/$#i', $path)) {
                $path = rtrim($path, '/');
            }
        }

        return $path;
    }

    protected static function webOrFsToFs(modX $modx, $path)
    {
        $path = trim((string)$path);
        if ($path === '') {
            return '';
        }

        if (preg_match('#^(?:[a-z]:)?/#i', $path)) {
            return $path;
        }

        return rtrim($modx->getOption('base_path'), '/\\') . '/' . ltrim($path, '/');
    }
}

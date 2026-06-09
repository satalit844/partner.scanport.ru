<?php
/** @var modX $modx */
/** @var array $scriptProperties */

$corePath = $modx->getOption(
    'training.core_path',
    null,
    $modx->getOption('core_path') . 'components/training/'
);

require_once $corePath . 'model/training/training.class.php';
require_once $corePath . 'model/training/services/trainingprogress.class.php';
require_once $corePath . 'processors/web/_helpers.php';

if (!function_exists('trainingMyCoursesRenderChunk')) {
    function trainingMyCoursesRenderChunk($corePath, $relativePath, array $placeholders = array())
    {
        $basePath = rtrim(str_replace('\\', '/', (string)$corePath), '/');
        $path = $basePath . '/elements/chunks/' . ltrim($relativePath, '/');
        if (!is_file($path)) {
            return '';
        }

        $content = (string)file_get_contents($path);
        if ($content === '') {
            return '';
        }

        $replace = array();
        foreach ($placeholders as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = '';
            }
            $replace['{$' . $key . '}'] = (string)$value;
        }

        return strtr($content, $replace);
    }
}

if (!function_exists('trainingMyCoursesEsc')) {
    function trainingMyCoursesEsc($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('trainingMyCoursesCountStats')) {
    function trainingMyCoursesCountStats(TrainingProgressService $service, modX $modx, $courseId, $userId)
    {
        $videoStats = TrainingWebHelper::getCourseVideoStats($modx, $service, (int)$courseId, (int)$userId);
        $activityStats = $service->getCourseActivityStats((int)$courseId, (int)$userId);

        return array(
            'total_videos' => (int)$videoStats['total_videos'],
            'completed_videos' => (int)$videoStats['completed_videos'],
            'started_videos' => (int)$videoStats['started_videos'],
            'total_tests' => (int)$activityStats['tests_total'],
            'passed_tests' => (int)$activityStats['tests_passed'],
            'started_tests' => (int)$activityStats['tests_started'],
            'practices_total' => (int)$activityStats['practices_total'],
            'practices_completed' => (int)$activityStats['practices_completed'],
            'practices_started' => (int)$activityStats['practices_started'],
        );
    }
}

if (!function_exists('trainingMyCoursesBuildState')) {
    function trainingMyCoursesBuildState($status, $progressPercent)
    {
        $status = (string)$status;
        $progressPercent = (float)$progressPercent;

        if ($status === 'completed' || $progressPercent >= 100) {
            return array(
                'state' => 'done',
                'item_class' => 'is-done',
                'status_text' => 'Курс завершен',
            );
        }

        if ($status === 'in_progress' || $progressPercent > 0) {
            return array(
                'state' => 'progress',
                'item_class' => 'is-progress',
                'status_text' => 'В процессе',
            );
        }

        return array(
            'state' => 'new',
            'item_class' => 'is-new',
            'status_text' => 'Не начат',
        );
    }
}

if (!function_exists('trainingMyCoursesStatsBlock')) {
    function trainingMyCoursesStatsBlock($videosText, $practicesText, $testsText, $progressPercent)
    {
        return '' .
            '<div class="course-item__stats">' .
                '<div class="course-stat"><div class="course-stat__value">' . trainingMyCoursesEsc($videosText) . '</div><div class="course-stat__label">видео</div></div>' .
                '<div class="course-stat"><div class="course-stat__value">' . trainingMyCoursesEsc($practicesText) . '</div><div class="course-stat__label">Практические работы</div></div>' .
                '<div class="course-stat"><div class="course-stat__value">' . trainingMyCoursesEsc($testsText) . '</div><div class="course-stat__label">Тесты</div></div>' .
            '</div>' .
            '<div class="course-item__progress">' .
                '<div class="progress-track"><div class="progress-fill" style="width: ' . (int)$progressPercent . '%"></div></div>' .
                '<div class="progress-value">' . (int)$progressPercent . '%</div>' .
            '</div>';
    }
}

if (!function_exists('trainingMyCoursesStatusBlock')) {
    function trainingMyCoursesStatusBlock($itemClass, $statusText, $progressPercent)
    {
        return '' .
            '<div class="course-item__status">' .
                '<span class="course-status__dot" aria-hidden="true"></span>' .
                '<span>' . trainingMyCoursesEsc($statusText) . '</span>' .
            '</div>' .
            '<div class="course-item__progress">' .
                '<div class="progress-track"><div class="progress-fill" style="width: ' . (int)$progressPercent . '%"></div></div>' .
                '<div class="progress-value">' . (int)$progressPercent . '%</div>' .
            '</div>';
    }
}

if (!$modx->user || !(int)$modx->user->get('id') || !$modx->user->isAuthenticated($modx->context->get('key'))) {
    return '';
}

$pageTpl = trim((string)$modx->getOption('pageTpl', $scriptProperties, 'training/mycourses/page.tpl'));
$itemTpl = trim((string)$modx->getOption('itemTpl', $scriptProperties, 'training/mycourses/item.tpl'));

$training = new Training($modx);
$service = new TrainingProgressService($modx, $training);
$userId = (int)$modx->user->get('id');
$rows = $service->getMyCourses($userId, array(
    'include_revoked' => false,
    'recalculate' => true,
));

$itemsHtml = '';
foreach ($rows as $row) {
    $courseId = (int)$row['course_id'];
    $resourceId = (int)$row['resource_id'];
    if ($courseId <= 0 || $resourceId <= 0) {
        continue;
    }
    if (empty($row['course_is_active']) || empty($row['published'])) {
        continue;
    }

    /** @var modResource|null $resource */
    $resource = $modx->getObject('modResource', array('id' => $resourceId));
    if (!$resource) {
        continue;
    }

    $image = trim((string)$resource->getTVValue('image_curse'));
    if ($image === '') {
        $image = 'theme/images/training/image_2.jpg';
    }

    $url = $modx->makeUrl($resourceId, $resource->get('context_key'), '', 'full');
    $stats = trainingMyCoursesCountStats($service, $modx, $courseId, $userId);

    $videosCompleted = (int)$stats['completed_videos'];
    $videosTotal = (int)$stats['total_videos'];
    $practicesCompleted = (int)$stats['practices_completed'];
    $practicesTotal = (int)$stats['practices_total'];
    $testsPassed = (int)$stats['passed_tests'];
    $testsTotal = (int)$stats['total_tests'];

    $totalTrackItems = $videosTotal + $practicesTotal + $testsTotal;
    $completedTrackItems = $videosCompleted + $practicesCompleted + $testsPassed;
    $progressPercent = $totalTrackItems > 0
        ? (int)round(($completedTrackItems / $totalTrackItems) * 100)
        : (int)round((float)$row['progress_percent']);

    $status = (string)$row['status'];
    if ($totalTrackItems > 0 && $completedTrackItems >= $totalTrackItems) {
        $status = 'completed';
    } elseif ($progressPercent > 0 && $status === 'assigned') {
        $status = 'in_progress';
    }

    $state = trainingMyCoursesBuildState($status, $progressPercent);
    $videosText = $videosCompleted . '/' . $videosTotal;
    $practicesText = $practicesCompleted . '/' . $practicesTotal;
    $testsText = $testsPassed . '/' . max(0, $testsTotal);

    if ($state['state'] === 'progress') {
        $bodyHtml = trainingMyCoursesStatsBlock($videosText, $practicesText, $testsText, $progressPercent);
    } else {
        $bodyHtml = trainingMyCoursesStatusBlock($state['item_class'], $state['status_text'], $progressPercent);
    }

    $title = trim((string)($row['longtitle'] ?: $row['pagetitle']));
    if ($title === '') {
        $title = trim((string)$resource->get('pagetitle'));
    }

    $itemsHtml .= trainingMyCoursesRenderChunk($corePath, $itemTpl, array(
        'course_id' => $courseId,
        'item_class' => trainingMyCoursesEsc($state['item_class']),
        'course_url' => trainingMyCoursesEsc($url),
        'course_title' => trainingMyCoursesEsc($title),
        'course_image' => trainingMyCoursesEsc($image),
        'course_body_html' => $bodyHtml,
    ));
}

if ($itemsHtml === '') {
    $itemsHtml = '<div class="w-100">У вас пока нет назначенных курсов</div>';
}

return trainingMyCoursesRenderChunk($corePath, $pageTpl, array(
    'connector_url' => trainingMyCoursesEsc($modx->getOption('assets_url') . 'components/training/web.connector.php'),
    'context_key' => trainingMyCoursesEsc($modx->context ? $modx->context->get('key') : 'web'),
    'course_items_html' => $itemsHtml,
));

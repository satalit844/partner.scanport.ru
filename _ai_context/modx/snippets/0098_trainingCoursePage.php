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

if (!function_exists('trainingCoursePageRenderChunk')) {
    function trainingCoursePageRenderChunk(modX $modx, $corePath, $relativePath, array $placeholders = array())
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

if (!function_exists('trainingCoursePageEsc')) {
    function trainingCoursePageEsc($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('trainingCoursePagePlural')) {
    function trainingCoursePagePlural($number, array $forms)
    {
        $number = abs((int)$number) % 100;
        $n1 = $number % 10;
        if ($number > 10 && $number < 20) {
            return $forms[2];
        }
        if ($n1 > 1 && $n1 < 5) {
            return $forms[1];
        }
        if ($n1 == 1) {
            return $forms[0];
        }
        return $forms[2];
    }
}

if (!function_exists('trainingCoursePageFormatDuration')) {
    function trainingCoursePageFormatDuration($seconds)
    {
        $seconds = (int)$seconds;
        if ($seconds <= 0) {
            return '';
        }

        $hours = (int)floor($seconds / 3600);
        $minutes = (int)floor(($seconds % 3600) / 60);
        if ($hours > 0) {
            return $hours . ' часов ' . $minutes . ' минут';
        }
        if ($minutes > 0) {
            return $minutes . ' минут';
        }
        return $seconds . ' сек';
    }
}

if (!function_exists('trainingCoursePageMakePlayerUrl')) {
    function trainingCoursePageMakePlayerUrl(modX $modx, $moduleResourceId, $lessonId, $videoId = 0)
    {
        return TrainingWebHelper::makePlayerUrl($modx, (int)$moduleResourceId, (int)$lessonId, (int)$videoId);
    }
}

if (!function_exists('trainingCoursePageFindActivityResourceId')) {
    function trainingCoursePageFindActivityResourceId(modX $modx, $linkType, $preferredResourceId = 0)
    {
        $linkType = $linkType === 'practice' ? 'practice' : 'test';
        $preferredResourceId = (int)$preferredResourceId;

        $settingKey = $linkType === 'practice'
            ? 'training.training_practice_resource_id'
            : 'training.training_test_resource_id';
        $shortSettingKey = $linkType === 'practice'
            ? 'training_practice_resource_id'
            : 'training_test_resource_id';
        $defaultResourceId = $linkType === 'practice' ? 176 : 170;

        $ids = array();
        foreach (array(
            $preferredResourceId,
            (int)$modx->getOption($settingKey, null, 0),
            (int)$modx->getOption($shortSettingKey, null, 0),
            $defaultResourceId,
        ) as $id) {
            if ($id > 0 && !in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }

        foreach ($ids as $id) {
            /** @var modResource|null $resource */
            $resource = $modx->getObject('modResource', array('id' => $id, 'deleted' => 0));
            if ($resource) {
                return $id;
            }
        }

        $table = $modx->getTableName('modResource');
        $contentNeedle = $linkType === 'practice' ? '%trainingPracticePage%' : '%trainingActivityPage%';
        $aliasNeedle = $linkType === 'practice' ? '%prakt%' : '%test%';
        $titleNeedle = $linkType === 'practice' ? '%практи%' : '%тест%';
        $uriNeedle = $linkType === 'practice' ? '%praktich%' : '%stranitsa-testa%';

        $sql = "SELECT `id` FROM {$table}
            WHERE `deleted` = 0
              AND (
                    `content` LIKE :content_needle
                 OR `alias` LIKE :alias_needle
                 OR `pagetitle` LIKE :title_needle
                 OR `uri` LIKE :uri_needle
              )
            ORDER BY
                CASE WHEN `content` LIKE :content_needle_order THEN 0 ELSE 1 END,
                `published` DESC,
                `menuindex` ASC,
                `id` ASC
            LIMIT 1";

        $stmt = $modx->prepare($sql);
        if ($stmt && $stmt->execute(array(
            ':content_needle' => $contentNeedle,
            ':alias_needle' => $aliasNeedle,
            ':title_needle' => $titleNeedle,
            ':uri_needle' => $uriNeedle,
            ':content_needle_order' => $contentNeedle,
        ))) {
            $found = (int)$stmt->fetchColumn();
            if ($found > 0) {
                return $found;
            }
        }

        return 0;
    }
}

if (!function_exists('trainingCoursePageMakeResourceUrl')) {
    function trainingCoursePageMakeResourceUrl(modX $modx, $resourceId, array $params = array())
    {
        $resourceId = (int)$resourceId;
        if ($resourceId <= 0) {
            return '';
        }

        /** @var modResource|null $resource */
        $resource = $modx->getObject('modResource', array('id' => $resourceId, 'deleted' => 0));
        if (!$resource) {
            return '';
        }

        $contextKey = trim((string)$resource->get('context_key'));
        $contexts = array();
        if ($contextKey !== '') {
            $contexts[] = $contextKey;
        }
        $currentContext = $modx->context ? (string)$modx->context->get('key') : '';
        if ($currentContext !== '' && !in_array($currentContext, $contexts, true)) {
            $contexts[] = $currentContext;
        }
        $contexts[] = '';

        foreach ($contexts as $ctx) {
            $url = (string)$modx->makeUrl($resourceId, $ctx, $params);
            if ($url !== '') {
                $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
                return str_replace('&amp;', '&', $url);
            }
        }

        return '';
    }
}

if (!function_exists('trainingCoursePageMakeActivityUrl')) {
    function trainingCoursePageMakeActivityUrl(modX $modx, $activityId, $linkType, $backResourceId = 0, $linkId = 0)
    {
        $activityId = (int)$activityId;
        $backResourceId = (int)$backResourceId;
        $linkId = (int)$linkId;
        $linkType = $linkType === 'practice' ? 'practice' : 'test';

        if ($activityId <= 0) {
            return '';
        }

        $settingKey = $linkType === 'practice'
            ? 'training.training_practice_resource_id'
            : 'training.training_test_resource_id';
        $defaultResourceId = $linkType === 'practice' ? 176 : 170;
        $preferredResourceId = (int)$modx->getOption($settingKey, null, $defaultResourceId);
        $resourceId = trainingCoursePageFindActivityResourceId($modx, $linkType, $preferredResourceId);

        $params = array(
            'activity' => $activityId,
        );

        if ($linkType === 'practice') {
            $params['type'] = 'practice';
            if ($linkId > 0) {
                $params['link'] = $linkId;
            }
        }

        if ($backResourceId > 0) {
            $params['back'] = $backResourceId;
        }

        $url = trainingCoursePageMakeResourceUrl($modx, $resourceId, $params);
        if ($url !== '') {
            return $url;
        }

        $fallbackPath = $linkType === 'practice'
            ? 'obuchenie/obuchenie-glavnaya/prakticheskoe-zadanie/'
            : 'obuchenie/obuchenie-glavnaya/stranitsa-testa/';
        $fallbackPath = '/' . ltrim($fallbackPath, '/');
        return $fallbackPath . '?' . http_build_query($params, '', '&');
    }
}

if (!function_exists('trainingCoursePageGetUserLabel')) {
    function trainingCoursePageGetUserLabel(modUser $user)
    {
        $profile = $user->getOne('Profile');
        if ($profile) {
            $fullname = trim((string)$profile->get('fullname'));
            if ($fullname !== '') {
                return $fullname;
            }
        }
        return trim((string)$user->get('username'));
    }
}

if (!function_exists('trainingCoursePageGetModuleLessonStats')) {
    function trainingCoursePageGetModuleLessonStats(TrainingProgressService $service, modX $modx, $courseId, $moduleId, $userId)
    {
        $stats = array(
            'total_lessons' => 0,
            'completed_lessons' => 0,
            'started_lessons' => 0,
            'duration_seconds' => 0,
        );

        $lessons = $service->getModuleLessons((int)$moduleId, true, false);
        /** @var TrainingModuleLesson $lesson */
        foreach ($lessons as $lesson) {
            $lessonId = (int)$lesson->get('id');
            $stats['total_lessons']++;

            $videoRows = TrainingWebHelper::fetchLessonVideos($modx, $lessonId, true);
            $lessonDuration = 0;
            foreach ($videoRows as $videoRow) {
                $lessonDuration += max(0, (int)$videoRow['duration_seconds']);
            }
            if ($lessonDuration <= 0) {
                $lessonDuration = max(0, (int)$lesson->get('duration_seconds'));
            }
            $stats['duration_seconds'] += $lessonDuration;

            $videoStats = TrainingWebHelper::getLessonVideoStats($modx, $service, (int)$courseId, $lessonId, (int)$userId);
            if ((int)$videoStats['total_videos'] > 0 && (int)$videoStats['completed_videos'] >= (int)$videoStats['total_videos']) {
                $stats['completed_lessons']++;
            }

            if ((int)$videoStats['started_videos'] > 0 || ((int)$videoStats['total_videos'] > 0 && (int)$videoStats['completed_videos'] > 0)) {
                $stats['started_lessons']++;
            }
        }

        return $stats;
    }
}

if (!function_exists('trainingCoursePageGetCourseLessonStats')) {
    function trainingCoursePageGetCourseLessonStats(TrainingProgressService $service, modX $modx, $courseId, $userId)
    {
        $stats = array(
            'total_lessons' => 0,
            'completed_lessons' => 0,
            'started_lessons' => 0,
            'duration_seconds' => 0,
        );

        $modules = $service->getCourseModules((int)$courseId, true, false);
        /** @var TrainingModule $module */
        foreach ($modules as $module) {
            $moduleStats = trainingCoursePageGetModuleLessonStats($service, $modx, $courseId, (int)$module->get('id'), $userId);
            $stats['total_lessons'] += (int)$moduleStats['total_lessons'];
            $stats['completed_lessons'] += (int)$moduleStats['completed_lessons'];
            $stats['started_lessons'] += (int)$moduleStats['started_lessons'];
            $stats['duration_seconds'] += (int)$moduleStats['duration_seconds'];
        }

        return $stats;
    }
}

if (!function_exists('trainingCoursePageActivityIconHtml')) {
    function trainingCoursePageActivityIconHtml($linkType, $url = '')
    {
        $html = '<span class="cs-item__ico" aria-hidden="true"><img src="theme/images/training/curses/play-ico.png" alt=""></span>';
        if ($url !== '') {
            $html = '<a href="' . trainingCoursePageEsc($url) . '" class="cs-item__playlink" aria-label="Открыть активность">' . $html . '</a>';
        }

        return $html;
    }
}

$resourceId = (int)$modx->getOption('resource_id', $scriptProperties, $modx->resource ? $modx->resource->get('id') : 0);
$pageTpl = trim((string)$modx->getOption('pageTpl', $scriptProperties, 'training/course/page.tpl'));
$moduleTpl = trim((string)$modx->getOption('moduleTpl', $scriptProperties, 'training/course/module.tpl'));
$lessonTpl = trim((string)$modx->getOption('lessonTpl', $scriptProperties, 'training/course/lesson.tpl'));

if ($resourceId <= 0) {
    return '<div class="section-block"><div class="alert alert-warning mb-0">Курс не найден.</div></div>';
}

if (!$modx->user || !(int)$modx->user->get('id') || !$modx->user->isAuthenticated($modx->context->get('key'))) {
    return '';
}

$training = new Training($modx);
$service = new TrainingProgressService($modx, $training);
$userId = (int)$modx->user->get('id');

/** @var TrainingCourse $course */
$course = $modx->getObject('TrainingCourse', array('resource_id' => $resourceId, 'is_active' => 1));
/** @var modResource $resource */
$resource = $modx->getObject('modResource', array('id' => $resourceId, 'deleted' => 0));

if (!$course || !$resource) {
    return '<div class="section-block"><div class="alert alert-warning mb-0">Курс недоступен.</div></div>';
}

$courseId = (int)$course->get('id');
if (!$service->hasCourseAccess($courseId, $userId)) {
    return '<div class="section-block"><div class="alert alert-warning mb-0">У вас нет доступа к этому курсу.</div></div>';
}

$service->syncUserCourseForUser($courseId, $userId);
/** @var TrainingUserCourse $userCourse */
$userCourse = $service->recalculateUserCourse($courseId, $userId);
if (!$userCourse || (string)$userCourse->get('status') === 'revoked') {
    return '<div class="section-block"><div class="alert alert-warning mb-0">У вас нет доступа к этому курсу.</div></div>';
}

$courseTitle = trim((string)$resource->get('pagetitle'));
$courseImage = trim((string)$resource->getTVValue('image_curse'));
if ($courseImage === '') {
    $courseImage = 'theme/images/training/image_2.jpg';
}
$courseDescription = trim((string)$resource->getTVValue('desc'));
if ($courseDescription === '') {
    $courseDescription = trim((string)$resource->get('description'));
}
if ($courseDescription === '') {
    $courseDescription = 'Описание курса пока не заполнено.';
}
$courseDescription = nl2br($courseDescription);
$courseDurationText = trim((string)$resource->getTVValue('time_curse'));

$courseLessonStats = trainingCoursePageGetCourseLessonStats($service, $modx, $courseId, $userId);
$courseVideoStats = TrainingWebHelper::getCourseVideoStats($modx, $service, $courseId, $userId);
$courseActivityStats = $service->getCourseActivityStats($courseId, $userId);
$courseVideosCompleted = (int)$courseVideoStats['completed_videos'];
$courseVideosTotal = (int)$courseVideoStats['total_videos'];
$courseTestsPassed = (int)$courseActivityStats['tests_passed'];
$courseTestsTotal = (int)$courseActivityStats['tests_total'];
$coursePracticesCompleted = (int)$courseActivityStats['practices_completed'];
$coursePracticesTotal = (int)$courseActivityStats['practices_total'];

$courseStatusText = 'Не начат';
$courseStatusClass = 'label-chip--blue';
$totalCourseTrackItems = $courseVideosTotal + $coursePracticesTotal + $courseTestsTotal;
$completedCourseTrackItems = $courseVideosCompleted + $coursePracticesCompleted + $courseTestsPassed;
$courseProgressPercent = $totalCourseTrackItems > 0
    ? (int)round(($completedCourseTrackItems / $totalCourseTrackItems) * 100)
    : (int)round((float)$userCourse->get('progress_percent'));

if ((string)$userCourse->get('status') === 'completed' || $courseProgressPercent >= 100) {
    $courseStatusText = 'Курс завершен';
    $courseStatusClass = 'label-chip--green';
} elseif ((string)$userCourse->get('status') === 'in_progress' || $courseProgressPercent > 0) {
    $courseStatusText = 'В процессе';
    $courseStatusClass = 'label-chip--purple';
}

$modulesHtml = '';
$courseDurationSeconds = (int)$courseLessonStats['duration_seconds'];
$modules = $service->getCourseModules($courseId, true, false);
$moduleIds = array();
/** @var TrainingModule $module */
foreach ($modules as $module) {
    $moduleIds[] = (int)$module->get('id');
}
$openModuleId = (int)$userCourse->get('current_module_id');
if ($openModuleId <= 0 && !empty($moduleIds)) {
    $openModuleId = (int)reset($moduleIds);
}

/** @var TrainingModule $module */
foreach ($modules as $module) {
    $moduleId = (int)$module->get('id');
    $moduleResourceId = (int)$module->get('resource_id');
    /** @var modResource $moduleResource */
    $moduleResource = $module->getOne('Resource');
    $moduleTitle = $moduleResource ? trim((string)$moduleResource->get('pagetitle')) : ('Модуль ' . $moduleId);

    $lessonRows = $service->getModuleLessons($moduleId, true, false);
    $moduleLessonStats = trainingCoursePageGetModuleLessonStats($service, $modx, $courseId, $moduleId, $userId);
    $moduleActivityRows = $service->getModuleActivityRows($courseId, $moduleId, $userId);
    $moduleActivityStats = $service->getModuleActivityStats($courseId, $moduleId, $userId);

    /** @var TrainingUserModuleProgress $moduleProgress */
    $moduleProgress = $service->recalculateModuleProgressFromLessons($courseId, $moduleId, $userId);
    $moduleProgressPercent = $moduleProgress ? round((float)$moduleProgress->get('progress_percent')) : 0;
    $moduleCompleted = $moduleProgress ? ((int)$moduleProgress->get('completed') === 1) : false;
    $moduleLocked = !$service->canAccessModule($courseId, $moduleId, $userId);
    $moduleStarted =
        (int)$moduleLessonStats['started_lessons'] > 0
        || (int)$moduleActivityStats['tests_started'] > 0
        || (int)$moduleActivityStats['practices_started'] > 0
        || $moduleProgressPercent > 0
        || ((int)$userCourse->get('current_module_id') === $moduleId);

    $moduleDurationSeconds = $moduleProgress ? (int)$moduleProgress->get('duration_seconds') : 0;
    if ($moduleDurationSeconds <= 0) {
        $moduleDurationSeconds = (int)$moduleLessonStats['duration_seconds'];
    }
    $moduleDurationText = trainingCoursePageFormatDuration($moduleDurationSeconds);
    $moduleDurationClass = $moduleDurationText !== '' ? '' : 'd-none';

    $requiredItemsCompleted = (int)$moduleLessonStats['completed_lessons'] + (int)$moduleActivityStats['required_completed'];
    $requiredItemsTotal = (int)$moduleLessonStats['total_lessons'] + (int)$moduleActivityStats['required_total'];
    $moduleItemsTotal = count($lessonRows) + count($moduleActivityRows);

    if ($moduleLocked) {
        $moduleStatusHtml = '<span class="cs-lock"><img src="theme/images/training/curses/lock-keyhole-ico.svg" class="img-svg" alt=""><span>Заблокировано</span></span>';
        $moduleState = 'locked';
    } elseif ($moduleCompleted) {
        $moduleStatusHtml = '<span class="label-chip label-chip--green"><span>Завершен</span></span>';
        $moduleState = 'done';
    } elseif ($moduleStarted) {
        $moduleStatusHtml = '<span class="label-chip label-chip--purple"><span>В процессе (' . (int)$requiredItemsCompleted . '/' . (int)$requiredItemsTotal . ')</span></span>';
        $moduleState = 'progress';
    } else {
        $moduleStatusHtml = '<span class="label-chip label-chip--blue"><span>Не начат</span></span>';
        $moduleState = 'new';
    }

    $moduleLessonsHtml = '';

    usort($lessonRows, function ($a, $b) {
        $aSort = (int)$a->get('sort_order');
        $bSort = (int)$b->get('sort_order');
        if ($aSort !== $bSort) {
            return $aSort < $bSort ? -1 : 1;
        }

        $aId = (int)$a->get('id');
        $bId = (int)$b->get('id');
        if ($aId === $bId) {
            return 0;
        }

        return $aId < $bId ? -1 : 1;
    });

    usort($moduleActivityRows, function ($a, $b) {
        $aSort = (int)$a['sort_order'];
        $bSort = (int)$b['sort_order'];
        if ($aSort !== $bSort) {
            return $aSort < $bSort ? -1 : 1;
        }

        $aId = (int)$a['id'];
        $bId = (int)$b['id'];
        if ($aId === $bId) {
            return 0;
        }

        return $aId < $bId ? -1 : 1;
    });

    $previousLessonsCompleted = true;
    $previousRequiredActivitiesCompleted = true;

    /** @var TrainingModuleLesson $lesson */
    foreach ($lessonRows as $lesson) {
        $lessonId = (int)$lesson->get('id');
        $lessonTitle = trim((string)$lesson->get('title'));
        if ($lessonTitle === '') {
            $lessonTitle = 'Урок ' . ((int)$lesson->get('sort_order') + 1);
        }

        $lessonVideoStats = TrainingWebHelper::getLessonVideoStats($modx, $service, $courseId, $lessonId, $userId);
        $lessonCompleted = (int)$lessonVideoStats['total_videos'] > 0 && (int)$lessonVideoStats['completed_videos'] >= (int)$lessonVideoStats['total_videos'];
        $lessonStarted = (int)$lessonVideoStats['started_videos'] > 0 || (int)$lessonVideoStats['completed_videos'] > 0;
        $lessonPreferredVideoId = TrainingWebHelper::getPreferredLessonVideoId($modx, $courseId, $lessonId, $userId, 0);
        $lessonLocked = !$service->canAccessLesson($courseId, $moduleId, $lessonId, $userId);
        $lessonProgressPercent = 0;
        if ((int)$lessonVideoStats['total_videos'] > 0) {
            $lessonProgressPercent = (int)round(((int)$lessonVideoStats['completed_videos'] / (int)$lessonVideoStats['total_videos']) * 100);
        }
        $lessonUrl = !$lessonLocked ? trainingCoursePageMakePlayerUrl($modx, $moduleResourceId, $lessonId, $lessonPreferredVideoId) : '';

        if ($lessonLocked) {
            $lessonStatusHtml = '<span class="cs-lock"><img src="theme/images/training/curses/lock-keyhole-ico.svg" class="img-svg" alt=""><span>Заблокировано</span></span>';
            $lessonStatusMobileHtml = '<span class="cs-lock"><img src="theme/images/training/curses/lock-keyhole-ico.svg" class="img-svg" alt=""></span>';
        } elseif ($lessonCompleted) {
            $lessonStatusHtml = '<a class="label-chip label-chip--green" href="' . trainingCoursePageEsc($lessonUrl) . '" style="text-decoration:none;"><span>Просмотрено</span></a>';
            $lessonStatusMobileHtml = '<a class="label-chip label-chip--green" href="' . trainingCoursePageEsc($lessonUrl) . '" style="text-decoration:none;"><span>✓</span></a>';
        } elseif ($lessonStarted) {
            $lessonStatusHtml = '<a class="label-chip label-chip--purple" href="' . trainingCoursePageEsc($lessonUrl) . '" style="text-decoration:none;"><span>Продолжить</span></a>';
            $lessonStatusMobileHtml = '<a class="label-chip label-chip--purple" href="' . trainingCoursePageEsc($lessonUrl) . '" style="text-decoration:none;"><span>' . $lessonProgressPercent . '%</span></a>';
        } else {
            $lessonStatusHtml = '<a class="label-chip label-chip--blue" href="' . trainingCoursePageEsc($lessonUrl) . '" style="text-decoration:none;"><span>Начать</span></a>';
            $lessonStatusMobileHtml = '<a class="label-chip label-chip--blue" href="' . trainingCoursePageEsc($lessonUrl) . '" style="text-decoration:none;"><span>→</span></a>';
        }

        $lessonIconHtml = '<span class="cs-item__ico" aria-hidden="true"><img src="theme/images/training/curses/play-ico.png" alt=""></span>';
        if (!$lessonLocked && $lessonUrl !== '') {
            $lessonIconHtml = '<a href="' . trainingCoursePageEsc($lessonUrl) . '" class="cs-item__playlink" aria-label="Открыть урок"><span class="cs-item__ico" aria-hidden="true"><img src="theme/images/training/curses/play-ico.png" alt=""></span></a>';
        }

        $lessonTitleHtml = $lessonUrl !== ''
            ? '<a href="' . trainingCoursePageEsc($lessonUrl) . '" style="color:inherit;text-decoration:none;">' . trainingCoursePageEsc($lessonTitle) . '</a>'
            : trainingCoursePageEsc($lessonTitle);

        $moduleLessonsHtml .= trainingCoursePageRenderChunk($modx, $corePath, $lessonTpl, array(
            'lesson_locked_class' => $lessonLocked ? ' is-locked' : '',
            'lesson_icon_html' => $lessonIconHtml,
            'lesson_title_html' => $lessonTitleHtml,
            'lesson_type' => 'Учебный материал',
            'lesson_status_html' => $lessonStatusHtml,
            'lesson_status_mobile_html' => $lessonStatusMobileHtml,
        ));

        if (!$lessonCompleted) {
            $previousLessonsCompleted = false;
        }
    }

    foreach ($moduleActivityRows as $activity) {
        $activityLocked = $moduleLocked || !$previousLessonsCompleted || !$previousRequiredActivitiesCompleted;
        $isPractice = $activity['link_type'] === 'practice';
        $activityUrlId = isset($activity['activity_id']) ? (int)$activity['activity_id'] : (int)$activity['id'];
        if ($isPractice && !empty($activity['practice_id'])) {
            $activityUrlId = (int)$activity['practice_id'];
        }
        $activityLinkId = isset($activity['link_id']) ? (int)$activity['link_id'] : 0;
        $activityUrl = !$activityLocked ? trainingCoursePageMakeActivityUrl($modx, $activityUrlId, $activity['link_type'], $resourceId, $activityLinkId) : '';
        $activityStatusKey = (string)$activity['status_key'];
        $activityCompleted = !empty($activity['completed']);
        $activityStarted = !empty($activity['started']);
        $activityTypeLabel = $isPractice ? 'Практическое задание' : 'Тест';

        if ($activityLocked) {
            $activityStatusHtml = '<span class="cs-lock"><img src="theme/images/training/curses/lock-keyhole-ico.svg" class="img-svg" alt=""><span>Заблокировано</span></span>';
            $activityStatusMobileHtml = '<span class="cs-lock"><img src="theme/images/training/curses/lock-keyhole-ico.svg" class="img-svg" alt=""></span>';
        } elseif ($activityCompleted) {
            $activityDoneText = $isPractice ? 'Принято' : 'Пройден';
            $activityDoneMobileText = '✓';
            if ($activityUrl !== '') {
                $activityStatusHtml = '<a class="label-chip label-chip--green" href="' . trainingCoursePageEsc($activityUrl) . '" style="text-decoration:none;"><span>' . trainingCoursePageEsc($activityDoneText) . '</span></a>';
                $activityStatusMobileHtml = '<a class="label-chip label-chip--green" href="' . trainingCoursePageEsc($activityUrl) . '" style="text-decoration:none;"><span>' . $activityDoneMobileText . '</span></a>';
            } else {
                $activityStatusHtml = '<span class="label-chip label-chip--green"><span>' . trainingCoursePageEsc($activityDoneText) . '</span></span>';
                $activityStatusMobileHtml = '<span class="label-chip label-chip--green"><span>' . $activityDoneMobileText . '</span></span>';
            }
        } elseif (in_array($activityStatusKey, array('submitted', 'in_review', 'checking', 'pending_review'), true)) {
            $reviewText = $isPractice ? 'На проверке' : 'На проверке';
            if ($activityUrl !== '') {
                $activityStatusHtml = '<a class="label-chip label-chip--orange" href="' . trainingCoursePageEsc($activityUrl) . '" style="text-decoration:none;"><span>' . trainingCoursePageEsc($reviewText) . '</span></a>';
                $activityStatusMobileHtml = '<a class="label-chip label-chip--orange" href="' . trainingCoursePageEsc($activityUrl) . '" style="text-decoration:none;"><span>…</span></a>';
            } else {
                $activityStatusHtml = '<span class="label-chip label-chip--orange"><span>' . trainingCoursePageEsc($reviewText) . '</span></span>';
                $activityStatusMobileHtml = '<span class="label-chip label-chip--orange"><span>…</span></span>';
            }
        } elseif ($isPractice && in_array($activityStatusKey, array('revision', 'revision_requested'), true)) {
            if ($activityUrl !== '') {
                $activityStatusHtml = '<a class="label-chip label-chip--purple" href="' . trainingCoursePageEsc($activityUrl) . '" style="text-decoration:none;"><span>На доработке</span></a>';
                $activityStatusMobileHtml = '<a class="label-chip label-chip--purple" href="' . trainingCoursePageEsc($activityUrl) . '" style="text-decoration:none;"><span>!</span></a>';
            } else {
                $activityStatusHtml = '<span class="label-chip label-chip--purple"><span>На доработке</span></span>';
                $activityStatusMobileHtml = '<span class="label-chip label-chip--purple"><span>!</span></span>';
            }
        } elseif (in_array($activityStatusKey, array('failed', 'rejected'), true)) {
            $failedText = $isPractice ? 'Отклонено' : 'Не пройден';
            if ($activityUrl !== '') {
                $activityStatusHtml = '<a class="label-chip label-chip--red" href="' . trainingCoursePageEsc($activityUrl) . '" style="text-decoration:none;"><span>' . trainingCoursePageEsc($failedText) . '</span></a>';
                $activityStatusMobileHtml = '<a class="label-chip label-chip--red" href="' . trainingCoursePageEsc($activityUrl) . '" style="text-decoration:none;"><span>×</span></a>';
            } else {
                $activityStatusHtml = '<span class="label-chip label-chip--red"><span>' . trainingCoursePageEsc($failedText) . '</span></span>';
                $activityStatusMobileHtml = '<span class="label-chip label-chip--red"><span>×</span></span>';
            }
        } elseif ($activityStarted) {
            if ($activityUrl !== '') {
                $activityStatusHtml = '<a class="label-chip label-chip--purple" href="' . trainingCoursePageEsc($activityUrl) . '" style="text-decoration:none;"><span>В процессе</span></a>';
                $activityStatusMobileHtml = '<a class="label-chip label-chip--purple" href="' . trainingCoursePageEsc($activityUrl) . '" style="text-decoration:none;"><span>…</span></a>';
            } else {
                $activityStatusHtml = '<span class="label-chip label-chip--purple"><span>В процессе</span></span>';
                $activityStatusMobileHtml = '<span class="label-chip label-chip--purple"><span>…</span></span>';
            }
        } else {
            if ($activityUrl !== '') {
                $activityStatusHtml = '<a class="label-chip label-chip--blue" href="' . trainingCoursePageEsc($activityUrl) . '" style="text-decoration:none;"><span>Начать</span></a>';
                $activityStatusMobileHtml = '<a class="label-chip label-chip--blue" href="' . trainingCoursePageEsc($activityUrl) . '" style="text-decoration:none;"><span>→</span></a>';
            } else {
                $activityStatusHtml = '<span class="label-chip label-chip--blue"><span>Доступно</span></span>';
                $activityStatusMobileHtml = '<span class="label-chip label-chip--blue"><span>→</span></span>';
            }
        }

        $activityTitleHtml = $activityUrl !== ''
            ? '<a href="' . trainingCoursePageEsc($activityUrl) . '" style="color:inherit;text-decoration:none;">' . trainingCoursePageEsc($activity['title']) . '</a>'
            : trainingCoursePageEsc($activity['title']);

        $moduleLessonsHtml .= trainingCoursePageRenderChunk($modx, $corePath, $lessonTpl, array(
            'lesson_locked_class' => $activityLocked ? ' is-locked' : '',
            'lesson_icon_html' => trainingCoursePageActivityIconHtml($activity['link_type'], $activityUrl),
            'lesson_title_html' => $activityTitleHtml,
            'lesson_type' => trainingCoursePageEsc($activityTypeLabel),
            'lesson_status_html' => $activityStatusHtml,
            'lesson_status_mobile_html' => $activityStatusMobileHtml,
        ));

        if (!empty($activity['is_required']) && !$activityCompleted) {
            $previousRequiredActivitiesCompleted = false;
        }
    }

    $moduleItemsCountText = $moduleItemsTotal . ' ' . trainingCoursePagePlural($moduleItemsTotal, array('материал', 'материала', 'материалов'));
    $modulesHtml .= trainingCoursePageRenderChunk($modx, $corePath, $moduleTpl, array(
        'module_open_class' => $openModuleId === $moduleId ? ' is-open' : '',
        'module_aria_expanded' => $openModuleId === $moduleId ? 'true' : 'false',
        'module_state' => $moduleState,
        'module_title' => trainingCoursePageEsc($moduleTitle),
        'module_lessons_count_text' => trainingCoursePageEsc($moduleItemsCountText),
        'module_duration_text' => trainingCoursePageEsc($moduleDurationText),
        'module_duration_class' => $moduleDurationClass,
        'module_status_html' => $moduleStatusHtml,
        'module_lessons_html' => $moduleLessonsHtml,
    ));
}

if ($courseDurationText === '') {
    $courseDurationText = trainingCoursePageFormatDuration($courseDurationSeconds);
}

$courseVideosText = $courseVideosCompleted . '/' . $courseVideosTotal;
$coursePracticesText = $coursePracticesCompleted . '/' . $coursePracticesTotal;
$courseTestsText = $courseTestsPassed . '/' . max(0, $courseTestsTotal);

return trainingCoursePageRenderChunk($modx, $corePath, $pageTpl, array(
    'course_title' => trainingCoursePageEsc($courseTitle),
    'course_image' => trainingCoursePageEsc($courseImage),
    'course_status_text' => trainingCoursePageEsc($courseStatusText),
    'course_status_class' => $courseStatusClass,
    'course_user_label' => trainingCoursePageEsc(trainingCoursePageGetUserLabel($modx->user)),
    'course_duration_text' => trainingCoursePageEsc($courseDurationText),
    'course_duration_class' => $courseDurationText !== '' ? '' : 'd-none',
    'course_description' => $courseDescription,
    'course_progress_percent' => $courseProgressPercent,
    'course_completed_modules' => (int)$userCourse->get('completed_modules'),
    'course_total_modules' => (int)$userCourse->get('total_modules'),
    'course_videos_completed' => $courseVideosCompleted,
    'course_videos_total' => $courseVideosTotal,
    'course_videos_text' => trainingCoursePageEsc($courseVideosText),
    'course_practices_completed' => $coursePracticesCompleted,
    'course_practices_total' => $coursePracticesTotal,
    'course_practices_text' => trainingCoursePageEsc($coursePracticesText),
    'course_tests_passed' => $courseTestsPassed,
    'course_tests_total' => $courseTestsTotal,
    'course_tests_text' => trainingCoursePageEsc($courseTestsText),
    'course_modules_html' => $modulesHtml,
));
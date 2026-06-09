<?php
/** @var modX $modx */

if (!function_exists('trainingManageCoursesPageRenderFile')) {
    function trainingManageCoursesPageRenderFile(modX $modx, $path, array $placeholders = [])
    {
        if (!is_file($path)) {
            return '';
        }

        $chunk = $modx->newObject('modChunk');
        $chunk->setCacheable(false);
        $chunk->setContent(file_get_contents($path));

        return $chunk->process($placeholders);
    }
}

$userId = (int)$modx->user->get('id');
if ($userId <= 0) {
    return '';
}

$corePath = $modx->getOption(
    'training.core_path',
    null,
    $modx->getOption('core_path') . 'components/training/'
);

$chunkBase = rtrim($corePath, '/\\') . '/elements/chunks/training/manage/';
$pageChunkPath = $chunkBase . 'courses.tpl';
$modalChunkPath = $chunkBase . 'request-course-modal.tpl';
$accessModalChunkPath = $chunkBase . 'course-access-modal.tpl';

$tablePrefix = $modx->getOption('table_prefix');
$courseAccessTable = $tablePrefix . 'partnerstraining_course_access';
$userCoursesTable = $tablePrefix . 'partnerstraining_user_courses';
$coursesTable = $tablePrefix . 'partnerstraining_courses';
$resourceTable = $modx->getTableName('modResource');

$coursesMap = [];
$coursesOrder = [];

$addCourse = function (array $row) use (&$coursesMap, &$coursesOrder) {
    $courseId = (int)$row['course_id'];
    $resourceId = (int)$row['resource_id'];
    if ($courseId <= 0 || $resourceId <= 0) {
        return;
    }

    if (!isset($coursesMap[$courseId])) {
        $title = trim((string)$row['title']);
        $coursesMap[$courseId] = [
            'course_id' => $courseId,
            'resource_id' => $resourceId,
            'title' => $title !== '' ? $title : ('Курс #' . $courseId),
        ];
        $coursesOrder[] = $courseId;
    }
};

$now = date('Y-m-d H:i:s');

// 1. Основной источник: прямые права директора на курс.
$sqlAccess = "
    SELECT DISTINCT
        ca.course_id,
        c.resource_id,
        COALESCE(NULLIF(TRIM(r.pagetitle), ''), CONCAT('Курс #', ca.course_id)) AS title
    FROM {$courseAccessTable} AS ca
    INNER JOIN {$coursesTable} AS c
        ON c.id = ca.course_id
       AND c.is_active = 1
    LEFT JOIN {$resourceTable} AS r
        ON r.id = c.resource_id
       AND r.deleted = 0
    WHERE ca.principal_type = 'user'
      AND ca.principal_id = :user_id
      AND ca.access_role = 'director'
      AND ca.is_active = 1
      AND (ca.active_from IS NULL OR ca.active_from = '0000-00-00 00:00:00' OR ca.active_from <= :now)
      AND (ca.active_to IS NULL OR ca.active_to = '0000-00-00 00:00:00' OR ca.active_to >= :now)
    ORDER BY ca.course_id ASC
";

$stmtAccess = $modx->prepare($sqlAccess);
if ($stmtAccess) {
    $stmtAccess->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmtAccess->bindValue(':now', $now, PDO::PARAM_STR);
    if ($stmtAccess->execute()) {
        while ($row = $stmtAccess->fetch(PDO::FETCH_ASSOC)) {
            $addCourse($row);
        }
    }
}

// 2. Fallback: user_courses, если у директора уже есть синхронизированная запись.
$sqlUserCourses = "
    SELECT DISTINCT
        uc.course_id,
        c.resource_id,
        COALESCE(NULLIF(TRIM(r.pagetitle), ''), CONCAT('Курс #', uc.course_id)) AS title
    FROM {$userCoursesTable} AS uc
    INNER JOIN {$coursesTable} AS c
        ON c.id = uc.course_id
       AND c.is_active = 1
    LEFT JOIN {$resourceTable} AS r
        ON r.id = c.resource_id
       AND r.deleted = 0
    WHERE uc.user_id = :user_id
      AND uc.access_role = 'director'
      AND uc.status <> 'revoked'
    ORDER BY uc.course_id ASC
";

$stmtUserCourses = $modx->prepare($sqlUserCourses);
if ($stmtUserCourses) {
    $stmtUserCourses->bindValue(':user_id', $userId, PDO::PARAM_INT);
    if ($stmtUserCourses->execute()) {
        while ($row = $stmtUserCourses->fetch(PDO::FETCH_ASSOC)) {
            $addCourse($row);
        }
    }
}

$courses = [];
foreach ($coursesOrder as $courseId) {
    if (isset($coursesMap[$courseId])) {
        $courses[] = $coursesMap[$courseId];
    }
}

$coursesButtons = '';
if (!empty($courses)) {
    foreach ($courses as $index => $course) {
        $coursesButtons .= '<div class="swiper-slide w-auto">';
        $coursesButtons .= '<button type="button" class="course-filter__chip' . ($index === 0 ? ' is-active' : '') . '"';
        $coursesButtons .= ' data-course-id="' . (int)$course['course_id'] . '"';
        $coursesButtons .= ' data-resource-id="' . (int)$course['resource_id'] . '"';
        $coursesButtons .= ' data-filter="course_' . (int)$course['course_id'] . '"';
        $coursesButtons .= ' aria-selected="' . ($index === 0 ? 'true' : 'false') . '">';
        $coursesButtons .= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8');
        $coursesButtons .= '</button>';
        $coursesButtons .= '</div>';
    }
} else {
    $coursesButtons = '<div class="manage-courses-empty">Доступных курсов пока нет</div>';
}

$hasCourses = !empty($courses);

return trainingManageCoursesPageRenderFile($modx, $pageChunkPath, [
    'context_key' => $modx->context->get('key'),
    'available_count' => count($courses),
    'courses_buttons' => $coursesButtons,
    'controls_disabled' => $hasCourses ? '' : ' disabled="disabled"',
    'request_modal' => trainingManageCoursesPageRenderFile($modx, $modalChunkPath, []),
    'course_access_modal' => trainingManageCoursesPageRenderFile($modx, $accessModalChunkPath, []),
]);

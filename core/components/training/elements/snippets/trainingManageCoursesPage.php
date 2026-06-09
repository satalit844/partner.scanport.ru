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


if (!function_exists('trainingManageCoursesPageResolveTable')) {
    function trainingManageCoursesPageResolveTable(modX $modx, $className, $plainName)
    {
        $prefix = (string)$modx->getOption('table_prefix');
        $candidates = array();

        if ($className !== '') {
            $table = trim((string)$modx->getTableName($className), '`');
            if ($table !== '') {
                $candidates[] = $table;
            }
        }

        $plainName = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$plainName);
        if ($plainName !== '') {
            $candidates[] = $prefix . $plainName;
            $candidates[] = $prefix . '_' . $plainName;
            $candidates[] = 'modx_' . $plainName;
            $candidates[] = 'modx_partners' . $plainName;
        }

        $checked = array();
        foreach ($candidates as $table) {
            $table = trim((string)$table, '`');
            $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
            if ($table === '' || isset($checked[$table])) {
                continue;
            }
            $checked[$table] = true;

            $stmt = $modx->query('SHOW TABLES LIKE ' . $modx->quote($table));
            if ($stmt && $stmt->fetchColumn()) {
                return $table;
            }
        }

        return preg_replace('/[^a-zA-Z0-9_]/', '', $prefix . $plainName);
    }
}

if (!function_exists('trainingManageCoursesPageSafe')) {
    function trainingManageCoursesPageSafe($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}


if (!function_exists('trainingManageCoursesPageExtractCompany')) {
    function trainingManageCoursesPageExtractCompany($profile)
    {
        if (!$profile) {
            return '';
        }

        foreach (array('field_list_company', 'field_company', 'company', 'organization', 'organisation', 'company_name', 'employer') as $key) {
            $value = trim((string)$profile->get($key));
            if ($value !== '') {
                return $value;
            }
        }

        $extended = $profile->get('extended');
        if (is_string($extended) && $extended !== '') {
            $decoded = json_decode($extended, true);
            if (is_array($decoded)) {
                $extended = $decoded;
            }
        }

        if (is_array($extended)) {
            foreach (array('field_list_company', 'field_company', 'company', 'organization', 'organisation', 'company_name', 'employer') as $key) {
                if (!empty($extended[$key])) {
                    return trim((string)$extended[$key]);
                }
            }
        }

        return '';
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

$courseAccessTable = trainingManageCoursesPageResolveTable($modx, 'TrainingCourseAccess', 'training_course_access');
$userCoursesTable = trainingManageCoursesPageResolveTable($modx, 'TrainingUserCourse', 'training_user_courses');
$managerLinkTable = trainingManageCoursesPageResolveTable($modx, 'TrainingUserManagerLink', 'training_user_manager_link');
$coursesTable = trainingManageCoursesPageResolveTable($modx, 'TrainingCourse', 'training_courses');
$resourceTable = $modx->getTableName('modResource');

$coursesMap = [];
$coursesOrder = [];

$addCourse = function (array $row) use (&$coursesMap, &$coursesOrder) {
    $courseId = (int)(isset($row['course_id']) ? $row['course_id'] : 0);
    if ($courseId <= 0) {
        return;
    }

    if (!isset($coursesMap[$courseId])) {
        $resourceId = (int)(isset($row['resource_id']) ? $row['resource_id'] : 0);
        $title = trim((string)(isset($row['title']) ? $row['title'] : ''));
        $coursesMap[$courseId] = [
            'course_id' => $courseId,
            'resource_id' => $resourceId,
            'title' => $title !== '' ? $title : ('Курс #' . $courseId),
        ];
        $coursesOrder[] = $courseId;
    }
};

$runCourseQuery = function ($sql, array $params = []) use ($modx, $addCourse) {
    $stmt = $modx->prepare($sql);
    if (!$stmt) {
        return;
    }
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $addCourse($row);
        }
    }
};

// 1. Прямое назначение директором на курс.
// ВАЖНО: для управления курсом НЕ проверяем ca.is_active / даты / c.is_active.
// Если директора выключили, он не проходит курс, но управляет своими сотрудниками.
$runCourseQuery("
    SELECT DISTINCT
        ca.`course_id`,
        COALESCE(c.`resource_id`, 0) AS `resource_id`,
        COALESCE(NULLIF(TRIM(r.`pagetitle`), ''), CONCAT('Курс #', ca.`course_id`)) AS `title`
    FROM `{$courseAccessTable}` AS ca
    LEFT JOIN `{$coursesTable}` AS c
        ON c.`id` = ca.`course_id`
    LEFT JOIN {$resourceTable} AS r
        ON r.`id` = c.`resource_id`
       AND r.`deleted` = 0
    WHERE ca.`principal_type` = 'user'
      AND (ca.`principal_id` = :principal_id OR ca.`user_id` = :legacy_user_id)
      AND ca.`access_role` = 'director'
    ORDER BY ca.`course_id` ASC
", [
    ':principal_id' => $userId,
    ':legacy_user_id' => $userId,
]);

// 2. Fallback: user_courses. Если запись не revoked, курс можно показывать в управлении.
$runCourseQuery("
    SELECT DISTINCT
        uc.`course_id`,
        COALESCE(c.`resource_id`, 0) AS `resource_id`,
        COALESCE(NULLIF(TRIM(r.`pagetitle`), ''), CONCAT('Курс #', uc.`course_id`)) AS `title`
    FROM `{$userCoursesTable}` AS uc
    LEFT JOIN `{$coursesTable}` AS c
        ON c.`id` = uc.`course_id`
    LEFT JOIN {$resourceTable} AS r
        ON r.`id` = c.`resource_id`
       AND r.`deleted` = 0
    WHERE uc.`user_id` = :user_id
      AND uc.`access_role` = 'director'
      AND uc.`status` <> 'revoked'
    ORDER BY uc.`course_id` ASC
", [':user_id' => $userId]);

// 3. Если есть только связь директор -> сотрудник, показываем активные курсы для назначения.
$managedCount = 0;
$stmtManaged = $modx->prepare("SELECT COUNT(*) FROM `{$managerLinkTable}` WHERE `manager_user_id` = :user_id AND `is_active` = 1");
if ($stmtManaged) {
    $stmtManaged->bindValue(':user_id', $userId, PDO::PARAM_INT);
    if ($stmtManaged->execute()) {
        $managedCount = (int)$stmtManaged->fetchColumn();
    }
}

if ($managedCount > 0) {
    $runCourseQuery("
        SELECT DISTINCT
            c.`id` AS `course_id`,
            c.`resource_id`,
            COALESCE(NULLIF(TRIM(r.`pagetitle`), ''), CONCAT('Курс #', c.`id`)) AS `title`
        FROM `{$coursesTable}` AS c
        LEFT JOIN {$resourceTable} AS r
            ON r.`id` = c.`resource_id`
           AND r.`deleted` = 0
        WHERE c.`is_active` = 1
        ORDER BY c.`id` ASC
    ");
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

$profile = $modx->user->getOne('Profile');
$userFullname = '';
$userEmail = '';
$userPhone = '';
$userCompany = '';
if ($profile) {
    $userFullname = trim((string)$profile->get('fullname'));
    $userEmail = trim((string)$profile->get('email'));
    $userPhone = trim((string)$profile->get('mobilephone'));
    if ($userPhone === '') {
        $userPhone = trim((string)$profile->get('phone'));
    }
    $userCompany = trainingManageCoursesPageExtractCompany($profile);
}
if ($userFullname === '') {
    $userFullname = trim((string)$modx->user->get('username'));
}

$recipient = trim((string)$modx->getOption('training.course_request_email', null, 'satalit844@mail.ru'));
if ($recipient === '') {
    $recipient = 'satalit844@mail.ru';
}

$modx->setPlaceholders([
    'user_id' => (string)$userId,
    'context_key' => (string)$modx->context->get('key'),
    'fullname' => trainingManageCoursesPageSafe($userFullname),
    'email' => trainingManageCoursesPageSafe($userEmail),
    'phone' => trainingManageCoursesPageSafe($userPhone),
    'company' => trainingManageCoursesPageSafe($userCompany),
], 'training_course_request_');

$requestFormHtml = '';
if ($modx->getObject('modSnippet', ['name' => 'AjaxForm'])) {
    $requestFormHtml = $modx->runSnippet('AjaxForm', [
        'snippet' => 'FormIt',
        'form' => 'training.manage.request-course.form',
        'hooks' => 'email',
        'emailTpl' => 'training.manage.request-course.email',
        'emailTo' => $recipient,
        'emailFrom' => $modx->getOption('emailsender'),
        'emailFromName' => $modx->getOption('site_name'),
        'emailReplyTo' => '[[+email]]',
        'emailSubject' => 'Запрос на курс',
        'emailHtml' => true,
        'submitVar' => 'training_course_request_submit',
        'validate' => 'fullname:required,email:required:email,personal_data_agree:required',
        'validationErrorMessage' => 'Проверьте заполнение формы.',
        'successMessage' => 'Заявка отправлена. Мы свяжемся с вами.',
        'clearFieldsOnSuccess' => true,
    ]);
}

if ($requestFormHtml === '') {
    $requestFormHtml = '<div class="training-request-modal__fallback">Форма временно недоступна: проверьте сниппет AjaxForm и чанки training.manage.request-course.form / training.manage.request-course.email.</div>';
}

$hasCourses = !empty($courses);

return trainingManageCoursesPageRenderFile($modx, $pageChunkPath, [
    'context_key' => $modx->context->get('key'),
    'available_count' => count($courses),
    'courses_buttons' => $coursesButtons,
    'controls_disabled' => $hasCourses ? '' : ' disabled="disabled"',
    'request_modal' => trainingManageCoursesPageRenderFile($modx, $modalChunkPath, [
        'ajax_form' => $requestFormHtml,
    ]),
    'course_access_modal' => trainingManageCoursesPageRenderFile($modx, $accessModalChunkPath, []),
]);

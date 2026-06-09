<?php
/** @var modX $modx */
/** @var array $scriptProperties */

$corePath = $modx->getOption('training.core_path', null, $modx->getOption('core_path') . 'components/training/');
require_once $corePath . 'elements/snippets/_certificateHelper.php';

$pageTpl = trim((string)$modx->getOption('pageTpl', $scriptProperties, 'training/certificates/page.tpl'));
$itemTpl = trim((string)$modx->getOption('itemTpl', $scriptProperties, 'training/certificates/item.tpl'));
$modalTpl = trim((string)$modx->getOption('modalTpl', $scriptProperties, 'training/certificates/modal.tpl'));
$userSelectTpl = trim((string)$modx->getOption('userSelectTpl', $scriptProperties, 'training/certificates/user-select.tpl'));

$contextKey = $modx->context ? (string)$modx->context->get('key') : '';
$actorUserId = ($modx->user && (int)$modx->user->get('id')) ? (int)$modx->user->get('id') : 0;
$isAdmin = trainingCertificatesIsAdmin($modx);

$debug = array(
    'snippet' => 'trainingManageCertificatesPage.php',
    'context' => $contextKey,
    'actor_user_id' => $actorUserId,
    'is_admin' => $isAdmin ? 'yes' : 'no',
    'auth_context' => ($actorUserId > 0 && $contextKey !== '' && $modx->user->isAuthenticated($contextKey)) ? 'yes' : 'no',
    'auth_web' => ($actorUserId > 0 && $modx->user->isAuthenticated('web')) ? 'yes' : 'no',
    'pageTpl_exists' => is_file(trainingCertificatesChunkPath($corePath, $pageTpl)) ? 'yes' : 'no',
    'itemTpl_exists' => is_file(trainingCertificatesChunkPath($corePath, $itemTpl)) ? 'yes' : 'no',
    'modalTpl_exists' => is_file(trainingCertificatesChunkPath($corePath, $modalTpl)) ? 'yes' : 'no',
    'userSelectTpl_exists' => is_file(trainingCertificatesChunkPath($corePath, $userSelectTpl)) ? 'yes' : 'no',
);

if (!trainingCertificatesIsAuthenticated($modx)) {
    if (trainingCertificatesDebugEnabled()) {
        return trainingCertificatesDebugHtml('CERT DEBUG: auth failed', $debug);
    }
    return '';
}

$tables = array(
    'certificates' => trainingCertificatesResolveTable($modx, array('partnerstraining_user_certificates', 'training_user_certificates')),
    'templates' => trainingCertificatesResolveTable($modx, array('partnerstraining_certificate_templates', 'training_certificate_templates')),
    'courses' => trainingCertificatesResolveTable($modx, array('partnerstraining_courses', 'training_courses')),
    'course_access' => trainingCertificatesResolveTable($modx, array('partnerstraining_course_access', 'training_course_access')),
    'user_courses' => trainingCertificatesResolveTable($modx, array('partnerstraining_user_courses', 'training_user_courses')),
    'manager_link' => trainingCertificatesResolveTable($modx, array('partnerstraining_user_manager_link', 'training_user_manager_link')),
);

$debug['certificates_table'] = $tables['certificates'];
$debug['certificates_table_exists'] = $tables['certificates'] !== '' && trainingCertificatesTableExists($modx, $tables['certificates']) ? 'yes' : 'no';
$debug['certificates_count_total'] = $tables['certificates'] !== '' && trainingCertificatesTableExists($modx, $tables['certificates']) ? trainingCertificatesCount($modx, $tables['certificates']) : 0;
$debug['certificates_count_actor'] = $tables['certificates'] !== '' && trainingCertificatesTableExists($modx, $tables['certificates']) ? trainingCertificatesCount($modx, $tables['certificates'], '`user_id` = :user_id', array(':user_id' => $actorUserId)) : 0;
$debug['courses_table'] = $tables['courses'];
$debug['courses_table_exists'] = $tables['courses'] !== '' && trainingCertificatesTableExists($modx, $tables['courses']) ? 'yes' : 'no';

if ($tables['certificates'] === '' || !trainingCertificatesTableExists($modx, $tables['certificates'])) {
    return trainingCertificatesRenderChunk($corePath, $pageTpl, array(
        'page_title' => 'Управление сертификатами',
        'page_subtitle' => 'Мои сертификаты',
        'toolbar_html' => '',
        'items_html' => trainingCertificatesDebugHtml('CERT DEBUG: manage page', $debug) . '<div class="w-100">Таблица сертификатов не найдена</div>',
        'modal_html' => trainingCertificatesRenderChunk($corePath, $modalTpl, array()),
    ));
}

$allowedCourseIds = array();
$allowedUserIds = array($actorUserId);

if ($tables['course_access'] !== '' && trainingCertificatesTableExists($modx, $tables['course_access'])) {
    $allowedCourseIds = array_merge($allowedCourseIds, trainingCertificatesFetchColumn(
        $modx,
        'SELECT DISTINCT ca.`course_id` '
        . 'FROM `' . $tables['course_access'] . '` ca '
        . 'WHERE ca.`is_active` = 1 '
        . 'AND ca.`access_role` IN ("director", "manager", "admin") '
        . 'AND ((ca.`principal_type` = "user" AND ca.`principal_id` = :actor1) OR ca.`user_id` = :actor2 OR ca.`assigned_by` = :actor3)',
        array(':actor1' => $actorUserId, ':actor2' => $actorUserId, ':actor3' => $actorUserId),
        'trainingManageCertificatesPage:course_access'
    ));
}

if ($tables['user_courses'] !== '' && trainingCertificatesTableExists($modx, $tables['user_courses'])) {
    $allowedCourseIds = array_merge($allowedCourseIds, trainingCertificatesFetchColumn(
        $modx,
        'SELECT DISTINCT uc.`course_id` '
        . 'FROM `' . $tables['user_courses'] . '` uc '
        . 'WHERE uc.`user_id` = :actor '
        . 'AND uc.`access_role` IN ("director", "manager", "admin") '
        . 'AND uc.`status` <> "revoked"',
        array(':actor' => $actorUserId),
        'trainingManageCertificatesPage:user_courses_courses'
    ));
}

if ($tables['manager_link'] !== '' && trainingCertificatesTableExists($modx, $tables['manager_link'])) {
    $allowedUserIds = array_merge($allowedUserIds, trainingCertificatesFetchColumn(
        $modx,
        'SELECT DISTINCT ml.`employee_user_id` '
        . 'FROM `' . $tables['manager_link'] . '` ml '
        . 'WHERE ml.`manager_user_id` = :actor AND ml.`is_active` = 1',
        array(':actor' => $actorUserId),
        'trainingManageCertificatesPage:manager_link'
    ));
}

$allowedCourseIds = array_values(array_unique(array_filter(array_map('intval', $allowedCourseIds))));
$allowedUserIds = array_values(array_unique(array_filter(array_map('intval', $allowedUserIds))));

$debug['allowed_course_ids'] = $allowedCourseIds;
$debug['allowed_user_ids'] = $allowedUserIds;

if (class_exists('TrainingCertificateService') && class_exists('Training')) {
    try {
        $service = new TrainingCertificateService($modx, new Training($modx));
        foreach ($allowedUserIds as $uid) {
            $service->ensureCertificatesForUser((int)$uid, false);
        }
    } catch (Exception $e) {
        $debug['ensure_error'] = $e->getMessage();
        $modx->log(modX::LOG_LEVEL_ERROR, '[trainingManageCertificatesPage] ensure error: ' . $e->getMessage());
    }
}

$where = array();
$params = array();

if (!$isAdmin && !empty($allowedCourseIds)) {
    $ph = array();
    foreach ($allowedCourseIds as $i => $id) {
        $key = ':course_' . $i;
        $ph[] = $key;
        $params[$key] = (int)$id;
    }
    $where[] = 'cert.`course_id` IN (' . implode(',', $ph) . ')';
}

if (!$isAdmin && !empty($allowedUserIds)) {
    $ph = array();
    foreach ($allowedUserIds as $i => $id) {
        $key = ':user_' . $i;
        $ph[] = $key;
        $params[$key] = (int)$id;
    }
    $where[] = 'cert.`user_id` IN (' . implode(',', $ph) . ')';
}

$sql = trainingCertificatesCertificateRowsSql($tables);

if (!$isAdmin && !empty($where)) {
    $sql .= 'WHERE (' . implode(' OR ', $where) . ') ';
}

$sql .= 'ORDER BY cert.`issuedon` DESC, cert.`id` DESC';

$allRows = trainingCertificatesFetchAll($modx, $sql, $params, 'trainingManageCertificatesPage:list');
$allRows = trainingCertificatesEnrichRows($modx, $allRows, $tables);

$isDirectorMode = $isAdmin || !empty($allowedCourseIds);

if (empty($allRows) && $isDirectorMode) {
    $allRows = trainingCertificatesFetchAll(
        $modx,
        trainingCertificatesCertificateRowsSql($tables) . 'ORDER BY cert.`issuedon` DESC, cert.`id` DESC',
        array(),
        'trainingManageCertificatesPage:fallback_all'
    );

    $allRows = trainingCertificatesEnrichRows($modx, $allRows, $tables);
    $debug['fallback_all'] = 'yes';
} else {
    $debug['fallback_all'] = 'no';
}

$userMap = array();

foreach ($allRows as $row) {
    $uid = (int)(isset($row['user_id']) ? $row['user_id'] : 0);
    if ($uid <= 0) {
        continue;
    }

    $name = trim((string)(isset($row['display_user']) ? $row['display_user'] : ''));
    if ($name === '') {
        $name = 'Пользователь #' . $uid;
    }

    $userMap[$uid] = $name;
}

$selectedUserId = array_key_exists('certificate_user', $_GET) ? (int)$modx->getOption('certificate_user', $_GET, 0) : 0;
$rows = $allRows;

if ($selectedUserId > 0 && isset($userMap[$selectedUserId])) {
    $rows = array();

    foreach ($allRows as $row) {
        if ((int)$row['user_id'] === $selectedUserId) {
            $rows[] = $row;
        }
    }
}

$debug['all_rows_count'] = count($allRows);
$debug['selected_user_id'] = $selectedUserId;
$debug['rows_count'] = count($rows);

$toolbarHtml = '';

if (!empty($userMap)) {
    asort($userMap, SORT_NATURAL | SORT_FLAG_CASE);

    $optionsHtml = '<option value="0"' . ($selectedUserId === 0 ? ' selected="selected"' : '') . '>Все пользователи</option>';

    foreach ($userMap as $id => $name) {
        $id = (int)$id;
        $optionsHtml .= '<option value="' . $id . '"' . ($id === $selectedUserId ? ' selected="selected"' : '') . '>' . trainingCertificatesEsc($name) . '</option>';
    }

    $toolbarHtml = trainingCertificatesRenderChunk($corePath, $userSelectTpl, array(
        'select_name' => 'certificate_user',
        'select_options_html' => $optionsHtml,
    ));
}

$itemsHtml = '';

foreach ($rows as $row) {
    $itemsHtml .= trainingCertificatesBuildItemHtml($corePath, $itemTpl, $row, true);
}

if ($itemsHtml === '') {
    $itemsHtml = '<div class="w-100">Сертификатов пока нет</div>';
}

$debugHtml = trainingCertificatesDebugHtml('CERT DEBUG: manage page', $debug);

return trainingCertificatesRenderChunk($corePath, $pageTpl, array(
    'page_title' => 'Управление сертификатами',
    'page_subtitle' => 'Мои сертификаты',
    'toolbar_html' => $toolbarHtml,
    'items_html' => $debugHtml . $itemsHtml,
    'modal_html' => trainingCertificatesRenderChunk($corePath, $modalTpl, array()),
));

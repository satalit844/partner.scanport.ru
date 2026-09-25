<?php
/** @var modX $modx */
/** @var array $scriptProperties */

$corePath = $modx->getOption(
    'training.core_path',
    null,
    $modx->getOption('core_path') . 'components/training/'
);

require_once $corePath . 'elements/snippets/_certificateHelper.php';

$pageTpl = trim((string)$modx->getOption('pageTpl', $scriptProperties, 'training/certificates/page.tpl'));
$itemTpl = trim((string)$modx->getOption('itemTpl', $scriptProperties, 'training/certificates/item.tpl'));
$modalTpl = trim((string)$modx->getOption('modalTpl', $scriptProperties, 'training/certificates/modal.tpl'));
$userSelectTpl = trim((string)$modx->getOption('userSelectTpl', $scriptProperties, 'training/certificates/user-select.tpl'));

$contextKey = $modx->context ? (string)$modx->context->get('key') : '';
$actorUserId = ($modx->user && (int)$modx->user->get('id')) ? (int)$modx->user->get('id') : 0;
$isAdmin = trainingCertificatesIsAdmin($modx);

/*
 * Временная диагностика области сертификатов.
 * Включается только параметром ?debug_cert_scope=1.
 * Пишет в MODX error log понятные блоки [trainingManageCertificatesPage][scope].
 */
$certScopeDebug = isset($_GET['debug_cert_scope'])
    && (string)$modx->getOption('debug_cert_scope', $_GET, '') === '1';

$certScopeLog = function ($event, array $data = array()) use ($modx, $certScopeDebug) {
    if (!$certScopeDebug) {
        return;
    }

    $modx->log(
        modX::LOG_LEVEL_ERROR,
        '[trainingManageCertificatesPage][scope] ' . $event . "\n" . print_r($data, true)
    );
};

$debug = array(
    'snippet' => 'trainingManageCertificatesPage.php',
    'context' => $contextKey,
    'actor_user_id' => $actorUserId,
    'is_admin' => $isAdmin ? 'yes' : 'no',
    'auth_context' => ($actorUserId > 0 && $contextKey !== '' && $modx->user->isAuthenticated($contextKey)) ? 'yes' : 'no',
    'auth_web' => ($actorUserId > 0 && $modx->user->isAuthenticated('web')) ? 'yes' : 'no',
    'scope_debug' => $certScopeDebug ? 'yes' : 'no',
    'pageTpl_exists' => is_file(trainingCertificatesChunkPath($corePath, $pageTpl)) ? 'yes' : 'no',
    'itemTpl_exists' => is_file(trainingCertificatesChunkPath($corePath, $itemTpl)) ? 'yes' : 'no',
    'modalTpl_exists' => is_file(trainingCertificatesChunkPath($corePath, $modalTpl)) ? 'yes' : 'no',
    'userSelectTpl_exists' => is_file(trainingCertificatesChunkPath($corePath, $userSelectTpl)) ? 'yes' : 'no',
);

if (!trainingCertificatesIsAuthenticated($modx)) {
    $certScopeLog('auth_failed', $debug);

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
$debug['certificates_count_total'] = $tables['certificates'] !== '' && trainingCertificatesTableExists($modx, $tables['certificates'])
    ? trainingCertificatesCount($modx, $tables['certificates'])
    : 0;
$debug['certificates_count_actor'] = $tables['certificates'] !== '' && trainingCertificatesTableExists($modx, $tables['certificates'])
    ? trainingCertificatesCount($modx, $tables['certificates'], '`user_id` = :user_id', array(':user_id' => $actorUserId))
    : 0;
$debug['courses_table'] = $tables['courses'];
$debug['courses_table_exists'] = $tables['courses'] !== '' && trainingCertificatesTableExists($modx, $tables['courses']) ? 'yes' : 'no';

$certScopeLog('start', array(
    'actor_user_id' => $actorUserId,
    'actor_name' => trainingCertificatesGetUserName($modx, $actorUserId),
    'is_admin' => $isAdmin,
    'tables' => $tables,
));

if ($tables['certificates'] === '' || !trainingCertificatesTableExists($modx, $tables['certificates'])) {
    $certScopeLog('certificates_table_not_found', $debug);

    return trainingCertificatesRenderChunk($corePath, $pageTpl, array(
        'page_title' => 'Управление сертификатами',
        'page_subtitle' => 'Мои сертификаты',
        'toolbar_html' => '',
        'items_html' => trainingCertificatesDebugHtml('CERT DEBUG: manage page', $debug) . '<div class="w-100">Таблица сертификатов не найдена</div>',
        'modal_html' => trainingCertificatesRenderChunk($corePath, $modalTpl, array()),
    ));
}

/*
 * Область пользователя:
 * - сам директор/менеджер;
 * - только его активные сотрудники из manager_link.
 *
 * allowed_course_ids оставляем для диагностики и существующей логики доступов,
 * но НЕ используем для вывода чужих сертификатов.
 */
$allowedCourseIds = array();
$allowedUserIds = array($actorUserId);
$managerLinkRows = array();

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
    $managerLinkRows = trainingCertificatesFetchAll(
        $modx,
        'SELECT ml.`manager_user_id`, ml.`employee_user_id`, ml.`is_active` '
        . 'FROM `' . $tables['manager_link'] . '` ml '
        . 'WHERE ml.`manager_user_id` = :actor '
        . 'ORDER BY ml.`is_active` DESC, ml.`employee_user_id` ASC',
        array(':actor' => $actorUserId),
        'trainingManageCertificatesPage:manager_link_rows'
    );

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

/*
 * training-certificates-director-scope-v1
 *
 * A user with active direct employees is treated as a team director:
 * their certificate scope is always the director plus those employees.
 * This is intentionally stronger than a false positive from isAdmin().
 */
$hasManagedEmployees = false;
foreach ($allowedUserIds as $scopeUserId) {
    if ((int)$scopeUserId !== $actorUserId) {
        $hasManagedEmployees = true;
        break;
    }
}

$useScopedUsers = !$isAdmin || $hasManagedEmployees;

$debug['has_managed_employees'] = $hasManagedEmployees ? 'yes' : 'no';
$debug['scope_mode'] = $useScopedUsers
    ? 'director_and_active_employees'
    : 'admin_all_rows';

$debug['allowed_course_ids'] = $allowedCourseIds;
$debug['allowed_user_ids'] = $allowedUserIds;
$debug['manager_link_rows'] = $managerLinkRows;

$certScopeLog('scope_resolved', array(
    'allowed_user_ids' => $allowedUserIds,
    'allowed_course_ids' => $allowedCourseIds,
    'has_managed_employees' => $hasManagedEmployees,
    'use_scoped_users' => $useScopedUsers,
    'manager_link_rows' => $managerLinkRows,
));

if (class_exists('TrainingCertificateService') && class_exists('Training')) {
    try {
        $service = new TrainingCertificateService($modx, new Training($modx));

        foreach ($allowedUserIds as $uid) {
            $service->ensureCertificatesForUser((int)$uid, false);
        }

        $certScopeLog('ensure_certificates_done', array(
            'user_ids' => $allowedUserIds,
        ));
    } catch (Exception $e) {
        $debug['ensure_error'] = $e->getMessage();

        $certScopeLog('ensure_certificates_error', array(
            'message' => $e->getMessage(),
            'user_ids' => $allowedUserIds,
        ));

        $modx->log(modX::LOG_LEVEL_ERROR, '[trainingManageCertificatesPage] ensure error: ' . $e->getMessage());
    }
}

/*
 * Главное исправление:
 * не-администратор выбирает сертификаты ТОЛЬКО своих user_id.
 *
 * Старый вариант:
 * cert.course_id IN (...) OR cert.user_id IN (...)
 * добавлял всех посторонних людей с сертификатом на том же курсе.
 */
$where = array();
$params = array();

if ($useScopedUsers) {
    if (!empty($allowedUserIds)) {
        $ph = array();

        foreach ($allowedUserIds as $i => $id) {
            $key = ':user_' . $i;
            $ph[] = $key;
            $params[$key] = (int)$id;
        }

        $where[] = 'cert.`user_id` IN (' . implode(',', $ph) . ')';
    } else {
        $where[] = '1 = 0';
    }
}

$sql = trainingCertificatesCertificateRowsSql($tables);

if (!empty($where)) {
    $sql .= 'WHERE (' . implode(' AND ', $where) . ') ';
}

$sql .= 'ORDER BY cert.`issuedon` DESC, cert.`id` DESC';

$certScopeLog('certificate_sql', array(
    'sql' => $sql,
    'params' => $params,
    'filter_mode' => $useScopedUsers ? 'only_director_and_active_employees' : 'admin_all_rows',
));

$allRows = trainingCertificatesFetchAll($modx, $sql, $params, 'trainingManageCertificatesPage:list');
$allRows = trainingCertificatesEnrichRows($modx, $allRows, $tables);

/*
 * Важно: fallback_all удалён.
 * Когда у директора/сотрудников ещё нет сертификатов, страница не должна
 * подгружать всех пользователей системы.
 */
$debug['fallback_all'] = 'disabled_for_scope_safety';

/*
 * select строится из области директора, а не из строк сертификатов.
 * Поэтому сотрудник без сертификата всё равно появляется в выборе.
 */
$userMap = array();
$certificateCounts = array();

if ($useScopedUsers && !empty($allowedUserIds)) {
    $countParams = array();
    $countPh = array();

    foreach ($allowedUserIds as $i => $id) {
        $key = ':count_user_' . $i;
        $countPh[] = $key;
        $countParams[$key] = (int)$id;
    }

    $countRows = trainingCertificatesFetchAll(
        $modx,
        'SELECT cert.`user_id`, COUNT(*) AS `certificate_count` '
        . 'FROM `' . $tables['certificates'] . '` cert '
        . 'WHERE cert.`user_id` IN (' . implode(',', $countPh) . ') '
        . 'GROUP BY cert.`user_id`',
        $countParams,
        'trainingManageCertificatesPage:certificate_counts'
    );

    foreach ($countRows as $countRow) {
        $uid = (int)(isset($countRow['user_id']) ? $countRow['user_id'] : 0);

        if ($uid > 0) {
            $certificateCounts[$uid] = (int)(isset($countRow['certificate_count']) ? $countRow['certificate_count'] : 0);
        }
    }

    foreach ($allowedUserIds as $uid) {
        $uid = (int)$uid;
        $name = trim((string)trainingCertificatesGetUserName($modx, $uid));

        if ($name === '') {
            $name = 'Пользователь #' . $uid;
        }

        $userMap[$uid] = $name;
    }
} else {
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

        if (!isset($certificateCounts[$uid])) {
            $certificateCounts[$uid] = 0;
        }

        $certificateCounts[$uid]++;
    }
}

$selectedUserId = array_key_exists('certificate_user', $_GET)
    ? (int)$modx->getOption('certificate_user', $_GET, 0)
    : 0;

if ($selectedUserId > 0 && !isset($userMap[$selectedUserId])) {
    $certScopeLog('selected_user_rejected', array(
        'selected_user_id' => $selectedUserId,
        'reason' => 'user_is_not_in_allowed_scope',
        'allowed_user_ids' => array_keys($userMap),
    ));

    $selectedUserId = 0;
}

$rows = $allRows;

if ($selectedUserId > 0) {
    $rows = array();

    foreach ($allRows as $row) {
        if ((int)$row['user_id'] === $selectedUserId) {
            $rows[] = $row;
        }
    }
}

$allRowsUserIds = array();
foreach ($allRows as $row) {
    $allRowsUserIds[] = (int)(isset($row['user_id']) ? $row['user_id'] : 0);
}
$allRowsUserIds = array_values(array_unique(array_filter($allRowsUserIds)));

$scopeUsersLog = array();
foreach ($userMap as $uid => $name) {
    $scopeUsersLog[] = array(
        'user_id' => (int)$uid,
        'name' => $name,
        'certificate_count' => isset($certificateCounts[$uid]) ? (int)$certificateCounts[$uid] : 0,
        'has_certificate' => !empty($certificateCounts[$uid]),
    );
}

$debug['all_rows_count'] = count($allRows);
$debug['all_rows_user_ids'] = $allRowsUserIds;
$debug['selector_user_ids'] = array_keys($userMap);
$debug['selected_user_id'] = $selectedUserId;
$debug['rows_count'] = count($rows);
$debug['certificate_counts'] = $certificateCounts;

$certScopeLog('result', array(
    'selector_users' => $scopeUsersLog,
    'all_rows_user_ids' => $allRowsUserIds,
    'selected_user_id' => $selectedUserId,
    'selected_rows_count' => count($rows),
));

$toolbarHtml = '';

if (!empty($userMap)) {
    asort($userMap, SORT_NATURAL | SORT_FLAG_CASE);

    $optionsHtml = '<option value="0"' . ($selectedUserId === 0 ? ' selected="selected"' : '') . '>Все пользователи</option>';

    foreach ($userMap as $id => $name) {
        $id = (int)$id;
        $certificateCount = isset($certificateCounts[$id]) ? (int)$certificateCounts[$id] : 0;
        $noCertificateSuffix = ($useScopedUsers && $certificateCount === 0) ? ' — нет сертификатов' : '';

        $optionsHtml .= '<option value="' . $id . '"' . ($id === $selectedUserId ? ' selected="selected"' : '') . '>'
            . trainingCertificatesEsc($name . $noCertificateSuffix)
            . '</option>';
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
    if ($selectedUserId > 0 && isset($userMap[$selectedUserId])) {
        $itemsHtml = '<div class="w-100">У пользователя '
            . trainingCertificatesEsc($userMap[$selectedUserId])
            . ' сертификатов пока нет</div>';
    } elseif ($useScopedUsers && !empty($userMap)) {
        $itemsHtml = '<div class="w-100">У вас и назначенных сотрудников сертификатов пока нет</div>';
    } else {
        $itemsHtml = '<div class="w-100">Сертификатов пока нет</div>';
    }
}

$debugHtml = trainingCertificatesDebugHtml('CERT DEBUG: manage page', $debug);

return trainingCertificatesRenderChunk($corePath, $pageTpl, array(
    'page_title' => 'Управление сертификатами',
    'page_subtitle' => 'Мои сертификаты',
    'toolbar_html' => $toolbarHtml,
    'items_html' => $debugHtml . $itemsHtml,
    'modal_html' => trainingCertificatesRenderChunk($corePath, $modalTpl, array()),
));

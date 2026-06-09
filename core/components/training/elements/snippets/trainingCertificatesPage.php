<?php
/** @var modX $modx */
/** @var array $scriptProperties */

$corePath = $modx->getOption('training.core_path', null, $modx->getOption('core_path') . 'components/training/');
require_once $corePath . 'elements/snippets/_certificateHelper.php';

$pageTpl = trim((string)$modx->getOption('pageTpl', $scriptProperties, 'training/certificates/page.tpl'));
$itemTpl = trim((string)$modx->getOption('itemTpl', $scriptProperties, 'training/certificates/item.tpl'));
$modalTpl = trim((string)$modx->getOption('modalTpl', $scriptProperties, 'training/certificates/modal.tpl'));

$contextKey = $modx->context ? (string)$modx->context->get('key') : '';
$userId = ($modx->user && (int)$modx->user->get('id')) ? (int)$modx->user->get('id') : 0;

$debug = array(
    'snippet' => 'trainingCertificatesPage.php',
    'context' => $contextKey,
    'user_id' => $userId,
    'auth_context' => ($userId > 0 && $contextKey !== '' && $modx->user->isAuthenticated($contextKey)) ? 'yes' : 'no',
    'auth_web' => ($userId > 0 && $modx->user->isAuthenticated('web')) ? 'yes' : 'no',
    'pageTpl_exists' => is_file(trainingCertificatesChunkPath($corePath, $pageTpl)) ? 'yes' : 'no',
    'itemTpl_exists' => is_file(trainingCertificatesChunkPath($corePath, $itemTpl)) ? 'yes' : 'no',
    'modalTpl_exists' => is_file(trainingCertificatesChunkPath($corePath, $modalTpl)) ? 'yes' : 'no',
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
);

$debug['certificates_table'] = $tables['certificates'];
$debug['certificates_table_exists'] = $tables['certificates'] !== '' && trainingCertificatesTableExists($modx, $tables['certificates']) ? 'yes' : 'no';
$debug['courses_table'] = $tables['courses'];
$debug['courses_table_exists'] = $tables['courses'] !== '' && trainingCertificatesTableExists($modx, $tables['courses']) ? 'yes' : 'no';
$debug['certificates_count_for_user'] = $tables['certificates'] !== '' ? trainingCertificatesCount($modx, $tables['certificates'], '`user_id` = :user_id', array(':user_id' => $userId)) : 0;

if (class_exists('TrainingCertificateService') && class_exists('Training')) {
    try {
        $service = new TrainingCertificateService($modx, new Training($modx));
        $generated = $service->ensureCertificatesForUser($userId, false);
        $debug['ensure_count'] = is_array($generated) ? count($generated) : 0;
    } catch (Exception $e) {
        $debug['ensure_error'] = $e->getMessage();
        $modx->log(modX::LOG_LEVEL_ERROR, '[trainingCertificatesPage] ensure error: ' . $e->getMessage());
    }
} else {
    $debug['service_loaded'] = 'no';
}

$rows = array();
if ($tables['certificates'] !== '' && trainingCertificatesTableExists($modx, $tables['certificates'])) {
    $sql = trainingCertificatesCertificateRowsSql($tables)
        . 'WHERE cert.`user_id` = :user_id '
        . 'ORDER BY cert.`issuedon` DESC, cert.`id` DESC';

    $rows = trainingCertificatesFetchAll($modx, $sql, array(':user_id' => $userId), 'trainingCertificatesPage');
}

$rows = trainingCertificatesEnrichRows($modx, $rows, $tables);
$debug['rows_count'] = count($rows);

$itemsHtml = '';
foreach ($rows as $row) {
    $itemsHtml .= trainingCertificatesBuildItemHtml($corePath, $itemTpl, $row, false);
}

if ($itemsHtml === '') {
    $itemsHtml = '<div class="w-100">Сертификатов пока нет</div>';
}

$debugHtml = trainingCertificatesDebugHtml('CERT DEBUG: personal page', $debug);

return trainingCertificatesRenderChunk($corePath, $pageTpl, array(
    'page_title' => 'Мои Сертификаты',
    'page_subtitle' => '',
    'toolbar_html' => '',
    'items_html' => $debugHtml . $itemsHtml,
    'modal_html' => trainingCertificatesRenderChunk($corePath, $modalTpl, array()),
));

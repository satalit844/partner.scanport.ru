<?php
/** @var modX $modx */
/** @var array $scriptProperties */

$corePath = $modx->getOption('training.core_path', null, $modx->getOption('core_path') . 'components/training/');
require_once $corePath . 'elements/snippets/_certificateHelper.php';

if (!$modx->user || !(int)$modx->user->get('id') || !$modx->user->isAuthenticated($modx->context->get('key'))) {
    return '';
}

$pageTpl = trim((string)$modx->getOption('pageTpl', $scriptProperties, 'training/certificates/page.tpl'));
$itemTpl = trim((string)$modx->getOption('itemTpl', $scriptProperties, 'training/certificates/item.tpl'));
$modalTpl = trim((string)$modx->getOption('modalTpl', $scriptProperties, 'training/certificates/modal.tpl'));

$userId = (int)$modx->user->get('id');
$service = new TrainingCertificateService($modx, new Training($modx));
$service->ensureCertificatesForUser($userId, false);
$rows = $service->listUserCertificates($userId);

$itemsHtml = '';
foreach ($rows as $row) {
    $itemsHtml .= trainingCertificatesRenderChunk($corePath, $itemTpl, array(
        'certificate_title' => trainingCertificatesEsc($row['course_title'] ?: $row['course_pagetitle']),
        'certificate_status' => trainingCertificatesEsc(!empty($row['issuedon']) ? 'Сертификат выдан' : 'Сертификат готов'),
        'certificate_preview' => trainingCertificatesEsc($row['preview_image'] ?: $row['template_preview']),
        'certificate_file' => trainingCertificatesEsc($row['file_path'] ?: $row['preview_image']),
        'certificate_issuedon' => !empty($row['issuedon']) ? trainingCertificatesEsc(date('d.m.Y', strtotime($row['issuedon']))) : '—',
    ));
}
if ($itemsHtml === '') {
    $itemsHtml = '<div class="w-100">Сертификатов пока нет</div>';
}

return trainingCertificatesRenderChunk($corePath, $pageTpl, array(
    'page_title' => 'Сертификаты',
    'page_subtitle' => 'Мои сертификаты',
    'toolbar_html' => '',
    'items_html' => $itemsHtml,
    'modal_html' => trainingCertificatesRenderChunk($corePath, $modalTpl, array()),
));
<?php
if (!$ajaxLogin = $modx->getService('ajaxlogin', 'ajaxLogin', $modx->getOption('ajaxlogin_core_path', null,
        $modx->getOption('core_path') . 'components/ajaxlogin/') . 'model/ajaxlogin/', $scriptProperties)
) {
    return '';
}

$tplAjax = $modx->getOption('tplAjax', $scriptProperties, 'ajaxLoginTpl');
$tplModal = $modx->getOption('tplModal', $scriptProperties, 'ajaxLoginModalTpl');
$loginTpl = $modx->getOption('loginTpl', $scriptProperties, 'ajaxLoginFormTpl');
$errTpl = $modx->getOption('errTpl', $scriptProperties, 'ajaxLoginErrTpl');
$tpl = $modx->getOption('tpl', $scriptProperties, 'ajaxLoginForgotFormTpl');
$sentTpl = $modx->getOption('sentTpl', $scriptProperties, 'ajaxLoginForgotPassSentTpl');
$emailTpl = $modx->getOption('emailTpl', $scriptProperties, 'ajaxLoginForgotPassEmailTpl');
$activationEmailTpl = $modx->getOption('activationEmailTpl', $scriptProperties, 'ajaxLoginActivateEmailTpl');
$registerTpl = $modx->getOption('registerTpl', $scriptProperties, 'ajaxLoginRegisterFormTpl');
$frontendJs = trim($modx->getOption('frontendJs', $scriptProperties, 'components/ajaxlogin/js/web/ajaxlogin.js'));
$frontendCss = trim($modx->getOption('frontendCss', $scriptProperties, 'components/ajaxlogin/css/web/ajaxlogin.css'));
$logoutResourceId = $modx->getOption('logoutResourceId', $scriptProperties, '');
$tplType = $modx->getOption('tplType', $scriptProperties, 'embedded');
$resetResourceId = $modx->getOption('resetResourceId', $scriptProperties, '');
$activationResourceId = $modx->getOption('activationResourceId', $scriptProperties, '');
$loginResourceId = $modx->getOption('loginResourceId', $scriptProperties, '');
$submittedResourceId = $modx->getOption('submittedResourceId', $scriptProperties, '');

if (empty($logoutResourceId)) {
    $scriptProperties['logoutResourceId'] = $modx->resource->id;
}
if (empty($resetResourceId)) {
    $modx->log(modX::LOG_LEVEL_ERROR, $modx->lexicon('ajaxlogin_err_not_parameter') . ' &resetResourceId');
}
if (empty($loginResourceId)) {
    $scriptProperties['loginResourceId'] = $modx->resource->id;
}
if (!empty($submittedResourceId)) {
    $submittedResourceId = $modx->makeUrl($submittedResourceId);
}

$redirectLoginResId = $modx->makeUrl($scriptProperties['loginResourceId']);
$chunk = '';
$output = '';

$output = $ajaxLogin->process('Login', $scriptProperties);

if ($pdoTools = $modx->getService('pdoTools')) {
    $chunk = $pdoTools->getChunk($tplAjax);
    $modal = $pdoTools->getChunk($tplModal);
} else {
    $chunk = $ajaxLogin->getChunk($tplAjax);
    $modal = $ajaxLogin->getChunk($tplModal);
}

// разбить чанк по сепаратору
if (stripos($chunk, '<!--ajaxLogin-->')) {
    $tmp = explode('<!--ajaxLogin-->', $chunk);

    if ($modx->user->isAuthenticated($modx->context->key)) {

        return $tmp[1];
    } else {
        $output .= $tmp[0];
        $modx->regClientHTMLBlock($modal);
    }
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, $modx->lexicon('ajaxlogin_err_separator') . $tplAjax);

    return false;
}

if (!isset($_SESSION['ajaxLogin'])) {
    $_SESSION['ajaxLogin'] = [];
}

if ($_SESSION['ajaxLogin'] !== $scriptProperties) {
    $_SESSION['ajaxLogin'] = $scriptProperties;
}

if (!empty($frontendJs)) {
    $modx->regClientScript(MODX_ASSETS_URL . $frontendJs);
}
if (!empty($frontendCss)) {
    $modx->regClientCSS(MODX_ASSETS_URL . $frontendCss);
}
$modx->regClientHTMLBlock('<script>AjaxLogin.initialize({ 
    "actionUrl":"' . $ajaxLogin->config['actionUrl'] . '",
    "loading":"' . $ajaxLogin->config['loading'] . '",
    "redirectLoginResId":"' . $redirectLoginResId . '",
    "redirectSubmitResId":"' . $submittedResourceId . '",
    "ctx":"' . $modx->context->key . '"});
</script>');

return $output;
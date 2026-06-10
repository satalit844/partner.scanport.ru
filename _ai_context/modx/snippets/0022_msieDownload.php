<?php
/**
 * msieDownload
 * @package msimportexport
 *
 */

/**
 * @var modX $modx
 * @var array $scriptProperties
 * @var Msie $msie
 * @var MsIeTools $tools
 * @var modResource $resource
 * @var int $rid
 * @var string $tpl
 * @var string $caption
 */

$msie = $modx->getService('msimportexport', 'Msie');
$tools = $msie->getTools();
$resource = $modx->resource;
$savedProperties = array();

if ($usergroup) {
    $usergroup = $tools->explodeAndClean($usergroup);
    if (!$modx->user->isMember($usergroup)) return;
}

if (empty($preset)) return 'Parameter "preset" not set!';

if (!$modx->getObject('MsiePreset', $preset)) {
    return 'Preset not find!';
}

if ($rid) {
    if (!$resource = $modx->getObject('modResource', $rid)) {
        $resource = $modx->resource;
    }
}

$ip = $tools->getClientIp();

$scriptProperties['time'] = time();
$scriptProperties['ip'] = ip2long($ip['ip']);
$scriptProperties['caption'] = $tools->getPdoTools()->getChunk('@INLINE ' . $caption, $resource->toArray());
$scriptProperties['sing'] = $tools->generateSign($scriptProperties);

$modx->loadClass('MsieTask');
$key = sha1(serialize($scriptProperties));
$_SESSION[$msie->sessionDownloadKey][$key] = $scriptProperties;
$config = array(
    'url' => $msie->getDoUrl(),
    'statuses' => MsieTask::getStatuses(),
    'loading_text' => $modx->lexicon('msimportexport_do_loading_text'),
);

if (!empty($css)) {
    $modx->regClientCSS($tools->preparePath($css));
}

if (!empty($js)) {
    $modx->regClientScript($tools->preparePath($js));
}

$modx->regClientScript('
<script type="text/javascript">
   (function ($) {
        new MsIeDownload(' . $modx->toJSON($config) . ');
   })(jQuery);
 </script>', true);

return $tools->getPdoTools()->getChunk($tpl, array(
    'caption' => $scriptProperties['caption'],
    'key' => $key,
));
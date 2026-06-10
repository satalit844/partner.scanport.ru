<?php
/**
 * @var modX $modx
 * @var Msie $msie
 * @var MsIeService $service
 * @var array $scriptProperties
 * @var string $serviceName
 * @var bool $checking
 */

switch ($modx->event->name) {
    case 'msieOnLoadServices':
        $modx->event->output($msie->getTools()->getServices($mode));
        break;
    case 'OnBeforeEmptyTrash':
        return;
        if (empty($ids) || !is_array($ids)) return;
        $msie = $modx->getService('msimportexport', 'Msie', $modx->getOption('msimportexport.core_path', null, $modx->getOption('core_path') . 'components/msimportexport/') . 'model/msimportexport/', array());
        $msie->removeСategoryFromPresets($ids);

        $parents = $modx->getOption('msimportexport.export.parents', null, '');
        if (!$parents = array_map('trim', explode(',', $parents))) return;
        $parents = array_diff($parents, $ids);
        $msie->setOption('msimportexport.export.parents', implode(',', $parents), true);
        break;
}
return;
<?php
/** @var modX $modx */

$corePath = $modx->getOption(
    'training.core_path',
    null,
    $modx->getOption('core_path') . 'components/training/'
);

$modelPath = $corePath . 'model/';

if (is_dir($modelPath . 'training/')) {
    $modx->addPackage('training', $modelPath);
}
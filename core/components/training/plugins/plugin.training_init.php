<?php
/** @var modX $modx */

switch ($modx->event->name) {
    case 'OnMODXInit':
        $corePath = $modx->getOption(
            'training.core_path',
            null,
            $modx->getOption('core_path') . 'components/training/'
        );

        $modelPath = $corePath . 'model/';

        if (!$modx->getMap('TrainingCourse')) {
            $modx->addPackage('training', $modelPath);
        }
        break;
}
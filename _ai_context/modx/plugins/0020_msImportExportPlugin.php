<?php
switch ($modx->event->name) {
    case 'msieOnBeforeImport':
        $data['parent'] = 16;
        $data['template'] = 9;
        $modx->event->returnedValues['data'] = $data;
        break;
        
    case 'msieOnGetServiceFields':
        
        break;
}
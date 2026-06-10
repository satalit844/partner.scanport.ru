<?php
switch ($modx->event->name) {
    case "OnLoadWebDocument":
        $modx->log(1, '[OnLoadWebDocument-end_date- $resource] '. print_r($modx->resource->id,1));
        $modx->log(1, '[OnLoadWebDocument-end_date- time()] '. print_r(time(),1));
        $modx->log(1, '[OnLoadWebDocument-end_date- $date] '. print_r(strtotime($date),1));
        if ($modx->resource->id == 119) {
            $profile = $modx->getObject('modUserProfile', ['internalKey' => $modx->user->id]);
            if (!empty($profile)) {
                $date = $profile->get('rebeit');
                if (time() > strtotime($date)) {
                        $profile->set('field_summ', 0);
                        $profile->save();
                        $modx->log(1, '[end_date field_summ] '. print_r($profile->get('field_summ'),1));
                    
                }
            }
        }
        break;
}
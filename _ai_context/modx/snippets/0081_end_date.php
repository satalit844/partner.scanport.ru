<?php
if (time() > strtotime($date)) {
    $profile = $modx->getObject('modUserProfile', ['internalKey' => $modx->user->id]);
    if (!empty($profile)) {
        $profile->set('field_summ', 0);
        $profile->save();
        return $profile->get('field_summ');
    }
} else {
    return $field_summ;
}
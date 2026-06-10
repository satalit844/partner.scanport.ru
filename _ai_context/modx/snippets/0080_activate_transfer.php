<?php
$user = $modx->getObject('modUser', ['username' => $_GET['email']]);
if (is_object($user)) {
    $profile = $user->getOne('Profile');
    $extended = $profile->get('extended');
    if ($extended['office_activation_key'] == $_GET['hash']) {
        $modx->user = $user;
        $user->set('active', 1);
        unset($extended['office_activation_key']);
        $profile->set('extended', $extended);
        $profile->save();
        $user->save();
        $modx->user->addSessionContext('web');
        $url = $modx->makeUrl(1);
        $modx->sendRedirect($url);
    }
}
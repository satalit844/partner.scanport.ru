<?php
switch ($modx->event->name) {
    case "OnBeforeWebLogin":
        $check = 0;
        if ($_SESSION['user_custom']['Auth']['remember_me'] == 'true') {
            $check = 1;
        } 
        unset($_SESSION['user_custom']['Auth']['remember_me']);
        $_SESSION['Office']['Auth'][$attributes['loginContext']]['rememberme'] = $check;
        break;
}
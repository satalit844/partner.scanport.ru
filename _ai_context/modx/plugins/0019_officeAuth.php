<?php
/** @var modX $modx */
switch ($modx->event->name) {
    case 'OnHandleRequest':
        $actions = array('transfer');
        if (!empty($_REQUEST['action']) && in_array(rawurldecode($_REQUEST['action']), $actions)) {
            $user = $modx->getObject('modUser', ['username' => $_REQUEST['email']]);
            if (!empty($user)) {
                $profile = $user->getOne('Profile');
                $extended = $profile->get('extended');
                if ($_REQUEST['hash'] == $extended['office_activation_key']) {
                    unset($extended['office_activation_key']);
                    $profile->set('extended', $extended);
                    $user->set('active', 1);
                    $chars = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP'; 
            	    $size = strlen($chars) - 1; 
            	    $password = ''; 
                	$length = 8;
                	while($length--) {
                		$password .= $chars[random_int(0, $size)]; 
                	}
            	    
            	    $user->set('password', $password);
            	    $profile->save();
                    $user->save();
                    $logindata = array(
                        'username' => $_REQUEST['email'],
                        'password' => $password,
                        'rememberme' => true
                    );
                    $response = $modx->runProcessor('/security/login', $logindata);
                    if($response->isError()) {
                        $modx->log(1, '[officeAuth getMessage]'. print_r($response->getMessage(),1));
                    } else {
                        /*Отправка сообщения пользователю*/
                        $modx->getService('mail', 'mail.modPHPMailer');
                        $modx->mail->set(modMail::MAIL_FROM, $modx->getOption('emailsender'));
                        $modx->mail->set(modMail::MAIL_FROM_NAME, $modx->getOption('site_name'));
                        
                        $userEmail = $profile->get('email');
                        $userName = $profile->get('fullname');
                        
                        $modx->mail->address('to', $userEmail);
                        $modx->mail->set(modMail::MAIL_SUBJECT, 'Ваш аккаунт в партнёрском разделе Сканпорт подтверждён');
                        $modx->mail->set(modMail::MAIL_BODY, $modx->getChunk('tpl.user.activate-transfer', array(
                            'email' => $userEmail,
                            'password' => $password,
                            'name' => $userName,
                        )));
                        $modx->mail->setHTML(true);
                        if (!$modx->mail->send()) {
                            $modx->log(modX::LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: '.$modx->mail->mailer->ErrorInfo);
                        }
                        $modx->mail->reset();
                        
                        /*Отправка сообщения 1с*/
                        $modx->getService('mail', 'mail.modPHPMailer');
                        $modx->mail->set(modMail::MAIL_FROM, $modx->getOption('emailsender'));
                        $modx->mail->set(modMail::MAIL_FROM_NAME, $modx->getOption('site_name'));
                        
                        $modx->mail->address('to', 'lic@data-mobile.ru');
                        $modx->mail->set(modMail::MAIL_SUBJECT, 'Перевод пользователя в новый партнерский ЛК');
                        $modx->mail->set(modMail::MAIL_BODY, $userEmail);
                        $modx->mail->setHTML(false);
                        if (!$modx->mail->send()) {
                            $modx->log(modX::LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: '.$modx->mail->mailer->ErrorInfo);
                        }
                        $modx->mail->reset();
                    }
                    
                }
            }
            
        }
        
        $actions = array('auth/login', 'auth/logout', 'remote/login', 'remote/logout', 'auth/change');
        if (!empty($_REQUEST['action']) && in_array(rawurldecode($_REQUEST['action']), $actions)) {
            $params = array();
            foreach ($_REQUEST as $k => $v) {
                $params[$k] = rawurldecode($v);
            }

            list($controller, $action) = explode('/', $params['action']);
            $cfg = !empty($_SESSION['Office'][ucfirst($controller)][$modx->context->key])
                ? $_SESSION['Office'][ucfirst($controller)][$modx->context->key]
                : array();

            /** @var Office $Office */
            $Office = $modx->getService('office', 'Office', MODX_CORE_PATH . 'components/office/model/office/', $cfg);
            if ($Office) {
                $Office->loadAction($params['action'], array_merge($cfg, $params));
            }
        } elseif ($modx->context->key != 'web' && !$modx->user->id) {
            if ($user = $modx->getAuthenticatedUser($modx->context->key)) {
                $modx->user = $user;
                $modx->getUser($modx->context->key);
            }
        }

        if (!empty($_SESSION['Office']['ReturnTo'][$modx->context->key]) && $modx->user->isAuthenticated($modx->context->key)) {
            $return = $_SESSION['Office']['ReturnTo'][$modx->context->key];
            unset($_SESSION['Office']['ReturnTo'][$modx->context->key]);
            $modx->sendRedirect($return);
        }
        break;

    case 'OnWebAuthentication':
        $modx->event->_output = !empty($_SESSION['Office']['Auth']['verified']);
        break;

    case 'OnUserSave':
        if (!empty($user) && !empty($mode) && $mode == 'new') {
            if (!$user->get('remote_key')) {
                $user->set('remote_key', $user->get('id'));
                $user->save();
            }
        }
        break;
}
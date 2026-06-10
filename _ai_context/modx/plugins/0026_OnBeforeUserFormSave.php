<?php
if ($modx->context->key != 'mgr') {
    
    switch ($modx->event->name) {
        case 'OnUserFormSave':
            if ($mode == 'new') {
                $profile = $user->getOne('Profile');
                $email_manager = $modx->getOption('email_reg');
                
                $modx->getService('mail', 'mail.modPHPMailer');
                $modx->mail->set(modMail::MAIL_FROM, $modx->getOption('emailsender'));
                $modx->mail->set(modMail::MAIL_FROM_NAME, $modx->getOption('site_name'));
               
                $userId = $profile->get('internalKey');
                $userEmail = $profile->get('email');
                $userName = $profile->get('fullname');
                $company = $profile->get('field_list_company');
                $region = $_POST['state'];
                $profile->set('state', $region);
                $profile->save();
                $phone = $profile->get('mobilephone');
                
                $email_addresses = explode(',', $email_manager);
                
                foreach ($email_addresses as $email) {
                    $modx->mail->address('to', trim($email));
                }
                $modx->mail->set(modMail::MAIL_SUBJECT, 'Зарегистрирован новый пользователь');
                $modx->mail->set(modMail::MAIL_BODY, $modx->getChunk('tpl.user.manager',array(
                    'id'=>$userId,
                    'email' => $userEmail,
                    'phone' => $phone,
                    'name' => $userName,
                    'company' => $company,
                    'region' => $region,
                    'inn' => $_POST['inn_company'],
                )));
        
                $modx->mail->setHTML(true);
                if (!$modx->mail->send()) {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: '.$modx->mail->mailer->ErrorInfo);
                }
                $modx->mail->reset();
            }
            if ($mode == 'upd') {
                if ($_POST['action'] == 'Profile/Update') {
                    
                    $emails = $_POST['emails'];
                    $check_all = ['webinar','partner_events','news','software_updates'];
                    
                    $webinar = explode(';', $user->get('webinar'));
                    $partner_events = explode(';',$user->get('partner_events'));
                    $news = explode(';',$user->get('news'));
                    $software_updates = explode(';',$user->get('software_updates'));
                    
                    $personal_data = $_POST['personal_data'] ? 1 : 0;
                    $user->set('personal_data', $personal_data);
                    $webinar_arr = [];
                    $partner_events_arr = [];
                    $news_arr = [];
                    $software_updates_arr = [];
                    $data = [];
                    $data_check = [];
                    foreach ($emails as $key => $item) {
                        foreach ($check_all as $check) {
                            $check_tmp = $_POST[$check][$key] ? '1' : '0';
                            $data[$check][] =  $item .':'. $check_tmp;
                            if ($check_tmp == 1) {
                                $data_check[$check][] = $item;
                            }
                        }
                    }
                    foreach ($data as $key => $item) {
                        $user->set($key, implode(';', $item));
                    }
                    $data_unisender = array(
                        'api_key' => '59e7i3mqpsmbiscci9pi68b1q77bff9kytjk4nba',
                        'tags' => 'Личный кабинет',
                        'double_optin' => 0,
                        'overwrite' => 0,
                    ); 
                    $list_ids_array = ['webinar'=> '20651181', 'partner_events' => '20651182', 'news' => '20651183', 'software_updates' => '20651184'];
                    
                    $list_ids_up = [];
                    $list_ids_down = [];
                    
                    if (count($webinar) > count($_POST['emails'])) {
                        $webinar_cleaned = array_map(function($item) {
                            $colon_position = strpos($item, ':');
                            if ($colon_position !== false) {
                                return substr($item, 0, $colon_position);
                            } else {
                                return $item;
                            }
                        }, $webinar);
                        $item = $webinar_cleaned;
                    } else {
                        $item = $_POST['emails'];
                    }
                    $data_old = [];
                    foreach ($item as $key => $item) {
                        $webinar[$key] = explode(':', $webinar[$key]);
                        $partner_events[$key] = explode(':', $partner_events[$key]);
                        $news[$key] = explode(':', $news[$key]);
                        $software_updates[$key] = explode(':', $software_updates[$key]);
                        
                        $data_old[$item]['webinar'] = $webinar[$key][1];
                        $data_old[$item]['partner_events'] = $partner_events[$key][1];
                        $data_old[$item]['news'] = $news[$key][1];
                        $data_old[$item]['software_updates'] = $software_updates[$key][1];
                    }
                    foreach ($data_check as $event => $emails) {
                        foreach ($emails as $email) {
                            if (!isset($data_old[$email]) || $data_old[$email][$event] == 0) {
                                $list_ids_up[$email][] = $list_ids_array[$event];
                            }
                        }
                    }
                    $all_new_emails = [];
                    foreach ($data_check as $emails) {
                        $all_new_emails = array_merge($all_new_emails, $emails);
                    }
                    $all_new_emails = array_unique($all_new_emails);
                    foreach ($data_old as $email => $subscriptions) {
                        if (!in_array($email, $all_new_emails)) {
                            $list_ids_down[$email] = array_values($list_ids_array);
                        }
                    }
                    if (!empty($list_ids_up)) {
                        $method = 'subscribe';
                        foreach ($list_ids_up as $key => $item) {
                            $subscribe = array (
                                'api_key' => $data_unisender['api_key'],
                                'double_optin' => $data_unisender['double_optin'],
                                'list_ids' => implode(',', $item),
                                'tags' => $data_unisender['tags'],
                                'fields[email]' => $key,
                                'overwrite' => $data_unisender['overwrite'],
                            );
                            
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $subscribe);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                            curl_setopt($ch, CURLOPT_URL,
                                        'https://api.unisender.com/ru/api/'. $method .'?format=json');
                            $result = curl_exec($ch);
                            $result = json_decode($result, true);
                            
                            if (!empty($result['error'])) {
                                $output = array(
                                    'success' => false,
                                    'message' => $result['error'],
                                );
                            } else {
                                $output = array(
                                    'success' => true,
                                );
                            }
                        }
                    }
                    if (!empty($list_ids_down)) {
                        $method = 'exclude';
                        foreach ($list_ids_down as $key => $item) {
                            $subscribe = array (
                                'api_key' => $data_unisender['api_key'],
                                'contact_type' => 'email',
                                'list_ids' => implode(',', $item),
                                'contact' => $key,
                            );
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $subscribe);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                            curl_setopt($ch, CURLOPT_URL,
                                        'https://api.unisender.com/ru/api/'. $method .'?format=json');
                            $result = curl_exec($ch);
                            $result = json_decode($result, true);
                            
                            $modx->log(1,'[ajax $result] '. print_r($result,1));
                            
                            if (!empty($result['error'])) {
                                $output = array(
                                    'success' => false,
                                    'message' => $result['error'],
                                );
                            } else {
                                $output = array(
                                    'success' => true,
                                );
                            }
                        }
                    }
                    if ($output['success']) {
                        $modx->log(1,'[OnUserFormSave POST] '. print_r($_POST,1));
                        $user->save();
                    }
                }
            }
            break;
        case 'OnBeforeUserFormSave':
            if ($mode == 'upd') {
                if ($_POST['action'] == 'Profile/Update') {
                    if ($_POST['specifiedpassword'] != $_POST['confirmpassword']) {
                        $modx->event->output('Пароль и Подтверждение пароля не совпадают');
                    } else {
                        if (!empty($_POST['specifiedpassword'])) {
                            $user->set('password', $_POST['specifiedpassword']);
                            $user->save();
                        }
                    }
                }
                $phone = $_POST['mobilephone'];
                $digitsWithPlus = preg_replace('/(?!^\+)[^\d]/', '', $phone);
                if (strpos($phone, '+') === 0) {
                    $digitsWithPlus = '+' . ltrim($digitsWithPlus, '+');
                }
                $user->Profile->set('mobilephone', $digitsWithPlus);
                $user->Profile->save();
            }
            if ($mode == 'new') {
                $required = ['fullname', 'field_list_company', 'mobilephone', 'email','inn_company','region','password', 're-password'];
                $required_fields = ['fullname' => 'Имя, фамилия', 'field_list_company' => 'Компания', 'mobilephone' => 'Телефон', 'email' => 'Электронная почта', 'password' => 'Пароль', 're-password' => 'Подтверждение пароля', 'inn_company' => 'ИНН компании','state' => 'Регион компании'];
                foreach ($_POST as $key => $field) {
                    if (in_array($key, $required)) {
                        if (empty($field)) {
                            $modx->event->output('Не указано поле: '. $required_fields[$key] . '<br/>');
                        }
                    }
                }
                if (!isset($_POST['inn_company']) || !preg_match('/^\d+$/', $_POST['inn_company'])) {
                    $modx->event->output('Поле ИНН компании должно содержать только цифры.');
                }
                if ($_POST['password'] != $_POST['re-password']) {
                    $modx->event->output('Пароль и Подтверждение пароля не совпадают');
                }
                $user->Profile->set('field_list_company', $_POST['field_list_company']);
            }
            break;
    }
}
<?php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {return;}
if (empty($_POST['action'])) return;

$modx->log(1,'[ajax $_POST] '. print_r($_POST,1));

switch ($_POST['action']) {
    case 'check-modal-seen':
        if (isset($_SESSION['modal_seen']['check']) && $_SESSION['modal_seen']['time'] > time()) {
            $seen = true;
        } else {
            $seen = false;
            unset($_SESSION['modal_seen']);
        }
        
        $modx->log(1,'[ajax $seen] '. print_r($seen,1));
        return json_encode(['seen' => $seen]);
        break;
    case 'message':
        $_SESSION['modal_seen']['check'] = true;
        $_SESSION['modal_seen']['time'] = time() + 86400;
        
        return json_encode(['status' => 'success']);
        break;
    case 'license':
        $name = str_replace('---', '->', $_POST['value']);
        $res = $modx->getObject('modResource', 16);
        $array_modules = json_decode($res->getTVValue('price_module'), true);
        $stringResult = [];
        foreach ($_POST['product_name'] as $item) {
            if ($item != '—') {
                $stringResult[] = $item; 
            }
        }
        $stringResult = implode(', ', $stringResult);
        
        $modules = [];
        
        foreach ($array_modules as $item) {
            $modules[$item['first_module']] = explode(';', $item['second_module']);
        }
        
        $modx->log(1,'[ajax $modules] '. print_r($modules,1));
        $modx->log(1,'[ajax $name] '. print_r($name,1));
        
        if (array_key_exists($name, $modules)) {
            $mid = [];
            foreach ($modules[$name] as $item) {
                $mod = $modx->getObject('msopModification', ['name' => $item, 'active' => 1]);
                if (is_object($mod)) {
                    $id = $mod->get('rid');
                    $mid[] = $mod->get('id');
                }
            }
            $output = array(
                'success' => true,
                'id' => $id,
                'mid' => implode(',', $mid),
                'product_name' => $stringResult
            );
        } else {
            $mod = $modx->getObject('msopModification', ['name' => $name, 'active' => 1]);
            if (is_object($mod)) {
                $id = $mod->get('rid');
                $output = array(
                    'success' => true,
                    'id' => $id,
                    'mid' => $mod->get('id'),
                    'product_name' => $stringResult
                );
            }
        }
        
        $modx->log(1,'[ajax $output] '. print_r($output,1));
        
        return json_encode($output);
        break;
    case 'selected':
        $pdo = $modx->getService('pdoTools');
        $pdo->addTime('pdoTools loaded');
        $tvId = 8;
        $resourceId = 16;
        $key = $_POST['index'] + 1;
        
        $tvr = $modx->getObject('modTemplateVarResource', array(
            'tmplvarid' => $tvId,
            'contentid' => $resourceId
        ));
        if ($tvr) {
            $software_list =  $tvr->get('value');
        } else {
            $tv = $modx->getObject('modTemplateVar', $tvId);
            if ($tv) $software_list =  $tv->get('default_text');
        }
        
        switch ($_POST['index']) {
            case 0:case 1:case 2:
                $array_ids = ['po' => 'ver', 'ver' => 'modul', 'modul' => 'lic'];
                $software_list = json_decode($software_list, true);
                $software_list = $software_list[$key];
                $id_select = $software_list['$software_list'];
                $fields = json_decode($software_list['fields'], true);
                $fields_array = [];
                $i = 0;
                foreach ($fields as $item) {
                    if ($item['parent_field'] == $_POST['value']) {
                        $fields_array[$i]['name_field'] = $item['name_field'];
                        $fields_array[$i]['id_field'] = $item['id_field'];
                        $fields_array[$i]['parent_field'] = $item['parent_field'];
                        $i = $i + 1;
                    }
                }
                $tpl = 'tpl.options.lic';
                $res = '<option value=""></option>';
                
                $modx->log(1, '[ajax $fields_array] '. print_r($fields_array,1));
                // $fields_array = array_unique($fields_array);
                foreach ($fields_array as $option) {
                    $pls = array('name' => $option['name_field'], 'value' => $option['id_field'], 'parent' => $option['parent_field']);
                    if (count($fields_array) > 1) {
                        $res .= $pdo->getChunk($tpl, $pls);
                    } else {
                        $res = $pdo->getChunk($tpl, $pls);
                    }
                }
            break;
            default:
                
                break;
        }
        $modx->log(1, '[ajax $res] '. print_r($res,1));
        
        return json_encode(array(
            'success' => true,
            'id' => $array_ids[$_POST['name']],
            'options' => $res,
        ));
        break;
    case 'collapsedmenu':
        $collapsed = $_POST['collapsed'];
        $modx->log(1, '[collapsedmenu $collapsed] '. print_r($collapsed,1));
        if ($collapsed === 'false') {
            $collapsed = 1;
            $_SESSION['user_custom']['menu_collapsed'] = 1;
        } else {
            $collapsed = 0;
            $_SESSION['user_custom']['menu_collapsed'] = 0;
        }
        return json_encode(array(
            'success' => true,
            'collapsed' => $collapsed,
        ));
        break;
    case 'rememberMeUser':
        $_SESSION['user_custom']['Auth']['remember_me'] = $_POST['check'];
        return json_encode(array(
            'success' => true,
        ));
        break;
    case 'subscription':
        $user = $modx->getObject('modUser', ['id' => $modx->user->id]);
        if (!empty($user)) {
            $checked = $_POST;
            if (!isset($_POST['personal_data'])) {
                $output = array(
                    'success' => false,
                    'message' => 'Требуется согласие на обработку персональных данных!'
                );
                return json_encode($output);
            }
            
            $data = array(
                'api_key' => '59e7i3mqpsmbiscci9pi68b1q77bff9kytjk4nba', // Ваш ключ с личного кабинета unisender
                'tags' => 'Личный кабинет', // Метка для пользователя
                'double_optin' => 3, // аргумент взятый с https://www.unisender.com/ru/support/integration/api/subscribe
                'overload' => 0, // аргумент взятый с https://www.unisender.com/ru/support/integration/api/subscribe
                'email' => $_POST['email'],
            ); 
            unset($checked['action']);
            unset($checked['email']);
            $modx->log(1,'[ajax $data] '. print_r($data,1));
            
            $check_all = ['webinar','partner_events','news','software_updates','personal_data'];
            $list_ids_array = ['webinar'=> '20651181', 'partner_events' => '20651182', 'news' => '20651183', 'software_updates' => '20651184'];
            foreach ($check_all as $item) {
                $user->set($item, 0);
            }
            $list_ids_up = [];
            $list_ids_down = [];
            foreach ($checked as $key => $check) {
                $user->set($key, $check ? 1 : 0);
                if ($check == 1) {
                    $list_ids_up[] = $list_ids_array[$key];
                }
            }
            
            $list_ids_up = array_filter($list_ids_up);
            
            foreach ($list_ids_array as $key => $item) {
                if (!in_array($item, $list_ids_up)) {
                    $list_ids_down[] = $item;
                }
            }
            $list_ids_up = implode(',', $list_ids_up);
            $list_ids_down = implode(',', $list_ids_down);
            
            $modx->log(1,'[ajax $list_ids_up] '. print_r($list_ids_up,1));
            $modx->log(1,'[ajax $list_ids_down] '. print_r($list_ids_down,1));
            
            if (!empty($list_ids_up)) {
                $method = 'subscribe';
                $subscribe = array (
                    'api_key' => $data['api_key'],
                    'double_optin' => $data['double_optin'],
                    'list_ids' => $list_ids_up,
                    'tags' => $data['tags'],
                    'fields[email]' => $data['email'],
                    'fields[overload]' => $data['overload'],
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
            if (!empty($list_ids_down)) {
                $method = 'exclude';
                $subscribe = array (
                    'api_key' => $data['api_key'],
                    'contact_type' => 'email',
                    'list_ids' => $list_ids_down,
                    'contact' => $data['email'],
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
            $modx->log(1,'[ajax $output] '. print_r($output,1));
            if ($output['success']) {
                $user->save();
            }
            return json_encode($output);
        } 
        break;
}
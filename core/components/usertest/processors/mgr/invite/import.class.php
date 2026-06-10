<?php
class ImportInvitesConsoleProcessor extends modProcessor {

    public function process() {
        set_time_limit(3600);
        $this->modx->getService('lexicon','modLexicon');
        $this->modx->lexicon->load($this->modx->config['manager_language'].':usertest:default');

        $this->modx->addPackage('usertest', $this->modx->getOption('core_path') . 'components/usertest/model/');
        $loadEmailQueue = false;
        if($this->modx->getOption('usertest_use_emailqueue_for_invites', null, false)){
            if ($EmailQueue = $this->modx->getService('emailqueue', 'EmailQueue', $this->modx->getOption('emailqueue_core_path', null,
                    $this->modx->getOption('core_path') . 'components/emailqueue/') . 'model/emailqueue/', array())) {
                $loadEmailQueue = true;
            }
        }
        if ((include MODX_ASSETS_PATH.'components/usertest/PHPExcel/IOFactory.php') != TRUE) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'Не удалось загрузить PHPExcel!');
            $this->modx->log(modX::LOG_LEVEL_INFO,'COMPLETED');
            sleep(1);
            return $this->modx->error->success();
        }
        $excel_file = MODX_BASE_PATH.$_POST['excel_file'];
        $test_id = $_POST['test_id'];
        $test_page_id = $_POST['test_page_id'];
        $auth_page_id = $_POST['auth_page_id'];
        $url_scheme = $_POST['url_scheme'];
        $date_expired = $_POST['date_expired'];
        if(isset($_POST['send_email_if_empty_test'])){
            $send_email_if_empty_test = filter_var($_POST['send_email_if_empty_test'], FILTER_VALIDATE_BOOLEAN);;
        }else{
            $send_email_if_empty_test = false;
        }
        $inputFileType = PHPExcel_IOFactory::identify($excel_file);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        try {
            if (!($objPHPExcel = $objReader->load($excel_file))) {
                $this->modx->log(modX::LOG_LEVEL_ERROR,'Не удалось загрузить файл! '.$excel_file);
                $this->modx->log(modX::LOG_LEVEL_INFO,'COMPLETED');
                sleep(1);
                return $this->modx->error->success();
            }
        } catch(Exception $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,'Не удалось загрузить файл! Exception. '.$excel_file);
            $this->modx->log(modX::LOG_LEVEL_INFO,'COMPLETED');
            sleep(1);
            return $this->modx->error->success();
        }
        $ar = $objPHPExcel->getActiveSheet()->toArray();

        foreach($ar as $ar_colls){
            $email = $ar_colls[0];
            if (!preg_match("/^(?:[a-z0-9]+(?:[-_.]?[a-z0-9]+)?@[a-z0-9_.-]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i", $email)){ 
            //if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->modx->log(modX::LOG_LEVEL_ERROR,'Не корректный email '.$email);
                continue;
            }
            //создаем юзера если его нет
            $user_id = 0;
            if ($profile = $this->modx->getObject("modUserProfile", array("email" => $email))) {
                $user = $profile->getOne("User");
                $user_id = $profile->get("internalKey");
                if($invite_user = $this->modx->getObject('UserTestInvites', array("user_email" => $email))){
                    $password = $invite_user->user_pass;
                    $fullname = $invite_user->user_name;
                }else{
                    //$this->modx->log(modX::LOG_LEVEL_ERROR,'Юзер уже существует и для него нет пароля. '.$email);
                    //continue;
                    $password = "";
                    $fullname = $profile->fullname;
                }
            } else {
                $username = $email;
                $password = md5(time());
                $active = true;
                if($ar_colls[1]){
                    $fullname = $ar_colls[1];
                }else{
                    $fullname = $email;
                }
                $data = array(
                    "sudo"     => 0,
                    "email"    => $email,
                    "username" => $username,
                    "fullname" => $fullname,
                    "active"   => $active,
                    "password" => $password,
                );

                // set extended
                $extended = array();

                $extended["password"] = $password;
                $data["extended"] = $extended;

                $user = $this->modx->newObject("modUser", $data);
                $profile = $this->modx->newObject("modUserProfile", $data);
                $user->addOne($profile);
                if ($user->save()) {
                    $user_id = $user->get("id");
                    $groups = $this->explodeAndClean($this->modx->getOption('usertest_user_groups'));
                    foreach ($groups as $group) {
                        $user->joinGroup($group);
                    }
                }
            }
            //end создаем юзера если его нет
            
            //создаем инвайт
            if($invite = $this->modx->newObject('UserTestInvites')){
                $data = array(
                    'test_id'=>$test_id,
                    'user_id'=>$user_id,
                    'user_email'=>$email,
                    'user_name'=>$fullname,
                    'user_pass'=>$password,
                    'active'=>1,
                    'test_page_id'=>$test_page_id,
                    'auth_page_id'=>$auth_page_id,
                    'user_auth_code'=>$this->generateCode(40),//
                    'url_scheme'=>$url_scheme,
                    'date_expired'=>$date_expired,
                    'send_email_if_empty_test'=>$send_email_if_empty_test,
                    'date'=>date("Y-m-d H:i:s"),
                );
                //test_id в 3 колонке файла
                if(trim($ar_colls[2])) $data['test_id'] = trim($ar_colls[2]);
                
                $data['url'] = $this->modx->makeUrl($auth_page_id,'',array('user_auth_code' => $data['user_auth_code']),$url_scheme);
                $invite->fromArray($data);
                if($invite->save()){
                    $this->modx->log(modX::LOG_LEVEL_INFO,"На $email добавлено приглашение!");
                }else{
                    $this->modx->log(modX::LOG_LEVEL_ERROR,'Ошибка сохранения! '.$email);
                }
                //send email if need
                if($loadEmailQueue and $this->modx->getOption('usertest_use_emailqueue_for_invites', null, false)){
                    $test = $this->modx->getObject('UserTestTests',$invite->test_id);
                    $queue_email = $this->modx->newObject('EmailQueueItem');
                    if($test and $queue_email){
                        $data1 = array(
                            'sender_package'=>'UserTest',
                            'to'=>$invite->user_email,
                            'subject'=>$this->modx->lexicon('usertest_invite_subject',array('test_name' => $test->name)),
                            'body'=>$this->modx->getChunk('tpl.UserTest.InviteEmail',array('test_name' => $test->name,'link'=>$invite->url)),
                            'date'=>date("Y-m-d H:i:s"),
                        );
                        if($this->modx->getOption('usertest_invite_email_from', null, false))
                            $data1['from'] = $this->modx->getOption('usertest_invite_email_from');
                        if($this->modx->getOption('usertest_invite_email_from_name', null, false))
                            $data1['from_name'] = $this->modx->getOption('usertest_invite_email_from_name');
                        $queue_email->fromArray($data1);
                        if($queue_email->save())
                            $this->modx->log(modX::LOG_LEVEL_INFO,"Приглашение на $email добавлено в очередь писем!");
                    }
                }
            }
        }
        //$this->modx->log(modX::LOG_LEVEL_ERROR,'Отправлено '.$count.' писем!');
        $this->modx->log(modX::LOG_LEVEL_INFO,'COMPLETED');
        sleep(1);
        return $this->success("");
    }
    function generateCode($length = 8){
        $chars = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }
        return $string;
    }
    function explodeAndClean($array, $delimiter = ",")
    {
        $array = explode($delimiter, $array);     // Explode fields to array
        $array = array_map("trim", $array);       // Trim array"s values
        $array = array_keys(array_flip($array));  // Remove duplicate fields
        $array = array_filter($array);            // Remove empty values from array

        return $array;
    }
}

return 'ImportInvitesConsoleProcessor';	
?>
<?php
$start_step = $modx->getOption('start_step', $scriptProperties, "");
$addContext = $modx->getOption('addContext', $scriptProperties, "web");

if (!$UserTest = $modx->getService('usertest', 'UserTest', $modx->getOption('usertest_core_path', null,
        $modx->getOption('core_path') . 'components/usertest/') . 'model/usertest/', $scriptProperties)
) {
    return $modx->lexicon('usertest_snippet_not_load_service');
}
if(empty($_GET['user_auth_code'])){
    return $modx->lexicon('usertest_snippet_no_invite_code');
}
//$user_auth_code = $_GET['user_auth_code'];
$user_auth_code = $UserTest->fooClearPostGet('user_auth_code', 'fooStrClear');
if(!$invite = $modx->getObject('UserTestInvites',array('user_auth_code'=>$user_auth_code))){
    return $modx->lexicon('usertest_snippet_invite_code_error');
}
if($invite->date_expired != "0000-00-00 00:00:00"){
	if(time() > strtotime($invite->date_expired)){
		return $modx->lexicon('usertest_snippet_invite_code_timeout');
	}	
}

if($modx->user->id == 0){
    $addContext = explode(",", $addContext);
	if($profile = $modx->getObject("modUserProfile", array("email" => $invite->user_email))) {
		$modx->user = $profile->getOne("User");
		foreach($addContext as $context){
			$modx->user->addSessionContext(trim($context));
		}
	}else{
		return $modx->lexicon('usertest_snippet_not_found_user');
	}
	/* // задаем параметры
    $logindata = array(
      'username' => $invite->user_email,   // имя пользователя
      'password' => $invite->user_pass, // пароль
      'rememberme' => true        // запомнить?
    );
    // сам процесс авторизации
    $response = $modx->runProcessor('/security/login', $logindata);
    // проверяем, успешно ли
    if ($response->isError()) {
      // произошла ошибка, например неверный пароль
      $modx->log(modX::LOG_LEVEL_ERROR, 'Login error. Message: '.$response->getMessage());
      return 'Не удалось авторизовать пользователя!';
    } */
}
if($invite->result_id == 0){
    if($Result = $modx->newObject('UserTestResults')){
        $Result->user_id = $invite->user_id;
        $Result->user_name = $invite->user_name;
        $Result->user_email = $invite->user_email;
        
        $Result->test_id = $invite->test_id;
        $Result->status_id = 1;
		$Result->date = strftime('%Y-%m-%d %H:%M:%S');
        $Result->save();
        $invite->result_id = $Result->id;
        $invite->save();
    }
}
$params = array(
    'test_id'=>$invite->test_id,
    'result_id'=>$invite->result_id,
    );
if($start_step) $params['step']="start";
$test_url = $modx->makeUrl($invite->test_page_id,'',$params,$invite->url_scheme);
$modx->sendRedirect($test_url);
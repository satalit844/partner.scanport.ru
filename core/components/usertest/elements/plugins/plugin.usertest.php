<?php
/** @var modX $modx */
switch ($modx->event->name) {
	case 'OnTestComplect':
		//$modx->log(1, 'OnTestComplect'. print_r($test,1). print_r($result,1));
	    $pdoFetch = $modx->getService('pdoFetch');
        $pdoFetch->setConfig($scriptProperties);
        $pdoFetch->addTime('pdoTools loaded');
		
		$modx->getService('lexicon','modLexicon');
		$modx->lexicon->load($modx->config['manager_language'].':usertest:default');
		
		$teachers_email = $modx->getOption('usertest_teachers_email',null,"");
		if($teachers_email == ""){
			$teachers_email = $modx->getOption('emailsender');
		}
		$data = array(
			'user_name'=>$result['user_name'],
			'test_name'=>$test['name'],
			'test_point'=>$result['test_point'],
			'max_point'=>$result['max_point'],
			'var_result'=>$result['var_result'],
			'cat_email_results'=>$result['cat_email_results'],
		);
		
		/*Активируем почтовый сервис MODX*/
		$modx->getService('mail', 'mail.modPHPMailer');
		$modx->mail->set(modMail::MAIL_FROM, $modx->getOption('emailsender'));
		$modx->mail->set(modMail::MAIL_FROM_NAME, $modx->getOption('site_name'));

		/*Адрес получателя нашего письма*/
		$teachers_email = explode(",",$teachers_email);
		foreach($teachers_email as $te){
			$modx->mail->address('to', trim($te));
		}
		

		/*Заголовок сообщения*/
		$modx->mail->set(modMail::MAIL_SUBJECT, $modx->lexicon('usertest_teacher_subject',array('test_name' => $test['name'],'user_name' => $data['user_name'])));

		/*Подставляем чанк с телом письма (предварительно его нужно создать)*/
		$modx->mail->set(modMail::MAIL_BODY, $pdoFetch->getChunk('tpl.UserTest.Teacher.Email', $data));

		/*Отправляем*/
		$modx->mail->setHTML(true);
		if (!$modx->mail->send()) {
			$modx->log(modX::LOG_LEVEL_ERROR,'An error occurred while trying to send the email: '.$modx->mail->mailer->ErrorInfo);
		}
		$modx->mail->reset();
		
	break;
}
<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

if (!$UserTest = $modx->getService('usertest', 'UserTest', $modx->getOption('usertest_core_path', null,
        $modx->getOption('core_path') . 'components/usertest/') . 'model/usertest/', array())
) {
    return 'Could not load UserTest class!';
}

$loadEmailQueue = false;
if($modx->getOption('usertest_use_emailqueue_for_invites', null, false)){
	if ($EmailQueue = $modx->getService('emailqueue', 'EmailQueue', $modx->getOption('emailqueue_core_path', null,
			$modx->getOption('core_path') . 'components/emailqueue/') . 'model/emailqueue/', array())) {
		$loadEmailQueue = true;
	}
}
		
$invites = $modx->getCollection('UserTestInvites',array('active'=>1,'send_email_if_empty_test'=>1));
foreach($invites as $invite){
	$ResultCount = $modx->getCount('UserTestResults', array('user_id'=>$invite->user_id,'test_id'=>$invite->test_id));
	if(!$ResultCount){
		if($loadEmailQueue and $modx->getOption('usertest_use_emailqueue_for_invites', null, false)){
			$test = $modx->getObject('UserTestTests',$invite->test_id);
			$queue_email = $modx->newObject('EmailQueueItem');
			if($test and $queue_email){
				$data1 = array(
					'sender_package'=>'UserTest',
					'to'=>$invite->user_email,
					'subject'=>$modx->lexicon('usertest_invite_with_empty_test_subject',array('test_name' => $test->name)),
					'body'=>$modx->getChunk('tpl.UserTest.InviteEmailUserWithEmptyTest',array('test_name' => $test->name,'link'=>$invite->url)),
					'date'=>date("Y-m-d H:i:s"),
				);
				if($modx->getOption('usertest_invite_email_from', null, false))
					$data1['from'] = $modx->getOption('usertest_invite_email_from');
				if($modx->getOption('usertest_invite_email_from_name', null, false))
					$data1['from_name'] = $modx->getOption('usertest_invite_email_from_name');
				$queue_email->fromArray($data1);
				if($queue_email->save())
					echo "Приглашение на $email добавлено в очередь писем!";
			}
		}
	}
}
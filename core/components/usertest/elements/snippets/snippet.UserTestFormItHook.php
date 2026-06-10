<?php
/** @var modX $modx */
/** @var array $scriptProperties */
/** @var UserTest $UserTest */
if (!$UserTest = $modx->getService('usertest', 'UserTest', $modx->getOption('usertest_core_path', null,
        $modx->getOption('core_path') . 'components/usertest/') . 'model/usertest/', $scriptProperties)
) {
    return $modx->lexicon('usertest_snippet_not_load_service');
}
$pkg = 'usertest';
$modelpath = $modx->getOption('core_path') . 'components/usertest/model/';
$modx->addPackage($pkg, $modelpath);

$formFields = $hook->getValues();
$sanitizePatterns = $modx->sanitizePatterns;
$sanitizePatterns['fenom_syntax'] = '@\{(.*?)\}@si';
$formFields = $modx->sanitize($formFields, $sanitizePatterns);

$name=$formFields['name'];
$email=$formFields['email'];
$result_id=abs((int)$formFields['result_id']);

if($Result = $modx->getObject("UserTestResults", $result_id)){
    if(!$Result->user_id and $modx->getOption('usertest_create_modx_users')){
        $Result->user_id = $UserTest->createUser(['name'=>$name,'email'=>$email]);
    }
    $Result->user_name = $name;
    $Result->user_email = $email;
    $Result->save();
   
}

return true;
<?php
/** @var modX $modx */
/** @var array $scriptProperties */
/** @var UserTest $UserTest */
$tplError = $modx->getOption('tplError', $scriptProperties, 'tpl.UserTest.error');
$frontend_css = $modx->getOption('frontend_css', $scriptProperties, 'components/usertest/css/web/default.css');

$pdoFetch = $modx->getService('pdoFetch');
$pdoFetch->setConfig($scriptProperties);
$pdoFetch->addTime('pdoTools loaded');

if (!$UserTest = $modx->getService('usertest', 'UserTest', $modx->getOption('usertest_core_path', null,
        $modx->getOption('core_path') . 'components/usertest/') . 'model/usertest/', $scriptProperties)
) {
    return $pdoFetch->getChunk($tplError, ['error'=>$modx->lexicon('usertest_snippet_not_load_service')]);
}
$tpl = $modx->getOption('tpl', $scriptProperties, 'tpl.UserTest.ResultShow');

$result_id = $UserTest->fooClearPostGet('result_id', 'fooIntAbsClear');
if(!$Result = $modx->getObject('UserTestResults', $result_id)){
    return $pdoFetch->getChunk($tplError, ['error'=>$modx->lexicon('usertest_snippet_answer_not_found')]);
}

if(!$test = $modx->getObject('UserTestTests', $Result->test_id)){
    return $pdoFetch->getChunk($tplError, ['error'=>$modx->lexicon('usertest_snippet_not_found_test')]);
}

$var_id = $Result->variant_id;
if($var = $modx->getObject('UserTestVariants',$var_id)){
    $var_result = $var->result;
    $var_passed = $var->passed;
}
$max_point = $Result->max_point;
$result_status = $Result->status_id;
$test_point = $Result->test_point;
$test_time = $Result->test_time;
$result_id = $Result->id;

if($answer_page_id){
    $params = array(
        'result_id'=>$result_id,
        );
    $answer_page_url = $modx->makeUrl($answer_page_id,'',$params);
}
return $pdoFetch->getChunk($tpl, array(
    'result_id'=>$result_id,
    'test_id'=>$id,
    'test'=>$test->toArray(),
    'test_point'=>$test_point,
    'max_point'=>$max_point,
    'var_result'=>$var_result,
    'var_passed'=>$var_passed,
    'test_time'=>$test_time,
    'result_status'=>$result_status,
    'answer_page_url'=>$answer_page_url,
    ));
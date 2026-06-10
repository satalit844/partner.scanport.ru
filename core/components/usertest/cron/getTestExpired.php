<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

if (!$UserTest = $modx->getService('usertest', 'UserTest', $modx->getOption('usertest_core_path', null,
        $modx->getOption('core_path') . 'components/usertest/') . 'model/usertest/', array())
) {
    return 'Could not load UserTest class!';
}
$time = time();
$Results = $modx->getIterator('UserTestResults',['status_id'=>1]);

foreach($Results as $Result){
    if(empty($Result->session)) continue;
    $session = json_decode($Result->session,1);
    if(empty($session["test_time_start"])) continue;
    if(!$test = $modx->getObject('UserTestTests',$Result->test_id)) continue;
    if($session["test_time_start"] + $test->time_test > $time) continue;
    
    $Result->status_id = 4;
    $Result->session = "";
    $Result->save();
    $id = $Result->test_id;
    
    //$temp_result = $Result->toArray();
    // $temp_result['var_result'] = $var_result;
    // $temp_result['var_passed'] = $var_passed;
    // $temp_result['cat_email_results'] = $cat_email_results;

    $c = $modx->newQuery("UserTestVariants");
    $c->leftJoin('UserTestTestVariantLink','UserTestTestVariantLink', '`UserTestTestVariantLink`.`variant_id` = `UserTestVariants`.`id`');
    $c->select($modx->getSelectColumns('UserTestVariants','UserTestVariants','',array(
        'id',
        'passed',
        'category_id',
        'result'
        )));
    $c->select($modx->getSelectColumns('UserTestTestVariantLink','UserTestTestVariantLink','',array(
        'menuindex',
        'test_id',
        )));
    $c->select('IF(`UserTestTestVariantLink`.`use_custom_point` = 1, `UserTestTestVariantLink`.`start_point`, `UserTestVariants`.`start_point`) as start_point,
        IF(`UserTestTestVariantLink`.`use_custom_point` = 1, `UserTestTestVariantLink`.`end_point`, `UserTestVariants`.`end_point`) as end_point');

    $c->where(array('`UserTestTestVariantLink`.`test_id`'=>$id,'`UserTestVariants`.`category_id`'=>0));
    
    //$c->prepare(); echo $c->toSQL();
    $Variants = $modx->getCollection('UserTestVariants', $c);
    foreach($Variants as $var){
        if($Result->test_point >= $var->start_point and $Result->test_point <= $var->end_point){
            $var_id = $var->id;
            $var_result = $var->result;
            $var_passed = $var->passed;
            break;
        }
    }
    //$modx->log(1,"UserTest $var_id");
    //echo '$test->use_category '.$test->use_category;
    $cat_email_results = "";
    if($Result and ($test->use_category or $test->test_type == 2)){
        $c = $modx->newQuery('UserTestResultAnswers');
        $c->leftJoin('UserTestQuestions', 'UserTestQuestions', 'UserTestResultAnswers.question_id = UserTestQuestions.id');
        $c->select("`UserTestQuestions`.`category_id` as cat_id, sum(`UserTestResultAnswers`.`point`) as sum_point, count(*) as count_cat");
        $c->where(array('result_id'=>$Result->id));
        $c->groupby('`UserTestQuestions`.`category_id`');
        //$c->prepare();echo $c->toSQL();
        $cat_results = array();
        
        $cat_points = $modx->getIterator('UserTestResultAnswers', $c);
        foreach($cat_points as $cp){
            $c_var_id = 0;$cat_result="";$cat_var_result='';
            $c = $modx->newQuery("UserTestVariants");
            $c->leftJoin('UserTestTestVariantLink','UserTestTestVariantLink', '`UserTestTestVariantLink`.`variant_id` = `UserTestVariants`.`id`');
            $c->select($modx->getSelectColumns('UserTestVariants','UserTestVariants','',array(
                'id',
                'passed',
                'category_id',
                'result'
                )));
            $c->select($modx->getSelectColumns('UserTestTestVariantLink','UserTestTestVariantLink','',array(
                'menuindex',
                'test_id',
                )));
            $c->select('IF(`UserTestTestVariantLink`.`use_custom_point` = 1, `UserTestTestVariantLink`.`start_point`, `UserTestVariants`.`start_point`) as start_point,
                IF(`UserTestTestVariantLink`.`use_custom_point` = 1, `UserTestTestVariantLink`.`end_point`, `UserTestVariants`.`end_point`) as end_point');
        
            $c->where(array('`UserTestTestVariantLink`.`test_id`'=>$id,'`UserTestVariants`.`category_id`'=>$cp->cat_id));
            $Variants = $modx->getIterator('UserTestVariants', $c);
            foreach($Variants as $var){
                if($cp->sum_point >= $var->start_point and $cp->sum_point <= $var->end_point){
                    $c_var_id = $var->id;
                    $cat_var_result = $var->result;
                    break;
                }
            }
            //$modx->log(1,"UserTest $c_var_id");
            if($cat_result = $modx->newObject('UserTestResultCategorys')){
                $cat_result->result_id = $Result->id;
                $cat_result->category_id = $cp->cat_id;
                $cat_result->variant_id = $c_var_id;
                $cat_result->cat_point = $cp->sum_point;
                if($test->test_type == 2 and $cp->count_cat > 0) $cat_result->cat_point = $cp->sum_point/$cp->count_cat; //Для опросник САН
                $cat_result->max_point = $UserTest->getMaxPoint($id, $cp->cat_id);
                $cat_result->save();
                $cr = $cat_result->toArray();
                if($cat = $modx->getObject('UserTestCategorys', $cp->cat_id)){
                $cr['cat_name'] = $cat->name;
                }
                $cr['result'] = $cat_var_result;
                $cat_results[] = $cr;
                $cat_email_results .=$cr['cat_name'].": балл ".$cat_result->cat_point.", результат ".$cr['result']." ";
            }
        }
        
    }

    if($Result){
        //$modx->log(1,"UserTest $var_id");
        $Result->variant_id = $var_id;
        // if($test->type == 2){
        //     $Result->status_id = 3;
        // }else{
        //     $Result->status_id = 2;
        // }
        $Result->max_point = $UserTest->getMaxPoint($id);
        $Result->save();
        
        $response = $modx->invokeEvent('OnTestCalculate', array(
                'test'=>$test->toArray(),
                'variants'=>$Variants,
                'result'=>$Result,
                'cat_results'=>$cat_results,
                'sp' => [],
            ));
        if($Result = $modx->getObject('UserTestResults',$Result->id)){
            $var_id = $Result->variant_id;
            if($var = $modx->getObject('UserTestVariants',$var_id)){
                $var_result = $var->result;
                $var_passed = $var->passed;
            }
            $max_point = $Result->max_point;
            $result_status = $Result->status_id;
        
            $result_id = $Result->id;
            
            $Result->session = "";
            $Result->save();
            if($Result->user_id > 0){
                $user_name = $Result->user_name;
                $user_email = $Result->user_email;
            }
            $temp_result = $Result->toArray();
            $temp_result['var_result'] = $var_result;
            $temp_result['var_passed'] = $var_passed;
            $temp_result['cat_email_results'] = $cat_email_results;
            $modx->invokeEvent('OnTestComplect', array(
                    'test'=>$test->toArray(),
                    'result' => $temp_result,
                    'sp' => [],
                ));
        }
    }
    

    // echo "<p>{$Result->id}</p>";        
    // $modx->invokeEvent('OnTestComplect', array(
    //     'test'=>$test->toArray(),
    //     'result' => $temp_result,
    //     'sp' => [],
    // ));
}

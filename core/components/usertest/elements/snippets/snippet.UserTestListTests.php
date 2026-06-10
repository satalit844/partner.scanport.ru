<?php
/** @var modX $modx */
/** @var array $scriptProperties */
/** @var UserTest $UserTest */
if (!$UserTest = $modx->getService('usertest', 'UserTest', $modx->getOption('usertest_core_path', null,
        $modx->getOption('core_path') . 'components/usertest/') . 'model/usertest/', $scriptProperties)
) {
    return $modx->lexicon('usertest_snippet_not_load_service');
}
$tpl = $modx->getOption('tpl', $scriptProperties, 'tpl.UserTest.ListTests');
$test_page_id = $modx->getOption('test_page_id', $scriptProperties, 0);
$answer_page_id = $modx->getOption('answer_page_id', $scriptProperties, 0);
$group_ids = $modx->getOption('group_ids', $scriptProperties, "");
$start_step = $modx->getOption('start_step', $scriptProperties, 0);
$IsComplete = $modx->getOption('IsComplete', $scriptProperties, 0);

//$tpl = 'tpl.UserTest.ListTests2';
$pdoFetch = $modx->getService('pdoFetch');
$pdoFetch->setConfig($scriptProperties);
$pdoFetch->addTime('pdoTools loaded');

$where ="";
if($group_ids){
    /*$where = array(
        '`UserTestGroupsLink`.`group_id`:IN'=>explode(',', $group_ids),
        '`UserTestTests`.`active`'=>1,
		'`UserTestTests`.`pub_date`:>'=>time(),
		'`UserTestTests`.`unpub_date`:<'=>time()
    );*/
	$where .= "`UserTestGroupsLink`.`group_id` IN ($group_ids) AND ";
}
$where .="`UserTestTests`.`active` = 1 AND (`UserTestTests`.`pub_date` = 0 OR `UserTestTests`.`pub_date` < ".time().") AND ";
$where .="(`UserTestTests`.`unpub_date` = 0 OR `UserTestTests`.`unpub_date` > ".time().")";
//echo $where;
$default = array(
    'class' => 'UserTestGroupsLink',
    'where' => '["('.$where.')"]',
    'leftJoin' => array(
        'UserTestGroups' => array(
            'class' => 'UserTestGroups',
            'on' => 'UserTestGroups.id = UserTestGroupsLink.group_id',
            ),
        'UserTestTests' => array(
            'class' => 'UserTestTests',
            'on' => 'UserTestTests.id = UserTestGroupsLink.test_id',
            ),
    ),
    'select' => array(
        'UserTestGroups' => 'UserTestGroups.id as group_id, UserTestGroups.name as group_name,UserTestGroups.description as group_description',
        'UserTestTests' => '*',
    ),
    'sortby' => array(
        'UserTestGroups.id' => 'ASC',
        'UserTestGroupsLink.menuindex' => 'ASC',
    ),
    'fastMode' => true,
    'return' => 'data',
    //'limit' => 0,
);
$pdoFetch->config = array_merge($pdoFetch->config, $default, $scriptProperties);
$groupLinks = $pdoFetch->run();
//echo $groupLinks;

$groups = array();
$user_id = $modx->user->get('id');
foreach($groupLinks as $groupLink){
    //print_r($groupLink);
    $group = array(
        'name'=>$groupLink['group_name'],
        'description'=>$groupLink['group_description'],
        );
    unset($groupLink['group_name']);
    unset($groupLink['group_description']);
    if(!isset($groups[$groupLink['group_id']])){
        $groups[$groupLink['group_id']] = $group;
    }
    
    if($user_id > 0){
        $c = $modx->newQuery('UserTestResults');
        $c->sortby('id','DESC');
        $c->where(array('UserTestResults.user_id'=>$user_id, 'UserTestResults.test_id'=> $groupLink['id']));
        //$c->prepare(); echo $c->toSQL();
        $result_count = $modx->getCount('UserTestResults', $c);
        $result = array();
        if($result_count > 0){
            $c->limit(1);
            
		    $c->leftJoin('UserTestVariants','UserTestVariants', '`UserTestResults`.`variant_id` = `UserTestVariants`.`id`');
		    $c->leftJoin('UserTestResultStatus','UserTestResultStatus', '`UserTestResults`.`status_id` = `UserTestResultStatus`.`id`');
		    $Columns = $modx->getSelectColumns('UserTestResults', 'UserTestResults', '', array(), true);
		    $c->select($Columns . ', `UserTestVariants`.`result` as `variant`,`UserTestVariants`.`passed`, `UserTestResultStatus`.`label` as `status`');
		    
            $results = $modx->getIterator('UserTestResults', $c);
            foreach($results as $r){
                $result = $r->toArray();
            }
        }
        $groupLink['result_count'] = $result_count;
        $groupLink['last_result'] = $result;
    }
    switch($IsComplete){
        case 0:
            $groups[$groupLink['group_id']]['tests'][] = $groupLink;
        break;
        case 1:
            if($groupLink['count_test_answer'] == 0 or $groupLink['count_test_answer'] > $groupLink['result_count']){
                $groups[$groupLink['group_id']]['tests'][] = $groupLink;
            }
        break;
        case 2:
            if($groupLink['result_count'] == 0){
                $groups[$groupLink['group_id']]['tests'][] = $groupLink;
            }
        break;
        case 3:
            if($groupLink['result_count'] > 0 and $groupLink['count_test_answer'] == $groupLink['result_count']){
                $groups[$groupLink['group_id']]['tests'][] = $groupLink;
            }
        break;
        
    }
    
}
return $pdoFetch->getChunk($tpl, array(
    'groups'=>$groups,
    'test_page_id'=>$test_page_id,
    'answer_page_id'=>$answer_page_id,
    'start_step'=>$start_step,
    ));
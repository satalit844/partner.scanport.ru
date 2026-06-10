<?php
/** @var modX $modx */
/** @var array $scriptProperties */
/** @var UserTest $UserTest */
$defaultTplErrorFile = '@FILE ' . rtrim($modx->getOption('usertest_core_path', null, $modx->getOption('core_path') . 'components/usertest/'), '/\\') . '/elements/chunks/chunk.tpl.UserTest.error.tpl';
$tplError = usertestTplToInlineTraining($modx->getOption('tplError', $scriptProperties, $defaultTplErrorFile));
$frontend_css = $modx->getOption('frontend_css', $scriptProperties, 'components/usertest/css/web/default.css');

$pdoFetch = $modx->getService('pdoFetch');
$pdoFetch->setConfig($scriptProperties);
$pdoFetch->addTime('pdoTools loaded');

if (!$UserTest = $modx->getService('usertest', 'UserTest', $modx->getOption('usertest_core_path', null,
        $modx->getOption('core_path') . 'components/usertest/') . 'model/usertest/', $scriptProperties)
) {
    return $pdoFetch->getChunk($tplError, ['error'=>$modx->lexicon('usertest_snippet_not_load_service')]);
}

if (!function_exists('usertestTplToInlineTraining')) {
    function usertestTplToInlineTraining($tpl)
    {
        $tpl = (string)$tpl;
        if (stripos($tpl, '@FILE') !== 0) {
            return $tpl;
        }
        $path = trim(substr($tpl, 5));
        $path = str_replace('\\', '/', $path);
        if ($path !== '' && is_file($path)) {
            $content = (string)file_get_contents($path);
            if ($content !== '') {
                return '@INLINE ' . $content;
            }
        }
        return $tpl;
    }
}

$defaultTplFile = '@FILE ' . rtrim($modx->getOption('usertest_core_path', null, $modx->getOption('core_path') . 'components/usertest/'), '/\\') . '/elements/chunks/chunk.tpl.UserTest.ResultAnswer.tpl';
$tpl = usertestTplToInlineTraining($modx->getOption('tpl', $scriptProperties, $defaultTplFile));

$result_id = $UserTest->fooClearPostGet('result_id', 'fooIntAbsClear');
if(!$Result = $modx->getObject('UserTestResults', $result_id)){
    return $pdoFetch->getChunk($tplError, ['error'=>$modx->lexicon('usertest_snippet_answer_not_found')]);
}
$user_id = $modx->user->get('id');
if($user_id != $Result->user_id){
    return $pdoFetch->getChunk($tplError, ['error'=>$modx->lexicon('usertest_access_error')]);
}
if(!$test = $modx->getObject('UserTestTests', $Result->test_id)){
    return $pdoFetch->getChunk($tplError, ['error'=>$modx->lexicon('usertest_snippet_not_found_test')]);
}

//load js and css
$modx->regClientCSS($modx->getOption('assets_url').$frontend_css);

if(isset($getlimit)){
    return $test->count_questions_on_page;
}
$where = array(
    '`UserTestResultAnswers`.`result_id`'=>$result_id
);

$default = array(
    'class' => 'UserTestResultAnswers',
    'where' => $modx->toJSON($where),
    'sortby' => array(
        'UserTestResultAnswers.id' => 'ASC',
    ),
    'fastMode' => true,
    'return' => 'data',
    //'limit' => $limit,
);
$pdoFetch->config = array_merge($pdoFetch->config, $default, $scriptProperties);
$ResultAnswers = $pdoFetch->run();

foreach($ResultAnswers as &$ResultAnswer){
    $ResultAnswer['question'] = $pdoFetch->getArray('UserTestQuestions', $ResultAnswer['question_id']);
    switch($ResultAnswer['question']['type']){
        case 1: //Одиночный выбор
        case 12: //Опросник САН
            if($Answer = $pdoFetch->getArray('UserTestAnswers', array('question_id'=>$ResultAnswer['question_id'], 'right'=> true))){
                $ResultAnswer['rightAnswers'] = $Answer['answer'];
            }else{
                $ResultAnswer['rightAnswers'] = "";
            }
            $c = $modx->newQuery('UserTestAnswers');
            $c->sortby('menuindex', 'ASC');
            $Answers = $modx->getIterator('UserTestAnswers', array('question_id'=>$ResultAnswer['question_id']));
            $Ans = array();
            foreach($Answers as $a){
                $Ans[] = $a->toArray();
            }
            $ResultAnswer['question']['answers'] = $Ans;
            $ResultAnswer['question']['answer_id'] = $ResultAnswer['answer_id'];
            break;
        case 2: //Множественный выбор
            $Answers = $pdoFetch->getCollection('UserTestAnswers', array('question_id'=>$ResultAnswer['question_id'], 'right'=> true));
            $ans = array();
            foreach($Answers as $Answer){
                $ans[] = $Answer['answer'];
            }
            $ResultAnswer['rightAnswers'] = implode(', ', $ans);
            $c = $modx->newQuery('UserTestAnswers');
            
            $c->sortby('menuindex', 'ASC');
            $Answers = $modx->getIterator('UserTestAnswers', array('question_id'=>$ResultAnswer['question_id']));
            $Ans = array();
            $answer_ids = explode(',', $ResultAnswer['answer_ids']);
            foreach($Answers as $a){
                $a1 = $a->toArray();
                if (in_array($a->id, $answer_ids)) {
                    $a1['check'] = 1;
                }else{
                    $a1['check'] = 0;
                }
                $Ans[] = $a1;
            }
            $ResultAnswer['question']['answers'] = $Ans;
            break;
        case 3: //Простой текст
            $Answers = $pdoFetch->getCollection('UserTestAnswers', array('question_id'=>$ResultAnswer['question_id'], 'right'=> true));
            $ans = array();
            foreach($Answers as $Answer){
                $ans[] = $Answer['answer'];
            }
            $ResultAnswer['rightAnswers'] = implode(', ', $ans);
            break;
        case 4: //Открытый вопрос
            $ResultAnswer['rightAnswers'] = "Правильный ответ в комментарии преподователя.";
            break;
        case 5://На сопоставление. Простой
            $ext = $ResultAnswer['question']['extended'];
            $ans = array();
            foreach($ext['q'] as $k=>$v){
                $ans[] = $v." -> ".$ext['a'][$k];
            }
            $ResultAnswer['rightAnswers'] = implode('<br>', $ans);
            break;
        case 6: //Комбинированный множественный выбор
            $Answers = $pdoFetch->getCollection('UserTestAnswers', array('question_id'=>$ResultAnswer['question_id'], 'right'=> true));
            $ans = array();
            foreach($Answers as $Answer){
                $ans[] = $Answer['answer'];
            }
            $ResultAnswer['rightAnswers'] = implode(', ', $ans);
            break;
        case 7: //Таблица чек-боксов
            $qcs = $pdoFetch->getCollection('UserTestQuestions', array('parent'=>$ResultAnswer['question_id']));
            foreach($qcs as $qc){
                $Answers = $pdoFetch->getCollection('UserTestAnswers', array('question_id'=>$qc['id'], 'right'=> true));
                $ans = array();
                foreach($Answers as $Answer){
                    $ans[] = $Answer['answer'];
                }
                $ResultAnswer['rightAnswers'] .= $qc['question']."[".$qc['id']."]"."->".implode('# ', $ans)."\r\n";
            }
            $ResultAnswer['rightAnswers'] = nl2br($ResultAnswer['rightAnswers']);
            break;
        case 8: //Таблица текстовых полей
            $ResultAnswer['rightAnswers'] = "Правильный ответ в комментарии преподователя.";
            break;
        case 9: //Селекты в тексте
            $q_childs = $modx->getIterator('UserTestQuestions', array('parent' =>$ResultAnswer['question']['id']));
            $q_str = $ResultAnswer['question']['question'];
            $q_right_str = $ResultAnswer['question']['question'];
            foreach($q_childs as $k_child=>$q_child){
                $c = $modx->newQuery('UserTestAnswers');
                $c->sortby('menuindex', 'ASC');
                $c->where(array('question_id'=>$q_child->id));
                $Answers = $modx->getIterator('UserTestAnswers', $c);
                $c->where(array('right'=>1));
                $RightAnswer = $modx->getObject('UserTestAnswers', $c);
                $opt = "";
                $opt .= $pdoFetch->getChunk('@INLINE <option value="{$id}">{$answer}</option>', array(
                        'id'=>0,
                        'answer'=>"Выбрать",
                        ));
                foreach($Answers as $a){
                        $opt .= $pdoFetch->getChunk('@INLINE <option value="{$id}">{$answer}</option>', array(
                        'id'=>$a->id,
                        'answer'=>$a->answer,
                        ));
                }
                $sel = $pdoFetch->getChunk('@INLINE <select class="select-in-text" name="{$q_id}[{$qc_id}]">{$opt}</select>', array(
                'q_id'=>$q_id['id'],
                'qc_id'=>$q_child->id,
                'opt'=>$opt,
                ));
                //echo $sel.' '.$q_str;
                $q_str = str_replace('[['.$q_child->question.']]',$sel,$q_str);
                $q_right_str = str_replace('[['.$q_child->question.']]','<span style="color:blue;">'.$RightAnswer->answer.'</span>',$q_right_str);
            }
            $ResultAnswer['question']['question'] = $q_str;
            $ResultAnswer['rightAnswers'] = $q_right_str;
            break;
        case 10: //Комбинированный одиночный выбор
            $Answers = $pdoFetch->getCollection('UserTestAnswers', array('question_id'=>$ResultAnswer['question_id'], 'right'=> true));
            $ans = array();
            foreach($Answers as $Answer){
                $ans[] = $Answer['answer'];
            }
            $ResultAnswer['rightAnswers'] = implode(', ', $ans);
            break;
    }
    $ResultAnswer['answer'] = nl2br($ResultAnswer['answer']);
}
return $pdoFetch->getChunk($tpl, array(
    'ResultAnswers'=>$ResultAnswers,
    'test'=>$test->toArray()
    ));
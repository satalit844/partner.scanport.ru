<?php
/* TRAINING_STAGE27_USERTEST_NO_EMPTY_POST_SAVE */
$Result = null;
/** @var modX $modx */
/** @var array $scriptProperties */
/** @var UserTest $UserTest */
$tplError = $modx->getOption('tplError', $scriptProperties, 'tpl.UserTest.error');
$AjaxMode = $modx->getOption('AjaxMode', $scriptProperties, '1');
$frontend_js = $modx->getOption('frontend_js', $scriptProperties, 'components/usertest/js/web/default.js');
$frontend_css = $modx->getOption('frontend_css', $scriptProperties, 'components/usertest/css/web/default.css');

$pdoFetch = $modx->getService('pdoFetch');
$pdoFetch->setConfig($scriptProperties);
$pdoFetch->addTime('pdoTools loaded');

if (!$UserTest = $modx->getService('usertest', 'UserTest', $modx->getOption('usertest_core_path', null,
        $modx->getOption('core_path') . 'components/usertest/') . 'model/usertest/', $scriptProperties)
) {
    return $pdoFetch->getChunk($tplError, ['error'=>$modx->lexicon('usertest_snippet_not_load_service')]);
}



if (!function_exists('usertestNormalizeIdsTraining')) {
    function usertestNormalizeIdsTraining($value)
    {
        if (is_array($value)) {
            $items = $value;
        } else {
            $items = explode(',', (string)$value);
        }

        $result = array();
        foreach ($items as $item) {
            $item = (int)$item;
            if ($item > 0) {
                $result[] = $item;
            }
        }

        $result = array_values(array_unique($result));
        sort($result);
        return $result;
    }
}

if (!function_exists('usertestFormatPointTraining')) {
    function usertestFormatPointTraining($value)
    {
        $value = (float)$value;
        if ((float)(int)$value === $value) {
            return (string)(int)$value;
        }
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}

if (!function_exists('usertestGetResultScoreTraining')) {
    function usertestGetResultScoreTraining(modX $modx, $resultId)
    {
        $resultId = (int)$resultId;
        if ($resultId <= 0) {
            return 0;
        }

        $c = $modx->newQuery('UserTestResultAnswers');
        $c->select('SUM(point) AS sum_point');
        $c->where(array('result_id' => $resultId));
        $row = $modx->getObject('UserTestResultAnswers', $c);
        if (!$row) {
            return 0;
        }

        return (float)$row->get('sum_point');
    }
}



if (!function_exists('usertestBuildUrlTraining')) {
    function usertestBuildUrlTraining($baseUrl, array $params)
    {
        $baseUrl = html_entity_decode(trim((string)$baseUrl), ENT_QUOTES, 'UTF-8');
        if ($baseUrl === '') {
            return '';
        }

        $hash = '';
        $hashPos = strpos($baseUrl, '#');
        if ($hashPos !== false) {
            $hash = substr($baseUrl, $hashPos);
            $baseUrl = substr($baseUrl, 0, $hashPos);
        }

        $parts = parse_url($baseUrl);
        if (!is_array($parts)) {
            return $baseUrl;
        }

        $query = array();
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        unset($query['_ut_modal_refresh'], $query['result_id']);
        foreach ($params as $key => $value) {
            if ($value === null || $value === '') {
                unset($query[$key]);
            } else {
                $query[$key] = $value;
            }
        }

        $url = '';
        if (!empty($parts['scheme'])) {
            $url .= $parts['scheme'] . '://';
        }
        if (!empty($parts['user'])) {
            $url .= $parts['user'];
            if (!empty($parts['pass'])) {
                $url .= ':' . $parts['pass'];
            }
            $url .= '@';
        }
        if (!empty($parts['host'])) {
            $url .= $parts['host'];
        }
        if (!empty($parts['port'])) {
            $url .= ':' . $parts['port'];
        }
        $url .= !empty($parts['path']) ? $parts['path'] : '';

        $queryString = http_build_query($query, '', '&');
        if ($queryString !== '') {
            $url .= '?' . $queryString;
        }

        return $url . $hash;
    }
}

if (!function_exists('usertestCurrentPageUrlTraining')) {
    function usertestCurrentPageUrlTraining(modX $modx)
    {
        $requestUri = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '';
        $isAction = (strpos($requestUri, '/assets/components/usertest/action.php') !== false);

        if ($isAction && !empty($_SERVER['HTTP_REFERER'])) {
            return (string)$_SERVER['HTTP_REFERER'];
        }

        if ($requestUri !== '') {
            $siteUrl = rtrim((string)$modx->getOption('site_url'), '/');
            return $siteUrl . $requestUri;
        }

        if (!empty($_SERVER['HTTP_REFERER'])) {
            return (string)$_SERVER['HTTP_REFERER'];
        }

        return '';
    }
}

if (!function_exists('usertestQuestionResultMetaTraining')) {
    function usertestQuestionResultMetaTraining(modX $modx, $question, $resultId)
    {
        $maxPoint = 0;
        if (is_object($question) && method_exists($question, 'get')) {
            $maxPoint = (float)$question->get('max_point');
            $questionId = (int)$question->get('id');
            $type = (int)$question->get('type');
            $questionText = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$question->get('question'))));
        } else {
            $questionId = 0;
            $type = 0;
            $questionText = '';
        }

        $meta = array(
            'answered' => false,
            'status_text' => '-',
            'status_class' => 'is-empty',
            'point_text' => usertestFormatPointTraining(0) . ' / ' . usertestFormatPointTraining($maxPoint),
            'title' => $questionText,
        );

        if ($questionId <= 0 || (int)$resultId <= 0) {
            return $meta;
        }

        $resAns = $modx->getObject('UserTestResultAnswers', array(
            'result_id' => (int)$resultId,
            'question_id' => $questionId,
        ));
        if (!$resAns) {
            return $meta;
        }

        $point = (float)$resAns->get('point');
        $meta['answered'] = true;

        switch ($type) {
            case 1:
            case 12:
                $answerId = (int)$resAns->get('answer_id');
                if ($answerId > 0) {
                    $answerObj = $modx->getObject('UserTestAnswers', $answerId);
                    if ($answerObj && (int)$answerObj->get('right') === 1) {
                        $point = $maxPoint > 0 ? $maxPoint : max($point, (float)$answerObj->get('point'));
                    }
                }
                $meta['status_text'] = $point > 0 ? 'Правильно' : 'Неправильно';
                $meta['status_class'] = $point > 0 ? 'is-correct' : 'is-wrong';
                break;

            case 2:
                $selected = usertestNormalizeIdsTraining($resAns->get('answer_ids'));
                $rightIds = array();
                $c = $modx->newQuery('UserTestAnswers');
                $c->select(array('id'));
                $c->where(array(
                    'question_id' => $questionId,
                    'right' => 1,
                ));
                if ($answers = $modx->getIterator('UserTestAnswers', $c)) {
                    foreach ($answers as $answerObj) {
                        $rightIds[] = (int)$answerObj->get('id');
                    }
                }
                $rightIds = usertestNormalizeIdsTraining($rightIds);

                if ($selected === $rightIds && !empty($selected)) {
                    $point = $maxPoint > 0 ? $maxPoint : $point;
                    $meta['status_text'] = 'Правильно';
                    $meta['status_class'] = 'is-correct';
                } elseif (!empty(array_intersect($selected, $rightIds))) {
                    if ($point <= 0 && $maxPoint > 0) {
                        $point = round($maxPoint / 2, 2);
                    }
                    $meta['status_text'] = 'Частично верно';
                    $meta['status_class'] = 'is-partial';
                } else {
                    $meta['status_text'] = 'Неправильно';
                    $meta['status_class'] = 'is-wrong';
                }
                break;

            case 3:
                if ($point <= 0 && trim((string)$resAns->get('answer')) !== '') {
                    $point = $maxPoint > 0 ? $maxPoint : $point;
                }
                $meta['status_text'] = $point > 0 ? 'Правильно' : 'Неправильно';
                $meta['status_class'] = $point > 0 ? 'is-correct' : 'is-wrong';
                break;

            case 4:
                $meta['status_text'] = 'На проверке';
                $meta['status_class'] = 'is-review';
                break;

            default:
                $meta['status_text'] = $point > 0 ? 'Правильно' : 'Заполнен';
                $meta['status_class'] = $point > 0 ? 'is-correct' : 'is-filled';
                break;
        }

        $meta['point_text'] = usertestFormatPointTraining($point) . ' / ' . usertestFormatPointTraining($maxPoint);
        return $meta;
    }
}


if (!function_exists('usertestQuestionBelongsToTestTraining')) {
    function usertestQuestionBelongsToTestTraining(modX $modx, $questionId, $testId)
    {
        $questionId = (int)$questionId;
        $testId = (int)$testId;
        if ($questionId <= 0 || $testId <= 0) {
            return true;
        }

        return (bool)$modx->getCount('UserTestTestQuestionLink', array(
            'test_id' => $testId,
            'question_id' => $questionId,
        ));
    }
}


if (!function_exists('usertestBuildBlockQuestionTraining')) {
    function usertestBuildBlockQuestionTraining(modX $modx, $steps, $currentStep, $resultId, $testId = 0)
    {
        $block = array();
        $seenQuestionIds = array();
        if (!is_array($steps) || empty($steps)) {
            return $block;
        }

        foreach ($steps as $kStep => $step) {
            if (empty($step['question_ids']) || !is_array($step['question_ids'])) {
                continue;
            }

            $curStepCheck = ((string)$currentStep === (string)$kStep);

            foreach ($step['question_ids'] as $qIdMeta) {
                if (empty($qIdMeta['id'])) {
                    continue;
                }

                $questionId = (int)$qIdMeta['id'];
                if ($questionId <= 0) {
                    continue;
                }
                if (isset($seenQuestionIds[$questionId])) {
                    continue;
                }
                if ((int)$testId > 0 && !usertestQuestionBelongsToTestTraining($modx, $questionId, (int)$testId)) {
                    continue;
                }

                $q = $modx->getObject('UserTestQuestions', $questionId);
                if (!$q) {
                    continue;
                }
                $seenQuestionIds[$questionId] = true;

                $resultMeta = usertestQuestionResultMetaTraining($modx, $q, (int)$resultId);
                $statusText = $resultMeta['status_text'];
                $statusClass = $resultMeta['status_class'];

                if ($curStepCheck && !$resultMeta['answered']) {
                    $statusText = 'Текущий';
                    $statusClass = 'is-current';
                }

                $block[] = array(
                    'question_id' => (int)$qIdMeta['id'],
                    'numberQ' => isset($qIdMeta['numberQ']) ? (int)$qIdMeta['numberQ'] : 0,
                    'title' => trim(preg_replace('/\s+/u', ' ', strip_tags((string)$q->get('question')))),
                    'curStepCheck' => $curStepCheck,
                    'step' => $kStep,
                    'status_text' => $statusText,
                    'status_class' => $statusClass,
                    'point_text' => $resultMeta['point_text'],
                );
            }
        }

        return $block;
    }
}


if (!function_exists('usertestStepAnsweredTraining')) {
    function usertestStepAnsweredTraining($stepData)
    {
        if (empty($stepData['question_ids']) || !is_array($stepData['question_ids'])) {
            return true;
        }

        foreach ($stepData['question_ids'] as $qMeta) {
            if (!array_key_exists('ans', $qMeta)) {
                return false;
            }
            $ans = $qMeta['ans'];
            if (is_array($ans)) {
                if (empty($ans)) {
                    return false;
                }
                $has = false;
                foreach ($ans as $v) {
                    if (is_array($v)) {
                        if (!empty($v)) {
                            $has = true;
                            break;
                        }
                    } elseif (trim((string)$v) !== '' && (string)$v !== '0') {
                        $has = true;
                        break;
                    }
                }
                if (!$has) {
                    return false;
                }
            } elseif (trim((string)$ans) === '' || (string)$ans === '0') {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('usertestResolveSafeStepTraining')) {
    function usertestResolveSafeStepTraining($steps, $requestedStep, $fallbackStep = 1)
    {
        if (!is_array($steps) || empty($steps)) {
            return $requestedStep;
        }

        if ($requestedStep === 'start') {
            return $requestedStep;
        }

        $requestedFinish = ($requestedStep === 'finish');
        if ($requestedFinish) {
            $allAnswered = true;
            foreach ($steps as $idx => $stepData) {
                if (!is_numeric($idx) || (int)$idx <= 0) {
                    continue;
                }
                if (!usertestStepAnsweredTraining($stepData)) {
                    $allAnswered = false;
                    break;
                }
            }
            if ($allAnswered) {
                return 'finish';
            }
            $requestedStep = 1;
        }

        $requestedStep = (int)$requestedStep;
        if ($requestedStep <= 0) {
            $requestedStep = (int)$fallbackStep;
        }
        if ($requestedStep <= 0) {
            $requestedStep = 1;
        }

        $maxAllowed = 1;
        foreach ($steps as $idx => $stepData) {
            if (!is_numeric($idx) || (int)$idx <= 0) {
                continue;
            }
            $idx = (int)$idx;
            if (!isset($steps[$idx])) {
                continue;
            }
            if ($idx > $maxAllowed) {
                break;
            }
            if (usertestStepAnsweredTraining($steps[$idx])) {
                $nextIdx = $idx + 1;
                if (isset($steps[$nextIdx])) {
                    $maxAllowed = max($maxAllowed, $nextIdx);
                } else {
                    $maxAllowed = max($maxAllowed, $idx);
                }
            }
        }

        if ($requestedStep > $maxAllowed) {
            return $maxAllowed;
        }

        if (!isset($steps[$requestedStep])) {
            return $maxAllowed;
        }

        return $requestedStep;
    }
}


//$tpl = 'tpl.UserTest.main2';
$answer_page_id = $modx->getOption('answer_page_id', $scriptProperties, 0);

$skip_save = false;
$isPostRequest = (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST');
$questions_post = array();
$result_id = 0;
$result_status = 0;
$prevStep = '';
$nextStep = '';
$questions = array();
$cat_results = array();
$cat_history = array();
$cat_email_results = '';
$test_time = 0;
$user_name = '';
$user_email = '';
$answer_page_url = '';
$test_url = '';
$is_timeout = false;

//load js and css
$modx->regClientCSS($modx->getOption('assets_url').$frontend_css);

$actionUrl = $modx->getOption('assets_url') . 'components/usertest/action.php';
$modx->regClientScript($modx->getOption('assets_url'). "components/usertest/js/web/Sortable.min.js");
$modx->regClientScript($modx->getOption('assets_url'). "components/usertest/js/web/jquery.countdown.min.js");

$id = $modx->getOption('id', $scriptProperties, 0);
if(!$id){
    if(!$id = $UserTest->fooClearPostGet('test_id', 'fooIntAbsClear')){
        return $pdoFetch->getChunk($tplError, ['error'=>$modx->lexicon('usertest_snippet_not_test_id')]);
    }
}
unset($_POST['test_id']);
if($AjaxMode){
    $modx->regClientScript($modx->getOption('assets_url').$frontend_js);
    $modx->regClientScript(
            "<script type=\"text/javascript\">UserTestActionUrl = '{$actionUrl}';</script>", true
        );
}
$check_ajax = $modx->getOption('check_ajax', $scriptProperties, 0);
if (!$check_ajax) {
    $_SESSION['UserTest'][$id]['sp'] = $scriptProperties;
} else {
    if (empty($_SESSION['UserTest'][$id]['sp']) || !is_array($_SESSION['UserTest'][$id]['sp'])) {
        $_SESSION['UserTest'][$id]['sp'] = $scriptProperties;
    }
    $scriptProperties = array_merge($_SESSION['UserTest'][$id]['sp'], $scriptProperties);
}

$trainingBaseUrl = (string)$modx->getOption('training_activity_page_url', $scriptProperties, '');
if ($trainingBaseUrl === '') {
    $trainingBaseUrl = (string)$modx->getPlaceholder('training_activity_page_url');
}
if ($trainingBaseUrl === '') {
    $trainingBaseUrl = usertestCurrentPageUrlTraining($modx);
}

$trainingActivityId = (int)$modx->getOption('training_activity_id', $scriptProperties, (int)$modx->getPlaceholder('training_activity_test_link_id'));
$trainingBackUrl = trim((string)$modx->getOption('training_activity_back_url', $scriptProperties, (string)$modx->getPlaceholder('training_activity_back_url')));
$trainingRestartUrl = trim((string)$modx->getOption('training_activity_restart_url', $scriptProperties, (string)$modx->getPlaceholder('training_activity_restart_url')));
$trainingAnswerUrlTpl = trim((string)$modx->getOption('training_activity_answer_url', $scriptProperties, (string)$modx->getPlaceholder('training_activity_answer_url')));
$trainingMinPassPercentProp = $modx->getOption('training_activity_min_pass_percent', $scriptProperties, $modx->getPlaceholder('training_activity_min_pass_percent'));
$trainingCourseIdProp = (int)$modx->getOption('training_activity_course_id', $scriptProperties, (int)$modx->getPlaceholder('training_activity_course_id'));
$trainingModuleIdProp = (int)$modx->getOption('training_activity_module_id', $scriptProperties, (int)$modx->getPlaceholder('training_activity_module_id'));

// При AJAX-запросах action.php не знает resource/activity/back.
// Поэтому добираем training-параметры из POST/GET и сохраненной сессии.
$trainingRequestMap = array(
    'training_activity_id' => 'trainingActivityId',
    'training_activity_page_url' => 'trainingBaseUrl',
    'training_activity_back_url' => 'trainingBackUrl',
    'training_activity_restart_url' => 'trainingRestartUrl',
    'training_activity_answer_url' => 'trainingAnswerUrlTpl',
    'training_activity_min_pass_percent' => 'trainingMinPassPercentProp',
    'training_activity_course_id' => 'trainingCourseIdProp',
    'training_activity_module_id' => 'trainingModuleIdProp',
);
foreach ($trainingRequestMap as $requestKey => $varName) {
    if (!isset($_REQUEST[$requestKey]) || $_REQUEST[$requestKey] === '') {
        continue;
    }
    switch ($varName) {
        case 'trainingActivityId':
            if ($trainingActivityId <= 0) {
                $trainingActivityId = (int)$_REQUEST[$requestKey];
            }
            break;
        case 'trainingCourseIdProp':
            if ($trainingCourseIdProp <= 0) {
                $trainingCourseIdProp = (int)$_REQUEST[$requestKey];
            }
            break;
        case 'trainingModuleIdProp':
            if ($trainingModuleIdProp <= 0) {
                $trainingModuleIdProp = (int)$_REQUEST[$requestKey];
            }
            break;
        case 'trainingMinPassPercentProp':
            if ($trainingMinPassPercentProp === null || $trainingMinPassPercentProp === '' || (float)$trainingMinPassPercentProp <= 0) {
                $trainingMinPassPercentProp = $_REQUEST[$requestKey];
            }
            break;
        case 'trainingBaseUrl':
            if ($trainingBaseUrl === '') {
                $trainingBaseUrl = (string)$_REQUEST[$requestKey];
            }
            break;
        case 'trainingBackUrl':
            if ($trainingBackUrl === '') {
                $trainingBackUrl = trim((string)$_REQUEST[$requestKey]);
            }
            break;
        case 'trainingRestartUrl':
            if ($trainingRestartUrl === '') {
                $trainingRestartUrl = trim((string)$_REQUEST[$requestKey]);
            }
            break;
        case 'trainingAnswerUrlTpl':
            if ($trainingAnswerUrlTpl === '') {
                $trainingAnswerUrlTpl = trim((string)$_REQUEST[$requestKey]);
            }
            break;
    }
}

$trainingActivityLink = null;
if ($trainingActivityId > 0) {
    $trainingCorePathOption = $modx->getOption('training.core_path', null, $modx->getOption('core_path') . 'components/training/');
    $trainingModelPath = rtrim($trainingCorePathOption, '/\\') . '/model/';
    if (is_dir($trainingModelPath)) {
        $modx->addPackage('training', $trainingModelPath);
    }
    $trainingActivityLink = $modx->getObject('TrainingTestLink', $trainingActivityId);
    if ($trainingActivityLink) {
        if ($trainingMinPassPercentProp === null || $trainingMinPassPercentProp === '') {
            $trainingMinPassPercentProp = $trainingActivityLink->get('min_pass_percent');
        }
        if ($trainingCourseIdProp <= 0) {
            $trainingCourseIdProp = (int)$trainingActivityLink->get('course_id');
        }
        if ($trainingModuleIdProp <= 0) {
            $trainingModuleIdProp = (int)$trainingActivityLink->get('module_id');
        }
    }
}

if ($trainingRestartUrl === '' && $trainingBaseUrl !== '') {
    $trainingRestartUrl = usertestBuildUrlTraining($trainingBaseUrl, array(
        'screen' => 'test',
        'step' => 'start',
        'reset' => 1,
    ));
}
if ($trainingAnswerUrlTpl === '' && $trainingBaseUrl !== '') {
    $trainingAnswerUrlTpl = usertestBuildUrlTraining($trainingBaseUrl, array(
        'screen' => 'answers',
        'result_id' => '__RESULT_ID__',
        'step' => null,
        'reset' => null,
    ));
}

if ($trainingRestartUrl !== '') {
    $modx->setPlaceholder('training_activity_restart_url', $trainingRestartUrl);
}
if ($trainingAnswerUrlTpl !== '') {
    $modx->setPlaceholder('training_activity_answer_url', $trainingAnswerUrlTpl);
}
if ($trainingBackUrl !== '') {
    $modx->setPlaceholder('training_activity_back_url', $trainingBackUrl);
}
if ($trainingMinPassPercentProp !== null && $trainingMinPassPercentProp !== '') {
    $modx->setPlaceholder('training_activity_min_pass_percent', $trainingMinPassPercentProp);
}
if ($trainingCourseIdProp > 0) {
    $modx->setPlaceholder('training_activity_course_id', $trainingCourseIdProp);
}
if ($trainingModuleIdProp > 0) {
    $modx->setPlaceholder('training_activity_module_id', $trainingModuleIdProp);
}

if (isset($_SESSION['UserTest'][$id]['sp']) && is_array($_SESSION['UserTest'][$id]['sp'])) {
    $_SESSION['UserTest'][$id]['sp']['training_activity_id'] = $trainingActivityId;
    $_SESSION['UserTest'][$id]['sp']['training_activity_page_url'] = $trainingBaseUrl;
    $_SESSION['UserTest'][$id]['sp']['training_activity_course_id'] = $trainingCourseIdProp;
    $_SESSION['UserTest'][$id]['sp']['training_activity_module_id'] = $trainingModuleIdProp;
    $_SESSION['UserTest'][$id]['sp']['training_activity_min_pass_percent'] = $trainingMinPassPercentProp;
    $_SESSION['UserTest'][$id]['sp']['training_activity_back_url'] = $trainingBackUrl;
    $_SESSION['UserTest'][$id]['sp']['training_activity_restart_url'] = $trainingRestartUrl;
    $_SESSION['UserTest'][$id]['sp']['training_activity_answer_url'] = $trainingAnswerUrlTpl;
}

$tpl = $modx->getOption('tpl', $scriptProperties, 'tpl.UserTest.main');
$tplError = $modx->getOption('tplError', $scriptProperties, 'tpl.UserTest.error');

$answer_step = $UserTest->fooClearPostGet('answer_step', 'fooStrClear');
unset($_POST['answer_step']);

if(!$test = $modx->getObject('UserTestTests',$id)){
    return $pdoFetch->getChunk($tplError, ['error'=>$modx->lexicon('usertest_snippet_not_found_test')]);
}

if($test->test_type == 2) $modx->regClientScript($modx->getOption('assets_url'). "components/usertest/canvasjs/jquery.canvasjs.min.js");
if($test->use_category){
    $modx->regClientScript($modx->getOption('assets_url'). "components/usertest/pie_chart/Chart.bundle.js");
    $modx->regClientScript($modx->getOption('assets_url'). "components/usertest/pie_chart/utils.js");
}
//echo $test->pub_date.' '.time();
if($test->pub_date and $test->pub_date > time()){
    return $pdoFetch->getChunk($tplError, ['error'=>$modx->lexicon('usertest_snippet_not_publish_test')]);
}
if($test->unpub_date and $test->unpub_date < time()){
    return $pdoFetch->getChunk($tplError, ['error'=>$modx->lexicon('usertest_snippet_end_publish_test_time')]);
}
$user_id = $modx->user->get('id');
if($user_id > 0){
    if($test->count_test_answer > 0){
        $ResultCount = $modx->getCount('UserTestResults', array('user_id'=>$user_id,'test_id'=>$test->id, 'status_id:IN'=>array(2,3)));
        if($ResultCount >= $test->count_test_answer){
            return $pdoFetch->getChunk($tplError, ['error'=>$modx->lexicon('usertest_snippet_end_test_count_attempts')]);
        }
    }
}

$block_q_number = false;
$check_restore = false;
$curStepRequest = $UserTest->fooClearPostGet('step', 'fooStrClear');
unset($_POST['step']);
$curStep = $curStepRequest;
$activeUnfinishedResult = null;
$resetRequested = isset($_GET['reset']) || isset($_REQUEST['reset']);

if ($resetRequested) {
    $preservedSp = array();
    if (isset($_SESSION['UserTest'][$id]['sp']) && is_array($_SESSION['UserTest'][$id]['sp'])) {
        $preservedSp = $_SESSION['UserTest'][$id]['sp'];
    }

    unset($_SESSION['UserTest'][$id]);

    if (!isset($_SESSION['UserTest'][$id]) || !is_array($_SESSION['UserTest'][$id])) {
        $_SESSION['UserTest'][$id] = array();
    }
    $_SESSION['UserTest'][$id]['sp'] = array_merge($preservedSp, $scriptProperties, array(
        'training_activity_id' => $trainingActivityId,
        'training_activity_page_url' => $trainingBaseUrl,
        'training_activity_course_id' => $trainingCourseIdProp,
        'training_activity_module_id' => $trainingModuleIdProp,
        'training_activity_min_pass_percent' => $trainingMinPassPercentProp,
        'training_activity_back_url' => $trainingBackUrl,
        'training_activity_restart_url' => $trainingRestartUrl,
        'training_activity_answer_url' => $trainingAnswerUrlTpl,
    ));

    if ($user_id > 0) {
        $cReset = $modx->newQuery('UserTestResults');
        $cReset->where(array(
            'user_id' => $user_id,
            'test_id' => $test->id,
            'status_id' => 1,
        ));
        $activeResults = $modx->getIterator('UserTestResults', $cReset);
        foreach ($activeResults as $activeResult) {
            $activeResult->set('status_id', 4);
            $activeResult->set('session', '');
            $activeResult->save();
        }
    }
}

if ($user_id > 0 && !$resetRequested) {
    $cActive = $modx->newQuery('UserTestResults');
    $cActive->where(array(
        'user_id' => $user_id,
        'test_id' => $test->id,
        'status_id' => 1,
    ));
    $cActive->sortby('id', 'DESC');
    $cActive->limit(1);
    $activeUnfinishedResult = $modx->getObject('UserTestResults', $cActive);

    if ($activeUnfinishedResult && !isset($_GET['result_id'])) {
        $activeResultId = (int)$activeUnfinishedResult->get('id');
        $localResultId = !empty($_SESSION['UserTest'][$id]['result_id']) ? (int)$_SESSION['UserTest'][$id]['result_id'] : 0;

        if (!$isPostRequest || $localResultId !== $activeResultId || empty($_SESSION['UserTest'][$id]['steps'])) {
            $activeSession = json_decode((string)$activeUnfinishedResult->get('session'), true);
            if (is_array($activeSession) && !empty($activeSession)) {
                $_SESSION['UserTest'][$id] = $activeSession;
            }
        }

        $_SESSION['UserTest'][$id]['result_id'] = $activeResultId;
        $check_restore = true;

        if (!$isPostRequest) {
            $savedStep = !empty($_SESSION['UserTest'][$id]['curStep']) ? $_SESSION['UserTest'][$id]['curStep'] : '';
            if ($savedStep !== '' && ($curStepRequest === '' || $curStepRequest === 'start')) {
                $curStep = $savedStep;
                $skip_save = true;
            }
        }
    }
}

$hasPostedQuestion = $isPostRequest && isset($_POST['question']) && is_array($_POST['question']) && !empty($_POST['question']);
if ($hasPostedQuestion && $answer_step !== '' && $answer_step !== 'start') {
    $curStep = $answer_step;
}

if(isset($_SESSION['UserTest'][$id]['result_id'])){
    $check_restore = true;
}

if($curStep == "start"){
    if(!isset($_SESSION['UserTest'][$id]['result_id'])){	
        if($Result = $modx->newObject('UserTestResults')){
            if($modx->user->get('id') > 0){
                $Result->user_id = $modx->user->get('id');
                $profile = $modx->user->getOne('Profile');
                $Result->user_name = $profile->get('fullname');
                $Result->user_email = $profile->get('email');
            }
            if($test->ask_user_data and isset($_POST["userdata"])){
                $userdata = $UserTest->fooClearArr($_POST["userdata"]);
                if($modx->user->get('id') == 0 and $modx->getOption('usertest_create_modx_users')){
                    $Result->user_id = $UserTest->createUser($userdata);
                }
                $Result->user_name = $userdata["name"];//$_POST["userdata"]["name"];
                $Result->user_email = $userdata["email"];//$_POST["userdata"]["email"];
                $Result->properties = json_encode(array("userdata"=>$userdata));
                
            }
            $Result->test_id = $id;
            $Result->status_id = 1;
            $Result->date = strftime('%Y-%m-%d %H:%M:%S');
            $Result->save();
            $_SESSION['UserTest'][$id]['result_id'] = $Result->id;
        }
    }
}

if($curStep == "start") $curStep = 1;
if(!$curStep){
    if (!empty($_SESSION['UserTest'][$id]['curStep'])) {
        $curStep = $_SESSION['UserTest'][$id]['curStep'];
    } else {
        $curStep = "start";
    }
}
$curStep2 = $curStep;

//ссылка для возврата к тесту
if($modx->resource){
    $_SESSION['UserTestUrl'][$id]['test_url_id'] = $modx->resource->id;
}

//ссылка на ответы теста
if($answer_page_id){
    $_SESSION['UserTestUrl'][$id]['answer_page_id'] = $answer_page_id;
}
//Занесение вопросов в сессию. подготовка
if($curStep == 1 and !isset($_SESSION['UserTest'][$id]['steps'])){
    $c = $modx->newQuery('UserTestQuestions');
    $c->leftJoin('UserTestTestQuestionLink','UserTestTestQuestionLink', '`UserTestTestQuestionLink`.`question_id` = `UserTestQuestions`.`id`');
    $c->select($modx->getSelectColumns('UserTestQuestions','UserTestQuestions','',array(
        'id',
        'parent',
        'category_id',
        'question',
        'type',
        'type_file',
        'file',
        'extended',
        'max_point'
        )));
    $c->select($modx->getSelectColumns('UserTestTestQuestionLink','UserTestTestQuestionLink','',array(
        'menuindex',
        'test_id',
        )));
    if($test->count_questions > 0){
        $c->sortby('RAND()');
        $c->limit($test->count_questions);
    }else{
        $c->sortby('`UserTestTestQuestionLink`.`menuindex`', 'ASC');
    }
    $c->where(array('`UserTestTestQuestionLink`.`test_id`'=>$id,'`UserTestQuestions`.`parent`'=>0));
    
    $Questions = $modx->getCollection('UserTestQuestions', $c);
    //$countQ = $modx->getCount('UserTestQuestions', $c);
    $step = 1;
    $n = 1;
    $numberQ = 1;
    $steps = array();
    $steps[0] = array('id'=>"start", 'question_ids'=>array());
    $question_ids = array();
    $q_ids = array();
    foreach($Questions as $q){
        $question_ids[$n]=array('id'=>$q->id, 'ans'=>0, 'numberQ'=>$numberQ);
        $q_ids[] = $q->id;
        $n++; $numberQ++;
        if($test->count_questions_on_page > 0 and $n > $test->count_questions_on_page){
            $steps[] = array('id'=>$step, 'question_ids'=>$question_ids);
            $question_ids = array();
            $step++;
            $n = 1;
        }
    }
    if(count($question_ids)>0){
        $steps[$step] = array('id'=>$step, 'question_ids'=>$question_ids);
        $steps[$step+1] = array('id'=>"finish", 'question_ids'=>array());
    }else{
        $steps[$step] = array('id'=>"finish", 'question_ids'=>array());
    }
    
    
    $_SESSION['UserTest'][$id]['steps'] = $steps;
    $_SESSION['UserTest'][$id]['countQ'] = $numberQ - 1; //$countQ;
    $_SESSION['UserTest'][$id]['q_ids'] = $q_ids;
    
    $_SESSION['UserTest'][$id]['test_time_start'] = time();
}
if ($curStep !== "start" && isset($_SESSION['UserTest'][$id]['steps']) && is_array($_SESSION['UserTest'][$id]['steps'])) {
    $fallbackStep = !empty($_SESSION['UserTest'][$id]['curStep']) ? $_SESSION['UserTest'][$id]['curStep'] : 1;
    $curStep = usertestResolveSafeStepTraining($_SESSION['UserTest'][$id]['steps'], $curStep, $fallbackStep);
    $curStep2 = $curStep;
}

if(!isset($_SESSION['UserTest'][$id]) and $curStep != "start"){
    return $pdoFetch->getChunk($tplError, ['error'=>$modx->lexicon('usertest_snippet_end_test_renew')]);
}

//подготовка вопросов и ответов на вывод
if($curStep != "start" and $curStep != "finish"){
    $steps = $_SESSION['UserTest'][$id]['steps'];
    //print_r($steps);
    $prevStep = $steps[$curStep-1]['id'];
    $nextStep = $steps[$curStep+1]['id'];
    $question_ids = $steps[$curStep]['question_ids'];
    $questions = array();
    foreach($question_ids as $q_id1 => $q_id){
        if($q = $modx->getObject('UserTestQuestions', $q_id['id'])){
            $question = $q->toArray();
            $question['numberQ'] = $q_id['numberQ'];
            $question['countQ'] = $_SESSION['UserTest'][$id]['countQ'];
            switch($q->type){
                case 1: //Одиночный выбор
                case 12: //Опросник САН
                    $c = $modx->newQuery('UserTestAnswers');
                    if($q->random_answer){
                        $c->sortby('RAND()');
                    }else{
                        $c->sortby('menuindex', 'ASC');
                    }
                    $c->where(array('question_id'=>$q_id['id']));
                    $Answers = $modx->getIterator('UserTestAnswers', $c);
                    $Ans = array();
                    foreach($Answers as $a){
                        $Ans[] = $a->toArray();
                    }
                    $question['answers'] = $Ans;
                    $question['answer_id'] = (int)$q_id['ans'];
                    $question['is_answered'] = !empty($question['answer_id']);
                    $questions[] = $question;
                    break;
                case 2: //Множественный выбор
                    $c = $modx->newQuery('UserTestAnswers');
                    if($q->random_answer){
                        $c->sortby('RAND()');
                    }else{
                        $c->sortby('menuindex', 'ASC');
                    }
                    $c->where(array('question_id'=>$q_id['id']));
                    $Answers = $modx->getIterator('UserTestAnswers', $c);
                    $Ans = array();
                    foreach($Answers as $a){
                        $a1 = $a->toArray();
                        if (in_array($a->id, $q_id['ans'])) {
                            $a1['check'] = 1;
                        }else{
                            $a1['check'] = 0;
                        }
                        $Ans[] = $a1;
                    }
                    $question['answers'] = $Ans;
                    $question['is_answered'] = false;
                    foreach ($Ans as $answerMeta) {
                        if (!empty($answerMeta['check'])) {
                            $question['is_answered'] = true;
                            break;
                        }
                    }
                    $questions[] = $question;
                    break;
                case 3: //Простой текст
                    $question['answer'] = "";
                    if($q_id['ans']){
                        $question['answer'] = $q_id['ans'];
                    }
                    $question['is_answered'] = $question['answer'] !== "";
                    $questions[] = $question;
                    break;
                case 4: //Открытый вопрос
                    $question['answer'] = "";
                    if($q_id['ans']){
                        $question['answer'] = $q_id['ans'];
                    }
                    $question['is_answered'] = $question['answer'] !== "";
                    $questions[] = $question;
                    break;
                case 5: //На сопоставление. Простой
                    $ext = json_decode($question['extended'], 1);
                    $question['q'] = $ext['q'];
                    $a = $ext['a'];
                    if(!$q_id['ans']){
                        //сортировка массива в случайном порядке
                        $key = array_keys($a);
                        shuffle($key);
                        $_SESSION['UserTest'][$id]['steps'][$curStep]['question_ids'][$q_id1]['key'] = $key;
                        //print_r($_SESSION['UserTest'][$id]['steps'][$curStep]['question_ids'][$q_id1]);
                        $q_id['key'] = $key;
                        $q_id['ans'] = array_keys($key);
                    }
                    $key = $q_id['key'];
                    $a1 = array();
                    foreach($q_id['ans'] as $k => $v){
                        $a1[$v] = $a[$key[$v]];
                    }
                    $question['a'] = $a1;
                    $question['answer'] = implode('|',$q_id['ans']);
                    $questions[] = $question;
                    //print_r($q_id);
                    break;
                case 6: //Комбинированный вариант
                    $c = $modx->newQuery('UserTestAnswers');
                    if($q->random_answer){
                        $c->sortby('RAND()');
                    }else{
                        $c->sortby('menuindex', 'ASC');
                    }
                    $c->where(array('question_id'=>$q_id['id']));
                    $Answers = $modx->getIterator('UserTestAnswers', $c);
                    $Ans = array();
                    foreach($Answers as $a){
                        $a1 = $a->toArray();
                        if (in_array($a->id, $q_id['ans'])) {
                            $a1['check'] = 1;
                        }else{
                            $a1['check'] = 0;
                        }
                        $Ans[] = $a1;
                    }
                    $question['answers'] = $Ans;
                    if(isset($q_id['ans_add'])){
                        $question['answers_add'] = $q_id['ans_add'];
                    }else{
                        $question['answers_add'] = array(
                                    'check' => 0,
                                    'ans' => "",
                                    );
                    }
                    
                    $questions[] = $question;
                    break;
                case 7: //Таблица чек-боксов
                    $q_childs = $modx->getIterator('UserTestQuestions', array('parent' =>$q_id['id']));
                    foreach($q_childs as $k_child=>$q_child){
                        $c = $modx->newQuery('UserTestAnswers');
                        $c->sortby('menuindex', 'ASC');
                        $c->where(array('question_id'=>$q_child->id));
                        $Answers = $modx->getIterator('UserTestAnswers', $c);
                        $Ans_header = array();
                        $Ans_header[] = "";
                        if($k_child == 1){
                            foreach($Answers as $a){
                                $Ans_header[] = $a->answer;
                            }
                        }
                        $Ans = array();
                        foreach($Answers as $a){
                            $a1 = $a->toArray();
                            if (in_array($a->id, $q_id['ans'][$q_child->id])) {
                                $a1['check'] = 1;
                            }else{
                                $a1['check'] = 0;
                            }
                            $Ans[] = $a1;
                        }
                        $question['header'] = $Ans_header;
                        $qc = $q_child->toArray();
                        $qc['answers'] = $Ans;
                        $question['q_childs'][] = $qc;
                        //$question['answers'] = $Ans;
                    }
                    $questions[] = $question;
                    break;
                case 8: //Таблица текстовых полей
                    $q_childs = $modx->getIterator('UserTestQuestions', array('parent' =>$q_id['id']));
                    foreach($q_childs as $k_child=>$q_child){
                        $c = $modx->newQuery('UserTestAnswers');
                        $c->sortby('menuindex', 'ASC');
                        $c->where(array('question_id'=>$q_child->id));
                        $Answers = $modx->getIterator('UserTestAnswers', $c);
                        $Ans_header = array();
                        $Ans_header[] = "";
                        if($k_child = 1){
                            foreach($Answers as $a){
                                $Ans_header[] = $a->answer;
                            }
                        }
                        $Ans = array();
                        foreach($Answers as $a){
                            $a1 = $a->toArray();
                            if(isset($q_id['ans'][$q_child->id][$a->id])) $a1['ac'] = $q_id['ans'][$q_child->id][$a->id];
                            $Ans[] = $a1;
                        }
                        $question['header'] = $Ans_header;
                        $qc = $q_child->toArray();
                        $qc['answers'] = $Ans;
                        $question['q_childs'][] = $qc;
                        $question['answers'] = $Ans;
                    }
                    $questions[] = $question;
                    break;
                case 9: //Селекты в тексте
                    $q_childs = $modx->getIterator('UserTestQuestions', array('parent' =>$q_id['id']));
                    $q_str = $q->question;
                    foreach($q_childs as $k_child=>$q_child){
                        $c = $modx->newQuery('UserTestAnswers');
                        if($q->random_answer){
                            $c->sortby('RAND()');
                        }else{
                            $c->sortby('menuindex', 'ASC');
                        }
                        $c->where(array('question_id'=>$q_child->id));
                        $Answers = $modx->getIterator('UserTestAnswers', $c);
                        $opt = "";
                        $opt .= $pdoFetch->getChunk('@INLINE <option value="{$id}">{$answer}</option>', array(
                                'id'=>0,
                                'answer'=>"Выбрать",
                                ));
                        foreach($Answers as $a){
                            if(isset($q_id['ans'][$q_child->id]) and $q_id['ans'][$q_child->id] == $a->id){
                                $opt .= $pdoFetch->getChunk('@INLINE <option value="{$id}" selected>{$answer}</option>', array(
                                'id'=>$a->id,
                                'answer'=>$a->answer,
                                ));
                            }else{
                                $opt .= $pdoFetch->getChunk('@INLINE <option value="{$id}">{$answer}</option>', array(
                                'id'=>$a->id,
                                'answer'=>$a->answer,
                                ));
                            }
                        }
                        $sel = $pdoFetch->getChunk('@INLINE <select class="select-in-text" name="question[{$q_id}][{$qc_id}]">{$opt}</select>', array(
                        'q_id'=>$q_id['id'],
                        'qc_id'=>$q_child->id,
                        'opt'=>$opt,
                        ));
                        //echo $sel.' '.$q_str;
                        $q_str = str_replace('[['.$q_child->question.']]',$sel,$q_str);
                    }
                    $question['q_str'] = $q_str;
                    $questions[] = $question;
                    break;
                case 10: //Комбинированный одиночный выбор
                    //print_r($q_id);
                    $c = $modx->newQuery('UserTestAnswers');
                    if($q->random_answer){
                        $c->sortby('RAND()');
                    }else{
                        $c->sortby('menuindex', 'ASC');
                    }
                    $c->where(array('question_id'=>$q_id['id']));
                    $Answers = $modx->getIterator('UserTestAnswers', $c);
                    $Ans = array();
                    foreach($Answers as $a){
                        $a1 = $a->toArray();
                        if ($a->id == $q_id['ans']['ans']) {
                            $a1['check'] = 1;
                        }else{
                            $a1['check'] = 0;
                        }
                        $Ans[] = $a1;
                    }
                    $question['answers'] = $Ans;
                    if($q_id['ans']['ans']=="ans_add"){
                        $question['answers_add'] = $q_id['ans_add'];
                    }else{
                        $question['answers_add'] = array(
                                    'check' => 0,
                                    'ans' => "",
                                    );
                    }
                    $questions[] = $question;
                    break;
            }
        }
    }
}
$test_point = 0;

//сохранение ответов теста в сессию и базу

if($hasPostedQuestion and $answer_step != "start" and !$skip_save){
    $sanitizePatterns = $modx->sanitizePatterns;
    $sanitizePatterns['fenom_syntax'] = '@\{(.*?)\}@si';
    if(isset($_POST['question']))
        $questions_post = $modx->sanitize($_POST['question'], $sanitizePatterns);
    //$questions_post = $_POST['question'];
    $result_id = $_SESSION['UserTest'][$id]['result_id'];
    $resAns = null;
    $steps = $_SESSION['UserTest'][$id]['steps'];
    $resStep = $answer_step;
    //echo $resStep;
    if($Result = $modx->getObject('UserTestResults',$result_id)){
        //print_r($_POST);
        //print_r($steps[$resStep]);
        if(is_array($questions_post) and !empty($questions_post)){
            foreach($questions_post as $q_id=>$a_id){
                if(!$q = $modx->getObject('UserTestQuestions', $q_id)){
                    $params = array(
                    'test_id'=>$id,
                    'reset'=>1,
                    );
                    $reset_url = $modx->getOption('site_url').$modx->makeUrl($_SESSION['UserTestUrl'][$id]['test_url_id'],'',$params);
                    return $pdoFetch->getChunk($tplError, ['error'=>$modx->lexicon('usertest_snippet_question_not_found',['q_id'=>$q_id]),'reset_url'=>$reset_url]);
                }
                switch($q->type){
                    case 1: //Одиночный выбор
                    case 12: //Опросник САН
                        foreach($steps[$resStep]['question_ids'] as $s_id=>$v){
                            if($v['id'] == $q_id){
                                $steps[$resStep]['question_ids'][$s_id]['ans'] = $a_id;
                            }
                        }
                        if(!$resAns = $modx->getObject('UserTestResultAnswers', array('result_id'=>$result_id, 'question_id'=>$q_id))){
                            $resAns = $modx->newObject('UserTestResultAnswers');
                        }
                        $resAns->result_id = $result_id;
                        $resAns->question_id = $q_id;
                        $resAns->answer_id = $a_id;
                        $point = 0;
                        if($a = $modx->getObject('UserTestAnswers',$a_id)){
                            $answer = $a->answer;
                            $point = $a->point;
                        }
                        $resAns->answer = $answer;
                        $resAns->point = $point;
                        $resAns->save();
                        break;
                    case 2: //Множественный выбор
                        foreach($steps[$resStep]['question_ids'] as $s_id=>$v){
                            if($v['id'] == $q_id){
                                $steps[$resStep]['question_ids'][$s_id]['ans'] = $a_id;
                            }
                        }
                        if(!$resAns = $modx->getObject('UserTestResultAnswers', array('result_id'=>$result_id, 'question_id'=>$q_id))){
                            $resAns = $modx->newObject('UserTestResultAnswers');
                        }
                        $resAns->result_id = $result_id;
                        $resAns->question_id = $q_id;
                        $resAns->answer_ids = implode(',', $a_id);
                        $answer = array(); $point = 0; $right = true;
                        foreach($a_id as $a_id1){
                            if($a = $modx->getObject('UserTestAnswers',$a_id1)){
                                $answer[] = $a->answer;
                                $point += $a->point;
                                if(!$a->right) $right = false;
                            }
                        }
                        $resAns->answer = implode(', ', $answer);
                        if(!$right) $point = 0;
                        $resAns->point = $point;
                        $resAns->save();
                        break;
                        
                    case 3: //Простой текст
                        foreach($steps[$resStep]['question_ids'] as $s_id=>$v){
                            if($v['id'] == $q_id){
                                $steps[$resStep]['question_ids'][$s_id]['ans'] = $a_id;
                            }
                        }
                        if(!$resAns = $modx->getObject('UserTestResultAnswers', array('result_id'=>$result_id, 'question_id'=>$q_id))){
                            $resAns = $modx->newObject('UserTestResultAnswers');
                        }
                        $resAns->result_id = $result_id;
                        $resAns->question_id = $q_id;
                        
                        $c = $modx->newQuery('UserTestAnswers');
                        $c->sortby('menuindex', 'ASC');
                        $c->where(array('question_id'=>$q_id));
                        $Answers = $modx->getIterator('UserTestAnswers', $c);
                        $point = 0; $ans_id = 0;
                        foreach($Answers as $a){
                            if($a_id == $a->answer){
                                $point = $a->point;
                                $ans_id = $a->id;
                                break;
                            }
                        }
                        $resAns->answer_id = $ans_id;
                        $resAns->answer = $a_id;
                        $resAns->point = $point;
                        $resAns->save();
                        break;
                    case 4: //Открытый вопрос
                        foreach($steps[$resStep]['question_ids'] as $s_id=>$v){
                            if($v['id'] == $q_id){
                                $steps[$resStep]['question_ids'][$s_id]['ans'] = $a_id;
                            }
                        }
                        if(!$resAns = $modx->getObject('UserTestResultAnswers', array('result_id'=>$result_id, 'question_id'=>$q_id))){
                            $resAns = $modx->newObject('UserTestResultAnswers');
                        }
                        $resAns->result_id = $result_id;
                        $resAns->question_id = $q_id;
                        $resAns->answer = $a_id;
                        $resAns->save();
                        break;
                    case 5: //На сопоставление. Простой
                        $ans = explode('|', $a_id); $key = array(); $point = 0;
                        foreach($steps[$resStep]['question_ids'] as $s_id=>$v){
                            if($v['id'] == $q_id){
                                $steps[$resStep]['question_ids'][$s_id]['ans'] = $ans;
                                $key = $steps[$resStep]['question_ids'][$s_id]['key'];
                            }
                        }
                        if (!empty($key)){
                            $ext = json_decode($q->extended, 1);
                            $q1 = $ext['q'];
                            $a1 = $ext['a'];
                            //$type_point = $ext['type_point']; //0 за правильный ответ. 1 за совпадения.
                            $result_ans = array(); $check = true; $ids = array();
                            foreach($ans as $k=>$a){
                                $result_ans[] = $q1[$k]." -> ". $a1[$key[$a]];
                                $ids[] = $key[$a]; 
                                if($k == $key[$a]){
                                    $point += $ext['point'];
                                }else{
                                    $check = false;
                                }
                            }
                        }
                        if($ext['type_point'] == 0){
                            if($check){
                                $point = $ext['point'];
                            }else{
                                $point = 0;
                            }
                        }
                        //echo implode('<br>', $result_ans);
                        if(!$resAns = $modx->getObject('UserTestResultAnswers', array('result_id'=>$result_id, 'question_id'=>$q_id))){
                            $resAns = $modx->newObject('UserTestResultAnswers');
                        }
                        $resAns->result_id = $result_id;
                        $resAns->question_id = $q_id;
                        $resAns->answer_ids = implode(',', $ids);
                        $resAns->answer = implode("\r\n", $result_ans);
                        $resAns->point = $point;
                        $resAns->save();
                        break;
                    case 6: //Комбинированный вариант
                        foreach($steps[$resStep]['question_ids'] as $s_id=>$v){
                            if($v['id'] == $q_id){
                                if(isset($a_id['ans_add'])){
                                    $steps[$resStep]['question_ids'][$s_id]['ans_add'] = array(
                                        'check' => 1,
                                        'ans' => $a_id['ans_add_ans'],
                                        );
                                }else{
                                    $steps[$resStep]['question_ids'][$s_id]['ans_add'] = array(
                                        'check' => 0,
                                        'ans' => "",
                                        );
                                }
                                $a_id_add = $steps[$resStep]['question_ids'][$s_id]['ans_add'];
                                unset($a_id['ans_add']);
                                unset($a_id['ans_add_ans']);
                                $steps[$resStep]['question_ids'][$s_id]['ans'] = $a_id;
                            }
                        }
                        if(!$resAns = $modx->getObject('UserTestResultAnswers', array('result_id'=>$result_id, 'question_id'=>$q_id))){
                            $resAns = $modx->newObject('UserTestResultAnswers');
                        }
                        $resAns->result_id = $result_id;
                        $resAns->question_id = $q_id;
                        $resAns->answer_ids = implode(',', $a_id);
                        $answer = array(); $point = 0;
                        foreach($a_id as $a_id1){
                            if($a = $modx->getObject('UserTestAnswers',$a_id1)){
                                $answer[] = $a->answer;
                                $point += $a->point;
                            }
                        }
                        if($a_id_add['check']) $answer[] = $a_id_add['ans'];
                        $resAns->answer = implode(', ', $answer);
                        $resAns->point = $point;
                        if($resAns->answer){
                            $resAns->save();
                        }
                        break;
                    case 7: //Таблица чек-боксов
                        //print_r($a_id);
                        foreach($steps[$resStep]['question_ids'] as $s_id=>$v){
                            if($v['id'] == $q_id){
                                $steps[$resStep]['question_ids'][$s_id]['ans'] = $a_id;
                            }
                        }
                        if(!$resAns = $modx->getObject('UserTestResultAnswers', array('result_id'=>$result_id, 'question_id'=>$q_id))){
                                $resAns = $modx->newObject('UserTestResultAnswers');
                            }
                        $resAns->result_id = $result_id;
                        $resAns->question_id = $q_id;    
                        $resAns->point = 0;
                        $answer = "";
                        foreach($a_id as $qc_id=>$ac){
                            if(!$resAnsc = $modx->getObject('UserTestResultAnswers', array('result_id'=>$result_id, 'question_id'=>$qc_id))){
                                $resAnsc = $modx->newObject('UserTestResultAnswers');
                            }
                            $resAnsc->result_id = $result_id;
                            $resAnsc->question_id = $qc_id;
                            $resAnsc->answer_ids = implode(',', $ac);
                            $answerc = array(); $point = 0;
                            foreach($ac as $a_id1){
                                if($a = $modx->getObject('UserTestAnswers',$a_id1)){
                                    $answerc[] = $a->answer;
                                    $point += $a->point;
                                }
                            }
                            $resAnsc->answer = implode('# ', $answerc);
                            $resAnsc->point = $point;
                            $resAns->point += $point;
                            //$resAnsc->save();
                            if($q_child = $modx->getObject('UserTestQuestions', $qc_id)){
                                $answer .= $q_child->question."[".$q_child->id."]"."->".$resAnsc->answer."\r\n";
                            }
                        }
                        $resAns->answer = $answer;
                        $resAns->save();
                        break;
                    case 8: //Таблица текстовых полей
                        //print_r($a_id);
                        
                        foreach($steps[$resStep]['question_ids'] as $s_id=>$v){
                            if($v['id'] == $q_id){
                                $steps[$resStep]['question_ids'][$s_id]['ans'] = $a_id;
                            }
                        }
                        if(!$resAns = $modx->getObject('UserTestResultAnswers', array('result_id'=>$result_id, 'question_id'=>$q_id))){
                                $resAns = $modx->newObject('UserTestResultAnswers');
                            }
                        $resAns->result_id = $result_id;
                        $resAns->question_id = $q_id;    
                        $resAns->point = 0;
                        $answer = "";
                        foreach($a_id as $qc_id=>$ac){
                            if(!$resAnsc = $modx->getObject('UserTestResultAnswers', array('result_id'=>$result_id, 'question_id'=>$qc_id))){
                                $resAnsc = $modx->newObject('UserTestResultAnswers');
                            }
                            $resAnsc->result_id = $result_id;
                            $resAnsc->question_id = $qc_id;
                            //$resAns->answer_ids = implode(',', $ac);
                            $answerc = array(); $point = 0;
                            foreach($ac as $a_id1=>$ac_v){
                                if($a = $modx->getObject('UserTestAnswers',$a_id1) and $ac_v){    
                                    $answerc[] = $a->answer.'#'.$ac_v;
                                }
                            }
                            $resAnsc->answer = implode('# ', $answerc);
                            $resAnsc->point = $point;
                            if($resAnsc->answer){
                                //$resAnsc->save();
                            }
                            if($q_child = $modx->getObject('UserTestQuestions', $qc_id)){
                                $answer .= $q_child->question."[".$q_child->id."]"."->".$resAnsc->answer."\r\n";
                            }
                        }
                        $resAns->answer = $answer;
                        $resAns->save();
                        break;
                    case 9: //Селекты в тексте
                        foreach($steps[$resStep]['question_ids'] as $s_id=>$v){
                            if($v['id'] == $q_id){
                                $steps[$resStep]['question_ids'][$s_id]['ans'] = $a_id;
                            }
                        }
                        $all_point = 0;
                        $a_str = $q->question;
                        foreach($a_id as $qc_id=>$ac){
                            if(!$resAns = $modx->getObject('UserTestResultAnswers', array('result_id'=>$result_id, 'question_id'=>$qc_id))){
                                $resAns = $modx->newObject('UserTestResultAnswers');
                            }
                            $resAns->result_id = $result_id;
                            $resAns->question_id = $qc_id;
                            $resAns->answer_ids = implode(',', $ac);
                            $answer = ""; $point = 0;
                            if($a = $modx->getObject('UserTestAnswers',$ac)){
                                $answer = $a->answer;
                                $point += $a->point;
                            }
                            $resAns->answer = $answer;
                            $resAns->point = $point;
                            //$resAns->save();
                            $all_point += $point;
                            if($q_child = $modx->getObject('UserTestQuestions', $qc_id)){
                                $a_str = str_replace('[['.$q_child->question.']]',$answer,$a_str);
                            }
                        }
                        
                        if(!$resAns = $modx->getObject('UserTestResultAnswers', array('result_id'=>$result_id, 'question_id'=>$q_id))){
                            $resAns = $modx->newObject('UserTestResultAnswers');
                        }
                        $resAns->result_id = $result_id;
                        $resAns->question_id = $q_id;
                        $resAns->answer = $a_str;
                        $resAns->point = $all_point;
                        $resAns->save();
                        break;
                    case 10: //Комбинированный одиночный выбор
                        foreach($steps[$resStep]['question_ids'] as $s_id=>$v){
                            if($v['id'] == $q_id){
                                if($a_id['ans']=="ans_add"){
                                    $steps[$resStep]['question_ids'][$s_id]['ans_add'] = array(
                                        'check' => 1,
                                        'ans' => $a_id['ans_add_ans'],
                                        );
                                }else{
                                    $steps[$resStep]['question_ids'][$s_id]['ans_add'] = array(
                                        'check' => 0,
                                        'ans' => "",
                                        );
                                }
                                $a_id_add = $steps[$resStep]['question_ids'][$s_id]['ans_add'];
                                //unset($a_id['ans_add']);
                                //unset($a_id['ans_add_ans']);
                                $steps[$resStep]['question_ids'][$s_id]['ans'] = $a_id;
                            }
                        }
                        //print_r($a_id);
                        if(!$resAns = $modx->getObject('UserTestResultAnswers', array('result_id'=>$result_id, 'question_id'=>$q_id))){
                            $resAns = $modx->newObject('UserTestResultAnswers');
                        }
                        $resAns->result_id = $result_id;
                        $resAns->question_id = $q_id;
                        $answer = ""; $point = 0;
                        if($a_id["ans"] == "ans_add"){
                            $resAns->answer_id = 0;
                            $answer = $a_id['ans_add_ans'];
                            if($answer){
                            $answer = "Другое->".$answer; 
                            }
                        }else{
                            $resAns->answer_id = $a_id["ans"];
                            if($a = $modx->getObject('UserTestAnswers',$a_id["ans"])){
                                $answer = $a->answer;
                                $point = $a->point;
                            }
                        }
                        if($answer){
                            $resAns->answer = $answer;
                            $resAns->point = $point;
                            $resAns->save();
                        }
                        break;
                }
                
            }
        }
        $time_last_step = !empty($_SESSION['UserTest'][$id]['time_last_step']) ? (int)$_SESSION['UserTest'][$id]['time_last_step'] : 0;
        if(!$time_last_step) $time_last_step = !empty($_SESSION['UserTest'][$id]['test_time_start']) ? (int)$_SESSION['UserTest'][$id]['test_time_start'] : time();
        $_SESSION['UserTest'][$id]['time_last_step'] = time();
        $time = time() - $time_last_step;
        if($resAns){
            $resAns->time = $time;
            $resAns->save();
        }

        $c = $modx->newQuery('UserTestResultAnswers');
        //$c->leftJoin('UserTestAnswers', 'UserTestAnswers', 'UserTestAnswers.id = UserTestResultAnswers.answer_id');
        $c->select("sum(point) as sum_point");
        $c->where(array('result_id'=>$result_id));
        //$c->prepare();
        //echo $c->toSQL();
        if($object = $modx->getObject('UserTestResultAnswers', $c)){
            $Result->test_point = $object->get('sum_point');
            $test_time_start = $_SESSION['UserTest'][$id]['test_time_start'];// = time()
            $test_time = time() - $test_time_start;
            $Result->test_time = $test_time;
            $test_point = $Result->test_point;
        }
        $_SESSION['UserTest'][$id]['steps'] = $steps;
        $_SESSION['UserTest'][$id]['curStep'] = $curStep2;
        $Result->session = json_encode($_SESSION['UserTest'][$id]);
        $Result->save();

        if ($test->use_block_q_number) {
            $block_q_number = usertestBuildBlockQuestionTraining($modx, $steps, $curStep2, (int)$result_id, (int)$id);
        }

        if (!empty($questions) && is_array($questions) && !empty($questions_post) && (string)$answer_step === (string)$curStep2) {
            foreach ($questions as &$questionView) {
                $qvId = isset($questionView['id']) ? (int)$questionView['id'] : 0;
                if ($qvId <= 0 || !array_key_exists($qvId, $questions_post)) {
                    continue;
                }
                $postedValue = $questions_post[$qvId];
                switch ((int)$questionView['type']) {
                    case 1:
                    case 12:
                        $questionView['answer_id'] = (int)$postedValue;
                        if (!empty($questionView['answers']) && is_array($questionView['answers'])) {
                            foreach ($questionView['answers'] as &$answerView) {
                                $answerView['check'] = ((int)$answerView['id'] === (int)$postedValue) ? 1 : 0;
                            }
                            unset($answerView);
                        }
                        break;
                    case 2:
                        $postedIds = is_array($postedValue) ? array_map('intval', $postedValue) : array();
                        $questionView['ans'] = $postedIds;
                        if (!empty($questionView['answers']) && is_array($questionView['answers'])) {
                            foreach ($questionView['answers'] as &$answerView) {
                                $answerView['check'] = in_array((int)$answerView['id'], $postedIds, true) ? 1 : 0;
                            }
                            unset($answerView);
                        }
                        break;
                    case 3:
                    case 4:
                        $questionView['answer'] = is_array($postedValue) ? '' : (string)$postedValue;
                        break;
                }
            }
            unset($questionView);
        }
    }
}
//сохранение в базу сессии для возврата к тесту
if($curStep != "start" and !$skip_save){
    $result_id = $_SESSION['UserTest'][$id]['result_id'];
    if($Result = $modx->getObject('UserTestResults',$result_id)){
        $_SESSION['UserTest'][$id]['curStep'] = $curStep2;
        $Result->session = json_encode($_SESSION['UserTest'][$id]);
        $Result->save();
    }
}
//ограничение времени теста
$end_test_time = 0;
if($test->time_test > 0 and isset($_SESSION['UserTest'][$id]['test_time_start'])){
    $test_time_start = $_SESSION['UserTest'][$id]['test_time_start'];// = time()
    $test_time = time() - $test_time_start;
    $end_test_time = $test->time_test - $test_time;
    if($test_time >= $test->time_test){
        $curStep = "finish"; 
    }
}

//подготовка блока вопросов
if($curStep != "start" and $curStep != "finish"){
    $steps = $_SESSION['UserTest'][$id]['steps'];
    if($test->use_block_q_number){
        $activeResultId = !empty($_SESSION['UserTest'][$id]['result_id']) ? (int)$_SESSION['UserTest'][$id]['result_id'] : 0;
        $block_q_number = usertestBuildBlockQuestionTraining($modx, $steps, $curStep2, $activeResultId, (int)$id);
    }
}
$var_id = 0;
$var_result = "";
$var_passed = 0;
$max_point = (float)$UserTest->getMaxPoint($id);
if (!empty($_SESSION['UserTest'][$id]['result_id'])) {
    $activeResultForScore = $modx->getObject('UserTestResults', (int)$_SESSION['UserTest'][$id]['result_id']);
    if ($activeResultForScore) {
        $result_id = (int)$activeResultForScore->get('id');
        $loadedPoint = $activeResultForScore->get('test_point');
        if ($loadedPoint !== null && $loadedPoint !== '') {
            $test_point = (float)$loadedPoint;
        }
        $loadedMax = (float)$activeResultForScore->get('max_point');
        if ($loadedMax > 0) {
            $max_point = $loadedMax;
        }
    }
}
$trainingMinPassPercentRaw = $trainingMinPassPercentProp;
if ($trainingMinPassPercentRaw === null || $trainingMinPassPercentRaw === '') {
    $trainingMinPassPercentRaw = $modx->getPlaceholder('training_activity_min_pass_percent');
}
if ($trainingMinPassPercentRaw === null || $trainingMinPassPercentRaw === '') {
    $trainingMinPassPercentRaw = $modx->getOption('min_pass_percent', $scriptProperties, 0);
}
if (($trainingMinPassPercentRaw === null || $trainingMinPassPercentRaw === '' || (float)$trainingMinPassPercentRaw <= 0) && isset($_SESSION['UserTest'][$id]['sp']['training_activity_min_pass_percent'])) {
    $trainingMinPassPercentRaw = $_SESSION['UserTest'][$id]['sp']['training_activity_min_pass_percent'];
}
$trainingMinPassPercent = (float)$trainingMinPassPercentRaw;
if ($trainingMinPassPercent <= 0 && $trainingActivityLink) {
    $trainingMinPassPercent = (float)$trainingActivityLink->get('min_pass_percent');
}
if($curStep == "finish"){
    $result_id = !empty($_SESSION['UserTest'][$id]['result_id']) ? (int)$_SESSION['UserTest'][$id]['result_id'] : (int)$result_id;
    $finishSteps = isset($_SESSION['UserTest'][$id]['steps']) && is_array($_SESSION['UserTest'][$id]['steps']) ? $_SESSION['UserTest'][$id]['steps'] : array();
    $block_q_number = ($test->use_block_q_number && !empty($finishSteps) && $result_id > 0)
        ? usertestBuildBlockQuestionTraining($modx, $finishSteps, '', (int)$result_id, (int)$id)
        : false;
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
        if($test_point >= $var->start_point and $test_point <= $var->end_point){
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
        $c->where(array('result_id'=>$result_id));
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
    
    //ссылка на правильные ответы
    $trainingAnswerUrl = $trainingAnswerUrlTpl !== '' ? $trainingAnswerUrlTpl : (string)$modx->getPlaceholder('training_activity_answer_url');
    if ($trainingAnswerUrl !== '' && $result_id > 0) {
        $answer_page_url = str_replace('__RESULT_ID__', (string)$result_id, $trainingAnswerUrl);
    } elseif(isset($_SESSION['UserTestUrl'][$id]['answer_page_id'])){
        $params = array(
        'result_id'=>$_SESSION['UserTest'][$id]['result_id'],
        );
        $answer_page_url = $modx->getOption('site_url').$modx->makeUrl($_SESSION['UserTestUrl'][$id]['answer_page_id'],'',$params);
    }

    if($Result){
        //$modx->log(1,"UserTest $var_id");
        $Result->variant_id = $var_id;
        if($test->type == 2){
            $Result->status_id = 3;
        }else{
            $Result->status_id = 2;
        }
        $Result->max_point = $UserTest->getMaxPoint($id);
        if($test->time_test > 0 and isset($_SESSION['UserTest'][$id]['test_time_start'])){
            if($test_time > $test->time_test + 10){
                if($UserTestVariants = $modx->getObject('UserTestVariants',['haker'=>1])){
                    $Result->variant_id = $UserTestVariants->id;
                    $is_timeout = true;
                }
            }
        }
        $Result->save();
        
        $response = $modx->invokeEvent('OnTestCalculate', array(
                'test'=>$test->toArray(),
                'variants'=>$Variants,
                'result'=>$Result,
                'cat_results'=>$cat_results,
                'sp' => $scriptProperties,
            ));
        if($Result = $modx->getObject('UserTestResults',$Result->id)){
            $var_id = $Result->variant_id;
            if($var = $modx->getObject('UserTestVariants',$var_id)){
                $var_result = $var->result;
                $var_passed = $var->passed;
            }
            $max_point = $Result->max_point;
            $test_point = (float)$Result->test_point;
            if ($max_point > 0) {
                $scorePercentResolved = round(((float)$Result->test_point / (float)$max_point) * 100, 2);
                if ($trainingMinPassPercent > 0) {
                    $var_passed = $scorePercentResolved >= $trainingMinPassPercent ? 1 : 0;
                } elseif ($scorePercentResolved >= 100) {
                    $var_passed = 1;
                }
            }
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
                    'sp' => $scriptProperties,
                ));

            $trainingCourseId = $trainingCourseIdProp > 0 ? $trainingCourseIdProp : (int)$modx->getPlaceholder('training_activity_course_id');
            $trainingModuleId = $trainingModuleIdProp > 0 ? $trainingModuleIdProp : (int)$modx->getPlaceholder('training_activity_module_id');
            if ($trainingCourseId > 0 && $trainingModuleId > 0 && $user_id > 0) {
                $trainingCorePath = $modx->getOption('training.core_path', null, $modx->getOption('core_path') . 'components/training/');
                $trainingClassPath = rtrim($trainingCorePath, '/\\') . '/model/training/training.class.php';
                $progressClassPath = rtrim($trainingCorePath, '/\\') . '/model/training/services/trainingprogress.class.php';
                if (is_file($trainingClassPath) && is_file($progressClassPath)) {
                    require_once $trainingClassPath;
                    require_once $progressClassPath;
                    if (class_exists('Training') && class_exists('TrainingProgressService')) {
                        $trainingService = new Training($modx);
                        $progressService = new TrainingProgressService($modx, $trainingService);
                        $progressService->syncUserTestStatus($trainingCourseId, $trainingModuleId, $id, $user_id);
                    }
                }
            }
        }
        unset($_POST);
    }
    //извлекаем историю опросов САН для построения графика
    $cat_history = array();
    if($test->test_type == 2){
        $cch = $modx->newQuery('UserTestResultCategorys');
        $cch->leftJoin('UserTestResults','UserTestResults', '`UserTestResultCategorys`.`result_id` = `UserTestResults`.`id`');
        //$cch->leftJoin('UserTestCategorys','UserTestCategorys', '`UserTestResultCategorys`.`category_id` = `UserTestCategorys`.`id`');
        $cch->select('`UserTestResultCategorys`.`cat_point`, `UserTestResultCategorys`.`category_id`, `UserTestResults`.`date`');
        $cch->where(array(
            '`UserTestResults`.`user_id`'=>$user_id,
            '`UserTestResults`.`test_id`'=>$test->id,
            ));
        $chs = $modx->getIterator('UserTestResultCategorys', $cch);
        foreach($chs as $ch1){
            $cat_history[$ch1->date][$ch1->category_id] = $ch1->cat_point;
        }
    }
    unset($_SESSION['UserTest'][$id]);
    unset($_SESSION['UserTestUrl'][$id]);
}
//ссылка для возврата к тесту
if(isset($_SESSION['UserTestUrl'][$id]['test_url_id']) && isset($_SESSION['UserTest'][$id]['result_id'])){
    $params = array(
    'test_id'=>$id,
    'result_id'=>$_SESSION['UserTest'][$id]['result_id'],
    );
    $test_url = $modx->getOption('site_url').$modx->makeUrl($_SESSION['UserTestUrl'][$id]['test_url_id'],'',$params);
}

$trainingCurrentUrl = '';
if ($trainingBaseUrl !== '') {
    if ($curStep === 'finish') {
        $trainingCurrentUrl = usertestBuildUrlTraining($trainingBaseUrl, array(
            'screen' => 'test',
            'step' => 'finish',
            'reset' => null,
        ));
    } elseif ($curStep !== '' && $curStep !== 'start') {
        $trainingCurrentUrl = usertestBuildUrlTraining($trainingBaseUrl, array(
            'screen' => 'test',
            'step' => $curStep,
            'reset' => null,
        ));
    }
}

return $pdoFetch->getChunk($tpl, array(
    'result_id'=>$result_id,
    'check_ajax'=>$check_ajax,
    'test_id'=>$id,
    'test'=>$test->toArray(),
    'curStep'=>$curStep,
    'prevStep'=>$prevStep,
    'nextStep'=>$nextStep,
    'questions'=>$questions,
    'test_point'=>$test_point,
    'max_point'=>$max_point,
    'var_result'=>$var_result,
    'var_passed'=>$var_passed,
    'test_time'=>$test_time,
    'end_test_time'=>$end_test_time,
    'result_status'=>$result_status,
    'catResults'=>$cat_results,
    'cat_history'=>$cat_history,
    'cat_email_results'=>$cat_email_results,
    'block_q_number'=>$block_q_number,
    'test_url'=>$test_url,
    'answer_page_url'=>$answer_page_url,
    'training_activity_id'=>$trainingActivityId,
    'training_restart_url'=>$trainingRestartUrl,
    'training_back_url'=>$trainingBackUrl,
    'training_answer_url'=>$answer_page_url,
    'training_answer_url_tpl'=>$trainingAnswerUrlTpl,
    'training_current_url'=>$trainingCurrentUrl,
    'min_pass_percent'=>$trainingMinPassPercent,
    'check_restore'=>$check_restore,
    'is_timeout'=>$is_timeout,
    'user_name'=>$user_name,
    'user_email'=>$user_email,
    ));
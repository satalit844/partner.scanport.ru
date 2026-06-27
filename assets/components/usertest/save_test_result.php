<?php
if (!defined('MODX_API_MODE')) {
    define('MODX_API_MODE', false);
}

include(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php');
if (!defined('MODX_CORE_PATH')) define('MODX_CORE_PATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/core/');

include_once (MODX_CORE_PATH . "model/modx/modx.class.php");
$modx = new modX();
$modx->initialize('web');

//проверка доступа
$access = false;
$access_export_groups = explodeAndClean($modx->getOption('usertest_access_export_groups'));
if($modx->user->id and $user = $modx->getObject('modUser',$modx->user->id)){
    if($user->isMember($access_export_groups)){
        $access = true;
    }
}
if(!$access){
    header('Content-Type: text/html; charset=utf-8');
    echo "Нет доступа!"; exit;
}

$modx->addPackage('usertest', $modx->getOption('core_path') . 'components/usertest/model/');
//проверка пользователя
$test = (int)$_GET['test'];
$status_id = (int)$_GET['status'];

// $sanitizePatterns = $modx->sanitizePatterns;
// $sanitizePatterns['fenom_syntax'] = '@\{(.*?)\}@si';
// $user = $modx->sanitize($_GET['user_name'], $sanitizePatterns);

$user = $_GET['user_name'];
$date1 = $_GET['date1'];
$date2 = $_GET['date2'];

    $c = $modx->newQuery('UserTestResults');
        $c->leftJoin('UserTestTests','UserTestTests', '`UserTestResults`.`test_id` = `UserTestTests`.`id`');
        $c->leftJoin('UserTestVariants','UserTestVariants', '`UserTestResults`.`variant_id` = `UserTestVariants`.`id`');
        $c->leftJoin('UserTestResultStatus','UserTestResultStatus', '`UserTestResults`.`status_id` = `UserTestResultStatus`.`id`');
        
        $Columns = $modx->getSelectColumns('UserTestResults', 'UserTestResults', '', '', true);
        $c->select($Columns . ', `UserTestTests`.`name` as `test_name`, `UserTestVariants`.`result` as `variant`, `UserTestResultStatus`.`label` as `status`');

        if ($status_id) {
            $c->where(array(
                '`UserTestResults`.`status_id`' => "{$status_id}",
            ));
        }
        if ($test) {
            $c->where(array(
                '`UserTestResults`.`test_id`' => "{$test}",
                'OR:`UserTestTests`.`name`:LIKE' => "%{$test}%",
            ));
        }
        if ($user) {
            $c->where(array(
                '`UserTestResults`.`user_name`:LIKE' => "%{$user}%",
            ));
        }
        if($date1){
            $c->where(array(
                '`UserTestResults`.`date`:>=' => strftime('%Y-%m-%d %H:%M:%S',strtotime($date1)),
            ));
        }
        if($date2){
            $c->where(array(
                '`UserTestResults`.`date`:<=' => strftime('%Y-%m-%d %H:%M:%S',strtotime($date2)),
            ));
        }
    $c->sortby('id','DESC');
    /* $c->prepare();
        echo $c->toSQL();
        exit; */
    $records = $modx->getIterator('UserTestResults',$c);
    $start_str = 3;
    require_once ('PHPExcel/IOFactory.php');
    // Открываем файл
    $xls = PHPExcel_IOFactory::load('test_result.xls');
    // Устанавливаем индекс активного листа
    $xls->setActiveSheetIndex(0);
    // Получаем активный лист
    $sheet = $xls->getActiveSheet();
    $q_col_start = 7;
    $q_max_col = $q_col_start;
    foreach ($records as $key => $row) {
        //print_r($row);exit;
        $str_num=$start_str+$key;
        $sheet->setCellValue('A'.$str_num, $row->test_name); //Тест
        $sheet->setCellValue('B'.$str_num, $row->user_name); //Имя
        $sheet->setCellValue('C'.$str_num, $row->user_email); //Email
        $sheet->setCellValue('D'.$str_num, $row->date); //Дата
        $sheet->setCellValue('E'.$str_num, $row->test_point); //Баллы
        $sheet->setCellValue('F'.$str_num, $row->test_time); //Время
        $sheet->setCellValue('G'.$str_num, $row->variant); //Результат
        $sheet->setCellValue('H'.$str_num, $row->status); //Статус
        
        $c1 = $modx->newQuery('UserTestQuestions');
        $c1->leftJoin('UserTestResultAnswers','UserTestResultAnswers', '`UserTestResultAnswers`.`question_id` = `UserTestQuestions`.`id` and `UserTestResultAnswers`.`result_id`='.$row->id);
        $c1->leftJoin('UserTestTestQuestionLink','UserTestTestQuestionLink', '`UserTestTestQuestionLink`.`question_id` = `UserTestQuestions`.`id` and `UserTestTestQuestionLink`.`test_id`='.$row->test_id);
        $c1->select('`UserTestTestQuestionLink`.`menuindex` as `q_mid`,`UserTestQuestions`.`question` as `q`,`UserTestQuestions`.`id` as `q_id`, `UserTestResultAnswers`.`point` as `u_point`,`UserTestResultAnswers`.`answer` as `a`,`UserTestQuestions`.`type` as `q_type`');
        $c1->sortby('`UserTestTestQuestionLink`.`menuindex`','ASC');

        $c1->where(array(
                '`UserTestTestQuestionLink`.`test_id`' => $row->test_id,
                '`UserTestQuestions`.`parent`' => 0,
            ));
        //$c1->prepare();echo $c1->toSQL();exit;
        $user_points = $modx->getIterator('UserTestQuestions',$c1);
        
        foreach($user_points as $up){
            $q_col = $up->q_mid*3 + $q_col_start;
            $sheet->setCellValueByColumnAndRow($q_col - 2,$str_num,$up->q);
            $sheet->setCellValueByColumnAndRow($q_col - 1,$str_num,$up->a);
            $sheet->setCellValueByColumnAndRow($q_col,$str_num,$up->u_point);
            if($q_col > $q_max_col) $q_max_col=$q_col;
        }
        
        $c2 = $modx->newQuery('UserTestResultCategorys');
        $c2->leftJoin('UserTestCategorys','UserTestCategorys', '`UserTestResultCategorys`.`category_id` = `UserTestCategorys`.`id`');
        $c2->leftJoin('UserTestVariants','UserTestVariants', '`UserTestResultCategorys`.`variant_id` = `UserTestVariants`.`id`');
        $c2->select('`UserTestCategorys`.`name`,`UserTestResultCategorys`.`cat_point`,`UserTestVariants`.`result`');
        $c2->sortby('`UserTestCategorys`.`id`','ASC');
        $c2->where(array(
                '`UserTestResultCategorys`.`result_id`' => $row->id,
            ));
        $cat_points = $modx->getIterator('UserTestResultCategorys',$c2);
        foreach($cat_points as $k=>$cp){
            $q_col = ($k+1)*3 + $q_max_col;
            $sheet->setCellValueByColumnAndRow($q_col - 2,$str_num,$cp->name);
            $sheet->setCellValueByColumnAndRow($q_col - 1,$str_num,$cp->cat_point);
            $sheet->setCellValueByColumnAndRow($q_col,$str_num,$cp->result);
        }
    }
    $excel_name = "test_result";
    // Выводим HTTP-заголовки
     header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
     header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
     header ( "Cache-Control: no-cache, must-revalidate" );
     header ( "Pragma: no-cache" );
     header ( "Content-type: application/vnd.ms-excel;charset=utf-8;" );
     header ( "Content-Disposition: attachment; filename=$excel_name.xls" );

    // Выводим содержимое файла
     $objWriter = new PHPExcel_Writer_Excel5($xls);
     $objWriter->save('php://output');
     echo "<script>window.close;</script>";

function explodeAndClean($array, $delimiter = ",")
{
    $array = explode($delimiter, $array);     // Explode fields to array
    $array = array_map("trim", $array);       // Trim array"s values
    $array = array_keys(array_flip($array));  // Remove duplicate fields
    $array = array_filter($array);            // Remove empty values from array

    return $array;
}
?>
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
$date1 = $_GET['date1'];
$date2 = $_GET['date2'];

	$c = $modx->newQuery('UserTestInvites');
		$c->leftJoin('UserTestTests','UserTestTests', '`UserTestInvites`.`test_id` = `UserTestTests`.`id`');
		
		$Columns = $modx->getSelectColumns('UserTestInvites', 'UserTestInvites', '', '', true);
		$c->select($Columns . ', `UserTestTests`.`name` as `test_name`');

		$c->where(array(
				'`UserTestInvites`.`active`' => 1,
			));
		if ($test) {
			$c->where(array(
				'`UserTestInvites`.`test_id`' => "{$test}",
				'OR:`UserTestTests`.`name`:LIKE' => "%{$test}%",
			));
		}
		if($date1){
			$c->where(array(
				'`UserTestInvites`.`date`:>=' => strftime('%Y-%m-%d %H:%M:%S',strtotime($date1)),
			));
		}
		if($date2){
			$c->where(array(
				'`UserTestInvites`.`date`:<=' => strftime('%Y-%m-%d %H:%M:%S',strtotime($date2)),
			));
		}
	$c->sortby('id','DESC');
	/* $c->prepare();
		echo $c->toSQL();
		exit; */
	$records = $modx->getIterator('UserTestInvites',$c);
	$start_str = 2;
	require_once ('PHPExcel/IOFactory.php');
	// Открываем файл
	$xls = PHPExcel_IOFactory::load('test_invites.xls');
	// Устанавливаем индекс активного листа
	$xls->setActiveSheetIndex(0);
	// Получаем активный лист
	$sheet = $xls->getActiveSheet();
	foreach ($records as $key => $row) {
		//print_r($row);exit;
		$str_num=$start_str+$key;
		$sheet->setCellValue('A'.$str_num, $row->user_email); //Email
		$sheet->setCellValue('B'.$str_num, $row->user_name); //Имя
		$sheet->setCellValue('C'.$str_num, $row->user_pass); //Пароль
		$sheet->setCellValue('D'.$str_num, $row->url); //Url
		$sheet->setCellValue('E'.$str_num, $row->test_id); //тест id
	}
	$excel_name = "test_invites";
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
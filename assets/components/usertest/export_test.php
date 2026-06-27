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

	$c = $modx->newQuery('UserTestTestQuestionLink');
	$c->leftJoin('UserTestQuestions','UserTestQuestions', '`UserTestQuestions`.`id` = `UserTestTestQuestionLink`.`question_id`');
	
	$Columns = $modx->getSelectColumns('UserTestQuestions', 'UserTestQuestions', '', '', true);
	$c->select($Columns . ', `UserTestTestQuestionLink`.`menuindex` as `ql_menuindex`, `UserTestTestQuestionLink`.`test_id` as `ql_test_id`');
	
	$c->where(array(
			'`UserTestQuestions`.`parent`' => 0
		));
	if ($test) {
		$c->where(array(
			'`UserTestTestQuestionLink`.`test_id`' => $test
		));
	}

	$c->sortby('`UserTestTestQuestionLink`.`test_id`','ASC');
	$c->sortby('`UserTestTestQuestionLink`.`menuindex`','ASC');
	/* $c->prepare();
		echo $c->toSQL();
		exit; */
	$records = $modx->getIterator('UserTestTestQuestionLink',$c);
	
	require_once ('PHPExcel/IOFactory.php');
	// Открываем файл
	$xls = PHPExcel_IOFactory::load('export_test.xlsx');
	
	//вопросы
	$xls->setActiveSheetIndex(0);
	// Получаем активный лист
	$sheet = $xls->getActiveSheet();
	$str_num = 1;
	foreach ($records as $key => $row) {
		//print_r($row);exit;
		$str_num++;
		$sheet->setCellValue('A'.$str_num, $row->ql_test_id); //Тест ID 0
		$sheet->setCellValue('B'.$str_num, $row->id); //ID вопроса 1
		$sheet->setCellValue('C'.$str_num, $row->parent); //ID родителя 2
		$sheet->setCellValue('D'.$str_num, 0); //MenuIndex родителя 3
		$sheet->setCellValue('E'.$str_num, $row->ql_menuindex); //MenuIndex 4
		$sheet->setCellValue('F'.$str_num, $row->type); //Тип вопроса 5
		$sheet->setCellValue('G'.$str_num, $row->category_id); //Категория вопроса 6
		$sheet->setCellValue('H'.$str_num, $row->question); //Текс вопроса 7
		$sheet->setCellValue('I'.$str_num, $row->max_point); //Max балл 8
		$sheet->setCellValue('J'.$str_num, $row->random_answer); //Ответы в случайном порядке 9
		$sheet->setCellValue('K'.$str_num, $row->validate); //Ответ обязателен 10
		$sheet->setCellValue('L'.$str_num, $row->type_file); //Тип файла 11
		$sheet->setCellValue('M'.$str_num, $row->file); //Файл 12
		$sheet->setCellValue('N'.$str_num, $row->extended); //Extended 13
		
		switch($row->type){
			case 7: //Таблица чек-боксов 
			case 8: //Таблица текстовых полей
			case 9: //Селекты в тексте
				$c1 = $modx->newQuery('UserTestQuestions');
				$c1->where(array(
					'parent'=>$row->id
				));
				$records1 = $modx->getIterator('UserTestQuestions',$c1);
				foreach ($records1 as $row1){
					$str_num++;
					$sheet->setCellValue('A'.$str_num, $row->ql_test_id); //Тест ID
					$sheet->setCellValue('B'.$str_num, $row1->id); //ID вопроса
					$sheet->setCellValue('C'.$str_num, $row1->parent); //ID родителя
					$sheet->setCellValue('D'.$str_num, $row->ql_menuindex); //MenuIndex родителя
					$sheet->setCellValue('E'.$str_num, $row1->menuindex); //MenuIndex
					$sheet->setCellValue('F'.$str_num, $row1->type); //Тип вопроса
					//$sheet->setCellValue('G'.$str_num, $row->category_id); //Категория вопроса
					$sheet->setCellValue('H'.$str_num, $row1->question); //Текс вопроса
					//$sheet->setCellValue('I'.$str_num, $row->max_point); //Max балл
					//$sheet->setCellValue('J'.$str_num, $row->random_answer); //Ответы в случайном порядке
					//$sheet->setCellValue('K'.$str_num, $row->validate); //Ответ обязателен
					//$sheet->setCellValue('L'.$str_num, $row->type_file); //Тип файла
					//$sheet->setCellValue('M'.$str_num, $row->file); //Файл
					//$sheet->setCellValue('N'.$str_num, $row->extended); //Extended
				}
			break;
		}
	}
	
	//ответы
	// Устанавливаем индекс активного листа
	$xls->setActiveSheetIndex(1);
	// Получаем активный лист
	$sheet = $xls->getActiveSheet();
	$str_num = 1;
	foreach ($records as $key => $row) {
		
		
		$ca = $modx->newQuery('UserTestAnswers');
		$ca->where(array(
					'question_id'=>$row->id
				));
		$ca->sortby('menuindex','ASC');
		$answers = $modx->getIterator('UserTestAnswers',$ca);
		foreach($answers as $a){
			$str_num++;
			$sheet->setCellValue('A'.$str_num, $row->ql_test_id); //Тест ID
			$sheet->setCellValue('B'.$str_num, $row->id); //ID вопроса
			$sheet->setCellValue('C'.$str_num, $row->ql_menuindex); //MenuIndex Вопроса
			$sheet->setCellValue('D'.$str_num, 0); //Менюиндекс родителя вопроса.
			$sheet->setCellValue('E'.$str_num, $a->id); //ID ответа
			$sheet->setCellValue('F'.$str_num, $a->menuindex); //MenuIndex
			$sheet->setCellValue('G'.$str_num, $a->answer); //Текс ответа
			$sheet->setCellValue('H'.$str_num, $a->type_file); //Тип файла
			$sheet->setCellValue('I'.$str_num, $a->file); //Файл
			$sheet->setCellValue('J'.$str_num, $a->point); //Баллы
			$sheet->setCellValue('K'.$str_num, $a->right); //Правильный ответ
		}
		switch($row->type){
			case 7: case 8: case 9: case 11:
				$c1 = $modx->newQuery('UserTestQuestions');
				$c1->where(array(
					'parent'=>$row->id
				));
				$records1 = $modx->getIterator('UserTestQuestions',$c1);
				foreach ($records1 as $row1){
					$ca = $modx->newQuery('UserTestAnswers');
					$ca->where(array(
								'question_id'=>$row1->id
							));
					$ca->sortby('menuindex','ASC');
					$answers = $modx->getIterator('UserTestAnswers',$ca);
					foreach($answers as $a){
						$str_num++;
						$sheet->setCellValue('A'.$str_num, $row->ql_test_id); //Тест ID
						$sheet->setCellValue('B'.$str_num, $row1->id); //ID вопроса
						$sheet->setCellValue('C'.$str_num, $row1->menuindex); //MenuIndex Вопроса
						$sheet->setCellValue('D'.$str_num, $row->ql_menuindex); //Менюиндекс родителя вопроса.
						$sheet->setCellValue('E'.$str_num, $a->id); //ID ответа
						$sheet->setCellValue('F'.$str_num, $a->menuindex); //MenuIndex
						$sheet->setCellValue('G'.$str_num, $a->answer); //Текс ответа
						$sheet->setCellValue('H'.$str_num, $a->type_file); //Тип файла
						$sheet->setCellValue('I'.$str_num, $a->file); //Файл
						$sheet->setCellValue('J'.$str_num, $a->point); //Баллы
						$sheet->setCellValue('K'.$str_num, $a->right); //Правильный ответ
					}
				}
			break;
		}
	}
	
	$excel_name = "export_test";
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
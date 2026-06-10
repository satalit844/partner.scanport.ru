<?php
class ImportTestConsoleProcessor extends modProcessor {

    public function process() {
		set_time_limit(3600);
		$this->modx->getService('lexicon','modLexicon');
		$this->modx->lexicon->load($this->modx->config['manager_language'].':usertest:default');

		$this->modx->addPackage('usertest', $this->modx->getOption('core_path') . 'components/usertest/model/');

		if ((include MODX_ASSETS_PATH.'components/usertest/PHPExcel/IOFactory.php') != TRUE) {
			$this->modx->log(modX::LOG_LEVEL_ERROR,'Не удалось загрузить PHPExcel!');
			$this->modx->log(modX::LOG_LEVEL_INFO,'COMPLETED');
			sleep(1);
			return $this->modx->error->success();
		}
		$excel_file = MODX_BASE_PATH.$_POST['excel_file'];
		
		$inputFileType = PHPExcel_IOFactory::identify($excel_file);
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		try {
			if (!($objPHPExcel = $objReader->load($excel_file))) {
				$this->modx->log(modX::LOG_LEVEL_ERROR,'Не удалось загрузить файл! '.$excel_file);
				$this->modx->log(modX::LOG_LEVEL_INFO,'COMPLETED');
				sleep(1);
				return $this->modx->error->success();
			}
		} catch(Exception $e) {
			$this->modx->log(modX::LOG_LEVEL_ERROR,'Не удалось загрузить файл! Exception. '.$excel_file);
			$this->modx->log(modX::LOG_LEVEL_INFO,'COMPLETED');
			sleep(1);
			return $this->modx->error->success();
		}
		
		//вопросы
		$objPHPExcel->setActiveSheetIndex(0);
		$ar = $objPHPExcel->getActiveSheet()->toArray();

		foreach($ar as $k => $ar_colls){
			$data = array(
				'test_id'=>$ar_colls[0],//ID теста
				'q_id'=>$ar_colls[1],//ID вопроса
				'q_parent'=>$ar_colls[2],//ID родителя
				'parent_menuindex'=>$ar_colls[3],//MenuIndex родителя
				'ql_menuindex'=>$ar_colls[4],//MenuIndex
				);
			$q_data = array(	
				'type'=>$ar_colls[5],
				'category_id'=>$ar_colls[6],
				'question'=>$ar_colls[7],
				'max_point'=>$ar_colls[8],//
				'random_answer'=>$ar_colls[9],
				'validate'=>$ar_colls[10],
				'type_file'=>$ar_colls[11],
				'file'=>$ar_colls[12],
				'extended'=>$ar_colls[13],
			);
			$q_data['menuindex'] = 0;
			if((int)$data['test_id'] > 0){
				if(!$data['q_id'] or !$q = $this->modx->getObject('UserTestQuestions',(int)$data['q_id'])){
					if(!$data['parent_menuindex']){
						if($qpl = $this->modx->getObject('UserTestTestQuestionLink',array('test_id'=>(int)$data['test_id'],'menuindex'=>(int)$data['ql_menuindex']))){
							if(!$q = $this->modx->getObject('UserTestQuestions',$qpl->question_id)){
								$q = $this->modx->newObject('UserTestQuestions');
							}
						}else{
							$q = $this->modx->newObject('UserTestQuestions');
						}
					}else{
						if($qpl_p = $this->modx->getObject('UserTestTestQuestionLink',array('test_id'=>(int)$data['test_id'],'menuindex'=>(int)$data['parent_menuindex']))){
							if(!$q = $this->modx->getObject('UserTestQuestions',array('parent'=>$qpl_p->question_id, 'menuindex'=>(int)$data['ql_menuindex']))){
								$q = $this->modx->newObject('UserTestQuestions');
							}
							$q_data['parent'] = $qpl->question_id;
							$q_data['menuindex'] = $data['ql_menuindex'];
						}else{
							$q = $this->modx->newObject('UserTestQuestions');
						}
					}
				}
				if($q){
					if($data['q_parent'] and $q_p = $this->modx->getObject('UserTestQuestions',(int)$data['q_parent'])){
						$q_data['parent'] = $data['q_parent'];
						$q_data['menuindex'] = $data['ql_menuindex'];
					}
					
					$q->fromArray($q_data);
					if($q->save()){
						$this->modx->log(modX::LOG_LEVEL_INFO,'Строка '.($k+1).'. Добавлен вопрос '.$q->question.' id='.$q->id);
						if(!$q_data['menuindex']){
							$cqpl = array('test_id'=>(int)$data['test_id'],'question_id'=>$q->id);
							if(!$qpl = $this->modx->getObject('UserTestTestQuestionLink',$cqpl)){
								$qpl = $this->modx->newObject('UserTestTestQuestionLink');
							}
							$cqpl['menuindex'] = (int)$data['ql_menuindex'];
							$qpl->fromArray($cqpl);
							$qpl->save();
						}
					}else{
						$this->modx->log(modX::LOG_LEVEL_ERROR,'Строка '.($k+1).'. Ошибка сохранения! '.$q->question);
					}
				}
			}
		}
		
		//ответы
		$objPHPExcel->setActiveSheetIndex(1);
		$ar = $objPHPExcel->getActiveSheet()->toArray();

		foreach($ar as $k => $ar_colls){
			$data = array(
				'test_id'=>$ar_colls[0], //ID теста
				'question_id'=>$ar_colls[1], //ID Вопроса
				'q_menuindex'=>$ar_colls[2], //MenuIndex Вопроса
				'parent_menuindex'=>$ar_colls[3], //Менюиндекс родителя вопроса.
				'a_id'=>$ar_colls[4], //ID ответа
				);
			$a_data = array(	
				'menuindex'=>$ar_colls[5], //MenuIndex
				'answer'=>$ar_colls[6], //Текс ответа
				'type_file'=>$ar_colls[7], //Тип файла
				'file'=>$ar_colls[8],//Файл
				'point'=>$ar_colls[9], //Баллы
				'right'=>$ar_colls[10], //Правильный ответ
			);
			if((int)$data['test_id'] > 0){
				if(!$data['question_id'] or !$q = $this->modx->getObject('UserTestQuestions',(int)$data['question_id'])){
					//$this->modx->log(modX::LOG_LEVEL_ERROR,'вопрос с id ='.$data['question_id'].' не найден! ');
					if($data['parent_menuindex']){
						if($qpl_p = $this->modx->getObject('UserTestTestQuestionLink',array('test_id'=>(int)$data['test_id'],'menuindex'=>(int)$data['parent_menuindex']))){
							$q = $this->modx->getObject('UserTestQuestions',array('parent'=>$qpl_p->question_id, 'menuindex'=>(int)$data['q_menuindex']));
						}
						if(!$q){
							$this->modx->log(modX::LOG_LEVEL_ERROR,'Строка '.($k+1).'. Вопрос с Менюиндекс родителя вопроса ='.$data['parent_menuindex'].' и MenuIndex Вопроса='.$data['q_menuindex'].' не найден! ');
						}
					}else{
						if($qpl = $this->modx->getObject('UserTestTestQuestionLink',array('test_id'=>(int)$data['test_id'],'menuindex'=>(int)$data['q_menuindex']))){
							$q = $this->modx->getObject('UserTestQuestions',$qpl->question_id);
						}
					}
				}
				if($q){
					$a_data['question_id'] = $q->id;
				}else{
					$this->modx->log(modX::LOG_LEVEL_ERROR,'Строка '.($k+1).'. Вопрос не найден! ');
				}
				if(!$data['a_id'] or !$a = $this->modx->getObject('UserTestAnswers',(int)$a_data['a_id'])){
					if(!$a = $this->modx->getObject('UserTestAnswers',array('menuindex'=>(int)$a_data['menuindex'],'question_id'=>$q->id))){
						$a = $this->modx->newObject('UserTestAnswers');
					}
				}
				$a->fromArray($a_data);
				if($a->save()){
					$this->modx->log(modX::LOG_LEVEL_INFO,'Строка '.($k+1).'. Добавлен ответ '.$a->answer);
				}else{
					$this->modx->log(modX::LOG_LEVEL_ERROR,'Строка '.($k+1).'. Ошибка сохранения! '.$a->answer);
				}
			}
		}
		$this->modx->log(modX::LOG_LEVEL_INFO,'COMPLETED');
		sleep(1);
		return $this->success("");
	}

	function explodeAndClean($array, $delimiter = ",")
	{
		$array = explode($delimiter, $array);     // Explode fields to array
		$array = array_map("trim", $array);       // Trim array"s values
		$array = array_keys(array_flip($array));  // Remove duplicate fields
		$array = array_filter($array);            // Remove empty values from array

		return $array;
	}
}

return 'ImportTestConsoleProcessor';	
?>
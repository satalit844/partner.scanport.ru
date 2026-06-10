<?php

class UserTestAnswerCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'UserTestAnswers';
    public $classKey = 'UserTestAnswers';
    public $languageTopics = array('usertest');
    //public $permission = 'create';
	
	/**
     * @return bool
     */
    public function beforeSet()
    {
        
		$question_id = trim($this->getProperty('question_id'));
		if($q = $this->modx->getObject('UserTestQuestions',$question_id)){
			if($q->type == 3){
				$answer = strip_tags(trim($this->getProperty('answer')));
				$this->setProperty('answer', $answer);
			}
		}
        $count = $this->modx->getCount('UserTestAnswers',array('question_id'=>$question_id));
		$this->setProperty('menuindex', $count + 1);
        return parent::beforeSet();
    }
	
	public function afterSave() {
		$question_id = trim($this->getProperty('question_id'));
        //echo $question_id; exit;
		$max_point = $this->getMaxPoint($question_id);
		return parent::afterSave();
    }
	
	public function getMaxPoint($question_id)
    {
		if($q = $this->modx->getObject('UserTestQuestions',$question_id)){
			if($q->parent != 0){
				$q = $this->modx->getObject('UserTestQuestions',$q->parent);
			}
		}
		$maxPoint = 0;
		if($q){
			switch($q->type){
				case 8: //Таблица текстовых полей
				case 4: //Открытый вопрос
				case 6: //Комбинированный вариант
				case 10: //usertest_type_questions_combined_radiobutton
					$maxPoint += $q->max_point;
					break;
				case 1: //Одиночный выбор
					$c1 = $this->modx->newQuery('UserTestAnswers');
					$c1->where(array('question_id'=>$q->id));
					$c1->select('MAX(point) as max_point');
					if($max = $this->modx->getObject('UserTestAnswers',$c1)){
					   $maxPoint += $max->max_point;
					}
					break;
				case 2: //Множественный выбор
					$c1 = $this->modx->newQuery('UserTestAnswers');
					$c1->where(array('question_id'=>$q->id));
					$c1->select('SUM(point) as max_point');
					if($max = $this->modx->getObject('UserTestAnswers',$c1)){
					   $maxPoint += $max->max_point;
					}
					break;
				case 3: //Простой текст
					$c1 = $this->modx->newQuery('UserTestAnswers');
					$c1->where(array('question_id'=>$q->id));
					$c1->select('MAX(point) as max_point');
					if($max = $this->modx->getObject('UserTestAnswers',$c1)){
					   $maxPoint += $max->max_point;
					}
					break;
				case 5: //На сопоставление. Простой
					$ext = json_decode($q->extended, 1);
					$q1 = $ext['q'];
					//$type_point = $ext['type_point']; //0 за правильный ответ. 1 за совпадения.
					if($ext['type_point'] == 0){
						$maxPoint += $ext['point'];
					}else{
						$maxPoint += $ext['point']*count($q1);
					}
					break;
				case 6: //Комбинированный вариант
					$c1 = $this->modx->newQuery('UserTestAnswers');
					$c1->where(array('question_id'=>$q->id));
					$c1->select('SUM(point) as max_point');
					if($max = $this->modx->getObject('UserTestAnswers',$c1)){
					   $maxPoint += $max->max_point;
					}
					break;
				case 7: //Таблица чек-боксов
					$Q_childs = $this->modx->getIterator('UserTestQuestions', array('parent'=>$q->id));
					foreach($Q_childs as $qc){
						$c1 = $this->modx->newQuery('UserTestAnswers');
						$c1->where(array('question_id'=>$qc->id));
						$c1->select('SUM(point) as max_point');
						if($max = $this->modx->getObject('UserTestAnswers',$c1)){
						   $maxPoint += $max->max_point;
						}
					}
					break;
				case 9: //Селекты в тексте
					$Q_childs = $this->modx->getIterator('UserTestQuestions', array('parent'=>$q->id));
					foreach($Q_childs as $qc){
						$c1 = $this->modx->newQuery('UserTestAnswers');
						$c1->where(array('question_id'=>$qc->id));
						$c1->select('MAX(point) as max_point');
						if($max = $this->modx->getObject('UserTestAnswers',$c1)){
						   $maxPoint += $max->max_point;
						}
					}
					break;
			}
			//echo $maxPoint." ".$q->id." ".$question_id; exit;
			$q->max_point = $maxPoint; $q->save();
		}
		
		return $maxPoint;
	}

}

return 'UserTestAnswerCreateProcessor';
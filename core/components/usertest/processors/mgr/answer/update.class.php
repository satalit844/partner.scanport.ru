<?php

class UserTestAnswerUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'UserTestAnswers';
    public $classKey = 'UserTestAnswers';
    public $languageTopics = array('usertest');
    //public $permission = 'save';


    /**
     * We doing special check of permission
     * because of our objects is not an instances of modAccessibleObject
     *
     * @return bool|string
     */
    public function beforeSave()
    {
        if (!$this->checkPermissions()) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        if (empty($id)) {
            return $this->modx->lexicon('usertest_answer_err_ns');
        }
		
		$question_id = trim($this->getProperty('question_id'));
		if($q = $this->modx->getObject('UserTestQuestions',$question_id)){
			if($q->type == 3){
				$answer = strip_tags(trim($this->getProperty('answer')));
				$this->setProperty('answer', $answer);
			}
		}
        return parent::beforeSet();
    }
	public function afterSave() {
		$question_id = trim($this->getProperty('question_id'));
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
		$q->max_point = $maxPoint; $q->save();
		}
		return $maxPoint;
	}
}

return 'UserTestAnswerUpdateProcessor';

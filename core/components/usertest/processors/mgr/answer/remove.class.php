<?php

class UserTestAnswerRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'UserTestAnswers';
    public $classKey = 'UserTestAnswers';
    public $languageTopics = array('usertest');
    //public $permission = 'remove';


    /**
     * @return array|string
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $ids = $this->modx->fromJSON($this->getProperty('ids'));
        if (empty($ids)) {
            return $this->failure($this->modx->lexicon('usertest_answer_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var UserTestItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('usertest_answer_err_nf'));
            }
			$question_id = $object->question_id;
            $object->remove();
        }
		$max_point = $this->getMaxPoint($question_id);
        return $this->success();
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

return 'UserTestAnswerRemoveProcessor';
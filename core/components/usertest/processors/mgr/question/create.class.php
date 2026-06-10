<?php

class UserTestQuestionCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'UserTestQuestions';
    public $classKey = 'UserTestQuestions';
    public $languageTopics = array('usertest');
    //public $permission = 'create';
	
	/**
     * @return bool
     */
    public function beforeSet()
    {
		$parent = trim($this->getProperty('parent'));
		if(!$parent){
			$parent = 0;
		}else{
			$count = $this->modx->getCount('UserTestQuestions',array('parent'=>$parent));
			$this->setProperty('menuindex', $count + 1);
		}
        return parent::beforeSet();
    }
	
	/** {@inheritDoc} */
	public function afterSave()
	{
		$parent = $this->object->get('parent');
		$menuindex = $this->object->get('menuindex');
		if($parent and $menuindex > 1){
			if($parent_q = $this->modx->getObject($this->classKey,$parent)){
				if($parent_q->type == 7 or $parent_q->type == 8){
					if($first_q = $this->modx->getObject($this->classKey, array('parent'=>$parent,'menuindex'=>1))){
						$answers = $this->modx->getIterator('UserTestAnswers', array('question_id'=>$first_q->id));
						foreach($answers as $answer){
							$t = $answer->toArray();
							unset($t['id']);
							$t['question_id'] = $this->object->get('id');
							if($a = $this->modx->newObject('UserTestAnswers')){
								$a->fromArray($t);
								$a->save();
							}
						}
					}
				}
			}
		}
		$test_id = trim($this->getProperty('test_id'));
		if($test_id){
			$id = $this->object->get('id');
			if($test_question_link = $this->modx->newObject("UserTestTestQuestionLink",array("test_id"=>$test_id,"question_id"=>$id))){
				$count = $this->modx->getCount('UserTestTestQuestionLink',array('test_id'=>$test_id));
				$test_question_link->menuindex = $count + 1;
				$test_question_link->save();
			}
		}
		if($this->object->get('type') == 12){
			for ($i = 1; $i <= 7; $i++) {
				if($a = $this->modx->newObject('UserTestAnswers')){
					$a->question_id = $this->object->get('id');
					$a->menuindex = $i;
					$a->answer = abs($i-4);
					$a->point = 8-$i;
					$a->save();
				}
			}
		}
		return parent::afterSave();
	}
}

return 'UserTestQuestionCreateProcessor';
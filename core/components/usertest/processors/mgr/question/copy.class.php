<?php

class UserTestQuestionCopyProcessor extends modObjectProcessor {
	public $classKey = 'UserTestQuestions';

	/** {@inheritDoc} */
	public function process() {
		/* @var msProduct $source */
		$ids = $this->modx->fromJSON($this->getProperty('ids'));
		if (empty($ids)) {
            return $this->failure($this->modx->lexicon('usertest_question_err_ns'));
        }
		$test_id = $this->getProperty('test_id');
		$q_copy_ids = array();
		foreach ($ids as $id) {
			if(!$source = $this->modx->getObject($this->classKey, $id)){
				return $this->modx->error->failure();
			}
			
			if($q = $this->modx->newObject($this->classKey)){
				$t = $source->toArray();
				unset($t['id']);
				//$t['test_id'] = $this->getProperty('test_id');
				//$count = $this->modx->getCount('UserTestQuestions',array('test_id'=>$t['test_id'], 'parent'=>0));
				//$t['menuindex'] = $count + 1;
				$q->fromArray($t);
				$q->save();
				if (!empty($test_id)) {
					$count = $this->modx->getCount('UserTestTestQuestionLink',array('test_id'=>$test_id));
					$menuindex = $count + 1;
					if($TestQuestionLink = $this->modx->newObject('UserTestTestQuestionLink')){
						$TestQuestionLink->fromArray(array(
							'menuindex'=>$menuindex,
							'test_id'=>$test_id,
							'question_id'=>$q->id,
						));
						$TestQuestionLink->save();
					}
				} 
				
				$q_copy_ids[] = $q->id;
				if($q->type == 7 or $q->type == 8 or $q->type == 9){
					$q_childs = $this->modx->getIterator($this->classKey, array('parent' => $source->id));
					foreach($q_childs as $q_child){
						if($q1 = $this->modx->newObject($this->classKey)){
							$t = $q_child->toArray();
							unset($t['id']);
							//$t['test_id'] = $this->getProperty('test_id');
							$t['parent'] = $q->id;
							$q1->fromArray($t);
							$q1->save();
							$answers = $this->modx->getIterator('UserTestAnswers', array('question_id'=>$q_child->id));
							foreach($answers as $answer){
								$t = $answer->toArray();
								unset($t['id']);
								$t['question_id'] = $q1->id;
								if($a = $this->modx->newObject('UserTestAnswers')){
									$a->fromArray($t);
									$a->save();
								}
							}
						}
					}
				}else{
					$answers = $this->modx->getIterator('UserTestAnswers', array('question_id'=>$source->id));
					foreach($answers as $answer){
						$t = $answer->toArray();
						unset($t['id']);
						$t['question_id'] = $q->id;
						if($a = $this->modx->newObject('UserTestAnswers')){
							$a->fromArray($t);
							$a->save();
						}
					}
				}
			}
		}
		return $this->modx->error->success('',array('q_copy_ids'=>implode(",",$q_copy_ids)));
	}

}

return 'UserTestQuestionCopyProcessor';
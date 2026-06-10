<?php

class UserTestQuestionInsertProcessor extends modObjectProcessor {
	public $classKey = 'UserTestTestQuestionLink';

	/** {@inheritDoc} */
	public function process() {
		/* @var msProduct $source */
		$ids = $this->modx->fromJSON($this->getProperty('ids'));
		if (empty($ids)) {
            return $this->failure($this->modx->lexicon('usertest_question_err_ns'));
        }
		$test_id = $this->getProperty('test_id');
		if (empty($test_id)) {
            return $this->failure($this->modx->lexicon('usertest_question_err_ns'));
        }
		foreach ($ids as $id) {
			$dublicat = $this->modx->getCount('UserTestTestQuestionLink',array('test_id'=>$test_id,'question_id'=>$id));
			if($dublicat){
				return $this->failure($this->modx->lexicon('usertest_question_err_dublicat'));
			}
			$count = $this->modx->getCount('UserTestTestQuestionLink',array('test_id'=>$test_id));
			$menuindex = $count + 1;
			if($TestQuestionLink = $this->modx->newObject($this->classKey)){
				$TestQuestionLink->fromArray(array(
					'menuindex'=>$menuindex,
					'test_id'=>$test_id,
					'question_id'=>$id,
				));
				$TestQuestionLink->save();
			}
		}
		return $this->modx->error->success('',array());
	}

}

return 'UserTestQuestionInsertProcessor';
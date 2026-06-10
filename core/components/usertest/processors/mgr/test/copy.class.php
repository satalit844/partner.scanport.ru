<?php

class UserTestTestCopyProcessor extends modObjectProcessor {
	public $classKey = 'UserTestTests';

	/** {@inheritDoc} */
	public function process() {
		/* @var msProduct $source */
		$ids = $this->modx->fromJSON($this->getProperty('ids'));
		if (empty($ids)) {
            return $this->failure($this->modx->lexicon('usertest_item_err_ns'));
        }
		foreach ($ids as $id) {
			if(!$source = $this->modx->getObject($this->classKey, $id)){
				return $this->modx->error->failure();
			}
			
			if($target = $this->modx->newObject($this->classKey)){
				$t = $source->toArray();
				unset($t['id']);
				$target->fromArray($t);
				if($target->save()){
					$TestQuestionLinkSource = $this->modx->getCollection('UserTestTestQuestionLink', array('test_id'=>$source->id));
					foreach($TestQuestionLinkSource as $tqls){
						if($TestQuestionLink = $this->modx->newObject('UserTestTestQuestionLink')){
							$TestQuestionLink->fromArray(array(
								'menuindex'=>$tqls->menuindex,
								'test_id'=>$target->id,
								'question_id'=>$tqls->question_id,
							));
							$TestQuestionLink->save();
						}
					}
					$UserTestGroupsLinkSource = $this->modx->getCollection('UserTestGroupsLink', array('test_id'=>$source->id));
					foreach($UserTestGroupsLinkSource as $tqls){
						if($UserTestGroupsLink = $this->modx->newObject('UserTestGroupsLink')){
							$UserTestGroupsLink->fromArray(array(
								'menuindex'=>$tqls->menuindex,
								'test_id'=>$target->id,
								'group_id'=>$tqls->group_id,
							));
							$UserTestGroupsLink->save();
						}
					}
					$UserTestTestVariantLinkSource = $this->modx->getCollection('UserTestTestVariantLink', array('test_id'=>$source->id));
					foreach($UserTestTestVariantLinkSource as $tqls){
						if($UserTestTestVariantLink = $this->modx->newObject('UserTestTestVariantLink')){
							$UserTestTestVariantLink->fromArray($tqls->toArray());
							$UserTestTestVariantLink->test_id = $target->id;
							$UserTestTestVariantLink->save();
						}
					}
				}
			}
		}
		return $this->modx->error->success('',array());
	}

}

return 'UserTestTestCopyProcessor';
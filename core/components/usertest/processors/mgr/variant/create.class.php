<?php

class UserTestVariantCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'UserTestVariants';
    public $classKey = 'UserTestVariants';
    public $languageTopics = array('usertest');
    //public $permission = 'create';
	
	public function afterSave()
    {
        $variant_id = $this->object->get('id');
		$variant_set_id = (int)$this->getProperty('variant_set_id');
		$tests = $this->modx->getCollection("UserTestTests",array('variant_set_id'=>$variant_set_id));
		foreach($tests as $test){
			if($variant_link = $this->modx->newObject("UserTestTestVariantLink")){
				$variant_link->fromArray(array(
					'test_id'=>$test->id,
					'variant_id'=>$variant_id,
					));
				$variant_link->save();
			}
		}
        return parent::afterSave();
    }

}

return 'UserTestVariantCreateProcessor';
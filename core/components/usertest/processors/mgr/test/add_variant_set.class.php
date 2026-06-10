<?php

class UserTestTestAddVariantProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'UserTestTests';
    public $classKey = 'UserTestTests';
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
            return $this->modx->lexicon('usertest_test_err_ns');
        }

        return parent::beforeSet();
    }
	
	public function afterSave()
    {
        $test_id = $this->object->get('id');
		$variant_set_id = (int)$this->getProperty('variant_set_id');
		
		$variant_links = $this->modx->getCollection("UserTestTestVariantLink",array('test_id'=>$test_id));
		foreach($variant_links as $vl){
			$vl->remove();
		}
		
		$variants = $this->modx->getCollection("UserTestVariants",array('variant_set_id'=>$variant_set_id));
		foreach($variants as $v){
			if($variant_link = $this->modx->newObject("UserTestTestVariantLink")){
				$variant_link->fromArray(array(
					'test_id'=>$test_id,
					'variant_id'=>$v->id,
					));
				$variant_link->save();
			}
		}
        return parent::afterSave();
    }
}

return 'UserTestTestAddVariantProcessor';

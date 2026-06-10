<?php

class UserTestTestVariantLinkCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'UserTestTestVariantLink';
    public $classKey = 'UserTestTestVariantLink';
    public $languageTopics = array('usertest');
    //public $permission = 'create';
	
	/**
     * @return bool
     */
    public function beforeSet()
    {
        
		$test_id = trim($this->getProperty('test_id'));
        if (empty($test_id)) {
            $this->modx->error->addField('test_id', $this->modx->lexicon('usertest_test_question_link_err_test_id'));
        }
		$variant_id = trim($this->getProperty('variant_id'));
		if (empty($variant_id)) {
            $this->modx->error->addField('variant_id', $this->modx->lexicon('usertest_test_question_link_err_variant_id'));
        }
		
        return parent::beforeSet();
    }
	
}

return 'UserTestTestVariantLinkCreateProcessor';
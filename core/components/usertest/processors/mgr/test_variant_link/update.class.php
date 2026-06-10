<?php

class UserTestTestVariantLinkUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'UserTestTestVariantLink';
    public $classKey = 'UserTestTestVariantLink';
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
            return $this->modx->lexicon('usertest_item_err_ns');
        }
		
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

return 'UserTestTestVariantLinkUpdateProcessor';

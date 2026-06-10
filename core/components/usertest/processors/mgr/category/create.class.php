<?php

class UserTestCategoryCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'UserTestCategorys';
    public $classKey = 'UserTestCategorys';
    public $languageTopics = array('usertest');
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('usertest_test_err_name'));
        }

        return parent::beforeSet();
    }

}

return 'UserTestCategoryCreateProcessor';
<?php

class UserTestGroupCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'UserTestGroups';
    public $classKey = 'UserTestGroups';
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

return 'UserTestGroupCreateProcessor';
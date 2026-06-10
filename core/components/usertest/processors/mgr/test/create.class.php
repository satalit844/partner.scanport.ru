<?php

class UserTestTestCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'UserTestTests';
    public $classKey = 'UserTestTests';
    public $languageTopics = array('usertest');
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('usertest_item_err_name'));
        }
        return parent::beforeSet();
    }

}

return 'UserTestTestCreateProcessor';
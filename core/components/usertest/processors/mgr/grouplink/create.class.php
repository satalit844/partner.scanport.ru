<?php

class UserTestGroupsLinkCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'UserTestGroupsLink';
    public $classKey = 'UserTestGroupsLink';
    public $languageTopics = array('usertest');
    //public $permission = 'create';
	
	/**
     * @return bool
     */
    public function beforeSet()
    {
        $test_id = trim($this->getProperty('test_id'));
        $group_id = trim($this->getProperty('group_id'));
		if ($this->modx->getCount($this->classKey, array('group_id' => $group_id, 'test_id' => $test_id))) {
            $this->modx->error->addField('test_id', $this->modx->lexicon('usertest_group_err_ae'));
        }
        $count = $this->modx->getCount('UserTestGroupsLink',array('group_id'=>$group_id));
		$this->setProperty('menuindex', $count + 1);
		
        return parent::beforeSet();
    }

}

return 'UserTestGroupsLinkCreateProcessor';
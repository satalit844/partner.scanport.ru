<?php

class UserTestTestQuestionLinkCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'UserTestTestQuestionLink';
    public $classKey = 'UserTestTestQuestionLink';
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
		$question_id = trim($this->getProperty('question_id'));
		if (empty($question_id)) {
            $this->modx->error->addField('question_id', $this->modx->lexicon('usertest_test_question_link_err_queston_id'));
        }
		
        $count = $this->modx->getCount('UserTestTestQuestionLink',array('test_id'=>$test_id));
		$this->setProperty('menuindex', $count + 1);
		
        return parent::beforeSet();
    }
	
}

return 'UserTestTestQuestionLinkCreateProcessor';
<?php

class UserTestTestQuestionLinkUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'UserTestTestQuestionLink';
    public $classKey = 'UserTestTestQuestionLink';
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
		$question_id = trim($this->getProperty('question_id'));
		if (empty($question_id)) {
            $this->modx->error->addField('question_id', $this->modx->lexicon('usertest_test_question_link_err_queston_id'));
        }
		
        return parent::beforeSet();
    }
}

return 'UserTestTestQuestionLinkUpdateProcessor';

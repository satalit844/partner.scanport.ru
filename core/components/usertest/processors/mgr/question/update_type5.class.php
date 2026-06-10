<?php

class UserTestQuestionType5UpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'UserTestQuestions';
    public $classKey = 'UserTestQuestions';
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
            return $this->modx->lexicon('usertest_question_err_ns');
        }
		$q = $this->getProperty('q');
		$a = $this->getProperty('a');
		$type_point = $this->getProperty('type_point');
		$point = $this->getProperty('point');
		$q1 = array(); $a1 = array();
		for ($i = 0; $i < 10; $i++) {
		   if(!$q[$i] or !$a[$i]){
			   break;
		   }
		   $q1[$i] = $q[$i];
		   $a1[$i] = $a[$i];
		}
		$ext = array('q'=>$q1,'a'=>$a1, 'type_point'=>$type_point, 'point'=>$point);
		$this->setProperty('extended', json_encode($ext));
        return parent::beforeSet();
    }
}

return 'UserTestQuestionType5UpdateProcessor';

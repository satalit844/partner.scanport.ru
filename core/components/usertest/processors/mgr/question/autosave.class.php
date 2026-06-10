<?php

class UserTestQuestionAutosaveProcessor extends modObjectProcessor
{
    public $objectType = 'UserTestQuestions';
    public $classKey = 'UserTestQuestions';
    public $languageTopics = array('usertest');
    //public $permission = 'save';


    public function process() {
        $data = $this->getProperties();
		$data = json_decode($data['data'],true);
		if(empty($data['id'])){
			return $this->failure($this->modx->lexicon($this->objectType.'_err_save'));
		}
		$q = $this->modx->getObject($this->classKey,$data['id']);
		$q->menuindex = $data['menuindex'];
		if($q->save()){
			return $this->success('',$data);
		}else{
			return $this->failure($this->modx->lexicon($this->objectType.'_err_save'));
		}
	}
}

return 'UserTestQuestionAutosaveProcessor';

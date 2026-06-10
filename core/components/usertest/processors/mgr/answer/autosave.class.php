<?php

class UserTestAnswerAutosaveProcessor extends modObjectProcessor
{
    public $objectType = 'UserTestAnswers';
    public $classKey = 'UserTestAnswers';
    public $languageTopics = array('usertest');
    //public $permission = 'save';


    public function process() {
        $data = $this->getProperties();
		$data = json_decode($data['data'],true);
		if(empty($data['id'])){
			return $this->failure($this->modx->lexicon($this->objectType.'_err_save'));
		}
		$a = $this->modx->getObject($this->classKey,$data['id']);
		$a->menuindex = $data['menuindex'];
		if($a->save()){
			return $this->success('',$data);
		}else{
			return $this->failure($this->modx->lexicon($this->objectType.'_err_save'));
		}
	}
}

return 'UserTestAnswerAutosaveProcessor';

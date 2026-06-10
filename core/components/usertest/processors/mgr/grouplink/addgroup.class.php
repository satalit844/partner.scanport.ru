<?php

class UserTestAddGroupsLinkRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'UserTestGroupsLink';
    public $classKey = 'UserTestGroupsLink';
    public $languageTopics = array('usertest');
    //public $permission = 'remove';


    /**
     * @return array|string
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $test_ids = $this->modx->fromJSON($this->getProperty('test_ids'));
        if (empty($test_ids)) {
            return $this->failure($this->modx->lexicon('usertest_item_err_ns'));
        }
		$group_id = $this->getProperty('group_id');
        if (empty($group_id)) {
            return $this->failure($this->modx->lexicon('usertest_item_err_ns'));
        }
        foreach ($test_ids as $test_id) {
            /** @var UserTestItem $object */
            if (!$object = $this->modx->getObject($this->classKey, array('group_id'=>$group_id, 'test_id'=>$test_id))) {
                if($object = $this->modx->newObject($this->classKey)){
					$object->group_id = $group_id;
					$object->test_id = $test_id;
					$object->save();
				}
				//return $this->failure($this->modx->lexicon('usertest_item_err_nf'));
            }

            //$object->remove();
        }

        return $this->success();
    }

}

return 'UserTestAddGroupsLinkRemoveProcessor';
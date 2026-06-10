<?php

class UserTestGroupRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'UserTestGroups';
    public $classKey = 'UserTestGroups';
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

        $ids = $this->modx->fromJSON($this->getProperty('ids'));
        if (empty($ids)) {
            return $this->failure($this->modx->lexicon('usertest_item_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var UserTestItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('usertest_item_err_nf'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'UserTestGroupRemoveProcessor';
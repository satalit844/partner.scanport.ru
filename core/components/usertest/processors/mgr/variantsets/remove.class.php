<?php

class UserTestVariantSetsRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'UserTestVariantSets';
    public $classKey = 'UserTestVariantSets';
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
            if($this->modx->getCount("UserTestVariants",['haker'=>1,'variant_set_id'=>$object->id]) == 0){
                $object->remove();
            }else{
                return $this->failure($this->modx->lexicon('usertest_not_remove_haker'));
            }
        }

        return $this->success();
    }

}

return 'UserTestVariantSetsRemoveProcessor';
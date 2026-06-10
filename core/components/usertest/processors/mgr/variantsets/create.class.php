<?php

class UserTestVariantSetsCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'UserTestVariantSets';
    public $classKey = 'UserTestVariantSets';
    public $languageTopics = array('usertest');
    //public $permission = 'create';

}

return 'UserTestVariantSetsCreateProcessor';
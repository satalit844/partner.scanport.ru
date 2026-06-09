<?php

require_once dirname(__FILE__) . '/model/training/training.class.php';

abstract class TrainingManagerController extends modExtraManagerController
{
    /** @var Training $training */
    public $training;

    public function initialize()
    {
        $this->training = new Training($this->modx);
        parent::initialize();
    }

    public function getLanguageTopics()
    {
        return ['training:default'];
    }

    public function checkPermissions()
    {
        return true;
    }
}

class IndexManagerController extends TrainingManagerController
{
    public static function getDefaultController()
    {
        return 'home';
    }
}
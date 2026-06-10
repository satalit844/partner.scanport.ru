<?php

class UserTestResultGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'UserTestResults';
    public $classKey = 'UserTestResults';
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'DESC';
    //public $permission = 'list';


    /**
     * We do a special check of permissions
     * because our objects is not an instances of modAccessibleObject
     *
     * @return boolean|string
     */
    public function beforeQuery()
    {
        if (!$this->checkPermissions()) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $date1 = trim($this->getProperty('date1'));
		$date2 = trim($this->getProperty('date2'));
		$c->leftJoin('modUser','modUser', '`'.$this->classKey.'`.`user_id` = `modUser`.`id`');
		$c->leftJoin('UserTestTests','UserTestTests', '`'.$this->classKey.'`.`test_id` = `UserTestTests`.`id`');
		$c->leftJoin('UserTestVariants','UserTestVariants', '`'.$this->classKey.'`.`variant_id` = `UserTestVariants`.`id`');
		$c->leftJoin('UserTestResultStatus','UserTestResultStatus', '`'.$this->classKey.'`.`status_id` = `UserTestResultStatus`.`id`');
		
		$Columns = $this->modx->getSelectColumns($this->classKey, $this->classKey, '', array(), true);
		$c->select($Columns . ', `modUser`.`username` as `reg_user_name`, `UserTestTests`.`name` as `test_name`, `UserTestVariants`.`result` as `variant`, `UserTestResultStatus`.`label` as `status`');
		
		$query = trim($this->getProperty('query'));
        if ($query) {
			$c->where(array(
				'`UserTestTests`.`id`' => "{$query}",
				'OR:`modUser`.`username`:LIKE' => "%{$query}%",
				'OR:`UserTestResults`.`user_name`:LIKE' => "%{$query}%",
				'OR:`UserTestResults`.`user_email`:LIKE' => "%{$query}%",
				'OR:`UserTestTests`.`name`:LIKE' => "%{$query}%",
				'OR:`UserTestVariants`.`id`:=' => "{$query}",
				'OR:`UserTestVariants`.`result`:LIKE' => "%{$query}%",
			));
		}
		$status_id = trim($this->getProperty('status_id'));
		if ($status_id) {
			$c->where(array(
				'`'.$this->classKey.'`.`status_id`' => "{$status_id}",
			));
		}
		
		$test = trim($this->getProperty('test'));
		if ($test) {
			$c->where(array(
				'`'.$this->classKey.'`.`test_id`' => "{$test}",
				'OR:`UserTestTests`.`name`:LIKE' => "%{$test}%",
			));
		}
		
		$user = trim($this->getProperty('user'));
		if ($user) {
			$c->where(array(
				'`'.$this->classKey.'`.`user_name`:LIKE' => "%{$user}%",
			));
		}
		if($date1){
			$c->where(array(
				'`'.$this->classKey.'`.`date`:>=' => strftime('%Y-%m-%d %H:%M:%S',strtotime($date1)),
			));
		}
		if($date2){
			$c->where(array(
				'`'.$this->classKey.'`.`date`:<=' => strftime('%Y-%m-%d %H:%M:%S',strtotime($date2)),
			));
		}
		/* $c->prepare();
		echo $c->toSQL();
		exit; */
        return $c;
    }


    /**
     * @param xPDOObject $object
     *
     * @return array
     */
    public function prepareRow(xPDOObject $object)
    {
        $array = $object->toArray();
        $array['actions'] = array();
		
		// Edit
        $array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('usertest_userresult_update'),
            //'multiple' => $this->modx->lexicon('usertest_items_update'),
            'action' => 'updateItem',
            'button' => true,
            'menu' => true,
        );
		
		$array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-exclamation-circle action-green',
            'title' => $this->modx->lexicon('usertest_answers'),
            'action' => 'showAnswers',
            'button' => true,
            'menu' => true,
        );
		$array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-exclamation-triangle action-green',
            'title' => $this->modx->lexicon('usertest_userresult_category'),
            'action' => 'showCategorys',
            'button' => true,
            'menu' => true,
        );
        // Remove
        $array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('usertest_test_remove'),
            'multiple' => $this->modx->lexicon('usertest_tests_remove'),
            'action' => 'removeItem',
            'button' => true,
            'menu' => true,
        );

		
        return $array;
    }

}

return 'UserTestResultGetListProcessor';
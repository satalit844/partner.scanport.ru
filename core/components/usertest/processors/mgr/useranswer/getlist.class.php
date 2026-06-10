<?php

class UserTestResultAnswerGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'UserTestResultAnswers';
    public $classKey = 'UserTestResultAnswers';
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
        $c->leftJoin('UserTestQuestions','UserTestQuestions', '`'.$this->classKey.'`.`question_id` = `UserTestQuestions`.`id`');
		//$c->leftJoin('UserTestAnswers','UserTestAnswers', '`'.$this->classKey.'`.`answer_id` = `UserTestAnswers`.`id`');
		$c->leftJoin('UserTestQuestions','UserTestQuestions2', '`UserTestQuestions`.`parent` = `UserTestQuestions2`.`id`');
		$Columns = $this->modx->getSelectColumns($this->classKey, $this->classKey, '', array(), true);
		$c->select($Columns . ', `UserTestQuestions`.`question` as `user_question`, `UserTestQuestions`.`max_point` as `user_question_max_point`, IFNULL(`UserTestQuestions2`.`menuindex`,`UserTestQuestions`.`menuindex`) as `user_menuindex`');
		
		$query = trim($this->getProperty('query'));
        if ($query) {
			$c->where(array(
				'`UserTestQuestions`.`id`' => "{$query}",
				'OR:`UserTestQuestions`.`question`:LIKE' => "%{$query}%",
				//'OR:`UserTestAnswers`.`answer`:LIKE' => "%{$query}%",
				//'OR:`UserTestAnswers`.`id`:=' => "{$query}",
			));
		}
		$result_id = trim($this->getProperty('result_id'));
		if ($result_id) {
			$c->where(array(
				'`'.$this->classKey.'`.`result_id`' => $result_id,
			));
		}
		//$c->sortby('user_menuindex','ASC');
		//$c->sortby('`UserTestQuestions`.`menuindex`','ASC');
		/* $c->prepare();
		echo $c->toSQL();
		exit; */ 
		//echo $this->modx->getCount($this->classKey,$c); exit;
        return $c;
    }
	
	/**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryAfterCount(xPDOQuery $c)
    {
		$c->sortby('user_menuindex','ASC');
		$c->sortby('`UserTestQuestions`.`menuindex`','ASC');
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
		
        return $array;
    }
}

return 'UserTestResultAnswerGetListProcessor';
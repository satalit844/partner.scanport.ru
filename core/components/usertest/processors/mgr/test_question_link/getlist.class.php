<?php

class UserTestTestQuestionLinkGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'UserTestTestQuestionLink';
    public $classKey = 'UserTestTestQuestionLink';
    public $defaultSortField = 'menuindex';
    public $defaultSortDirection = 'ASC';
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
		$c->leftJoin('UserTestTests','UserTestTests', '`'.$this->classKey.'`.`test_id` = `UserTestTests`.`id`');
		$c->leftJoin('UserTestCategorys','UserTestCategorys', '`UserTestQuestions`.`category_id` = `UserTestCategorys`.`id`');
		
		$Columns = $this->modx->getSelectColumns($this->classKey, $this->classKey, '', array(), true);
		$c->select($Columns . ', `UserTestQuestions`.`question` as `question_name`, 
		`UserTestQuestions`.`id` as q_id,
		`UserTestQuestions`.`type`,
		`UserTestQuestions`.`category_id`,
		`UserTestCategorys`.`name` as `category_name`,
		`UserTestTests`.`name` as `test_name`');
		$c->where(array(
				'`UserTestQuestions`.`parent`' => 0,
			));
		
		$query = trim($this->getProperty('query'));
		
        if ($query) {
            $c->where(array(
				'`UserTestQuestions`.`question`:LIKE' => "%{$query}%",
				'OR:`UserTestTests`.`name`:LIKE' => "%{$query}%",
            ));
        }
		$test_id = trim($this->getProperty('test_id'));
		if ($test_id) {
			$c->where(array(
				'`'.$this->classKey.'`.`test_id`' => $test_id,
			));
		}
		$question_id = trim($this->getProperty('question_id'));
		if ($question_id) {
			$c->where(array(
				'`'.$this->classKey.'`.`question_id`' => $question_id,
			));
		}
		$type = trim($this->getProperty('type'));
		if ($type) {
			$c->where(array(
				'`UserTestQuestions`.`type`' => $type,
			));
		}
		$category = trim($this->getProperty('category'));
		if ($category) {
			$c->where(array(
				'`UserTestQuestions`.`category_id`' => $category,
			));
		}
		//$c->prepare();echo $c->toSQL();exit;
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
            'title' => $this->modx->lexicon('usertest_item_update'),
            //'multiple' => $this->modx->lexicon('usertest_items_update'),
            'action' => 'updateItem',
            'button' => true,
            'menu' => true,
        );
		
		$array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-question-circle action-green',
            'title' => $this->modx->lexicon('usertest_test_questions'),
            'action' => 'editQuestions',
            'button' => true,
            'menu' => true,
        );
		$array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-exclamation-circle action-green',
            'title' => $this->modx->lexicon('usertest_answers'),
            'action' => 'editAnswers',
            'button' => true,
            'menu' => true,
        );
        // Remove
        $array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('usertest_item_remove'),
            'multiple' => $this->modx->lexicon('usertest_items_remove'),
            'action' => 'removeItem',
            'button' => true,
            'menu' => true,
        );
		
        return $array;
    }

}

return 'UserTestTestQuestionLinkGetListProcessor';
<?php

class UserTestQuestionGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'UserTestQuestions';
    public $classKey = 'UserTestQuestions';
    public $defaultSortField = 'id';
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
        $c->leftJoin('UserTestCategorys','UserTestCategorys', '`'.$this->classKey.'`.`category_id` = `UserTestCategorys`.`id`');
		
		$Columns = $this->modx->getSelectColumns($this->classKey, $this->classKey, '', array(), true);
		$c->select($Columns . ', `UserTestCategorys`.`name` as `category_name`');
		
		$query = trim($this->getProperty('query'));
		
        if ($query) {
            $c->where(array(
				'`'.$this->classKey.'`.`question`:LIKE' => "%{$query}%",
            ));
        }
		$test_id = trim($this->getProperty('test_id'));
		if ($test_id) {
			$c->where(array(
				'`'.$this->classKey.'`.`test_id`' => $test_id,
			));
		}
		$parent = trim($this->getProperty('parent'));
		if(!$parent){
			$parent = 0;
		}else{
			$this->defaultSortField = 'menuindex';
		}
		$c->where(array(
			'`'.$this->classKey.'`.`parent`' => $parent,
		));
		
		$q_ids = trim($this->getProperty('q_ids'));
		if ($q_ids) {
			$c->where(array(
				'`'.$this->classKey.'`.`id`:IN' => explode(",",$q_ids),
			));
		}
		$type = trim($this->getProperty('type'));
		if ($type) {
			$c->where(array(
				'`'.$this->classKey.'`.`type`' => $type,
			));
		}
		$category = trim($this->getProperty('category'));
		if ($category) {
			$c->where(array(
				'`'.$this->classKey.'`.`category_id`' => $category,
			));
		}
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
            'title' => $this->modx->lexicon('usertest_question_update'),
            //'multiple' => $this->modx->lexicon('usertest_items_update'),
            'action' => 'updateItem',
            'button' => true,
            'menu' => true,
        );

        // Remove
        $array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('usertest_question_remove'),
            'multiple' => $this->modx->lexicon('usertest_questions_remove'),
            'action' => 'removeItem',
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
		$array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-check action-blue',
            'title' => $this->modx->lexicon('usertest_question_edit_link'),
            'action' => 'editTestQuestionLink',
            'button' => true,
            'menu' => true,
        );
		$array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-copy',
            'title' => $this->modx->lexicon('usertest_question_copy'),
			'multiple' => $this->modx->lexicon('usertest_questions_copy'),
            'action' => 'copyQuestion',
            'button' => true,
            'menu' => true,
        );
        return $array;
    }

}

return 'UserTestQuestionGetListProcessor';
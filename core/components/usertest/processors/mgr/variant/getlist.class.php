<?php

class UserTestVariantGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'UserTestVariants';
    public $classKey = 'UserTestVariants';
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
				'`'.$this->classKey.'`.`result`:LIKE' => "%{$query}%",
            ));
        }
		$variant_set_id = trim($this->getProperty('variant_set_id'));
		if ($variant_set_id) {
			$c->where(array(
				'`'.$this->classKey.'`.`variant_set_id`' => $variant_set_id,
			));
		}
		$category_id = trim($this->getProperty('category_id'));
		if (isset($category_id) and $category_id !='') {
			$c->where(array(
				'`'.$this->classKey.'`.`category_id`' => $category_id,
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
		
        return $array;
    }

}

return 'UserTestVariantGetListProcessor';
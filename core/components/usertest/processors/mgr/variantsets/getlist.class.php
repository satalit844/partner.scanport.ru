<?php

class UserTestVariantSetsGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'UserTestVariantSets';
    public $classKey = 'UserTestVariantSets';
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
		$query = trim($this->getProperty('query'));
		
        if ($query) {
            $c->where(array(
				'`'.$this->classKey.'`.`name`:LIKE' => "%{$query}%",
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
            'title' => $this->modx->lexicon('usertest_item_update'),
            //'multiple' => $this->modx->lexicon('usertest_items_update'),
            'action' => 'updateItem',
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
		
		$array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-cog action-blue',
            'title' => $this->modx->lexicon('usertest_test_variants'),
            'action' => 'editVariants',
            'button' => true,
            'menu' => true,
        );
		$array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-check action-blue',
            'title' => $this->modx->lexicon('usertest_question_edit_link'),
            'action' => 'showTests',
            'button' => true,
            'menu' => true,
        );
		
        return $array;
    }

}

return 'UserTestVariantSetsGetListProcessor';
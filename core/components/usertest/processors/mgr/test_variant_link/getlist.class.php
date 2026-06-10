<?php

class UserTestTestVariantLinkGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'UserTestTestVariantLink';
    public $classKey = 'UserTestTestVariantLink';
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
        $c->leftJoin('UserTestVariants','UserTestVariants', '`'.$this->classKey.'`.`variant_id` = `UserTestVariants`.`id`');
		$c->leftJoin('UserTestCategorys','UserTestCategorys', '`UserTestVariants`.`category_id` = `UserTestCategorys`.`id`');
		
		//$Columns = $this->modx->getSelectColumns($this->classKey, $this->classKey, '', array(), true);
		$c->select('`'.$this->classKey.'`.`id`, `'.$this->classKey.'`.`test_id`, `'.$this->classKey.'`.`variant_id`, `'.$this->classKey.'`.`use_custom_point`,
		IF(`'.$this->classKey.'`.`use_custom_point` = 1, `'.$this->classKey.'`.`start_point`, `UserTestVariants`.`start_point`) as start_point,
		IF(`'.$this->classKey.'`.`use_custom_point` = 1, `'.$this->classKey.'`.`end_point`, `UserTestVariants`.`end_point`) as end_point,
		`UserTestVariants`.`category_id`, `UserTestCategorys`.`name` as `category_name`, 
		`UserTestVariants`.`result`, `UserTestVariants`.`passed`');//IF(1>2,2,3)
		
		$query = trim($this->getProperty('query'));
		
        if ($query) {
            $c->where(array(
				'`UserTestVariants`.`result`:LIKE' => "%{$query}%",
            ));
        }
		$test_id = trim($this->getProperty('test_id'));
		if ($test_id) {
			$c->where(array(
				'`'.$this->classKey.'`.`test_id`' => $test_id,
			));
		}
		$variant_id = trim($this->getProperty('variant_id'));
		if ($variant_id) {
			$c->where(array(
				'`'.$this->classKey.'`.`variant_id`' => $variant_id,
			));
		}
		//$c->prepare(); echo $c->toSQL();exit;
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
		$array['passed'] = filter_var($array['passed'], FILTER_VALIDATE_BOOLEAN);
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
            'icon' => 'icon icon-cog action-blue',
            'title' => $this->modx->lexicon('usertest_test_variants'),
            'action' => 'editVariant',
            'button' => true,
            'menu' => true,
        );
		
        return $array;
    }

}

return 'UserTestTestVariantLinkGetListProcessor';
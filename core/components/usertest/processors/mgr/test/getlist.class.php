<?php

class UserTestTestsGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'UserTestTests';
    public $classKey = 'UserTestTests';
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
        $c->leftJoin('UserTestGroupsLink','UserTestGroupsLink', '`'.$this->classKey.'`.`id` = `UserTestGroupsLink`.`test_id`');
		
		$Columns = $this->modx->getSelectColumns($this->classKey, $this->classKey, '', array(), true);
		$c->select($Columns);
		
		$query = trim($this->getProperty('query'));
        if ($query) {
            $c->where(array(
                '`'.$this->classKey.'`.`name`:LIKE' => "%{$query}%",
                'OR:`'.$this->classKey.'`.`description`:LIKE' => "%{$query}%",
            ));
        }
		$variant_set_id = trim($this->getProperty('variant_set_id'));
		if ($variant_set_id) {
			$c->where(array(
				'`'.$this->classKey.'`.`variant_set_id`' => $variant_set_id,
			));
		}
		$group = trim($this->getProperty('group'));
		if ($group) {
			$c->where(array(
				'`UserTestGroupsLink`.`group_id`' => $group,
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
		$array['groups'] = "";
		$groups1 = array();
		$c = $this->modx->newQuery('UserTestGroupsLink');
		$c->leftJoin('UserTestGroups','UserTestGroups', '`UserTestGroupsLink`.`group_id` = `UserTestGroups`.`id`');
		$c->where(array(
				'`UserTestGroupsLink`.`test_id`' => $array['id'],
			));
		$c->select('`UserTestGroups`.`name` as `group_name`');
		$groups = $this->modx->getIterator('UserTestGroupsLink', $c);
		foreach($groups as $group){
			$groups1[] = $group->group_name;
		}
		$array['groups'] = implode(',', $groups1);
		
        // Edit
        $array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('usertest_test_update'),
            //'multiple' => $this->modx->lexicon('usertest_items_update'),
            'action' => 'updateItem',
            'button' => true,
            'menu' => true,
        );

        if (!$array['active']) {
            $array['actions'][] = array(
                'cls' => '',
                'icon' => 'icon icon-power-off action-green',
                'title' => $this->modx->lexicon('usertest_test_enable'),
                'multiple' => $this->modx->lexicon('usertest_tests_enable'),
                'action' => 'enableItem',
                'button' => true,
                'menu' => true,
            );
        } else {
            $array['actions'][] = array(
                'cls' => '',
                'icon' => 'icon icon-power-off action-gray',
                'title' => $this->modx->lexicon('usertest_test_disable'),
                'multiple' => $this->modx->lexicon('usertest_tests_disable'),
                'action' => 'disableItem',
                'button' => true,
                'menu' => true,
            );
        }

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
		
		$array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-question-circle action-green',
            'title' => $this->modx->lexicon('usertest_test_questions'),
            'action' => 'editTestQuestionLink',
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
            'icon' => 'icon icon-group action-blue',
            'title' => $this->modx->lexicon('usertest_grouplink_create'),
            'multiple' => $this->modx->lexicon('usertest_grouplink_create'),
            'action' => 'addGroup',
            'button' => true,
            'menu' => true,
        );
		$array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-copy',
            'title' => $this->modx->lexicon('usertest_test_copy'),
			'multiple' => $this->modx->lexicon('usertest_tests_copy'),
            'action' => 'copyTest',
            'button' => true,
            'menu' => true,
        );
        return $array;
    }

}

return 'UserTestTestsGetListProcessor';
<?php

class UserTestTestGroupGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'UserTestGroupsLink';
    public $classKey = 'UserTestGroupsLink';
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
        $c->leftJoin('UserTestTests','UserTestTests', '`'.$this->classKey.'`.`test_id` = `UserTestTests`.`id`');
		
		$Columns = $this->modx->getSelectColumns($this->classKey, $this->classKey, '', array(), true);
		$c->select($Columns . ', `UserTestTests`.`name` as `test_name`, `UserTestTests`.`description` as `test_description`');
		
		$query = trim($this->getProperty('query'));
        if ($query) {
            $c->where(array(
                '`UserTestTests`.`name`:LIKE' => "%{$query}%",
                'OR:`UserTestTests`.`description`:LIKE' => "%{$query}%",
            ));
        }
		
		$group_id = trim($this->getProperty('group_id'));
		if ($group_id) {
			$c->where(array(
				'`'.$this->classKey.'`.`group_id`' => $group_id,
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

        // Remove
        $array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('usertest_grouplink_remove'),
            'multiple' => $this->modx->lexicon('usertest_grouplinks_remove'),
            'action' => 'removeItem',
            'button' => true,
            'menu' => true,
        );
		
        return $array;
    }

}

return 'UserTestTestGroupGetListProcessor';
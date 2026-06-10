<?php

class UserTestInvitesGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'UserTestInvites';
    public $classKey = 'UserTestInvites';
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
        $c->leftJoin('UserTestTests','UserTestTests', '`'.$this->classKey.'`.`test_id` = `UserTestTests`.`id`');
		$query = trim($this->getProperty('query'));
        if ($query) {
            $c->where(array(
                '`'.$this->classKey.'`.`user_email`:LIKE' => "%{$query}%",
                'OR:`'.$this->classKey.'`.`user_auth_code`:LIKE' => "%{$query}%",
            ));
        }
		
		$test = trim($this->getProperty('test'));
		if ($test) {
			$c->where(array(
				'`'.$this->classKey.'`.`test_id`' => "{$test}",
				'OR:`UserTestTests`.`name`:LIKE' => "%{$test}%",
			));
		}
		$date1 = trim($this->getProperty('date1'));
		$date2 = trim($this->getProperty('date2'));
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
            'title' => $this->modx->lexicon('usertest_invite_update'),
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
            'title' => $this->modx->lexicon('usertest_invite_remove'),
            'multiple' => $this->modx->lexicon('usertest_invites_remove'),
            'action' => 'removeItem',
            'button' => true,
            'menu' => true,
        );
		
        return $array;
    }

}

return 'UserTestInvitesGetListProcessor';
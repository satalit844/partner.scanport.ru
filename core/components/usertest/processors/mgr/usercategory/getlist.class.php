<?php

class UserTestResultCategoryGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'UserTestResultCategorys';
    public $classKey = 'UserTestResultCategorys';
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
        $c->leftJoin('UserTestCategorys','UserTestCategorys', '`'.$this->classKey.'`.`category_id` = `UserTestCategorys`.`id`');
		$c->leftJoin('UserTestVariants','UserTestVariants', '`'.$this->classKey.'`.`variant_id` = `UserTestVariants`.`id`');
		
		$Columns = $this->modx->getSelectColumns($this->classKey, $this->classKey, '', array(), true);
		$c->select($Columns . ', `UserTestCategorys`.`name` as `category_name`, `UserTestVariants`.`result` as `variant`');
		
		$query = trim($this->getProperty('query'));
        if ($query) {
			$c->where(array(
				'`UserTestVariants`.`result`:LIKE' => "{$query}",
				'OR:`UserTestCategorys`.`name`:LIKE' => "%{$query}%",
				'OR:`UserTestVariants`.`id`' => "%{$query}%",
				//'OR:`UserTestAnswers`.`id`:=' => "{$query}",
			));
		}
		$result_id = trim($this->getProperty('result_id'));
		if ($result_id) {
			$c->where(array(
				'`'.$this->classKey.'`.`result_id`' => $result_id,
			));
		}
		/* $c->prepare();
		echo $c->toSQL();
		exit; */
        return $c;
    }
	
}

return 'UserTestResultCategoryGetListProcessor';
<?php

class UserTestResultAnswerUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'UserTestResultAnswers';
    public $classKey = 'UserTestResultAnswers';
    public $languageTopics = array('usertest');
    //public $permission = 'save';


    /**
     * We doing special check of permission
     * because of our objects is not an instances of modAccessibleObject
     *
     * @return bool|string
     */
    public function beforeSave()
    {
        if (!$this->checkPermissions()) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        if (empty($id)) {
            return $this->modx->lexicon('usertest_question_err_ns');
        }

        return parent::beforeSet();
    }
	
	public function afterSave()
	{
		$result_id = (int)$this->getProperty('result_id');
		if($Result = $this->modx->getObject('UserTestResults',$result_id)){
			$c = $this->modx->newQuery('UserTestResultAnswers');
			$c->select("sum(point) as sum_point");
			$c->where(array('result_id'=>$result_id));
			//$c->prepare();
			//echo $c->toSQL();
			if($object = $this->modx->getObject('UserTestResultAnswers', $c)){
				$Result->test_point = $object->get('sum_point');
				$test_point = $Result->test_point;
				
				$Variants = $this->modx->getIterator('UserTestVariants', array('test_id'=>$Result->test_id));
				foreach($Variants as $var){
					if($test_point >= $var->start_point and $test_point <= $var->end_point){
						$var_id = $var->id;
						$var_result = $var->result;
						break;
					}
				}
				
				$Result->variant_id = $var_id;
				$Result->save();
			}
			if($test = $this->modx->getObject('UserTestTests',$Result->test_id)){
				if($test->use_category){
					$c = $this->modx->newQuery('UserTestResultAnswers');
					$c->leftJoin('UserTestQuestions', 'UserTestQuestions', 'UserTestResultAnswers.question_id = UserTestQuestions.id');
					$c->select("`UserTestQuestions`.`category_id` as cat_id, sum(`UserTestResultAnswers`.`point`) as sum_point");
					$c->where(array('result_id'=>$result_id));
					$c->groupby('`UserTestQuestions`.`category_id`');
					//$c->prepare();echo $c->toSQL();
					
					$cat_points = $this->modx->getIterator('UserTestResultAnswers', $c);
					foreach($cat_points as $cp){
						$var_id = 0;$var_result="";
						$Variants = $this->modx->getIterator('UserTestVariants', array('test_id'=>$id, 'category_id'=> $cp->cat_id));
						foreach($Variants as $var){
							if($cp->sum_point >= $var->start_point and $cp->sum_point <= $var->end_point){
								$var_id = $var->id;
								$var_result = $var->result;
								break;
							}
						}
						if($cat_result = $this->modx->getObject('UserTestResultCategorys',array('result_id'=>$Result->id, 'category_id'=>$cp->cat_id))){
						   $cat_result->result_id = $Result->id;
						   $cat_result->category_id = $cp->cat_id;
						   $cat_result->variant_id = $var_id;
						   $cat_result->cat_point = $cp->sum_point;
						   $cat_result->save();
						}
					}
					
				}
			}
		}
		return true;
	}
}

return 'UserTestResultAnswerUpdateProcessor';

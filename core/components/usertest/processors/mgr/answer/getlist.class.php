<?php

class UserTestAnswerGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'UserTestAnswers';
    public $classKey = 'UserTestAnswers';
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
        $query = trim($this->getProperty('query'));
		$question_id = trim($this->getProperty('question_id'));
        if ($query) {
            $c->where(array(
                'answer:LIKE' => "%{$query}%"
            ));
        }
		if ($question_id) {
			$c->where(array(
				'question_id' => $question_id,
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
            'title' => $this->modx->lexicon('usertest_answer_update'),
            //'multiple' => $this->modx->lexicon('usertest_items_update'),
            'action' => 'updateItem',
            'button' => true,
            'menu' => true,
        );

        // Remove
        $array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('usertest_answer_remove'),
            'multiple' => $this->modx->lexicon('usertest_answers_remove'),
            'action' => 'removeItem',
            'button' => true,
            'menu' => true,
        );
		
        return $array;
    }

}

return 'UserTestAnswerGetListProcessor';
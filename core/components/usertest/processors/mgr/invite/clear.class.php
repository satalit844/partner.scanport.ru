<?php

class UserTestInvitesRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'UserTestInvites';
    public $classKey = 'UserTestInvites';
    public $languageTopics = array('usertest');
    //public $permission = 'remove';


    /**
     * @return array|string
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $days = $this->getProperty('days');
        if (empty($days)) {
            return $this->failure($this->modx->lexicon('usertest_item_err_ns'));
        }
		$old_date = strftime("%Y-%m-%d %H:%M:%S", strtotime('-'.$days.' days'));
		
		$invites = $this->modx->getCollection($this->classKey,array('date:<'=>$old_date));
        foreach ($invites as $invite) {
            $invite->remove();
        }

        return $this->success();
    }

}

return 'UserTestInvitesRemoveProcessor';
<?php

class TrainingManagerLinkCreateProcessor extends modObjectCreateProcessor
{
    public $classKey = 'TrainingUserManagerLink';
    public $objectType = 'training.manager.link';

    public function checkPermissions()
    {
        return true;
    }

    public function beforeSet()
    {
        $managerUserId = (int)$this->getProperty('manager_user_id');
        $employeeUserId = (int)$this->getProperty('employee_user_id');
        $isActive = (int)((string)$this->getProperty('is_active', '1') === '0' ? 0 : 1);

        if ($managerUserId <= 0) {
            return 'Не выбран директор';
        }
        if ($employeeUserId <= 0) {
            return 'Не выбран сотрудник';
        }
        if ($managerUserId === $employeeUserId) {
            return 'Директор и сотрудник не могут совпадать';
        }

        $exists = $this->modx->getObject('TrainingUserManagerLink', [
            'manager_user_id' => $managerUserId,
            'employee_user_id' => $employeeUserId,
        ]);
        if ($exists) {
            return 'Такая связь уже существует';
        }

        $this->setProperty('manager_user_id', $managerUserId);
        $this->setProperty('employee_user_id', $employeeUserId);
        $this->setProperty('is_active', $isActive);
        $this->setProperty('createdon', date('Y-m-d H:i:s'));
        $this->setProperty('createdby', $this->modx->user ? (int)$this->modx->user->get('id') : 0);

        return parent::beforeSet();
    }
}

return 'TrainingManagerLinkCreateProcessor';

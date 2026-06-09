<?php

class TrainingManagerLinkUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'TrainingUserManagerLink';
    public $objectType = 'training.manager.link';

    public function checkPermissions()
    {
        return true;
    }

    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        $managerUserId = (int)$this->getProperty('manager_user_id');
        $employeeUserId = (int)$this->getProperty('employee_user_id');
        $isActive = (int)((string)$this->getProperty('is_active', '1') === '0' ? 0 : 1);

        if ($id <= 0) {
            return 'Не указан ID связи';
        }
        if ($managerUserId <= 0) {
            return 'Не выбран директор';
        }
        if ($employeeUserId <= 0) {
            return 'Не выбран сотрудник';
        }
        if ($managerUserId === $employeeUserId) {
            return 'Директор и сотрудник не могут совпадать';
        }

        $c = $this->modx->newQuery('TrainingUserManagerLink');
        $c->where([
            'manager_user_id' => $managerUserId,
            'employee_user_id' => $employeeUserId,
            'id:!=' => $id,
        ]);
        if ($this->modx->getCount('TrainingUserManagerLink', $c) > 0) {
            return 'Такая связь уже существует';
        }

        $this->setProperty('manager_user_id', $managerUserId);
        $this->setProperty('employee_user_id', $employeeUserId);
        $this->setProperty('is_active', $isActive);

        return parent::beforeSet();
    }
}

return 'TrainingManagerLinkUpdateProcessor';

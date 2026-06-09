<?php

require_once __DIR__ . '/_helpers.php';

class TrainingModuleTestLinkTestsProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        if (!TrainingModuleTestLinkHelper::ensureUserTestPackage($this->modx)) {
            return $this->failure('Не удалось подключить компонент usertest');
        }

        $query = trim((string)$this->getProperty('query', ''));
        $limit = max(0, (int)$this->getProperty('limit', 20));
        $start = max(0, (int)$this->getProperty('start', 0));
        $activeOnly = TrainingModuleTestLinkHelper::boolValue($this->getProperty('active_only', 1), 1) === 1;

        $c = $this->modx->newQuery('UserTestTests');
        if ($activeOnly) {
            $c->where(['active' => 1]);
        }
        if ($query !== '') {
            $c->where([
                'name:LIKE' => '%' . $query . '%',
                'OR:description:LIKE' => '%' . $query . '%',
            ]);
        }

        $countQuery = clone $c;
        $total = (int)$this->modx->getCount('UserTestTests', $countQuery);

        $c->sortby('name', 'ASC');
        $c->sortby('id', 'ASC');
        if ($limit > 0) {
            $c->limit($limit, $start);
        }

        $results = [];
        /** @var UserTestTests $test */
        foreach ($this->modx->getCollection('UserTestTests', $c) as $test) {
            $id = (int)$test->get('id');
            $name = trim((string)$test->get('name'));
            $description = trim((string)$test->get('description'));
            $display = '#' . $id . ' ' . ($name !== '' ? $name : ('Тест #' . $id));
            if ($description !== '') {
                $display .= ' — ' . mb_substr($description, 0, 120, 'UTF-8');
            }

            $results[] = [
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'active' => (int)$test->get('active') === 1 ? 1 : 0,
                'type' => (int)$test->get('type'),
                'test_type' => (int)$test->get('test_type'),
                'count_questions' => (int)$test->get('count_questions'),
                'time_test' => (int)$test->get('time_test'),
                'display' => $display,
            ];
        }

        return $this->outputArray($results, $total);
    }
}

return 'TrainingModuleTestLinkTestsProcessor';

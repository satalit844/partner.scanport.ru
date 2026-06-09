<?php

class TrainingManagerLinkRemoveProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $ids = $this->collectIds();
        if (empty($ids)) {
            return $this->failure('Не выбраны связи для удаления');
        }

        $removed = 0;
        foreach ($ids as $id) {
            $item = $this->modx->getObject('TrainingUserManagerLink', ['id' => $id]);
            if ($item && $item->remove()) {
                $removed++;
            }
        }

        return $this->success('Связи удалены', ['removed' => $removed]);
    }

    protected function collectIds()
    {
        $raw = $this->getProperty('ids', $this->getProperty('id', ''));
        if (is_array($raw)) {
            return array_values(array_filter(array_map('intval', $raw)));
        }

        $raw = trim((string)$raw);
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('intval', array_map('trim', explode(',', $raw)))));
    }
}

return 'TrainingManagerLinkRemoveProcessor';

<?php

require_once dirname(__DIR__) . '/_video_helper.php';

class TrainingLessonSlideRemoveProcessor extends modProcessor
{
    public function checkPermissions(){return true;}

    public function process()
    {
        $ids = $this->collectIds();
        if (!$ids) { return $this->failure('Не выбраны слайды'); }
        $table = TrainingLessonVideoHelper::slidesTable($this->modx);
        foreach ($ids as $id) {
            $stmt = $this->modx->prepare('DELETE FROM `' . $table . '` WHERE `id` = :id');
            if ($stmt) { $stmt->execute([':id' => (int)$id]); }
        }
        return $this->success('Слайды удалены');
    }

    protected function collectIds()
    {
        $raw = $this->getProperty('ids', $this->getProperty('id', ''));
        if (is_array($raw)) { return array_values(array_filter(array_map('intval', $raw))); }
        $raw = trim((string)$raw);
        return $raw === '' ? [] : array_values(array_filter(array_map('intval', explode(',', $raw))));
    }
}
return 'TrainingLessonSlideRemoveProcessor';

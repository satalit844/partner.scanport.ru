<?php

require_once dirname(__DIR__) . '/_video_helper.php';

class TrainingLessonQualitiesGetListProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $lessonVideoId = (int)$this->getProperty('lesson_video_id');
        if ($lessonVideoId <= 0) {
            return $this->successJson([]);
        }

        $table = TrainingLessonVideoHelper::qualitiesTable($this->modx);
        $stmt = $this->modx->prepare('SELECT * FROM `' . $table . '` WHERE `lesson_video_id` = :lesson_video_id ORDER BY `id` ASC');
        if (!$stmt || !$stmt->execute([':lesson_video_id' => $lessonVideoId])) {
            return $this->failure('Не удалось получить качества видео');
        }

        $rows = [];
        foreach ((array)$stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $row['is_default'] = (int)!empty($row['is_default']);
            $row['is_active'] = (int)!empty($row['is_active']);
            $rows[] = $row;
        }

        return $this->successJson($rows);
    }

    protected function successJson(array $rows)
    {
        return $this->modx->toJSON([
            'success' => true,
            'total' => count($rows),
            'results' => array_values($rows),
        ]);
    }
}
return 'TrainingLessonQualitiesGetListProcessor';

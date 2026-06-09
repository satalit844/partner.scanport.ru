<?php

require_once dirname(__DIR__) . '/_video_helper.php';

class TrainingLessonSlidesGetListProcessor extends modProcessor
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

        $table = TrainingLessonVideoHelper::slidesTable($this->modx);
        $stmt = $this->modx->prepare('SELECT * FROM `' . $table . '` WHERE `lesson_video_id` = :lesson_video_id ORDER BY `slide_no` ASC, `id` ASC');
        if (!$stmt || !$stmt->execute([':lesson_video_id' => $lessonVideoId])) {
            return $this->failure('Не удалось получить слайды');
        }

        $rows = [];
        foreach ((array)$stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $row['is_active'] = (int)!empty($row['is_active']);
            $row['timecode_human'] = $this->formatMilliseconds((int)$row['timecode_ms']);
            $rows[] = $row;
        }

        return $this->successJson($rows);
    }

    protected function formatMilliseconds($milliseconds)
    {
        $seconds = (int) floor($milliseconds / 1000);
        $hours = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return $hours > 0
            ? sprintf('%d:%02d:%02d', $hours, $minutes, $seconds)
            : sprintf('%d:%02d', $minutes, $seconds);
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
return 'TrainingLessonSlidesGetListProcessor';

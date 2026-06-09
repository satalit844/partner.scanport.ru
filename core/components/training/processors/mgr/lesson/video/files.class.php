<?php

require_once dirname(__DIR__) . '/_video_helper.php';

class TrainingLessonVideoFilesProcessor extends modProcessor
{
    public function checkPermissions(){return true;}

    public function process()
    {
        $lessonId = (int)$this->getProperty('lesson_id');
        $lesson = TrainingLessonVideoHelper::getLesson($this->modx, $lessonId);
        if (!$lesson) {
            return $this->outputArray([]);
        }

        $module = $lesson->getOne('Module');
        $dirs = [];
        if ($module) {
            $legacy = TrainingMediaHelper::resolveLessonVideoDirs($this->modx, $lesson);
            $dirs[] = $legacy['base_absolute'];
        }
        $basePath = rtrim($this->modx->getOption('base_path'), '/\\') . '/';
        $dirs[] = $basePath . 'assets/uploads/training/';
        $dirs[] = $basePath . 'assets/training/';

        $results = TrainingMediaHelper::gatherFiles($this->modx, $dirs, ['mkv', 'mp4', 'm3u8', 'mov', 'avi', 'webm'], 8, 300);
        return $this->outputArray($results);
    }

    protected function outputArray(array $rows)
    {
        return $this->modx->toJSON(['success' => true, 'total' => count($rows), 'results' => array_values($rows)]);
    }
}
return 'TrainingLessonVideoFilesProcessor';

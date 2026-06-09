<?php

require_once dirname(__DIR__) . '/_video_helper.php';
require_once dirname(dirname(__DIR__)) . '/module/slide/_helpers.php';

class TrainingLessonSlideAvailableProcessor extends modProcessor
{
    public function checkPermissions(){return true;}

    public function process()
    {
        $lessonId = (int)$this->getProperty('lesson_id');
        if ($lessonId <= 0) { return $this->outputArray([]); }
        $scan = TrainingModuleSlideHelper::scanLessonSlides($this->modx, $lessonId);
        $rows = [];
        foreach ((array)$scan['slides'] as $slide) {
            $rows[] = [
                'path' => $slide['path'],
                'name' => $slide['filename'],
                'filename' => $slide['filename'],
                'slide_no' => (int)$slide['slide_no'],
                'dir' => $scan['dir']['web'],
            ];
        }
        return $this->outputArray($rows);
    }

    protected function outputArray(array $rows)
    {
        return $this->modx->toJSON(['success' => true, 'total' => count($rows), 'results' => array_values($rows)]);
    }
}
return 'TrainingLessonSlideAvailableProcessor';

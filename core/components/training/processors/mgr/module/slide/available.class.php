<?php

require_once dirname(__FILE__) . '/_helpers.php';

class TrainingModuleSlideAvailableProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $lessonId = (int)$this->getProperty('lesson_id');
        if ($lessonId <= 0) {
            return $this->outputArray([]);
        }

        $query = trim((string)$this->getProperty('query', ''));
        $scan = TrainingModuleSlideHelper::scanLessonSlides($this->modx, $lessonId);
        $results = [];

        foreach ($scan['slides'] as $slide) {
            if ($query !== '' && stripos($slide['filename'], $query) === false && stripos($slide['path'], $query) === false) {
                continue;
            }

            $results[] = [
                'path' => $slide['path'],
                'name' => $slide['filename'],
                'filename' => $slide['filename'],
                'slide_no' => (int)$slide['slide_no'],
                'dir' => $scan['dir']['web'],
            ];
        }

        return $this->outputArray($results);
    }

    protected function outputArray(array $results)
    {
        return $this->modx->toJSON([
            'success' => true,
            'total' => count($results),
            'results' => array_values($results),
        ]);
    }
}

return 'TrainingModuleSlideAvailableProcessor';

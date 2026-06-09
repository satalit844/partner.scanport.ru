<?php

require_once dirname(dirname(__DIR__)) . '/_media_helper.php';

class TrainingCoursePresentationFilesProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $courseId = (int)$this->getProperty('course_id');
        if ($courseId <= 0) {
            return $this->outputArray([]);
        }

        /** @var TrainingCourse $course */
        $course = $this->modx->getObject('TrainingCourse', ['id' => $courseId]);
        if (!$course) {
            return $this->outputArray([]);
        }

        $dirs = TrainingMediaHelper::resolveCoursePresentationDirs($this->modx, $course);
        $basePath = rtrim($this->modx->getOption('base_path'), '/\\') . '/';
        $query = trim((string)$this->getProperty('query', ''));

        $scanDirs = [
            $dirs['base_absolute'],
            $basePath . 'assets/uploads/training/',
            $basePath . 'assets/training/',
        ];

        $results = TrainingMediaHelper::gatherFiles($this->modx, $scanDirs, ['ppt', 'pptx', 'pdf'], 4, 200);
        if ($query !== '') {
            $results = array_values(array_filter($results, function ($row) use ($query) {
                return stripos($row['path'], $query) !== false || stripos($row['name'], $query) !== false;
            }));
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

return 'TrainingCoursePresentationFilesProcessor';

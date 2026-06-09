<?php

require_once dirname(dirname(__DIR__)) . '/_media_helper.php';

class TrainingModuleVideoFilesProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $moduleId = (int)$this->getProperty('module_id');
        if ($moduleId <= 0) {
            return $this->outputArray([]);
        }

        /** @var TrainingModule $module */
        $module = $this->modx->getObject('TrainingModule', ['id' => $moduleId]);
        if (!$module) {
            return $this->outputArray([]);
        }

        $dirs = TrainingMediaHelper::resolveModuleVideoDirs($this->modx, $module);
        $basePath = rtrim($this->modx->getOption('base_path'), '/\\') . '/';
        $query = trim((string)$this->getProperty('query', ''));

        $scanDirs = [
            $dirs['base_absolute'],
            $basePath . 'assets/uploads/training/',
            $basePath . 'assets/training/courses/' . (int)$module->get('course_id') . '/',
            $basePath . 'assets/training/',
        ];

        $results = TrainingMediaHelper::gatherFiles($this->modx, $scanDirs, ['mkv', 'mp4', 'm3u8', 'mov', 'avi', 'webm'], 6, 300);
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

return 'TrainingModuleVideoFilesProcessor';

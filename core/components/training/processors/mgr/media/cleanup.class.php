<?php

class TrainingMediaCleanupProcessor extends modProcessor
{
    /** @var Training */
    protected $training;

    public function checkPermissions()
    {
        return true;
    }

    public function initialize()
    {
        $corePath = $this->modx->getOption(
            'training.core_path',
            null,
            $this->modx->getOption('core_path') . 'components/training/'
        );

        require_once $corePath . 'model/training/training.class.php';
        require_once $corePath . 'processors/mgr/_media_helper.php';

        $this->training = new Training($this->modx);

        return parent::initialize();
    }

    public function process()
    {
        $dryRun = (int)$this->getProperty('dry_run', 1) === 1;
        $maxList = max(10, min(200, (int)$this->getProperty('max_list', 30)));

        $scanDirs = TrainingMediaHelper::getTrainingMediaBaseDirs($this->modx);
        $referenced = TrainingMediaHelper::collectReferencedMediaFiles($this->modx);
        $allFiles = TrainingMediaHelper::gatherFiles(
            $this->modx,
            $scanDirs,
            ['ppt', 'pptx', 'pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'mkv', 'mov', 'avi', 'webm', 'm3u8'],
            10,
            10000
        );

        $orphans = [];
        $orphansCount = 0;
        $orphansBytes = 0;

        foreach ($allFiles as $file) {
            $path = !empty($file['path']) ? TrainingMediaHelper::normalizeWebPath($file['path']) : '';
            if ($path === '') {
                continue;
            }

            if (isset($referenced[$path])) {
                continue;
            }

            $orphansCount++;
            $orphansBytes += (int)($file['filesize'] ?? 0);
            if (count($orphans) < $maxList) {
                $orphans[] = $file;
            }
        }

        $result = [
            'dry_run' => $dryRun ? 1 : 0,
            'scan_dirs' => array_values(array_filter(array_map(function ($dir) {
                return TrainingMediaHelper::normalizeWebPath($dir);
            }, $scanDirs))),
            'referenced_count' => count($referenced),
            'scanned_count' => count($allFiles),
            'orphans_count' => $orphansCount,
            'orphans_bytes' => $orphansBytes,
            'orphans_preview' => $orphans,
            'deleted_count' => 0,
            'deleted_bytes' => 0,
            'failed_count' => 0,
            'failed_preview' => [],
        ];

        if ($dryRun || $orphansCount === 0) {
            return $this->success($dryRun ? 'Проверка завершена' : 'Удалять нечего', $result);
        }

        $deletedCount = 0;
        $deletedBytes = 0;
        $failed = [];
        $stopAt = rtrim($this->modx->getOption('base_path'), '/\\') . '/assets/training/';

        foreach ($allFiles as $file) {
            $path = !empty($file['path']) ? TrainingMediaHelper::normalizeWebPath($file['path']) : '';
            if ($path === '' || isset($referenced[$path])) {
                continue;
            }

            $absolute = TrainingMediaHelper::resolveLocalPath($this->modx, $path);
            if ($absolute === '') {
                $absolute = TrainingMediaHelper::webPathToFs($this->modx, $path);
            }

            if ($absolute !== '' && is_file($absolute) && @unlink($absolute)) {
                $deletedCount++;
                $deletedBytes += (int)($file['filesize'] ?? 0);
                TrainingMediaHelper::removeEmptyParentDirs(dirname($absolute), $stopAt);
                continue;
            }

            if (count($failed) < $maxList) {
                $failed[] = $file;
            }
        }

        $result['deleted_count'] = $deletedCount;
        $result['deleted_bytes'] = $deletedBytes;
        $result['failed_count'] = max(0, $orphansCount - $deletedCount);
        $result['failed_preview'] = $failed;

        return $this->success('Очистка завершена', $result);
    }
}

return 'TrainingMediaCleanupProcessor';

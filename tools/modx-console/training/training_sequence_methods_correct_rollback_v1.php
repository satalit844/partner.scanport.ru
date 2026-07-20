<?php
/**
 * MODX Console — rollback correct Training sequence methods v1.
 */

$startedAt = microtime(true);

$corePath = rtrim(
    (string)$modx->getOption(
        'training.core_path',
        null,
        MODX_CORE_PATH . 'components/training/'
    ),
    '/\\'
) . DIRECTORY_SEPARATOR;

$targetFile = $corePath
    . 'model/training/services/trainingprogress.class.php';

$backupDir = rtrim(MODX_ASSETS_PATH, '/\\')
    . DIRECTORY_SEPARATOR
    . 'training_backups'
    . DIRECTORY_SEPARATOR
    . 'sequence_methods_correct_v1';

$output = array();

function trainingCorrectMethodsRollbackOut(array $items)
{
    echo implode('; ', $items);
}

try {
    $backups = glob(
        $backupDir
        . DIRECTORY_SEPARATOR
        . 'trainingprogress.before_*.php'
    );

    if (!$backups) {
        throw new RuntimeException('BACKUP_NOT_FOUND');
    }

    usort(
        $backups,
        function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        }
    );

    $backupFile = $backups[0];

    if (!is_file($backupFile)) {
        throw new RuntimeException('BACKUP_FILE_MISSING');
    }

    $backupSource = (string)file_get_contents($backupFile);

    if (
        $backupSource === ''
        || strpos($backupSource, '<?php') === false
    ) {
        throw new RuntimeException('BACKUP_FILE_INVALID');
    }

    $tempFile = $targetFile
        . '.rollback-'
        . date('Ymd_His')
        . '.tmp';

    if (
        file_put_contents(
            $tempFile,
            $backupSource,
            LOCK_EX
        ) === false
    ) {
        throw new RuntimeException('TEMP_WRITE_FAILED');
    }

    if (!rename($tempFile, $targetFile)) {
        @unlink($tempFile);

        throw new RuntimeException('FILE_RESTORE_FAILED');
    }

    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate($targetFile, true);
    }

    if ($modx->cacheManager) {
        $modx->cacheManager->refresh();
    }

    $output[] = 'RESULT=SUCCESS';
    $output[] = 'METHOD_FILE_RESTORED=YES';
    $output[] = 'PROGRESS_TABLES_CHANGED=NO';
    $output[] = 'BACKUP='
        . str_replace(MODX_BASE_PATH, '/', $backupFile);
    $output[] = 'ERRORS=0';
    $output[] = 'DURATION_SEC=' . round(
        microtime(true) - $startedAt,
        4
    );
} catch (Throwable $e) {
    $output[] = 'RESULT=FAILED';
    $output[] = 'CHANGES_COMMITTED=NO';
    $output[] = 'PROGRESS_TABLES_CHANGED=NO';
    $output[] = 'ERROR=' . preg_replace(
        '/[^A-Za-z0-9_=\-\.\/]/',
        '_',
        $e->getMessage()
    );
}

trainingCorrectMethodsRollbackOut($output);

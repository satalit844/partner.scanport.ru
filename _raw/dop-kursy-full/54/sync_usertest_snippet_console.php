<?php
/**
 * MODX Console: sync UserTest snippet from static file and clear cache.
 * Run after replacing core/components/usertest/elements/snippets/snippet.usertest.php
 */

echo "=== Sync UserTest snippet ===\n";

$snippetName = 'UserTest';
$relativeFile = 'core/components/usertest/elements/snippets/snippet.usertest.php';
$absoluteFile = MODX_BASE_PATH . $relativeFile;

$s = $modx->getObject('modSnippet', array('name' => $snippetName));
if (!$s) {
    echo "FAIL: snippet {$snippetName} not found\n";
    return;
}

echo "snippet id: " . $s->get('id') . "\n";
echo "file: {$absoluteFile}\n";

if (!is_file($absoluteFile)) {
    echo "FAIL: file not found\n";
    return;
}

$content = file_get_contents($absoluteFile);
$content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
$content = preg_replace('/^<\?php\s*/', '', $content);

if (strpos($content, 'rtrim($__pdoToolsCore, "/\\")') !== false || strpos($content, 'TRAINING_STAGE40_LOCAL_PDOFETCH_SERVICE') !== false) {
    echo "FAIL: file still contains broken Stage40 local service block\n";
    return;
}

$lintFile = MODX_CORE_PATH . 'cache/usertest_snippet_lint_' . time() . '.php';
file_put_contents($lintFile, "<?php\n" . $content);
$lint = array();
$code = 0;
@exec('php -l ' . escapeshellarg($lintFile) . ' 2>&1', $lint, $code);
@unlink($lintFile);

if ($code !== 0) {
    echo "FAIL: PHP lint failed\n";
    echo implode("\n", $lint) . "\n";
    return;
}

echo "OK: PHP lint passed\n";

$backupDir = MODX_CORE_PATH . 'components/training/backups/usertest_restore_' . date('Ymd_His') . '/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}
file_put_contents($backupDir . 'snippet_92_UserTest.db.php', (string)$s->get('snippet'));
file_put_contents($backupDir . 'snippet_92_UserTest.file.php', file_get_contents($absoluteFile));
echo "backup: {$backupDir}\n";

$s->set('snippet', $content);
$s->set('static_file', $relativeFile);
if (!$s->save()) {
    echo "FAIL: snippet save failed\n";
    return;
}

$modx->cacheManager->refresh();

echo "OK: UserTest snippet synced and MODX cache cleared\n";

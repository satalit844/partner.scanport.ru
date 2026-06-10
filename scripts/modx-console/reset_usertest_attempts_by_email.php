<?php
/**
 * MODX Console script: reset UserTest attempts by email.
 *
 * Usage:
 * 1. Paste into MODX Console with <?php.
 * 2. Set $email.
 * 3. Run with $APPLY = false first.
 * 4. If counts are correct, set $APPLY = true and run again.
 *
 * What it resets:
 * - UserTestResults for the user/email.
 * - UserTestResultAnswers linked to those results.
 * - UserTestResultCategorys linked to those results.
 * - TrainingUserTestStatus rows for this user.
 *
 * Backup:
 * - assets/_backups/usertest_reset_attempts/YYYYmmdd_HHMMSS/backup.json
 */

set_time_limit(0);

$email = 'deemon.seerbachev1998@gmail.com';
$APPLY = false; // first run false, then true

$out = array();
$out[] = '=== RESET USERTEST ATTEMPTS ===';
$out[] = 'Email: ' . $email;
$out[] = 'Mode: ' . ($APPLY ? 'APPLY / DELETE' : 'CHECK ONLY');

$corePath = $modx->getOption('core_path');

$modx->addPackage('usertest', $corePath . 'components/usertest/model/');
$modx->addPackage('training', $corePath . 'components/training/model/');

$user = $modx->getObject('modUser', array('username' => $email));

if (!$user) {
    $profile = $modx->getObject('modUserProfile', array('email' => $email));
    if ($profile) {
        $user = $modx->getObject('modUser', (int)$profile->get('internalKey'));
    }
}

$userId = $user ? (int)$user->get('id') : 0;

$out[] = 'User ID: ' . ($userId ?: 'NOT FOUND');

$resultIds = array();
$resultsBackup = array();

$c = $modx->newQuery('UserTestResults');

$where = array(
    'user_email' => $email,
);

if ($userId > 0) {
    $where = array(
        array('user_email' => $email),
        array('OR:user_id:=' => $userId),
    );
}

$c->where($where);
$c->sortby('id', 'ASC');

$results = $modx->getCollection('UserTestResults', $c);

foreach ($results as $result) {
    $arr = $result->toArray();
    $resultIds[] = (int)$arr['id'];
    $resultsBackup[] = $arr;
}

$resultIds = array_values(array_unique(array_filter($resultIds)));

$out[] = 'UserTestResults found: ' . count($resultIds);

$answersBackup = array();
$categoriesBackup = array();

if ($resultIds) {
    $answers = $modx->getCollection('UserTestResultAnswers', array('result_id:IN' => $resultIds));
    foreach ($answers as $answer) {
        $answersBackup[] = $answer->toArray();
    }

    $categories = $modx->getCollection('UserTestResultCategorys', array('result_id:IN' => $resultIds));
    foreach ($categories as $cat) {
        $categoriesBackup[] = $cat->toArray();
    }
}

$out[] = 'UserTestResultAnswers found: ' . count($answersBackup);
$out[] = 'UserTestResultCategorys found: ' . count($categoriesBackup);

$statusesBackup = array();

if ($userId > 0) {
    $statuses = $modx->getCollection('TrainingUserTestStatus', array('user_id' => $userId));
    foreach ($statuses as $status) {
        $statusesBackup[] = $status->toArray();
    }
}

$out[] = 'TrainingUserTestStatus found: ' . count($statusesBackup);

if (!$APPLY) {
    $out[] = '';
    $out[] = 'CHECK complete. Nothing deleted.';
    $out[] = 'If counts are correct, set $APPLY = true and run again.';
    return implode("\n", $out);
}

$assets = rtrim($modx->getOption('assets_path'), '/\\') . '/';
$backupDir = $assets . '_backups/usertest_reset_attempts/' . date('Ymd_His') . '/';

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0775, true);
}

$backup = array(
    'created_at' => date('c'),
    'email' => $email,
    'user_id' => $userId,
    'UserTestResults' => $resultsBackup,
    'UserTestResultAnswers' => $answersBackup,
    'UserTestResultCategorys' => $categoriesBackup,
    'TrainingUserTestStatus' => $statusesBackup,
);

file_put_contents(
    $backupDir . 'backup.json',
    json_encode($backup, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
);

$deletedAnswers = 0;
$deletedCategories = 0;
$deletedResults = 0;
$deletedStatuses = 0;

if ($resultIds) {
    foreach ($resultIds as $rid) {
        $items = $modx->getCollection('UserTestResultAnswers', array('result_id' => $rid));
        foreach ($items as $item) {
            if ($item->remove()) {
                $deletedAnswers++;
            }
        }

        $items = $modx->getCollection('UserTestResultCategorys', array('result_id' => $rid));
        foreach ($items as $item) {
            if ($item->remove()) {
                $deletedCategories++;
            }
        }

        $result = $modx->getObject('UserTestResults', $rid);
        if ($result && $result->remove()) {
            $deletedResults++;
        }
    }
}

if ($userId > 0) {
    $items = $modx->getCollection('TrainingUserTestStatus', array('user_id' => $userId));
    foreach ($items as $item) {
        if ($item->remove()) {
            $deletedStatuses++;
        }
    }
}

$modx->cacheManager->refresh(array(
    'db' => array(),
    'resource' => array(),
    'default' => array(),
));

$out[] = '';
$out[] = 'APPLY complete.';
$out[] = 'Backup: ' . $backupDir . 'backup.json';
$out[] = 'Deleted UserTestResultAnswers: ' . $deletedAnswers;
$out[] = 'Deleted UserTestResultCategorys: ' . $deletedCategories;
$out[] = 'Deleted UserTestResults: ' . $deletedResults;
$out[] = 'Deleted TrainingUserTestStatus: ' . $deletedStatuses;
$out[] = '';
$out[] = 'User can start tests again.';

return implode("\n", $out);

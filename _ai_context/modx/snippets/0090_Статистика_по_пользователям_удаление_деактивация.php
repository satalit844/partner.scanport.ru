<?php
/**
 * Активные пользователи без user_1c, исключая sudo.
 * Режимы: report | deactivate | delete
 * Экспорт: CSV (;) и TSV (\t), UTF-8 + BOM. Даты: дд.мм.гггг.
 */

// --- НАСТРОЙКИ ---
$action  = 'report'; // 'report' | 'deactivate' | 'delete'
$dir      = MODX_BASE_PATH . 'assets/tmp/';
$csvFile  = $dir . 'users_no_1c_utf8_semicolon.csv';
$tsvFile  = $dir . 'users_no_1c_utf8_tab.tsv';

// --- Тишина для PHP 8.2 ---
@ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('ECHO');

// --- Запрос пользователей ---
$q = $modx->newQuery('modUser');
$q->leftJoin('modUserProfile', 'Profile');
$q->select($modx->getSelectColumns('modUser', 'modUser', '', [
    'id','username','active','user_1c','createdon','sudo'
]));
$q->select($modx->getSelectColumns('modUserProfile', 'Profile', '', [
    'fullname','email','lastlogin'
]));
$q->where([
    'active' => 1,      // ищем только активных
    'sudo'   => 0,      // исключаем суперпользователей
    [
        'user_1c:IS' => null,
        'OR:user_1c:=' => '',
    ],
]);
$q->sortby('modUser.id', 'ASC');

$users = [];
if ($q->prepare() && $q->stmt->execute()) {
    $users = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- Утилиты ---
$formatTs = function($v) {
    if ($v === null || $v === '') return '-';
    if (is_numeric($v)) {
        $ts = (int)$v;
        if ($ts > 1000000000000) $ts = (int) floor($ts / 1000); // мс -> сек
        if ($ts < 315532800 || $ts > 4102444800) return '-';    // 1980..2100
        return date('d.m.Y', $ts);
    }
    $ts = strtotime($v);
    return $ts ? date('d.m.Y', $ts) : '-';
};
$sanitize = fn($v) => str_replace(["\r","\n"], ' ', (string)$v);

// --- Подготовка файлов ---
@mkdir($dir, 0775, true);
$csv = @fopen($csvFile, 'wb');
$tsv = @fopen($tsvFile, 'wb');
$BOM = "\xEF\xBB\xBF";
if ($csv) fwrite($csv, $BOM);
if ($tsv) fwrite($tsv, $BOM);

// Заголовки (добавили колонку Action для фиксации результата)
$headers = ['ID','Username','Fullname','Email','user_1c','Active','CreatedOn','LastLogin','sudo','Action'];
if ($csv) fwrite($csv, implode(';', $headers) . "\r\n");
if ($tsv) fwrite($tsv, implode("\t", $headers) . "\r\n");

// --- Шапка в консоль ---
printf("%-6s %-28s %-26s %-34s %-8s %-8s %-12s %-12s %-10s\n",
    'ID','Username','Fullname','Email','user_1c','Active','CreatedOn','LastLogin','Action');
echo str_repeat('-', 160) . "\n";

// --- Основной цикл ---
$rows=0; $affected=0; $failed=0; $skippedSudo=0; $mode=$action;

foreach ($users as $u) {
    $rows++;
    $created = $formatTs($u['createdon']);
    $last    = $formatTs($u['lastlogin']);

    $actionResult = strtoupper($mode); // REPORT / DEACTIVATE / DELETE

    // Консольная строка (sudo не печатаем тут)
    printf("%-6s %-28s %-26s %-34s %-8s %-8s %-12s %-12s %-10s\n",
        $u['id'],$u['username'],$u['fullname'],$u['email'],
        (string)$u['user_1c'],$u['active'],$created,$last,$actionResult
    );

    // Строки для файлов (sudo включён для контроля)
    $vals = [
        $sanitize($u['id']),
        $sanitize($u['username']),
        $sanitize($u['fullname']),
        $sanitize($u['email']),
        $sanitize($u['user_1c']),
        $sanitize($u['active']),
        $created,
        $last,
        (string)(int)$u['sudo'],
        $actionResult,
    ];

    // CSV
    if ($csv) {
        $quoted = array_map(fn($s)=>'"'.str_replace('"','""',$s).'"',$vals);
        fwrite($csv, implode(';', $quoted) . "\r\n");
    }
    // TSV
    if ($tsv) fwrite($tsv, implode("\t", $vals) . "\r\n");

    // --- Действия ---
    if ($mode === 'report') {
        continue;
    }

    // доп. защита
    if (!empty($u['sudo'])) {
        $skippedSudo++;
        $modx->log(modX::LOG_LEVEL_WARN, "Пропуск sudo user id {$u['id']} ({$u['username']})");
        continue;
    }

    if ($mode === 'deactivate') {
        // Процессор деактивации
        $resp = $modx->runProcessor('security/user/disable', ['id' => (int)$u['id']]);
        if ($resp && !$resp->isError()) {
            $affected++;
            $modx->log(modX::LOG_LEVEL_INFO, "Деактивирован (processor): {$u['id']} ({$u['username']})");
        } else {
            // Фолбэк: актив=0 вручную
            try {
                if ($userObj = $modx->getObject('modUser', (int)$u['id'])) {
                    $userObj->set('active', 0);
                    if ($userObj->save()) {
                        $affected++;
                        $modx->log(modX::LOG_LEVEL_INFO, "Деактивирован (fallback): {$u['id']} ({$u['username']})");
                    } else {
                        $failed++;
                        $msg = $resp ? $resp->getMessage() : 'processor returned null';
                        $modx->log(modX::LOG_LEVEL_ERROR, "Не удалось деактивировать {$u['id']} ({$u['username']}): {$msg}");
                    }
                }
            } catch (Exception $ex) {
                $failed++;
                $modx->log(modX::LOG_LEVEL_ERROR, "Ошибка деактивации {$u['id']}: ".$ex->getMessage());
            }
        }
        continue;
    }

    if ($mode === 'delete') {
        $resp = $modx->runProcessor('security/user/delete', ['id' => (int)$u['id']]);
        if ($resp && !$resp->isError()) {
            $affected++;
            $modx->log(modX::LOG_LEVEL_INFO, "Удалён (processor): {$u['id']} ({$u['username']})");
        } else {
            try {
                if ($userObj = $modx->getObject('modUser', (int)$u['id'])) {
                    $modx->removeCollection('modUserGroupMember', ['member'=>(int)$u['id']]);
                    if ($profile = $userObj->getOne('Profile')) $profile->remove();
                    $userObj->remove();
                    $affected++;
                    $modx->log(modX::LOG_LEVEL_INFO, "Удалён (fallback): {$u['id']} ({$u['username']})");
                } else {
                    $failed++;
                    $msg = $resp ? $resp->getMessage() : 'processor returned null';
                    $modx->log(modX::LOG_LEVEL_ERROR, "Не удалось удалить {$u['id']} ({$u['username']}): {$msg}");
                }
            } catch (Exception $ex) {
                $failed++;
                $modx->log(modX::LOG_LEVEL_ERROR, "Ошибка удаления {$u['id']}: ".$ex->getMessage());
            }
        }
        continue;
    }
}

// --- Завершение ---
if ($csv) fclose($csv);
if ($tsv) fclose($tsv);

echo "\nНайдено: {$rows}\n";
if ($rows>0) {
    echo "CSV: {$csvFile}\nTSV: {$tsvFile}\n";
} else {
    echo "Активных пользователей без user_1c не найдено.\n";
}
if ($action === 'deactivate') {
    echo "Деактивировано: {$affected}; Ошибок: {$failed}";
    if (!empty($skippedSudo)) echo "; Пропущено sudo: {$skippedSudo}";
    echo "\n";
} elseif ($action === 'delete') {
    echo "Удалено: {$affected}; Ошибок: {$failed}";
    if (!empty($skippedSudo)) echo "; Пропущено sudo: {$skippedSudo}";
    echo "\n";
} else {
    echo "Режим: REPORT (без изменений).\n";
}
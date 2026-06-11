<?php
/**
 * MODX Console script: find where course description text is stored.
 *
 * Usage:
 * 1) Paste into MODX Console with <?php.
 * 2) Set $resourceId and $phrases.
 * 3) Run.
 *
 * The script checks:
 * - modResource fields for the course resource;
 * - TVs for this resource;
 * - all text columns in MODX DB tables by phrases.
 *
 * Result is printed and saved to:
 * assets/_debug/course_description_source/YYYYmmdd_HHMMSS/result.json
 */

set_time_limit(0);

$resourceId = 159; // Курс для менеджеров по продажам
$phrases = array(
    'Этот курс создан',
    'Основные цели курса',
    'AutoID',
    'менеджеров по продажам',
    'DataMobile',
);
$maxRowsPerColumn = 5;

function ct_short_text($value, $limit = 350) {
    $value = (string)$value;
    $value = strip_tags($value);
    $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    $value = preg_replace('/\s+/u', ' ', $value);
    $value = trim($value);

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($value, 'UTF-8') > $limit) {
            return mb_substr($value, 0, $limit, 'UTF-8') . '...';
        }
        return $value;
    }

    if (strlen($value) > $limit) {
        return substr($value, 0, $limit) . '...';
    }

    return $value;
}

function ct_qi($name) {
    return '`' . str_replace('`', '``', $name) . '`';
}

function ct_add_line(&$out, $line = '') {
    $out[] = $line;
}

$out = array();
$matches = array();

ct_add_line($out, '=== FIND COURSE DESCRIPTION SOURCE ===');
ct_add_line($out, 'Resource ID: ' . $resourceId);
ct_add_line($out, 'Phrases: ' . implode(' | ', $phrases));
ct_add_line($out, '');

$corePath = $modx->getOption('core_path');
$assetsPath = rtrim($modx->getOption('assets_path'), '/\\') . '/';
$tablePrefix = $modx->getOption('table_prefix');

ct_add_line($out, 'Core path: ' . $corePath);
ct_add_line($out, 'Assets path: ' . $assetsPath);
ct_add_line($out, 'Table prefix: ' . $tablePrefix);
ct_add_line($out, '');

ct_add_line($out, '--- 1) modResource check ---');

$resource = $modx->getObject('modResource', (int)$resourceId);
if (!$resource) {
    ct_add_line($out, 'Resource not found.');
} else {
    $resourceData = $resource->toArray();
    $fields = array('id', 'pagetitle', 'longtitle', 'description', 'introtext', 'content', 'template', 'parent', 'uri');

    foreach ($fields as $field) {
        $value = isset($resourceData[$field]) ? $resourceData[$field] : '';
        if ($field === 'content') {
            ct_add_line($out, $field . ' length: ' . strlen((string)$value));
            if (trim((string)$value) !== '') {
                ct_add_line($out, $field . ' preview: ' . ct_short_text($value));
            }
        } else {
            ct_add_line($out, $field . ': ' . ct_short_text($value, 200));
        }
    }

    foreach ($phrases as $phrase) {
        foreach ($resourceData as $field => $value) {
            if (!is_scalar($value)) {
                continue;
            }
            if ($phrase !== '' && mb_stripos((string)$value, $phrase, 0, 'UTF-8') !== false) {
                $matches[] = array(
                    'source' => 'modResource',
                    'table' => $modx->getTableName('modResource'),
                    'column' => $field,
                    'phrase' => $phrase,
                    'row' => array('id' => $resourceId, 'field' => $field),
                    'preview' => ct_short_text($value),
                );
            }
        }
    }
}

ct_add_line($out, '');
ct_add_line($out, '--- 2) TV values for this resource ---');

$tvRows = array();
$tvTable = $modx->getTableName('modTemplateVar');
$tvrTable = $modx->getTableName('modTemplateVarResource');

$sql = "
    SELECT
        tv.id AS tv_id,
        tv.name AS tv_name,
        tv.caption AS tv_caption,
        tv.type AS tv_type,
        tvr.value AS tv_value
    FROM {$tvrTable} tvr
    INNER JOIN {$tvTable} tv ON tv.id = tvr.tmplvarid
    WHERE tvr.contentid = :resource_id
    ORDER BY tv.rank, tv.name
";

$stmt = $modx->prepare($sql);
if ($stmt && $stmt->execute(array(':resource_id' => (int)$resourceId))) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tvRows[] = $row;
        ct_add_line(
            $out,
            'TV #' . $row['tv_id'] . ' ' . $row['tv_name'] .
            ' (' . $row['tv_caption'] . '), type=' . $row['tv_type'] .
            ', value length=' . strlen((string)$row['tv_value'])
        );

        if (trim((string)$row['tv_value']) !== '') {
            ct_add_line($out, '  value: ' . ct_short_text($row['tv_value']));
        }

        foreach ($phrases as $phrase) {
            if ($phrase !== '' && mb_stripos((string)$row['tv_value'], $phrase, 0, 'UTF-8') !== false) {
                $matches[] = array(
                    'source' => 'TV',
                    'table' => trim($tvrTable, '`'),
                    'column' => 'value',
                    'phrase' => $phrase,
                    'row' => array(
                        'resource_id' => $resourceId,
                        'tv_id' => $row['tv_id'],
                        'tv_name' => $row['tv_name'],
                        'tv_caption' => $row['tv_caption'],
                    ),
                    'preview' => ct_short_text($row['tv_value']),
                );
            }
        }
    }

    if (!$tvRows) {
        ct_add_line($out, 'No TV values found for resource #' . $resourceId);
    }
} else {
    ct_add_line($out, 'TV query failed.');
    if ($stmt) {
        ct_add_line($out, print_r($stmt->errorInfo(), true));
    }
}

ct_add_line($out, '');
ct_add_line($out, '--- 3) DB-wide phrase search in text columns ---');

$dbName = '';
$dbStmt = $modx->query('SELECT DATABASE()');
if ($dbStmt) {
    $dbName = (string)$dbStmt->fetchColumn();
}
ct_add_line($out, 'Database: ' . $dbName);

$textTypes = array('char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'json');
$columnsSql = "
    SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = :db_name
      AND TABLE_NAME LIKE :table_prefix
      AND DATA_TYPE IN ('" . implode("','", $textTypes) . "')
    ORDER BY TABLE_NAME, ORDINAL_POSITION
";

$columns = array();
$columnsStmt = $modx->prepare($columnsSql);
if ($columnsStmt && $columnsStmt->execute(array(
    ':db_name' => $dbName,
    ':table_prefix' => $tablePrefix . '%',
))) {
    while ($row = $columnsStmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row;
    }
}
ct_add_line($out, 'Text columns found: ' . count($columns));

$totalQueries = 0;

foreach ($columns as $col) {
    $table = $col['TABLE_NAME'];
    $column = $col['COLUMN_NAME'];

    foreach ($phrases as $phrase) {
        $phrase = trim((string)$phrase);
        if ($phrase === '') {
            continue;
        }

        $selectSql = 'SELECT * FROM ' . ct_qi($table) .
            ' WHERE ' . ct_qi($column) . ' LIKE :needle LIMIT ' . (int)$maxRowsPerColumn;

        $selectStmt = $modx->prepare($selectSql);
        if (!$selectStmt) {
            continue;
        }

        $totalQueries++;
        if (!$selectStmt->execute(array(':needle' => '%' . $phrase . '%'))) {
            continue;
        }

        while ($row = $selectStmt->fetch(PDO::FETCH_ASSOC)) {
            $shortRow = array();
            foreach ($row as $k => $v) {
                if (is_scalar($v)) {
                    if (
                        in_array($k, array('id', 'resource_id', 'contentid', 'tmplvarid', 'tv_id', 'course_id', 'module_id', 'lesson_id', 'name', 'caption', 'pagetitle', 'key', 'namespace'), true)
                        || $k === $column
                    ) {
                        $shortRow[$k] = ct_short_text($v, 220);
                    }
                }
            }

            $matches[] = array(
                'source' => 'DB-wide',
                'table' => $table,
                'column' => $column,
                'phrase' => $phrase,
                'row' => $shortRow,
                'preview' => isset($row[$column]) ? ct_short_text($row[$column]) : '',
            );
        }
    }
}

ct_add_line($out, 'Search queries done: ' . $totalQueries);
ct_add_line($out, 'Matches found: ' . count($matches));
ct_add_line($out, '');

if ($matches) {
    ct_add_line($out, '--- MATCHES ---');

    foreach ($matches as $i => $match) {
        ct_add_line($out, '#' . ($i + 1));
        ct_add_line($out, 'Source: ' . $match['source']);
        ct_add_line($out, 'Table: ' . $match['table']);
        ct_add_line($out, 'Column: ' . $match['column']);
        ct_add_line($out, 'Phrase: ' . $match['phrase']);
        ct_add_line($out, 'Row: ' . json_encode($match['row'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        ct_add_line($out, 'Preview: ' . $match['preview']);
        ct_add_line($out, '');
    }
} else {
    ct_add_line($out, 'No matches by current phrases.');
    ct_add_line($out, 'Add a more exact text fragment from the page into $phrases and run again.');
    ct_add_line($out, '');
}

$saveDir = $assetsPath . '_debug/course_description_source/' . date('Ymd_His') . '/';
if (!is_dir($saveDir)) {
    mkdir($saveDir, 0775, true);
}

$result = array(
    'created_at' => date('c'),
    'resource_id' => $resourceId,
    'phrases' => $phrases,
    'db_name' => $dbName,
    'table_prefix' => $tablePrefix,
    'resource' => isset($resourceData) ? $resourceData : null,
    'tv_rows' => $tvRows,
    'matches' => $matches,
);

file_put_contents(
    $saveDir . 'result.json',
    json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
);

ct_add_line($out, 'Saved JSON: ' . $saveDir . 'result.json');

return '<pre>' . htmlspecialchars(implode("\n", $out), ENT_QUOTES, 'UTF-8') . '</pre>';

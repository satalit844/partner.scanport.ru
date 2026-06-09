<?php
if (!function_exists('trainingPracticeMgrTable')) {
    function trainingPracticeMgrTable(modX $modx, $name)
    {
        $name = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$name);
        $prefix = (string)$modx->getOption('table_prefix');
        $prefixTrim = rtrim($prefix, '_');

        $candidates = array(
            $prefix . $name,
            $prefixTrim . $name,
            $prefixTrim . '_' . $name,
            $prefix . '_' . $name,
            'modx_' . $name,
            'modx_partners' . $name,
            'modx_partners_' . $name,
        );

        foreach (array_unique($candidates) as $table) {
            $table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table);
            if ($table === '') {
                continue;
            }

            $stmt = $modx->query('SHOW TABLES LIKE ' . $modx->quote($table));
            if ($stmt && $stmt->fetchColumn()) {
                return '`' . $table . '`';
            }
        }

        $stmt = $modx->query('SHOW TABLES LIKE ' . $modx->quote('%' . $name));
        if ($stmt) {
            while ($table = $stmt->fetchColumn()) {
                $table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table);
                if ($table !== '') {
                    return '`' . $table . '`';
                }
            }
        }

        return '`' . preg_replace('/[^a-zA-Z0-9_]/', '', $prefix . $name) . '`';
    }
}

if (!function_exists('trainingPracticeMgrPlainTable')) {
    function trainingPracticeMgrPlainTable(modX $modx, $name)
    {
        return str_replace('`', '', trainingPracticeMgrTable($modx, $name));
    }
}



if (!function_exists('trainingPracticeMgrTableExists')) {
    function trainingPracticeMgrTableExists(modX $modx, $table)
    {
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table);
        if ($table === '') {
            return false;
        }

        $stmt = $modx->query('SHOW TABLES LIKE ' . $modx->quote($table));
        return (bool)($stmt && $stmt->fetchColumn());
    }
}

if (!function_exists('trainingPracticeMgrOptionalTable')) {
    function trainingPracticeMgrOptionalTable(modX $modx, $name)
    {
        $name = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$name);
        $prefix = (string)$modx->getOption('table_prefix');
        $prefixTrim = rtrim($prefix, '_');

        $candidates = array(
            $prefix . $name,
            $prefixTrim . $name,
            $prefixTrim . '_' . $name,
            $prefix . '_' . $name,
            'modx_' . $name,
            'modx_partners' . $name,
            'modx_partners_' . $name,
        );

        foreach (array_unique($candidates) as $table) {
            $table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table);
            if ($table !== '' && trainingPracticeMgrTableExists($modx, $table)) {
                return '`' . $table . '`';
            }
        }

        return '';
    }
}

if (!function_exists('trainingPracticeMgrEsc')) {
    function trainingPracticeMgrEsc($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('trainingPracticeMgrDate')) {
    function trainingPracticeMgrDate($value)
    {
        $value = trim((string)$value);
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return '';
        }
        $time = strtotime($value);
        return $time ? date('d.m.Y H:i', $time) : $value;
    }
}

if (!function_exists('trainingPracticeMgrStatusText')) {
    function trainingPracticeMgrStatusText($status)
    {
        $map = array(
            'not_started' => 'Не начато',
            'new' => 'Не начато',
            'draft' => 'Черновик',
            'submitted' => 'Отправлено',
            'in_review' => 'На проверке',
            'revision' => 'На доработке',
            'approved' => 'Принято',
            'rejected' => 'Отклонено',
            'overdue' => 'Просрочено',
        );
        return isset($map[$status]) ? $map[$status] : $status;
    }
}

if (!function_exists('trainingPracticeMgrJsonSuccess')) {
    function trainingPracticeMgrJsonSuccess(array $data)
    {
        return array_merge(array('success' => true), $data);
    }
}

if (!function_exists('trainingPracticeMgrGetInt')) {
    function trainingPracticeMgrGetInt(modProcessor $processor, $key, $default = 0)
    {
        return (int)$processor->getProperty($key, $default);
    }
}

if (!function_exists('trainingPracticeMgrGetString')) {
    function trainingPracticeMgrGetString(modProcessor $processor, $key, $default = '')
    {
        return trim((string)$processor->getProperty($key, $default));
    }
}

if (!function_exists('trainingPracticeMgrNormalizeDate')) {
    function trainingPracticeMgrNormalizeDate($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        $value = str_replace('T', ' ', $value);
        $time = strtotime($value);
        if (!$time) {
            return null;
        }

        return date('Y-m-d H:i:s', $time);
    }
}

if (!function_exists('trainingPracticeMgrEnsureModuleLink')) {
    function trainingPracticeMgrEnsureModuleLink(modX $modx, $courseId, $moduleId, $practiceId, $sortOrder = 0, $isRequired = 1)
    {
        $courseId = (int)$courseId;
        $moduleId = (int)$moduleId;
        $practiceId = (int)$practiceId;
        $sortOrder = (int)$sortOrder;
        $isRequired = (int)$isRequired ? 1 : 0;

        if ($courseId <= 0 || $moduleId <= 0 || $practiceId <= 0) {
            return false;
        }

        $linksTable = trainingPracticeMgrTable($modx, 'training_test_links');

        if ($sortOrder <= 0) {
            $stmt = $modx->prepare("SELECT MAX(`sort_order`) FROM {$linksTable} WHERE `course_id` = :course_id AND `module_id` = :module_id");
            $stmt->execute(array(':course_id' => $courseId, ':module_id' => $moduleId));
            $sortOrder = ((int)$stmt->fetchColumn()) + 1;
        }

        $stmt = $modx->prepare("SELECT `id` FROM {$linksTable} WHERE `link_type` = 'practice' AND `usertest_test_id` = :practice_id LIMIT 1");
        $stmt->execute(array(':practice_id' => $practiceId));
        $linkId = (int)$stmt->fetchColumn();

        if ($linkId > 0) {
            $stmt = $modx->prepare("\n                UPDATE {$linksTable}\n                SET\n                    `course_id` = :course_id,\n                    `module_id` = :module_id,\n                    `sort_order` = :sort_order,\n                    `is_required` = :is_required,\n                    `link_type` = 'practice'\n                WHERE `id` = :id\n            ");
            $stmt->execute(array(
                ':id' => $linkId,
                ':course_id' => $courseId,
                ':module_id' => $moduleId,
                ':sort_order' => $sortOrder,
                ':is_required' => $isRequired,
            ));

            return $linkId;
        }

        $stmt = $modx->prepare("\n            INSERT INTO {$linksTable}\n            (`course_id`, `module_id`, `usertest_test_id`, `link_type`, `sort_order`, `is_required`, `max_attempts`, `min_pass_percent`, `block_next_module_until_passed`, `createdon`)\n            VALUES\n            (:course_id, :module_id, :practice_id, 'practice', :sort_order, :is_required, 5, 0, 0, NOW())\n        ");
        $stmt->execute(array(
            ':course_id' => $courseId,
            ':module_id' => $moduleId,
            ':practice_id' => $practiceId,
            ':sort_order' => $sortOrder,
            ':is_required' => $isRequired,
        ));

        return (int)$modx->lastInsertId();
    }
}

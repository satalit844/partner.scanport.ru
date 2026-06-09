<?php
/** @var modX $modx */

if (!function_exists('trainingHistoryGetCorePath')) {
    function trainingHistoryGetCorePath(modX $modx)
    {
        $corePath = $modx->getOption('training.core_path', null, $modx->getOption('core_path') . 'components/training/');
        return rtrim(str_replace('\\', '/', (string)$corePath), '/') . '/';
    }
}

if (!function_exists('trainingHistoryGetPrefix')) {
    function trainingHistoryGetPrefix(modX $modx)
    {
        return (string)$modx->getOption('table_prefix', null, 'modx_');
    }
}

if (!function_exists('trainingHistoryGetTable')) {
    function trainingHistoryGetTable(modX $modx, $suffix)
    {
        return trainingHistoryGetPrefix($modx) . $suffix;
    }
}

if (!function_exists('trainingHistoryRenderFile')) {
    function trainingHistoryRenderFile(modX $modx, $absolutePath, array $placeholders = array())
    {
        $absolutePath = str_replace('\\', '/', (string)$absolutePath);
        if (!is_file($absolutePath)) {
            return '';
        }

        $pdoTools = $modx->getService('pdoTools');
        if (!$pdoTools) {
            $pdoTools = $modx->getService('pdoFetch');
        }
        if ($pdoTools && method_exists($pdoTools, 'getChunk')) {
            return (string)$pdoTools->getChunk('@FILE ' . $absolutePath, $placeholders);
        }

        $chunk = $modx->newObject('modChunk');
        $chunk->setCacheable(false);
        $chunk->setContent((string)file_get_contents($absolutePath));
        return (string)$chunk->process($placeholders);
    }
}

if (!function_exists('trainingHistoryEsc')) {
    function trainingHistoryEsc($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('trainingHistoryFormatSeconds')) {
    function trainingHistoryFormatSeconds($seconds)
    {
        $seconds = max(0, (int)$seconds);
        return sprintf('%02d:%02d:%02d', floor($seconds / 3600), floor(($seconds % 3600) / 60), $seconds % 60);
    }
}

if (!function_exists('trainingHistoryFormatDateParts')) {
    function trainingHistoryFormatDateParts($value)
    {
        if (!$value || $value === '0000-00-00 00:00:00') {
            return array('date' => '—', 'time' => '—', 'timestamp' => 0);
        }

        $ts = is_numeric($value) ? (int)$value : strtotime((string)$value);
        if (!$ts) {
            return array('date' => '—', 'time' => '—', 'timestamp' => 0);
        }

        $months = array(1=>'янв.',2=>'февр.',3=>'мар.',4=>'апр.',5=>'мая',6=>'июн.',7=>'июл.',8=>'авг.',9=>'сент.',10=>'окт.',11=>'нояб.',12=>'дек.');
        return array(
            'date' => (int)date('j', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts),
            'time' => date('G:i', $ts),
            'timestamp' => $ts,
        );
    }
}

if (!function_exists('trainingHistoryDecodeExtended')) {
    function trainingHistoryDecodeExtended($value)
    {
        if (is_array($value)) return $value;
        $value = trim((string)$value);
        if ($value === '') return array();
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : array();
    }
}

if (!function_exists('trainingHistoryExtractOrganization')) {
    function trainingHistoryExtractOrganization($extended)
    {
        $extended = trainingHistoryDecodeExtended($extended);
        foreach (array('company','organization','organisation','org','company_name','organisation_name','employer','workplace') as $key) {
            if (!empty($extended[$key])) return trim((string)$extended[$key]);
        }
        return '';
    }
}

if (!function_exists('trainingHistoryFetchManagedIds')) {
    function trainingHistoryFetchManagedIds(modX $modx, $managerUserId)
    {
        $table = trainingHistoryGetTable($modx, 'training_user_manager_link');
        $stmt = $modx->prepare("SELECT employee_user_id FROM `{$table}` WHERE manager_user_id = :manager AND is_active = 1 ORDER BY employee_user_id ASC");
        if (!$stmt || !$stmt->execute(array(':manager' => (int)$managerUserId))) {
            return array();
        }
        $ids = array();
        while (($id = $stmt->fetchColumn()) !== false) {
            $id = (int)$id;
            if ($id > 0 && $id !== (int)$managerUserId) $ids[$id] = $id;
        }
        return array_values($ids);
    }
}

if (!function_exists('trainingHistoryFetchUsers')) {
    function trainingHistoryFetchUsers(modX $modx, array $ids)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids) return array();

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $userTable = $modx->getTableName('modUser');
        $profileTable = $modx->getTableName('modUserProfile');
        $sql = "SELECT u.id, u.username, p.fullname, p.email, p.extended FROM {$userTable} u LEFT JOIN {$profileTable} p ON p.internalKey = u.id WHERE u.id IN ({$placeholders})";
        $stmt = $modx->prepare($sql);
        if (!$stmt || !$stmt->execute($ids)) return array();

        $map = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fullName = trim((string)$row['fullname']);
            $username = trim((string)$row['username']);
            $map[(int)$row['id']] = array(
                'id' => (int)$row['id'],
                'username' => $username,
                'fullname' => $fullName,
                'display_name' => $fullName !== '' ? $fullName : $username,
                'email' => trim((string)$row['email']),
                'organization' => trainingHistoryExtractOrganization($row['extended']),
            );
        }
        return $map;
    }
}

if (!function_exists('trainingHistoryBuildUrl')) {
    function trainingHistoryBuildUrl(modX $modx, array $params = array())
    {
        $resourceId = $modx->resource ? (int)$modx->resource->get('id') : 0;
        $contextKey = $modx->context ? $modx->context->get('key') : '';
        $url = $resourceId > 0 ? $modx->makeUrl($resourceId, $contextKey) : '';
        if ($url === '') $url = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '';
        $params = array_filter($params, function ($value) { return !($value === null || $value === ''); });
        if (!$params) return $url;
        return $url . (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
    }
}

if (!function_exists('trainingHistoryStatusMeta')) {
    function trainingHistoryStatusMeta($type, $status, $progressPercent)
    {
        $status = (string)$status;
        $progressPercent = (float)$progressPercent;
        if ($type === 'lesson') {
            if ($status === 'completed' || $progressPercent >= 100) return array('text' => 'Завершен', 'class' => 'label-chip--green');
            if ($status === 'in_progress' || $progressPercent > 0) return array('text' => 'В процессе', 'class' => 'label-chip--purple');
            return array('text' => 'Не начат', 'class' => 'label-chip--blue');
        }
        if ($type === 'test') {
            if (in_array($status, array('passed','completed'), true)) return array('text' => 'Завершен', 'class' => 'label-chip--green');
            if (in_array($status, array('failed','rejected'), true)) return array('text' => 'Не пройден', 'class' => 'label-chip--red');
            if ($status === 'in_progress') return array('text' => 'В процессе', 'class' => 'label-chip--purple');
            return array('text' => 'Не начат', 'class' => 'label-chip--blue');
        }
        if ($type === 'practice') {
            if (in_array($status, array('accepted','completed','passed'), true)) return array('text' => 'Завершен', 'class' => 'label-chip--green');
            if (in_array($status, array('submitted','pending_review','checking'), true)) return array('text' => 'На проверке', 'class' => 'label-chip--orange');
            if (in_array($status, array('rejected','failed'), true)) return array('text' => 'Не принято', 'class' => 'label-chip--red');
            if (in_array($status, array('draft','in_progress'), true)) return array('text' => 'В процессе', 'class' => 'label-chip--purple');
            return array('text' => 'Не начат', 'class' => 'label-chip--blue');
        }
        return array('text' => 'Не начат', 'class' => 'label-chip--blue');
    }
}

if (!function_exists('trainingHistoryBuildRows')) {
    function trainingHistoryBuildRows(modX $modx, $targetUserId)
    {
        $targetUserId = (int)$targetUserId;
        if ($targetUserId <= 0) return array();
        $rows = array();

        $lessonProgressTable = trainingHistoryGetTable($modx, 'training_user_lesson_progress');
        $lessonTable = trainingHistoryGetTable($modx, 'training_module_lessons');
        $moduleTable = trainingHistoryGetTable($modx, 'training_modules');
        $testStatusTable = trainingHistoryGetTable($modx, 'training_user_test_status');
        $testLinksTable = trainingHistoryGetTable($modx, 'training_test_links');
        $practiceTable = trainingHistoryGetTable($modx, 'training_practice_attempts');
        $resourceTable = $modx->getTableName('modResource');

        $lessonSql = "
            SELECT lp.lesson_id, lp.status, lp.current_time, lp.watched_seconds, lp.duration_seconds,
                   lp.progress_percent, lp.completed, COALESCE(lp.last_watch, lp.completedon) AS event_date,
                   l.title AS lesson_title, l.module_id, r.pagetitle AS module_title
            FROM {$lessonProgressTable} lp
            INNER JOIN {$lessonTable} l ON l.id = lp.lesson_id
            LEFT JOIN {$moduleTable} m ON m.id = l.module_id
            LEFT JOIN {$resourceTable} r ON r.id = m.resource_id AND r.deleted = 0
            WHERE lp.user_id = :user_id
              AND (lp.status <> 'not_started' OR lp.progress_percent > 0 OR lp.completed = 1 OR lp.last_watch IS NOT NULL OR lp.completedon IS NOT NULL)
        ";
        $stmt = $modx->prepare($lessonSql);
        if ($stmt && $stmt->execute(array(':user_id' => $targetUserId))) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $progress = (float)$row['progress_percent'];
                $meta = trainingHistoryStatusMeta('lesson', $row['status'], $progress);
                $viewed = round($progress) . '%';
                $durationTotal = (int)$row['duration_seconds'];
                $currentTime = max((int)$row['current_time'], 0);
                if ($durationTotal > 0) $viewed .= ' <span class="history-muted">(' . $currentTime . '/' . $durationTotal . ')</span>';
                $rows[] = array(
                    'type' => 'lesson',
                    'event_date' => $row['event_date'],
                    'title' => trim((string)$row['lesson_title']) !== '' ? trim((string)$row['lesson_title']) : ('Урок #' . (int)$row['lesson_id']),
                    'status_text' => $meta['text'],
                    'status_class' => $meta['class'],
                    'viewed_html' => $viewed,
                    'score_html' => '- <span class="history-muted">(0%)</span>',
                    'duration_text' => trainingHistoryFormatSeconds(max((int)$row['watched_seconds'], $currentTime)),
                    'icon' => 'theme/images/training/lesson-ico.svg',
                );
            }
        }

        $testSql = "
            SELECT uts.status, uts.last_score, uts.attempts, COALESCE(uts.updatedon, uts.last_passedon) AS event_date,
                   tl.module_id, tl.min_pass_percent, r.pagetitle AS module_title
            FROM {$testStatusTable} uts
            INNER JOIN {$testLinksTable} tl
                ON tl.course_id = uts.course_id
               AND tl.module_id = uts.module_id
               AND tl.usertest_test_id = uts.usertest_test_id
               AND tl.link_type = 'test'
            LEFT JOIN {$moduleTable} m ON m.id = tl.module_id
            LEFT JOIN {$resourceTable} r ON r.id = m.resource_id AND r.deleted = 0
            WHERE uts.user_id = :user_id
              AND (uts.status <> 'not_started' OR uts.attempts > 0 OR uts.updatedon IS NOT NULL OR uts.last_passedon IS NOT NULL)
        ";
        $stmt = $modx->prepare($testSql);
        if ($stmt && $stmt->execute(array(':user_id' => $targetUserId))) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $lastScore = (float)$row['last_score'];
                $meta = trainingHistoryStatusMeta('test', $row['status'], $lastScore);
                $scoreHtml = $lastScore > 0 ? round($lastScore) . '%' : '- <span class="history-muted">(0%)</span>';
                $viewedHtml = (int)$row['attempts'] > 0 ? '<span class="history-muted">(' . (int)$row['attempts'] . ' попыток)</span>' : '—';
                $moduleTitle = trim((string)$row['module_title']) !== '' ? trim((string)$row['module_title']) : ('Модуль #' . (int)$row['module_id']);
                $rows[] = array(
                    'type' => 'test',
                    'event_date' => $row['event_date'],
                    'title' => 'Тест: ' . $moduleTitle,
                    'status_text' => $meta['text'],
                    'status_class' => $meta['class'],
                    'viewed_html' => $viewedHtml,
                    'score_html' => $scoreHtml,
                    'duration_text' => '—',
                    'icon' => 'theme/images/training/lesson-ico.svg',
                );
            }
        }

        $practiceSql = "
            SELECT pa.status, pa.score, pa.max_score,
                   COALESCE(pa.reviewedon, pa.submittedon, pa.updatedon, pa.createdon) AS event_date,
                   pa.module_id, r.pagetitle AS module_title
            FROM {$practiceTable} pa
            LEFT JOIN {$moduleTable} m ON m.id = pa.module_id
            LEFT JOIN {$resourceTable} r ON r.id = m.resource_id AND r.deleted = 0
            WHERE pa.user_id = :user_id
        ";
        $stmt = $modx->prepare($practiceSql);
        if ($stmt && $stmt->execute(array(':user_id' => $targetUserId))) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $score = (float)$row['score'];
                $maxScore = (float)$row['max_score'];
                $percent = ($maxScore > 0) ? round(($score / $maxScore) * 100) : 0;
                $meta = trainingHistoryStatusMeta('practice', $row['status'], $percent);
                $scoreHtml = $maxScore > 0 ? rtrim(rtrim(number_format($score, 2, '.', ''), '0'), '.') . ' <span class="history-muted">(' . $percent . '%)</span>' : '- <span class="history-muted">(0%)</span>';
                $viewedHtml = in_array((string)$row['status'], array('accepted','completed','passed','submitted','pending_review','checking','rejected','failed'), true) ? '100%' : '—';
                $moduleTitle = trim((string)$row['module_title']) !== '' ? trim((string)$row['module_title']) : ('Модуль #' . (int)$row['module_id']);
                $rows[] = array(
                    'type' => 'practice',
                    'event_date' => $row['event_date'],
                    'title' => 'Практическая работа: ' . $moduleTitle,
                    'status_text' => $meta['text'],
                    'status_class' => $meta['class'],
                    'viewed_html' => $viewedHtml,
                    'score_html' => $scoreHtml,
                    'duration_text' => '—',
                    'icon' => 'theme/images/training/lesson-ico.svg',
                );
            }
        }

        return $rows;
    }
}

if (!function_exists('trainingHistoryPrepareRows')) {
    function trainingHistoryPrepareRows(array $rows, $sortDir)
    {
        $prepared = array();
        foreach ($rows as $row) {
            $parts = trainingHistoryFormatDateParts($row['event_date']);
            if ((int)$parts['timestamp'] <= 0) continue;
            $row['date_label'] = $parts['date'];
            $row['time_label'] = $parts['time'];
            $row['timestamp'] = $parts['timestamp'];
            $prepared[] = $row;
        }
        usort($prepared, function ($a, $b) use ($sortDir) {
            if ((int)$a['timestamp'] === (int)$b['timestamp']) return 0;
            return $sortDir === 'asc'
                ? (((int)$a['timestamp'] < (int)$b['timestamp']) ? -1 : 1)
                : (((int)$a['timestamp'] > (int)$b['timestamp']) ? -1 : 1);
        });
        return $prepared;
    }
}

if (!function_exists('trainingHistoryRenderRows')) {
    function trainingHistoryRenderRows(modX $modx, $corePath, array $rows)
    {
        $chunkBase = rtrim($corePath, '/') . '/elements/chunks/training/history/';
        $tableTpl = $chunkBase . 'table-row.tpl';
        $cardTpl = $chunkBase . 'card.tpl';
        $tableHtml = '';
        $cardsHtml = '';
        foreach ($rows as $row) {
            $ph = array(
                'date' => $row['date_label'],
                'time' => $row['time_label'],
                'title' => $row['title'],
                'status_text' => $row['status_text'],
                'status_class' => $row['status_class'],
                'viewed_html' => $row['viewed_html'],
                'score_html' => $row['score_html'],
                'duration_text' => $row['duration_text'],
                'icon' => $row['icon'],
            );
            $tableHtml .= trainingHistoryRenderFile($modx, $tableTpl, $ph);
            $cardsHtml .= trainingHistoryRenderFile($modx, $cardTpl, $ph);
        }
        if ($tableHtml === '') $tableHtml = '<div class="history-table__row"><div class="history-col" style="grid-column:1 / -1;">История пока пуста</div></div>';
        if ($cardsHtml === '') $cardsHtml = '<div class="col-12"><div class="lesson-card">История пока пуста</div></div>';
        return array($tableHtml, $cardsHtml);
    }
}

if (!function_exists('trainingHistoryRenderPage')) {
    function trainingHistoryRenderPage(modX $modx, array $config)
    {
        $corePath = trainingHistoryGetCorePath($modx);
        $currentUserId = (int)$modx->user->get('id');
        if ($currentUserId <= 0) return '';

        $isManage = !empty($config['manage']);
        $managedIds = trainingHistoryFetchManagedIds($modx, $currentUserId);
        $availableIds = $isManage ? array_values(array_unique(array_merge(array($currentUserId), $managedIds))) : array($currentUserId);

        $selectedUserId = $currentUserId;
        if ($isManage) {
            $requestedUserId = isset($_GET['history_user']) ? (int)$_GET['history_user'] : 0;
            if ($requestedUserId > 0 && in_array($requestedUserId, $availableIds, true)) $selectedUserId = $requestedUserId;
        }

        $sortDir = (isset($_GET['history_sort']) && $_GET['history_sort'] === 'asc') ? 'asc' : 'desc';
        $usersMap = trainingHistoryFetchUsers($modx, $availableIds);
        $rows = trainingHistoryPrepareRows(trainingHistoryBuildRows($modx, $selectedUserId), $sortDir);
        list($tableHtml, $cardsHtml) = trainingHistoryRenderRows($modx, $corePath, $rows);

        $userSelectHtml = '';
        if ($isManage && count($availableIds) > 1) {
            $optionsHtml = '';
            foreach ($availableIds as $availableId) {
                if (empty($usersMap[$availableId])) continue;
                $label = $usersMap[$availableId]['display_name'];
                if ($availableId === $currentUserId) $label .= ' (я)';
                $optionsHtml .= '<option value="' . (int)$availableId . '"' . ($availableId === $selectedUserId ? ' selected="selected"' : '') . '>' . trainingHistoryEsc($label) . '</option>';
            }
            if ($optionsHtml !== '') {
                $userSelectHtml = trainingHistoryRenderFile($modx, rtrim($corePath, '/') . '/elements/chunks/training/history/user-select.tpl', array(
                    'select_name' => 'history_user',
                    'sort_name' => 'history_sort',
                    'sort_value' => $sortDir,
                    'options_html' => $optionsHtml,
                ));
            }
        }

        $subtitle = !empty($config['subtitle']) ? (string)$config['subtitle'] : 'Моя история';
        if ($isManage && $selectedUserId !== $currentUserId) $subtitle = 'История сотрудника';

        $sortUrl = trainingHistoryBuildUrl($modx, array(
            'history_user' => $isManage ? $selectedUserId : null,
            'history_sort' => $sortDir === 'desc' ? 'asc' : 'desc',
        ));

        return trainingHistoryRenderFile($modx, rtrim($corePath, '/') . '/elements/chunks/training/history/page.tpl', array(
            'history_title' => !empty($config['title']) ? (string)$config['title'] : ($modx->resource ? (string)$modx->resource->get('pagetitle') : 'История'),
            'history_subtitle' => $subtitle,
            'history_user_select_html' => $userSelectHtml,
            'history_sort_url' => $sortUrl,
            'history_sort_class' => $sortDir === 'asc' ? ' is-asc' : ' is-desc',
            'history_table_rows_html' => $tableHtml,
            'history_cards_html' => $cardsHtml,
        ));
    }
}

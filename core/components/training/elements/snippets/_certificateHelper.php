<?php
/** @var modX $modx */

$corePath = $modx->getOption('training.core_path', null, $modx->getOption('core_path') . 'components/training/');

$trainingClass = $corePath . 'model/training/training.class.php';
$certificateServiceClass = $corePath . 'model/training/services/trainingcertificate.class.php';

if (is_file($trainingClass)) {
    require_once $trainingClass;
}
if (is_file($certificateServiceClass)) {
    require_once $certificateServiceClass;
}

if (!function_exists('trainingCertificatesEsc')) {
    function trainingCertificatesEsc($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('trainingCertificatesChunkPath')) {
    function trainingCertificatesChunkPath($corePath, $relativePath)
    {
        return rtrim(str_replace('\\', '/', (string)$corePath), '/') . '/elements/chunks/' . ltrim((string)$relativePath, '/');
    }
}

if (!function_exists('trainingCertificatesRenderChunk')) {
    function trainingCertificatesRenderChunk($corePath, $relativePath, array $placeholders = array())
    {
        $path = trainingCertificatesChunkPath($corePath, $relativePath);
        if (!is_file($path)) {
            return '';
        }

        $content = (string)file_get_contents($path);
        if ($content === '') {
            return '';
        }

        $replace = array();
        foreach ($placeholders as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = '';
            }

            $replace['{$' . $key . '}'] = (string)$value;
            $replace['[[+' . $key . ']]'] = (string)$value;
        }

        return strtr($content, $replace);
    }
}

if (!function_exists('trainingCertificatesTableExists')) {
    function trainingCertificatesTableExists(modX $modx, $table)
    {
        $table = str_replace('`', '', (string)$table);
        if ($table === '') {
            return false;
        }

        $stmt = $modx->query('SHOW TABLES LIKE ' . $modx->quote($table));
        return $stmt && $stmt->fetch(PDO::FETCH_NUM);
    }
}

if (!function_exists('trainingCertificatesTableName')) {
    function trainingCertificatesTableName(modX $modx, $name)
    {
        $prefix = (string)$modx->getOption('table_prefix', null, '');
        $name = ltrim((string)$name, '_');

        $candidates = array();
        $candidates[] = $prefix . $name;

        $fixed = rtrim($prefix, '_') . '_' . $name;
        if ($fixed !== $candidates[0]) {
            $candidates[] = $fixed;
        }

        $candidates[] = $name;

        foreach ($candidates as $table) {
            if (trainingCertificatesTableExists($modx, $table)) {
                return str_replace('`', '', $table);
            }
        }

        return str_replace('`', '', $prefix . $name);
    }
}

if (!function_exists('trainingCertificatesResolveTable')) {
    function trainingCertificatesResolveTable(modX $modx, array $names)
    {
        foreach ($names as $name) {
            $name = trim((string)$name);
            if ($name === '') {
                continue;
            }

            if (trainingCertificatesTableExists($modx, $name)) {
                return str_replace('`', '', $name);
            }

            $resolved = trainingCertificatesTableName($modx, $name);
            if (trainingCertificatesTableExists($modx, $resolved)) {
                return str_replace('`', '', $resolved);
            }
        }

        return '';
    }
}

if (!function_exists('trainingCertificatesFetchAll')) {
    function trainingCertificatesFetchAll(modX $modx, $sql, array $params = array(), $logPrefix = 'trainingCertificates')
    {
        $stmt = $modx->prepare($sql);
        if (!$stmt || !$stmt->execute($params)) {
            if ($stmt && method_exists($stmt, 'errorInfo')) {
                $modx->log(modX::LOG_LEVEL_ERROR, '[' . $logPrefix . '] SQL error: ' . print_r($stmt->errorInfo(), true) . "\n" . $sql);
            }
            return array();
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : array();
    }
}

if (!function_exists('trainingCertificatesFetchColumn')) {
    function trainingCertificatesFetchColumn(modX $modx, $sql, array $params = array(), $logPrefix = 'trainingCertificates')
    {
        $stmt = $modx->prepare($sql);
        if (!$stmt || !$stmt->execute($params)) {
            if ($stmt && method_exists($stmt, 'errorInfo')) {
                $modx->log(modX::LOG_LEVEL_ERROR, '[' . $logPrefix . '] SQL error: ' . print_r($stmt->errorInfo(), true) . "\n" . $sql);
            }
            return array();
        }

        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return is_array($rows) ? $rows : array();
    }
}

if (!function_exists('trainingCertificatesCount')) {
    function trainingCertificatesCount(modX $modx, $table, $where = '', array $params = array())
    {
        $sql = 'SELECT COUNT(*) FROM `' . str_replace('`', '', $table) . '`';
        if (trim((string)$where) !== '') {
            $sql .= ' WHERE ' . $where;
        }

        $stmt = $modx->prepare($sql);
        if (!$stmt || !$stmt->execute($params)) {
            return 0;
        }

        return (int)$stmt->fetchColumn();
    }
}

if (!function_exists('trainingCertificatesIsAuthenticated')) {
    function trainingCertificatesIsAuthenticated(modX $modx)
    {
        if (!$modx->user || !(int)$modx->user->get('id')) {
            return false;
        }

        $contextKey = $modx->context ? (string)$modx->context->get('key') : '';
        if ($contextKey !== '' && $modx->user->isAuthenticated($contextKey)) {
            return true;
        }

        if ($modx->user->isAuthenticated('web')) {
            return true;
        }

        if ($modx->user->isAuthenticated('mgr')) {
            return true;
        }

        return false;
    }
}

if (!function_exists('trainingCertificatesIsAdmin')) {
    function trainingCertificatesIsAdmin(modX $modx)
    {
        if (!$modx->user || !(int)$modx->user->get('id')) {
            return false;
        }

        if ((int)$modx->user->get('sudo') === 1) {
            return true;
        }

        if (method_exists($modx->user, 'isMember')) {
            if ($modx->user->isMember('Administrator') || $modx->user->isMember('Администратор')) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('trainingCertificatesDebugEnabled')) {
    function trainingCertificatesDebugEnabled()
    {
        return isset($_GET['debug_cert']) && (string)$_GET['debug_cert'] === '1';
    }
}

if (!function_exists('trainingCertificatesDebugHtml')) {
    function trainingCertificatesDebugHtml($title, array $data)
    {
        if (!trainingCertificatesDebugEnabled()) {
            return '';
        }

        $html = '<pre class="training-cert-debug" style="white-space:pre-wrap;background:#f6f8fa;border:1px solid #d0d7de;border-radius:10px;padding:12px;margin:0 0 16px;font-size:13px;line-height:1.45;">';
        $html .= trainingCertificatesEsc($title) . "\n";

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', array_map('strval', $value));
            } elseif (is_bool($value)) {
                $value = $value ? 'yes' : 'no';
            }

            $html .= trainingCertificatesEsc($key . ': ' . (string)$value) . "\n";
        }

        $html .= '</pre>';

        return $html;
    }
}

if (!function_exists('trainingCertificatesCertificateRowsSql')) {
    function trainingCertificatesCertificateRowsSql(array $tables)
    {
        return 'SELECT cert.*, '
            . '"" AS `template_pdf`, '
            . '"" AS `template_preview`, '
            . 'COALESCE(NULLIF(TRIM(cert.`fullname`), ""), CONCAT("Пользователь #", cert.`user_id`)) AS `display_user`, '
            . 'COALESCE(NULLIF(TRIM(cert.`course_title`), ""), CONCAT("Курс #", cert.`course_id`)) AS `display_course_title` '
            . 'FROM `' . $tables['certificates'] . '` cert ';
    }
}

if (!function_exists('trainingCertificatesGetUserName')) {
    function trainingCertificatesGetUserName(modX $modx, $userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return '';
        }

        $profile = $modx->getObject('modUserProfile', array('internalKey' => $userId));
        if ($profile) {
            $fullname = trim((string)$profile->get('fullname'));
            if ($fullname !== '') {
                return $fullname;
            }

            $email = trim((string)$profile->get('email'));
            if ($email !== '') {
                return $email;
            }
        }

        $user = $modx->getObject('modUser', $userId);
        if ($user) {
            $username = trim((string)$user->get('username'));
            if ($username !== '') {
                return $username;
            }
        }

        return 'Пользователь #' . $userId;
    }
}

if (!function_exists('trainingCertificatesGetCourseTitle')) {
    function trainingCertificatesGetCourseTitle(modX $modx, array $tables, $courseId)
    {
        $courseId = (int)$courseId;
        if ($courseId <= 0) {
            return '';
        }

        $coursesTable = isset($tables['courses']) ? str_replace('`', '', (string)$tables['courses']) : '';
        if ($coursesTable !== '' && trainingCertificatesTableExists($modx, $coursesTable)) {
            $stmt = $modx->prepare('SELECT `resource_id` FROM `' . $coursesTable . '` WHERE `id` = :id LIMIT 1');
            if ($stmt && $stmt->execute(array(':id' => $courseId))) {
                $resourceId = (int)$stmt->fetchColumn();
                if ($resourceId > 0) {
                    $resource = $modx->getObject('modResource', $resourceId);
                    if ($resource) {
                        $pagetitle = trim((string)$resource->get('pagetitle'));
                        if ($pagetitle !== '') {
                            return $pagetitle;
                        }
                    }
                }
            }
        }

        return 'Курс #' . $courseId;
    }
}

if (!function_exists('trainingCertificatesEnrichRows')) {
    function trainingCertificatesEnrichRows(modX $modx, array $rows, array $tables)
    {
        $userNames = array();
        $courseTitles = array();

        foreach ($rows as $row) {
            $uid = (int)(isset($row['user_id']) ? $row['user_id'] : 0);
            $courseId = (int)(isset($row['course_id']) ? $row['course_id'] : 0);

            if ($uid > 0 && !isset($userNames[$uid])) {
                $userNames[$uid] = trainingCertificatesGetUserName($modx, $uid);
            }

            if ($courseId > 0 && !isset($courseTitles[$courseId])) {
                $courseTitles[$courseId] = trainingCertificatesGetCourseTitle($modx, $tables, $courseId);
            }
        }

        foreach ($rows as $i => $row) {
            $uid = (int)(isset($row['user_id']) ? $row['user_id'] : 0);
            $courseId = (int)(isset($row['course_id']) ? $row['course_id'] : 0);

            $certFullname = trim((string)(isset($row['fullname']) ? $row['fullname'] : ''));
            if ($certFullname !== '') {
                $rows[$i]['display_user'] = $certFullname;
            } elseif ($uid > 0 && isset($userNames[$uid]) && trim((string)$userNames[$uid]) !== '') {
                $rows[$i]['display_user'] = $userNames[$uid];
            }

            $certCourseTitle = trim((string)(isset($row['course_title']) ? $row['course_title'] : ''));
            if ($certCourseTitle !== '') {
                $rows[$i]['display_course_title'] = $certCourseTitle;
            } elseif ($courseId > 0 && isset($courseTitles[$courseId]) && trim((string)$courseTitles[$courseId]) !== '') {
                $rows[$i]['display_course_title'] = $courseTitles[$courseId];
            }
        }

        return $rows;
    }
}

if (!function_exists('trainingCertificatesDateParts')) {
    function trainingCertificatesDateParts($value)
    {
        $value = trim((string)$value);
        if ($value === '' || $value === '0000-00-00 00:00:00' || $value === '0000-00-00') {
            return array('ts' => 0, 'iso' => '', 'ru' => '—');
        }

        $ts = strtotime($value);
        if (!$ts) {
            return array('ts' => 0, 'iso' => '', 'ru' => $value);
        }

        $months = array(
            1 => 'января',
            2 => 'февраля',
            3 => 'марта',
            4 => 'апреля',
            5 => 'мая',
            6 => 'июня',
            7 => 'июля',
            8 => 'августа',
            9 => 'сентября',
            10 => 'октября',
            11 => 'ноября',
            12 => 'декабря',
        );

        $month = (int)date('n', $ts);
        $ru = (int)date('j', $ts) . ' ' . (isset($months[$month]) ? $months[$month] : date('m', $ts)) . ' ' . date('Y', $ts);

        return array(
            'ts' => $ts,
            'iso' => date('Y-m-d', $ts),
            'ru' => $ru,
        );
    }
}

if (!function_exists('trainingCertificatesAddYears')) {
    function trainingCertificatesAddYears($timestamp, $years)
    {
        $timestamp = (int)$timestamp;
        $years = (int)$years;
        if ($timestamp <= 0) {
            return 0;
        }

        return strtotime('+' . $years . ' years', $timestamp);
    }
}

if (!function_exists('trainingCertificatesBuildItemHtml')) {
    function trainingCertificatesBuildItemHtml($corePath, $itemTpl, array $row, $withUserName = false)
    {
        $file = trim((string)(isset($row['file_path']) ? $row['file_path'] : ''));
        $preview = trim((string)(isset($row['preview_image']) ? $row['preview_image'] : ''));

        if ($preview === '') {
            $preview = trim((string)(isset($row['template_preview']) ? $row['template_preview'] : ''));
        }
        if ($preview === '') {
            $preview = $file;
        }

        $courseTitle = trim((string)(isset($row['display_course_title']) ? $row['display_course_title'] : ''));
        if ($courseTitle === '') {
            $courseTitle = trim((string)(isset($row['course_title']) ? $row['course_title'] : ''));
        }
        if ($courseTitle === '') {
            $courseTitle = 'Курс #' . (int)(isset($row['course_id']) ? $row['course_id'] : 0);
        }

        $title = $courseTitle;

        if ($withUserName) {
            $userName = trim((string)(isset($row['display_user']) ? $row['display_user'] : ''));
            if ($userName === '') {
                $userName = 'Пользователь #' . (int)(isset($row['user_id']) ? $row['user_id'] : 0);
            }

            $title = $userName . ' — ' . $courseTitle;
        }

        $issuedValue = trim((string)(isset($row['issuedon']) ? $row['issuedon'] : ''));
        if ($issuedValue === '' && isset($row['completedon'])) {
            $issuedValue = trim((string)$row['completedon']);
        }

        $from = trainingCertificatesDateParts($issuedValue);
        $validToTs = $from['ts'] > 0 ? trainingCertificatesAddYears($from['ts'], 1) : 0;
        $validTo = $validToTs > 0 ? trainingCertificatesDateParts(date('Y-m-d H:i:s', $validToTs)) : array('ts' => 0, 'iso' => '', 'ru' => '—');

        $isExpired = $validTo['ts'] > 0 && $validTo['ts'] < strtotime('today');
        $state = $isExpired ? 'expired' : 'active';
        $stateClass = $isExpired ? 'is-expired' : 'is-active';

        if ($isExpired) {
            $statusText = 'Срок истек ' . $validTo['ru'];
        } elseif ($from['ru'] !== '—' && $validTo['ru'] !== '—') {
            $statusText = $from['ru'] . ' • ' . $validTo['ru'];
        } elseif ($from['ru'] !== '—') {
            $statusText = $from['ru'];
        } else {
            $statusText = 'Сертификат готов';
        }

        return trainingCertificatesRenderChunk($corePath, $itemTpl, array(
            'certificate_title' => trainingCertificatesEsc($title),
            'certificate_status' => trainingCertificatesEsc($from['ru'] !== '—' ? 'Сертификат выдан' : 'Сертификат готов'),
            'certificate_status_text' => trainingCertificatesEsc($statusText),
            'certificate_state' => trainingCertificatesEsc($state),
            'certificate_state_class' => trainingCertificatesEsc($stateClass),
            'certificate_preview' => trainingCertificatesEsc($preview),
            'certificate_file' => trainingCertificatesEsc($file !== '' ? $file : $preview),
            'certificate_issuedon' => trainingCertificatesEsc($from['ru']),
            'certificate_valid_from' => trainingCertificatesEsc($from['ru']),
            'certificate_valid_from_iso' => trainingCertificatesEsc($from['iso']),
            'certificate_valid_to' => trainingCertificatesEsc($validTo['ru']),
            'certificate_valid_to_iso' => trainingCertificatesEsc($validTo['iso']),
        ));
    }
}

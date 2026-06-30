<?php
/** @var modX $modx */
/** @var array $scriptProperties */

if (!function_exists('trainingPracticeTable')) {
    function trainingPracticeTable(modX $modx, $name)
    {
        $prefix = (string)$modx->getOption('table_prefix');
        $raw = $prefix . $name;
        $fixed = rtrim($prefix, '_') . '_' . $name;

        $candidates = array($raw);
        if ($fixed !== $raw) {
            $candidates[] = $fixed;
        }

        foreach ($candidates as $table) {
            $stmt = $modx->query("SHOW TABLES LIKE " . $modx->quote($table));
            if ($stmt && $stmt->fetch(PDO::FETCH_NUM)) {
                return '`' . str_replace('`', '', $table) . '`';
            }
        }

        return '`' . str_replace('`', '', $raw) . '`';
    }
}

if (!function_exists('trainingPracticeEsc')) {
    function trainingPracticeEsc($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('trainingPracticeRenderChunk')) {
    function trainingPracticeRenderChunk($corePath, $relativePath, array $placeholders = array())
    {
        $path = rtrim(str_replace('\\', '/', $corePath), '/') . '/elements/chunks/' . ltrim($relativePath, '/');

        if (!is_file($path)) {
            return '';
        }

        $content = (string)file_get_contents($path);
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

if (!function_exists('trainingPracticeFetchRow')) {
    function trainingPracticeFetchRow(modX $modx, $sql, array $params = array())
    {
        $stmt = $modx->prepare($sql);

        if (!$stmt || !$stmt->execute($params)) {
            return array();
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : array();
    }
}

if (!function_exists('trainingPracticeFetchAll')) {
    function trainingPracticeFetchAll(modX $modx, $sql, array $params = array())
    {
        $stmt = $modx->prepare($sql);

        if (!$stmt || !$stmt->execute($params)) {
            return array();
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : array();
    }
}

if (!function_exists('trainingPracticeInsert')) {
    function trainingPracticeInsert(modX $modx, $sql, array $params = array())
    {
        $stmt = $modx->prepare($sql);

        if (!$stmt || !$stmt->execute($params)) {
            return 0;
        }

        return (int)$modx->lastInsertId();
    }
}

if (!function_exists('trainingPracticeFormatDate')) {
    function trainingPracticeFormatDate($value, $withTime = true)
    {
        $time = strtotime((string)$value);

        if (!$time) {
            return '';
        }

        return $withTime ? date('d.m.Y H:i', $time) : date('d.m.Y', $time);
    }
}

if (!function_exists('trainingPracticeStatusText')) {
    function trainingPracticeStatusText($status)
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

if (!function_exists('trainingPracticeStatusClass')) {
    function trainingPracticeStatusClass($status)
    {
        $map = array(
            'not_started' => 'practice-status--new',
            'new' => 'practice-status--new',
            'draft' => 'practice-status--draft',
            'submitted' => 'practice-status--submitted',
            'in_review' => 'practice-status--review',
            'revision' => 'practice-status--revision',
            'approved' => 'practice-status--approved',
            'rejected' => 'practice-status--rejected',
            'overdue' => 'practice-status--overdue',
        );

        return isset($map[$status]) ? $map[$status] : 'practice-status--new';
    }
}

if (!function_exists('trainingPracticeTextHtml')) {
    function trainingPracticeTextHtml($text)
    {
        $text = trim((string)$text);

        if ($text === '') {
            return '';
        }

        if ($text !== strip_tags($text)) {
            return $text;
        }

        return nl2br(trainingPracticeEsc($text));
    }
}

if (!function_exists('trainingPracticeAssetUrl')) {
    function trainingPracticeAssetUrl(modX $modx, $path)
    {
        $path = trim((string)$path);

        if ($path === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        if (strpos($path, '/') === 0) {
            return $path;
        }

        return rtrim($modx->getOption('base_url'), '/') . '/' . ltrim($path, '/');
    }
}


if (!function_exists('trainingPracticeAvatarUrl')) {
    function trainingPracticeAvatarUrl(modX $modx, $photo)
    {
        $photo = trim((string)$photo);

        if ($photo === '') {
            $photo = 'theme/images/profile/no-photo.svg';
        }

        if (preg_match('#^https?://#i', $photo)) {
            return $photo;
        }

        if (strpos($photo, '/') === 0) {
            return $photo;
        }

        return rtrim($modx->getOption('base_url'), '/') . '/' . ltrim($photo, '/');
    }
}

if (!function_exists('trainingPracticeHasUpload')) {
    function trainingPracticeHasUpload($field)
    {
        if (empty($_FILES[$field]) || empty($_FILES[$field]['name'])) {
            return false;
        }

        if (is_array($_FILES[$field]['name'])) {
            foreach ($_FILES[$field]['name'] as $i => $name) {
                if ($name !== '' && isset($_FILES[$field]['error'][$i]) && (int)$_FILES[$field]['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    return true;
                }
            }

            return false;
        }

        return (int)$_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE;
    }
}

if (!function_exists('trainingPracticeCleanFileName')) {
    function trainingPracticeCleanFileName($name)
    {
        $name = trim((string)$name);
        $name = preg_replace('/[^\pL\pN\.\-_]+/u', '-', $name);
        $name = trim($name, '-_.');

        if ($name === '') {
            $name = 'file';
        }

        return $name;
    }
}

if (!function_exists('trainingPracticeNormalizeFiles')) {
    function trainingPracticeNormalizeFiles($field)
    {
        $result = array();

        if (empty($_FILES[$field])) {
            return $result;
        }

        $files = $_FILES[$field];

        if (is_array($files['name'])) {
            foreach ($files['name'] as $i => $name) {
                $result[] = array(
                    'name' => $name,
                    'type' => isset($files['type'][$i]) ? $files['type'][$i] : '',
                    'tmp_name' => isset($files['tmp_name'][$i]) ? $files['tmp_name'][$i] : '',
                    'error' => isset($files['error'][$i]) ? (int)$files['error'][$i] : UPLOAD_ERR_NO_FILE,
                    'size' => isset($files['size'][$i]) ? (int)$files['size'][$i] : 0,
                );
            }

            return $result;
        }

        $result[] = array(
            'name' => $files['name'],
            'type' => isset($files['type']) ? $files['type'] : '',
            'tmp_name' => isset($files['tmp_name']) ? $files['tmp_name'] : '',
            'error' => isset($files['error']) ? (int)$files['error'] : UPLOAD_ERR_NO_FILE,
            'size' => isset($files['size']) ? (int)$files['size'] : 0,
        );

        return $result;
    }
}

if (!function_exists('trainingPracticeSaveFiles')) {
    function trainingPracticeSaveFiles(modX $modx, array $practice, $attemptId, $messageId, $userId)
    {
        $saved = array();
        $field = 'practice_files';
        $files = trainingPracticeNormalizeFiles($field);

        if (!$files) {
            return $saved;
        }

        $allowed = array();
        foreach (explode(',', (string)$practice['allowed_extensions']) as $ext) {
            $ext = strtolower(trim($ext));
            if ($ext !== '') {
                $allowed[] = $ext;
            }
        }

        $maxSize = !empty($practice['max_file_size']) ? (int)$practice['max_file_size'] : 52428800;

        $assetsPath = rtrim($modx->getOption('assets_path'), '/') . '/training/practices/';
        $assetsUrl = rtrim($modx->getOption('assets_url'), '/') . '/training/practices/';

        $relativeDir = (int)$practice['id'] . '/' . (int)$userId . '/' . (int)$attemptId . '/';
        $targetDir = $assetsPath . $relativeDir;

        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
            throw new Exception('Не удалось создать папку для файлов');
        }

        $filesTable = trainingPracticeTable($modx, 'training_practice_files');
        $now = date('Y-m-d H:i:s');

        foreach ($files as $file) {
            if ((int)$file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ((int)$file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Ошибка загрузки файла: ' . trainingPracticeEsc($file['name']));
            }

            $originalName = (string)$file['name'];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if ($extension === '' || !in_array($extension, $allowed, true)) {
                throw new Exception('Недопустимый тип файла: ' . trainingPracticeEsc($originalName));
            }

            if ((int)$file['size'] <= 0) {
                throw new Exception('Файл пустой: ' . trainingPracticeEsc($originalName));
            }

            if ((int)$file['size'] > $maxSize) {
                throw new Exception('Файл слишком большой: ' . trainingPracticeEsc($originalName));
            }

            $baseName = pathinfo($originalName, PATHINFO_FILENAME);
            $safeName = trainingPracticeCleanFileName($baseName);
            $targetName = (int)$attemptId . '_' . (int)$messageId . '_' . time() . '_' . mt_rand(1000, 9999) . '_' . $safeName . '.' . $extension;
            $targetPath = $targetDir . $targetName;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('Не удалось сохранить файл: ' . trainingPracticeEsc($originalName));
            }

            $url = $assetsUrl . $relativeDir . $targetName;
            $path = 'assets/training/practices/' . $relativeDir . $targetName;
            $hash = is_file($targetPath) ? sha1_file($targetPath) : '';

            trainingPracticeInsert($modx, "
                INSERT INTO {$filesTable}
                (`attempt_id`, `message_id`, `practice_id`, `user_id`, `path`, `url`, `name`, `original_name`, `mime`, `extension`, `size`, `hash`, `createdon`)
                VALUES
                (:attempt_id, :message_id, :practice_id, :user_id, :path, :url, :name, :original_name, :mime, :extension, :size, :hash, :createdon)
            ", array(
                ':attempt_id' => (int)$attemptId,
                ':message_id' => (int)$messageId,
                ':practice_id' => (int)$practice['id'],
                ':user_id' => (int)$userId,
                ':path' => $path,
                ':url' => $url,
                ':name' => $targetName,
                ':original_name' => $originalName,
                ':mime' => (string)$file['type'],
                ':extension' => $extension,
                ':size' => (int)$file['size'],
                ':hash' => $hash,
                ':createdon' => $now,
            ));

            $saved[] = $url;
        }

        return $saved;
    }
}

if (!$modx->user || !(int)$modx->user->get('id') || !$modx->user->isAuthenticated($modx->context->get('key'))) {
    return '';
}

$corePath = $modx->getOption(
    'training.core_path',
    null,
    $modx->getOption('core_path') . 'components/training/'
);

$pageTpl = trim((string)$modx->getOption('pageTpl', $scriptProperties, 'training/practice/page.tpl'));
$messageTpl = trim((string)$modx->getOption('messageTpl', $scriptProperties, 'training/practice/message.tpl'));

$resourceId = (int)$modx->getOption('resource_id', $scriptProperties, $modx->resource ? $modx->resource->get('id') : 0);
$practiceId = (int)$modx->getOption('activity', $scriptProperties, 0);

if ($practiceId <= 0 && isset($_GET['activity'])) {
    $practiceId = (int)$_GET['activity'];
}

if ($practiceId <= 0 && isset($_GET['practice'])) {
    $practiceId = (int)$_GET['practice'];
}

$userId = (int)$modx->user->get('id');

$practicesTable = trainingPracticeTable($modx, 'training_practices');
$attemptsTable = trainingPracticeTable($modx, 'training_practice_attempts');
$messagesTable = trainingPracticeTable($modx, 'training_practice_messages');
$filesTable = trainingPracticeTable($modx, 'training_practice_files');
$testLinksTable = trainingPracticeTable($modx, 'training_test_links');
$usersTable = trainingPracticeTable($modx, 'users');
$userAttributesTable = trainingPracticeTable($modx, 'user_attributes');

$linkId = (int)$modx->getOption('link', $scriptProperties, 0);
if ($linkId <= 0 && isset($_GET['link'])) {
    $linkId = (int)$_GET['link'];
}

$practice = array();

if ($practiceId > 0) {
    $practice = trainingPracticeFetchRow($modx, "
        SELECT *
        FROM {$practicesTable}
        WHERE `id` = :id AND `active` = 1
        LIMIT 1
    ", array(
        ':id' => $practiceId,
    ));
}

if (!$practice) {
    return '<div class="section-block"><div class="alert alert-danger mb-0">Практическое задание не найдено.</div></div>';
}

if ($linkId <= 0) {
    $linkRow = trainingPracticeFetchRow($modx, "
        SELECT `id`
        FROM {$testLinksTable}
        WHERE `course_id` = :course_id
          AND `module_id` = :module_id
          AND `usertest_test_id` = :practice_id
          AND `link_type` = 'practice'
        ORDER BY `sort_order` ASC, `id` ASC
        LIMIT 1
    ", array(
        ':course_id' => (int)$practice['course_id'],
        ':module_id' => (int)$practice['module_id'],
        ':practice_id' => (int)$practice['id'],
    ));

    if ($linkRow) {
        $linkId = (int)$linkRow['id'];
    }
}

$contextKey = $modx->context ? $modx->context->get('key') : 'web';
$connectorUrl = rtrim($modx->getOption('assets_url'), '/') . '/components/training/web.connector.php';
$backId = isset($_GET['back']) ? (int)$_GET['back'] : 0;

$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['training_practice_action']) && $_POST['training_practice_action'] === 'submit') {
    try {
        $message = trim((string)(isset($_POST['message']) ? $_POST['message'] : ''));
        $hasUpload = trainingPracticeHasUpload('practice_files');

        if ($message === '' && !$hasUpload) {
            throw new Exception('Напишите сообщение или прикрепите файл.');
        }

        $latest = trainingPracticeFetchRow($modx, "
            SELECT *
            FROM {$attemptsTable}
            WHERE `practice_id` = :practice_id AND `user_id` = :user_id
            ORDER BY `attempt_num` DESC, `id` DESC
            LIMIT 1
        ", array(
            ':practice_id' => (int)$practice['id'],
            ':user_id' => $userId,
        ));

        if ($latest && in_array((string)$latest['status'], array('approved', 'rejected'), true)) {
            throw new Exception('Это задание уже закрыто.');
        }

        $now = date('Y-m-d H:i:s');
        $newAttempt = false;

        if (!$latest || in_array((string)$latest['status'], array('revision'), true)) {
            $newAttempt = true;
        }

        if ($newAttempt) {
            $maxAttempt = trainingPracticeFetchRow($modx, "
                SELECT MAX(`attempt_num`) AS max_num
                FROM {$attemptsTable}
                WHERE `practice_id` = :practice_id AND `user_id` = :user_id
            ", array(
                ':practice_id' => (int)$practice['id'],
                ':user_id' => $userId,
            ));

            $attemptNum = isset($maxAttempt['max_num']) ? ((int)$maxAttempt['max_num'] + 1) : 1;

            $deadlineAt = null;

            if (!empty($practice['deadline_at'])) {
                $deadlineAt = $practice['deadline_at'];
            } elseif (!empty($practice['deadline_days'])) {
                $deadlineAt = date('Y-m-d H:i:s', time() + ((int)$practice['deadline_days'] * 86400));
            }

            $stmt = $modx->prepare("
                UPDATE {$attemptsTable}
                SET `is_latest` = 0
                WHERE `practice_id` = :practice_id AND `user_id` = :user_id
            ");
            if ($stmt) {
                $stmt->execute(array(
                    ':practice_id' => (int)$practice['id'],
                    ':user_id' => $userId,
                ));
            }

            $attemptId = trainingPracticeInsert($modx, "
                INSERT INTO {$attemptsTable}
                (`practice_id`, `test_link_id`, `course_id`, `module_id`, `user_id`, `attempt_num`, `attempt_no`, `status`, `deadline_at`, `title`, `createdon`, `submittedon`, `is_latest`)
                VALUES
                (:practice_id, :test_link_id, :course_id, :module_id, :user_id, :attempt_num, :attempt_no, 'submitted', :deadline_at, :title, :createdon, :submittedon, 1)
            ", array(
                ':practice_id' => (int)$practice['id'],
                ':test_link_id' => $linkId,
                ':course_id' => (int)$practice['course_id'],
                ':module_id' => (int)$practice['module_id'],
                ':user_id' => $userId,
                ':attempt_num' => $attemptNum,
                ':attempt_no' => $attemptNum,
                ':deadline_at' => $deadlineAt,
                ':title' => (string)$practice['title'],
                ':createdon' => $now,
                ':submittedon' => $now,
            ));
        } else {
            $attemptId = (int)$latest['id'];

            $stmt = $modx->prepare("
                UPDATE {$attemptsTable}
                SET `status` = 'submitted', `submittedon` = :submittedon, `test_link_id` = :test_link_id, `is_latest` = 1
                WHERE `id` = :id
            ");
            $stmt->execute(array(
                ':submittedon' => $now,
                ':test_link_id' => $linkId,
                ':id' => $attemptId,
            ));
        }

        if ($attemptId <= 0) {
            throw new Exception('Не удалось сохранить попытку.');
        }

        $messageId = trainingPracticeInsert($modx, "
            INSERT INTO {$messagesTable}
            (`attempt_id`, `practice_id`, `author_id`, `author_type`, `user_id`, `sender_role`, `message`, `is_system`, `createdon`)
            VALUES
            (:attempt_id, :practice_id, :author_id, 'user', :user_id, 'employee', :message, 0, :createdon)
        ", array(
            ':attempt_id' => $attemptId,
            ':practice_id' => (int)$practice['id'],
            ':author_id' => $userId,
            ':user_id' => $userId,
            ':message' => $message,
            ':createdon' => $now,
        ));

        if ($messageId <= 0) {
            throw new Exception('Не удалось сохранить сообщение.');
        }

        trainingPracticeSaveFiles($modx, $practice, $attemptId, $messageId, $userId);

        $params = array(
            'activity' => (int)$practice['id'],
            'sent' => 1,
        );

        if ($linkId > 0) {
            $params['link'] = $linkId;
        }

        if (isset($_GET['back'])) {
            $params['back'] = (int)$_GET['back'];
        }

        $modx->sendRedirect($modx->makeUrl($resourceId, $modx->context->get('key'), $params, 'full'));
    } catch (Exception $e) {
        $formError = $e->getMessage();
    }
}

$latestAttempt = trainingPracticeFetchRow($modx, "
    SELECT *
    FROM {$attemptsTable}
    WHERE `practice_id` = :practice_id AND `user_id` = :user_id
    ORDER BY `attempt_num` DESC, `id` DESC
    LIMIT 1
", array(
    ':practice_id' => (int)$practice['id'],
    ':user_id' => $userId,
));

$currentStatus = $latestAttempt ? (string)$latestAttempt['status'] : 'not_started';
$deadlineAt = '';

if ($latestAttempt && !empty($latestAttempt['deadline_at'])) {
    $deadlineAt = $latestAttempt['deadline_at'];
} elseif (!empty($practice['deadline_at'])) {
    $deadlineAt = $practice['deadline_at'];
}

$isOverdue = false;

if ($deadlineAt && strtotime($deadlineAt) && time() > strtotime($deadlineAt) && !in_array($currentStatus, array('approved', 'rejected'), true)) {
    $isOverdue = true;
}

$displayStatus = $isOverdue ? 'overdue' : $currentStatus;
$statusText = trainingPracticeStatusText($displayStatus);

if ($isOverdue) {
    $statusText = 'Просрочен с ' . trainingPracticeFormatDate($deadlineAt, false);
}

$canSubmit = !in_array($currentStatus, array('approved', 'rejected'), true);

$messages = trainingPracticeFetchAll($modx, "
    SELECT 
        m.*,
        a.`attempt_num`,
        a.`status` AS attempt_status,
        u.`username`,
        ua.`fullname`,
        ua.`photo`
    FROM {$messagesTable} m
    INNER JOIN {$attemptsTable} a ON a.`id` = m.`attempt_id`
    LEFT JOIN {$usersTable} u ON u.`id` = m.`author_id`
    LEFT JOIN {$userAttributesTable} ua ON ua.`internalKey` = u.`id`
    WHERE a.`practice_id` = :practice_id AND a.`user_id` = :user_id
    ORDER BY m.`createdon` ASC, m.`id` ASC
", array(
    ':practice_id' => (int)$practice['id'],
    ':user_id' => $userId,
));

$messageIds = array();

foreach ($messages as $messageRow) {
    $messageIds[] = (int)$messageRow['id'];
}

$filesByMessage = array();

if ($messageIds) {
    $idsSql = implode(',', array_map('intval', $messageIds));
    $fileRows = trainingPracticeFetchAll($modx, "
        SELECT *
        FROM {$filesTable}
        WHERE `message_id` IN ({$idsSql})
        ORDER BY `id` ASC
    ");

    foreach ($fileRows as $fileRow) {
        $mid = (int)$fileRow['message_id'];

        if (!isset($filesByMessage[$mid])) {
            $filesByMessage[$mid] = array();
        }

        $filesByMessage[$mid][] = $fileRow;
    }
}

$messagesHtml = '';
$commentsHtml = '';

foreach ($messages as $messageRow) {
    $mid = (int)$messageRow['id'];
    $authorType = (string)$messageRow['author_type'];
    $isUser = $authorType === 'user';
    $name = trim((string)$messageRow['fullname']);

    if ($name === '') {
        $name = trim((string)$messageRow['username']);
    }

    if ($name === '') {
        $name = $isUser ? 'Пользователь' : 'Ментор';
    }

    $filesHtml = '';

    if (!empty($filesByMessage[$mid])) {
        foreach ($filesByMessage[$mid] as $fileRow) {
            $filesHtml .= '<a class="practice-message-file" href="' . trainingPracticeEsc($fileRow['url']) . '" target="_blank" rel="noopener">' .
                '<span class="practice-message-file__ico"><img src="theme/images/practic/screpca.svg" class="img-svg" alt=""></span>' .
                '<span class="practice-message-file__name">' . trainingPracticeEsc($fileRow['original_name']) . '</span>' .
            '</a>';
        }
    }

    $messageHtml = trainingPracticeRenderChunk($corePath, $messageTpl, array(
        'message_class' => $isUser ? 'is-user' : 'is-mentor',
        'author_name' => trainingPracticeEsc($name),
        'author_avatar' => trainingPracticeEsc(trainingPracticeAvatarUrl($modx, isset($messageRow['photo']) ? $messageRow['photo'] : '')),
        'message_text' => trainingPracticeTextHtml($messageRow['message']),
        'message_date' => trainingPracticeEsc(trainingPracticeFormatDate($messageRow['createdon'])),
        'files_html' => $filesHtml,
    ));

    $messagesHtml .= $messageHtml;

    if (!$isUser) {
        $commentsHtml .= $messageHtml;
    }
}

if ($messagesHtml === '') {
    $messagesHtml = '
        <div class="practice-empty">
            <div class="practice-empty__ico"><img src="theme/images/practic/mail.svg" class="" alt=""></div>
            <div class="practice-empty__title">Нет истории сообщений</div>
            <div class="practice-empty__text">Напишите в чат, чтобы начать общение</div>
        </div>
    ';
}

if ($commentsHtml === '') {
    $commentsHtml = '
        <div class="practice-empty">
            <div class="practice-empty__ico"><img src="theme/images/practic/mail.svg" class="" alt=""></div>
            <div class="practice-empty__title">Комментариев пока нет</div>
            <div class="practice-empty__text">Ответ ментора появится здесь после проверки</div>
        </div>
    ';
}

$templateDownloadHtml = '';

$formErrorHtml = '';

if ($formError !== '') {
    $formErrorHtml = '<div class="practice-form-error">' . trainingPracticeEsc($formError) . '</div>';
}

$formSuccessHtml = '';

if (!empty($_GET['sent'])) {
    $formSuccessHtml = '<div class="practice-form-success">Сообщение отправлено.</div>';
}

$formHtml = '';

if ($canSubmit) {
    $formHtml = '
        <form class="practice-chat-form" method="post" enctype="multipart/form-data" data-practice-form data-practice-connector="' . trainingPracticeEsc($connectorUrl) . '" data-practice-context="' . trainingPracticeEsc($contextKey) . '">
            <input type="hidden" name="training_practice_action" value="submit">
            <input type="hidden" name="action" value="web/practice/submit">
            <input type="hidden" name="ctx" value="' . trainingPracticeEsc($contextKey) . '">
            <input type="hidden" name="practice_id" value="' . (int)$practice['id'] . '">
            <input type="hidden" name="activity" value="' . (int)$practice['id'] . '">
            <input type="hidden" name="link" value="' . (int)$linkId . '">
            <input type="hidden" name="back" value="' . (int)$backId . '">
            ' . $formErrorHtml . '
            ' . $formSuccessHtml . '
            <div class="practice-form-message" data-practice-form-message></div>
            <div class="practice-files-preview" data-practice-files-preview></div>
            <div class="practice-chat-form__row">
                <label class="practice-file-btn" title="Прикрепить файл">
                    <input type="file" name="practice_files[]" multiple data-practice-files>
                    <img src="theme/images/practic/screpca.svg" class="img-svg" alt="">
                </label>
                <textarea class="practice-chat-input" name="message" rows="1" placeholder="Ваш ответ" data-practice-message></textarea>
                <button type="submit" class="practice-send-btn" data-practice-submit disabled>
                    <img src="theme/images/practic/send.svg" class="img-svg" alt="">
                    <span>Отправить</span>
                </button>
            </div>
        </form>
    ';
} else {
    $formHtml = '
        <div class="practice-closed-note">
            Задание закрыто. Отправка новых сообщений недоступна.
        </div>
    ';
}

$image = trim((string)$practice['image']);

if ($image === '') {
    $image = 'theme/images/training/tests/logo-scanport.svg';
}

return trainingPracticeRenderChunk($corePath, $pageTpl, array(
    'practice_id' => (int)$practice['id'],
    'connector_url' => trainingPracticeEsc($connectorUrl),
    'context_key' => trainingPracticeEsc($contextKey),
    'practice_title' => trainingPracticeEsc($practice['title']),
    'practice_description' => trainingPracticeTextHtml($practice['description']),
    'practice_image' => trainingPracticeEsc($image),
    'status_text' => trainingPracticeEsc($statusText),
    'status_class' => trainingPracticeEsc(trainingPracticeStatusClass($displayStatus)),
    'template_download_html' => $templateDownloadHtml,
    'messages_html' => $messagesHtml,
    'comments_html' => $commentsHtml,
    'form_html' => $formHtml,
));

<?php

require_once dirname(dirname(__FILE__)) . '/_helpers.php';

class TrainingWebPracticeSubmitProcessor extends modProcessor
{
    protected static $tableCache = array();

    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        if ($failure = TrainingWebHelper::requireAuth($this)) {
            return $failure;
        }

        $userId = (int)$this->modx->user->get('id');
        $practiceId = (int)$this->getProperty('practice_id', 0);
        if ($practiceId <= 0) {
            $practiceId = (int)$this->getProperty('activity', 0);
        }
        if ($practiceId <= 0) {
            $practiceId = (int)$this->getProperty('practice', 0);
        }

        $linkId = (int)$this->getProperty('link', 0);
        $message = trim((string)$this->getProperty('message', ''));
        $hasUpload = $this->hasUpload('practice_files');

        if ($practiceId <= 0) {
            return $this->failure('Не указано практическое задание', array('code' => 400));
        }

        if ($message === '' && !$hasUpload) {
            return $this->failure('Напишите сообщение или прикрепите файл.', array('code' => 400));
        }

        $practicesTable = $this->table('training_practices');
        $attemptsTable = $this->table('training_practice_attempts');
        $messagesTable = $this->table('training_practice_messages');
        $testLinksTable = $this->table('training_test_links');

        $practice = $this->fetchRow("SELECT * FROM {$practicesTable} WHERE `id` = :id AND `active` = 1 LIMIT 1", array(
            ':id' => $practiceId,
        ));

        if (!$practice) {
            return $this->failure('Практическое задание не найдено.', array('code' => 404));
        }

        if ($linkId <= 0) {
            $link = $this->fetchRow("\n                SELECT `id`\n                FROM {$testLinksTable}\n                WHERE `course_id` = :course_id\n                  AND `module_id` = :module_id\n                  AND `usertest_test_id` = :practice_id\n                  AND `link_type` = 'practice'\n                ORDER BY `sort_order` ASC, `id` ASC\n                LIMIT 1\n            ", array(
                ':course_id' => (int)$practice['course_id'],
                ':module_id' => (int)$practice['module_id'],
                ':practice_id' => (int)$practice['id'],
            ));
            if ($link) {
                $linkId = (int)$link['id'];
            }
        }

        try {
            $latest = $this->fetchRow("\n                SELECT *\n                FROM {$attemptsTable}\n                WHERE `practice_id` = :practice_id AND `user_id` = :user_id\n                ORDER BY `attempt_num` DESC, `id` DESC\n                LIMIT 1\n            ", array(
                ':practice_id' => (int)$practice['id'],
                ':user_id' => $userId,
            ));

            if ($latest && in_array((string)$latest['status'], array('approved', 'rejected'), true)) {
                return $this->failure('Это задание уже закрыто.', array('code' => 403));
            }

            $now = date('Y-m-d H:i:s');
            $newAttempt = (!$latest || in_array((string)$latest['status'], array('revision'), true));

            if ($newAttempt) {
                $maxAttempt = $this->fetchRow("\n                    SELECT MAX(`attempt_num`) AS max_num\n                    FROM {$attemptsTable}\n                    WHERE `practice_id` = :practice_id AND `user_id` = :user_id\n                ", array(
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

                $stmt = $this->modx->prepare("\n                    UPDATE {$attemptsTable}\n                    SET `is_latest` = 0\n                    WHERE `practice_id` = :practice_id AND `user_id` = :user_id\n                ");
                if ($stmt) {
                    $stmt->execute(array(
                        ':practice_id' => (int)$practice['id'],
                        ':user_id' => $userId,
                    ));
                }

                $attemptId = $this->insert("\n                    INSERT INTO {$attemptsTable}\n                    (`practice_id`, `test_link_id`, `course_id`, `module_id`, `user_id`, `attempt_num`, `attempt_no`, `status`, `deadline_at`, `title`, `createdon`, `submittedon`, `is_latest`)\n                    VALUES\n                    (:practice_id, :test_link_id, :course_id, :module_id, :user_id, :attempt_num, :attempt_no, 'submitted', :deadline_at, :title, :createdon, :submittedon, 1)\n                ", array(
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
                $stmt = $this->modx->prepare("\n                    UPDATE {$attemptsTable}\n                    SET `status` = 'submitted', `submittedon` = :submittedon, `test_link_id` = :test_link_id, `is_latest` = 1, `updatedon` = :updatedon\n                    WHERE `id` = :id\n                ");
                if (!$stmt || !$stmt->execute(array(
                    ':submittedon' => $now,
                    ':test_link_id' => $linkId,
                    ':updatedon' => $now,
                    ':id' => $attemptId,
                ))) {
                    throw new Exception('Не удалось обновить попытку.');
                }
            }

            if ($attemptId <= 0) {
                throw new Exception('Не удалось сохранить попытку.');
            }

            $messageId = $this->insert("\n                INSERT INTO {$messagesTable}\n                (`attempt_id`, `practice_id`, `author_id`, `author_type`, `user_id`, `sender_role`, `message`, `is_system`, `createdon`)\n                VALUES\n                (:attempt_id, :practice_id, :author_id, 'user', :user_id, 'employee', :message, 0, :createdon)\n            ", array(
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

            $files = $this->saveFiles($practice, $attemptId, $messageId, $userId);
            $this->recalculateProgress((int)$practice['course_id'], (int)$practice['module_id'], $userId);

            $userProfile = $this->getUserProfile($userId);
            $this->sendPracticeSubmitNotification($practice, $userProfile, $message);

            $messageHtml = $this->renderMessage(array(
                'id' => $messageId,
                'author_type' => 'user',
                'message' => $message,
                'createdon' => $now,
                'fullname' => $userProfile['name'],
                'username' => '',
                'photo' => $userProfile['photo'],
            ), $files);

            return $this->success('Сообщение отправлено.', array(
                'attempt_id' => $attemptId,
                'message_id' => $messageId,
                'status' => 'submitted',
                'status_text' => $this->statusText('submitted'),
                'status_class' => $this->statusClass('submitted'),
                'message_html' => $messageHtml,
                'files' => $files,
            ));
        } catch (Exception $e) {
            return $this->failure($e->getMessage(), array('code' => 500));
        }
    }

    protected function table($name)
    {
        $name = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$name);
        if (isset(self::$tableCache[$name])) {
            return self::$tableCache[$name];
        }

        $prefix = (string)$this->modx->getOption('table_prefix');
        $raw = $prefix . $name;
        $fixed = rtrim($prefix, '_') . '_' . $name;
        $candidates = array($raw);
        if ($fixed !== $raw) {
            $candidates[] = $fixed;
        }
        $candidates[] = 'modx_' . $name;

        foreach (array_unique($candidates) as $table) {
            $safe = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table);
            if ($safe === '') {
                continue;
            }
            $stmt = $this->modx->query('SHOW TABLES LIKE ' . $this->modx->quote($safe));
            if ($stmt && $stmt->fetchColumn()) {
                self::$tableCache[$name] = '`' . $safe . '`';
                return self::$tableCache[$name];
            }
        }

        self::$tableCache[$name] = '`' . preg_replace('/[^a-zA-Z0-9_]/', '', $raw) . '`';
        return self::$tableCache[$name];
    }

    protected function fetchRow($sql, array $params = array())
    {
        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute($params)) {
            return array();
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : array();
    }

    protected function insert($sql, array $params = array())
    {
        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute($params)) {
            return 0;
        }
        return (int)$this->modx->lastInsertId();
    }

    protected function hasUpload($field)
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

    protected function normalizeFiles($field)
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

    protected function saveFiles(array $practice, $attemptId, $messageId, $userId)
    {
        $saved = array();
        $files = $this->normalizeFiles('practice_files');
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
        if (!$allowed) {
            $allowed = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'zip');
        }

        $maxSize = !empty($practice['max_file_size']) ? (int)$practice['max_file_size'] : 52428800;
        $assetsPath = rtrim($this->modx->getOption('assets_path'), '/') . '/training/practices/';
        $assetsUrl = rtrim($this->modx->getOption('assets_url'), '/') . '/training/practices/';
        $relativeDir = (int)$practice['id'] . '/' . (int)$userId . '/' . (int)$attemptId . '/';
        $targetDir = $assetsPath . $relativeDir;

        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
            throw new Exception('Не удалось создать папку для файлов');
        }

        $filesTable = $this->table('training_practice_files');
        $now = date('Y-m-d H:i:s');

        foreach ($files as $file) {
            if ((int)$file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ((int)$file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Ошибка загрузки файла: ' . $this->esc($file['name']));
            }

            $originalName = (string)$file['name'];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if ($extension === '' || !in_array($extension, $allowed, true)) {
                throw new Exception('Недопустимый тип файла: ' . $this->esc($originalName));
            }
            if ((int)$file['size'] <= 0) {
                throw new Exception('Файл пустой: ' . $this->esc($originalName));
            }
            if ((int)$file['size'] > $maxSize) {
                throw new Exception('Файл слишком большой: ' . $this->esc($originalName));
            }

            $baseName = pathinfo($originalName, PATHINFO_FILENAME);
            $safeName = $this->cleanFileName($baseName);
            $targetName = (int)$attemptId . '_' . (int)$messageId . '_' . time() . '_' . mt_rand(1000, 9999) . '_' . $safeName . '.' . $extension;
            $targetPath = $targetDir . $targetName;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('Не удалось сохранить файл: ' . $this->esc($originalName));
            }

            $url = $assetsUrl . $relativeDir . $targetName;
            $path = 'assets/training/practices/' . $relativeDir . $targetName;
            $hash = is_file($targetPath) ? sha1_file($targetPath) : '';

            $fileId = $this->insert("\n                INSERT INTO {$filesTable}\n                (`attempt_id`, `message_id`, `practice_id`, `user_id`, `path`, `url`, `name`, `original_name`, `mime`, `extension`, `size`, `hash`, `createdon`)\n                VALUES\n                (:attempt_id, :message_id, :practice_id, :user_id, :path, :url, :name, :original_name, :mime, :extension, :size, :hash, :createdon)\n            ", array(
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

            $saved[] = array(
                'id' => $fileId,
                'url' => $url,
                'name' => $targetName,
                'original_name' => $originalName,
                'extension' => $extension,
                'size' => (int)$file['size'],
            );
        }

        return $saved;
    }

    protected function cleanFileName($name)
    {
        $name = trim((string)$name);
        $name = preg_replace('/[^\pL\pN\.\-_]+/u', '-', $name);
        $name = trim($name, '-_.');
        return $name !== '' ? $name : 'file';
    }

    protected function renderMessage(array $messageRow, array $files = array())
    {
        $corePath = $this->modx->getOption('training.core_path', null, $this->modx->getOption('core_path') . 'components/training/');
        $tplPath = rtrim(str_replace('\\', '/', $corePath), '/') . '/elements/chunks/training/practice/message.tpl';

        $isUser = (string)$messageRow['author_type'] === 'user';
        $name = trim((string)(isset($messageRow['fullname']) ? $messageRow['fullname'] : ''));
        if ($name === '') {
            $name = trim((string)(isset($messageRow['username']) ? $messageRow['username'] : ''));
        }
        if ($name === '') {
            $name = $isUser ? 'Пользователь' : 'Ментор';
        }

        $avatar = '';
        if (isset($messageRow['author_avatar'])) {
            $avatar = (string)$messageRow['author_avatar'];
        } elseif (isset($messageRow['photo'])) {
            $avatar = (string)$messageRow['photo'];
        }
        $avatar = $this->avatarUrl($avatar);

        $filesHtml = '';
        foreach ($files as $file) {
            $url = isset($file['url']) ? (string)$file['url'] : '';
            $fileName = isset($file['original_name']) ? (string)$file['original_name'] : (isset($file['name']) ? (string)$file['name'] : 'Файл');
            if ($url === '') {
                continue;
            }
            $filesHtml .= '<a class="practice-message-file" href="' . $this->esc($url) . '" target="_blank" rel="noopener">' .
                '<span class="practice-message-file__ico">📎</span>' .
                '<span class="practice-message-file__name">' . $this->esc($fileName) . '</span>' .
            '</a>';
        }

        $replace = array(
            '{$message_class}' => $isUser ? 'is-user' : 'is-mentor',
            '{$author_name}' => $this->esc($name),
            '{$author_avatar}' => $this->esc($avatar),
            '{$message_text}' => $this->textHtml((string)$messageRow['message']),
            '{$message_date}' => $this->esc($this->formatDate($messageRow['createdon'])),
            '{$files_html}' => $filesHtml,
        );

        if (!is_file($tplPath)) {
            return '<div class="practice-message ' . $replace['{$message_class}'] . '"><div class="practice-message__avatar"><img src="' . $replace['{$author_avatar}'] . '" alt="" loading="lazy"></div><div class="practice-message__content"><div class="practice-message__author">' . $replace['{$author_name}'] . '</div><div class="practice-message__bubble"><div class="practice-message__text">' . $replace['{$message_text}'] . '</div>' . $filesHtml . '<div class="practice-message__time">' . $replace['{$message_date}'] . '</div></div></div></div>';
        }

        return strtr((string)file_get_contents($tplPath), $replace);
    }

    protected function sendPracticeSubmitNotification(array $practice, array $userProfile, $message)
    {
        $to = trim((string)$this->modx->getOption('training.practice_notification_email', null, 'partner@scanport.ru'));
        if ($to === '') {
            $to = 'partner@scanport.ru';
        }

        $subject = 'Студент оставил уведомление, необходимо проверить курс.';
        $userName = trim((string)(isset($userProfile['name']) ? $userProfile['name'] : ''));
        if ($userName === '') {
            $userName = 'Пользователь #' . (int)$this->modx->user->get('id');
        }

        $userEmail = trim((string)(isset($userProfile['email']) ? $userProfile['email'] : ''));
        if ($userEmail === '') {
            $userEmail = '—';
        }

        $company = trim((string)(isset($userProfile['company']) ? $userProfile['company'] : ''));
        if ($company === '') {
            $company = '—';
        }

        $courseLabel = $this->getCourseLabel((int)$practice['course_id']);
        $moduleLabel = $this->getModuleLabel((int)$practice['course_id'], (int)$practice['module_id']);
        $practiceLabel = $this->getPracticeLabel($practice);
        $messageText = trim((string)$message);
        if ($messageText === '') {
            $messageText = '—';
        }

        $body = "Студент оставил уведомление, необходимо проверить курс.\n\n" .
            "Пользователь: " . $userName . "\n" .
            "Email пользователя: " . $userEmail . "\n" .
            "Компания: " . $company . "\n" .
            "Курс: " . $courseLabel . "\n" .
            "Модуль: " . $moduleLabel . "\n" .
            "Практическое задание: " . $practiceLabel . "\n" .
            "Сообщение:\n" . $messageText;

        if (!$this->sendMail($to, $subject, $body)) {
            $this->modx->log(modX::LOG_LEVEL_WARN, '[training] practice submit mail notification failed. To: ' . $to . ', practice_id: ' . (int)$practice['id'] . ', user_id: ' . (int)$this->modx->user->get('id'));
        }
    }

    protected function getCourseLabel($courseId)
    {
        $courseId = (int)$courseId;
        if ($courseId <= 0) {
            return 'Курс —';
        }

        $coursesTable = $this->table('training_courses');
        $resourceTable = $this->modx->getTableName('modResource');

        $row = $this->fetchRow("
            SELECT COALESCE(NULLIF(TRIM(r.`pagetitle`), ''), CONCAT('Курс #', c.`id`)) AS `title`
            FROM {$coursesTable} AS c
            LEFT JOIN {$resourceTable} AS r
                ON r.`id` = c.`resource_id`
               AND r.`deleted` = 0
            WHERE c.`id` = :course_id
            LIMIT 1
        ", array(
            ':course_id' => $courseId,
        ));

        $title = trim((string)(isset($row['title']) ? $row['title'] : ''));
        if ($title === '') {
            $title = 'Курс #' . $courseId;
        }

        return $title;
    }

    protected function getModuleLabel($courseId, $moduleId)
    {
        $courseId = (int)$courseId;
        $moduleId = (int)$moduleId;
        if ($moduleId <= 0) {
            return 'Модуль —';
        }

        $modulesTable = $this->table('training_modules');
        $moduleNumber = 0;

        if ($courseId > 0) {
            $row = $this->fetchRow("\n                SELECT COUNT(*) AS num\n                FROM {$modulesTable}\n                WHERE `course_id` = :course_id AND `id` <= :module_id\n            ", array(
                ':course_id' => $courseId,
                ':module_id' => $moduleId,
            ));
            $moduleNumber = isset($row['num']) ? (int)$row['num'] : 0;
        }

        if ($moduleNumber <= 0) {
            $moduleNumber = $moduleId;
        }

        return 'Модуль ' . $moduleNumber;
    }

    protected function getPracticeLabel(array $practice)
    {
        $title = trim((string)(isset($practice['title']) ? $practice['title'] : ''));
        if ($title !== '') {
            return $title;
        }

        $practiceId = isset($practice['id']) ? (int)$practice['id'] : 0;
        return $practiceId > 0 ? ('Практическое задание ' . $practiceId) : 'Практическое задание —';
    }

    protected function sendMail($to, $subject, $body)
    {
        $to = trim((string)$to);
        if ($to === '') {
            return false;
        }

        $from = trim((string)$this->modx->getOption('training.practice_notification_email_from', null, ''));
        if ($from === '') {
            $from = trim((string)$this->modx->getOption('emailsender', null, ''));
        }
        if ($from === '') {
            $from = 'partner@scanport.ru';
        }

        $fromName = trim((string)$this->modx->getOption('training.practice_notification_from_name', null, ''));
        if ($fromName === '') {
            $fromName = trim((string)$this->modx->getOption('site_name', null, ''));
        }
        if ($fromName === '') {
            $fromName = 'Scanport';
        }

        $this->modx->getService('mail', 'mail.modPHPMailer');
        if (!$this->modx->mail) {
            return false;
        }

        $this->modx->mail->set(modMail::MAIL_BODY, (string)$body);
        $this->modx->mail->set(modMail::MAIL_FROM, $from);
        $this->modx->mail->set(modMail::MAIL_FROM_NAME, $fromName);
        $this->modx->mail->set(modMail::MAIL_SUBJECT, (string)$subject);
        $this->modx->mail->address('to', $to);
        $this->modx->mail->setHTML(false);

        $sent = (bool)$this->modx->mail->send();
        $this->modx->mail->reset();

        return $sent;
    }

    protected function getUserProfile($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return array('name' => '', 'email' => '', 'photo' => '', 'company' => '');
        }

        $usersTable = $this->table('users');
        $attributesTable = $this->table('user_attributes');
        $row = $this->fetchRow("\n            SELECT u.`username`, ua.`fullname`, ua.`email`, ua.`photo`, ua.`extended`, ua.`field_company`, ua.`field_list_company`
            FROM {$usersTable} u
            LEFT JOIN {$attributesTable} ua ON ua.`internalKey` = u.`id`
            WHERE u.`id` = :id
            LIMIT 1
        ", array(':id' => $userId));

        $name = trim((string)(isset($row['fullname']) ? $row['fullname'] : ''));
        if ($name === '') {
            $name = trim((string)(isset($row['username']) ? $row['username'] : ''));
        }

        $company = $this->extractCompanyFromProfileRow($row);

        return array(
            'name' => $name,
            'email' => isset($row['email']) ? (string)$row['email'] : '',
            'photo' => isset($row['photo']) ? (string)$row['photo'] : '',
            'company' => $company,
        );
    }

    protected function extractCompanyFromProfileRow(array $row)
    {
        $keys = array('field_list_company', 'field_company', 'company', 'organization');
        foreach ($keys as $key) {
            if (isset($row[$key])) {
                $value = trim((string)$row[$key]);
                if ($value !== '') {
                    return $value;
                }
            }
        }

        if (!empty($row['extended'])) {
            $extended = json_decode((string)$row['extended'], true);
            if (is_array($extended)) {
                $extendedKeys = array('company', 'organization', 'organisation', 'company_name', 'employer');
                foreach ($extendedKeys as $key) {
                    if (!empty($extended[$key])) {
                        return trim((string)$extended[$key]);
                    }
                }
            }
        }

        return '';
    }

    protected function getUserDisplayName($userId)
    {
        $profile = $this->getUserProfile($userId);
        return $profile['name'];
    }

    protected function avatarUrl($photo)
    {
        $photo = trim((string)$photo);
        if ($photo === '') {
            $photo = 'theme/images/profile/no-photo.svg';
        }
        if (preg_match('#^https?://#i', $photo) || strpos($photo, '/') === 0) {
            return $photo;
        }
        return rtrim($this->modx->getOption('base_url'), '/') . '/' . ltrim($photo, '/');
    }

    protected function recalculateProgress($courseId, $moduleId, $userId)
    {
        try {
            $service = TrainingWebHelper::getProgressService($this->modx);
            if ($service) {
                $service->recalculateModuleProgressFromLessons($courseId, $moduleId, $userId);
                $service->recalculateUserCourse($courseId, $userId);
            }
        } catch (Exception $e) {
            $this->modx->log(modX::LOG_LEVEL_WARN, '[training] practice submit progress recalc failed: ' . $e->getMessage());
        }
    }

    protected function statusText($status)
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
        return isset($map[$status]) ? $map[$status] : (string)$status;
    }

    protected function statusClass($status)
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

    protected function textHtml($text)
    {
        $text = trim((string)$text);
        if ($text === '') {
            return '';
        }
        if ($text !== strip_tags($text)) {
            return $text;
        }
        return nl2br($this->esc($text));
    }

    protected function formatDate($value)
    {
        $time = strtotime((string)$value);
        return $time ? date('d.m.Y H:i', $time) : '';
    }

    protected function esc($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

return 'TrainingWebPracticeSubmitProcessor';

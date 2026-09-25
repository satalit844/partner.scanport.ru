<?php

/**
 * Sends manager notifications when a TrainingUserCourse first transitions
 * to status=completed.
 *
 * Storage is intentionally plain SQL so notification logging does not depend
 * on a generated xPDO model map.
 */
class TrainingCompletionNotificationService
{
    /** @var modX */
    protected $modx;

    /** @var string */
    protected $coursesTable;

    /** @var string */
    protected $notificationsTable;

    public function __construct(modX $modx)
    {
        $this->modx = $modx;
        $prefix = (string)$modx->getOption('table_prefix');
        $this->coursesTable = $prefix . 'training_courses';
        $this->notificationsTable = $prefix . 'training_completion_notifications';
    }

    protected function normalizeRecipients($raw)
    {
        $emails = array();
        foreach (preg_split('/[\s,;]+/u', trim((string)$raw)) as $email) {
            $email = trim((string)$email);
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $key = strtolower($email);
            $emails[$key] = $email;
        }
        return array_values($emails);
    }

    protected function loadCourse($courseId)
    {
        $resources = trim((string)$this->modx->getTableName('modResource'), '`');

        $sql = 'SELECT c.*, r.`pagetitle`, r.`uri`, r.`context_key` '
            . 'FROM `' . $this->coursesTable . '` c '
            . 'LEFT JOIN `' . $resources . '` r ON r.`id` = c.`resource_id` '
            . 'WHERE c.`id` = :id LIMIT 1';

        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute(array(':id' => (int)$courseId))) {
            return null;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    protected function loadUser($userId)
    {
        $user = $this->modx->getObject('modUser', array('id' => (int)$userId));
        if (!$user) {
            return array(
                'id' => (int)$userId,
                'username' => '',
                'fullname' => 'Пользователь #' . (int)$userId,
                'email' => '',
            );
        }

        $profile = $user->getOne('Profile');
        $username = trim((string)$user->get('username'));
        $fullname = $profile ? trim((string)$profile->get('fullname')) : '';
        $email = $profile ? trim((string)$profile->get('email')) : '';

        if ($fullname === '') {
            $fullname = $username !== '' ? $username : ('Пользователь #' . (int)$userId);
        }

        return array(
            'id' => (int)$userId,
            'username' => $username,
            'fullname' => $fullname,
            'email' => $email,
        );
    }

    protected function courseUrl(array $course)
    {
        $resourceId = isset($course['resource_id']) ? (int)$course['resource_id'] : 0;
        if ($resourceId <= 0) {
            return '';
        }

        $context = trim((string)(isset($course['context_key']) ? $course['context_key'] : 'web'));
        if ($context === '') {
            $context = 'web';
        }

        try {
            $url = (string)$this->modx->makeUrl($resourceId, $context, '', 'full');
            if ($url !== '') {
                return $url;
            }
        } catch (Throwable $e) {
        }

        $siteUrl = rtrim((string)$this->modx->getOption('site_url', null, ''), '/');
        $uri = ltrim((string)(isset($course['uri']) ? $course['uri'] : ''), '/');
        return ($siteUrl !== '' && $uri !== '') ? ($siteUrl . '/' . $uri) : '';
    }

    protected function buildMaps(array $course, array $user, TrainingUserCourse $userCourse)
    {
        $courseTitle = trim((string)(isset($course['pagetitle']) ? $course['pagetitle'] : ''));
        if ($courseTitle === '') {
            $courseTitle = 'Курс #' . (int)$course['id'];
        }

        $completedRaw = trim((string)$userCourse->get('completedon'));
        $completedFormatted = $completedRaw;
        if ($completedRaw !== '') {
            $ts = strtotime($completedRaw);
            if ($ts) {
                $completedFormatted = date('d.m.Y H:i', $ts);
            }
        }

        $plain = array(
            '[[+user.id]]' => (string)$user['id'],
            '[[+user.username]]' => (string)$user['username'],
            '[[+user.fullname]]' => (string)$user['fullname'],
            '[[+user.email]]' => (string)$user['email'],
            '[[+course.id]]' => (string)$course['id'],
            '[[+course.name]]' => $courseTitle,
            '[[+course.url]]' => $this->courseUrl($course),
            '[[+course.completedon]]' => $completedFormatted,
        );

        $html = array();
        foreach ($plain as $key => $value) {
            $html[$key] = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        }

        return array('plain' => $plain, 'html' => $html);
    }

    protected function claim(
        $courseId,
        $userId,
        $userCourseId,
        $completedon,
        array $recipients,
        $subject
    ) {
        $sql = 'INSERT IGNORE INTO `' . $this->notificationsTable . '` '
            . '(`course_id`,`user_id`,`user_course_id`,`completedon`,`recipients`,`subject`,`status`,`createdon`,`updatedon`) '
            . 'VALUES (:course_id,:user_id,:user_course_id,:completedon,:recipients,:subject,"pending",NOW(),NOW())';

        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute(array(
            ':course_id' => (int)$courseId,
            ':user_id' => (int)$userId,
            ':user_course_id' => (int)$userCourseId,
            ':completedon' => $completedon !== '' ? $completedon : null,
            ':recipients' => implode(', ', $recipients),
            ':subject' => (string)$subject,
        ))) {
            throw new Exception('Cannot create notification log row.');
        }

        if ((int)$stmt->rowCount() !== 1) {
            return 0; // Already claimed/sent/failed for this user_course_id.
        }

        $stmt = $this->modx->prepare(
            'SELECT `id` FROM `' . $this->notificationsTable . '` '
            . 'WHERE `user_course_id` = :user_course_id LIMIT 1'
        );
        if (!$stmt || !$stmt->execute(array(':user_course_id' => (int)$userCourseId))) {
            throw new Exception('Cannot resolve created notification row.');
        }

        return (int)$stmt->fetchColumn();
    }

    protected function updateLog($id, $status, $error = '')
    {
        $status = $status === 'sent' ? 'sent' : 'failed';

        $sql = 'UPDATE `' . $this->notificationsTable . '` '
            . 'SET `status` = :status, '
            . '`senton` = ' . ($status === 'sent' ? 'NOW()' : 'NULL') . ', '
            . '`error_message` = :error, `updatedon` = NOW() '
            . 'WHERE `id` = :id';

        $stmt = $this->modx->prepare($sql);
        if ($stmt) {
            $stmt->execute(array(
                ':status' => $status,
                ':error' => (string)$error,
                ':id' => (int)$id,
            ));
        }
    }

    protected function mailError()
    {
        if (
            isset($this->modx->mail->mailer)
            && is_object($this->modx->mail->mailer)
            && isset($this->modx->mail->mailer->ErrorInfo)
        ) {
            return trim((string)$this->modx->mail->mailer->ErrorInfo);
        }
        return '';
    }

    public function notifyCompletion($courseId, $userId, TrainingUserCourse $userCourse)
    {
        $courseId = (int)$courseId;
        $userId = (int)$userId;
        $userCourseId = (int)$userCourse->get('id');

        if ($courseId <= 0 || $userId <= 0 || $userCourseId <= 0) {
            return array('status' => 'invalid_input');
        }

        $course = $this->loadCourse($courseId);
        if (!$course) {
            return array('status' => 'course_not_found');
        }

        if (empty($course['completion_notify_enabled'])) {
            return array('status' => 'disabled');
        }

        $recipients = $this->normalizeRecipients(
            isset($course['completion_notify_emails'])
                ? $course['completion_notify_emails']
                : ''
        );
        if (!$recipients) {
            $this->modx->log(
                modX::LOG_LEVEL_WARN,
                '[training completion notify] enabled but no valid recipient for course #' . $courseId
            );
            return array('status' => 'no_recipients');
        }

        $user = $this->loadUser($userId);
        $maps = $this->buildMaps($course, $user, $userCourse);

        $subjectTemplate = trim((string)(
            isset($course['completion_notify_subject'])
                ? $course['completion_notify_subject']
                : ''
        ));
        if ($subjectTemplate === '') {
            $subjectTemplate = 'Пользователь [[+user.fullname]] прошёл курс [[+course.name]]';
        }

        $bodyTemplate = trim((string)(
            isset($course['completion_notify_body'])
                ? $course['completion_notify_body']
                : ''
        ));
        if ($bodyTemplate === '') {
            $bodyTemplate =
                '<p>Пользователь <strong>[[+user.fullname]]</strong> ([[+user.email]]) завершил курс '
                . '«<strong>[[+course.name]]</strong>».</p>'
                . '<p>Дата завершения: [[+course.completedon]]</p>'
                . '<p><a href="[[+course.url]]">Открыть курс</a></p>';
        }

        $subject = strtr($subjectTemplate, $maps['plain']);
        $subject = trim(strip_tags($subject));
        if ($subject === '') {
            $subject = 'Завершение курса #' . $courseId;
        }
        if (function_exists('mb_substr')) {
            $subject = mb_substr($subject, 0, 255, 'UTF-8');
        } else {
            $subject = substr($subject, 0, 255);
        }

        $body = strtr($bodyTemplate, $maps['html']);

        $completedon = trim((string)$userCourse->get('completedon'));
        $logId = $this->claim(
            $courseId,
            $userId,
            $userCourseId,
            $completedon,
            $recipients,
            $subject
        );

        if ($logId <= 0) {
            return array('status' => 'already_claimed');
        }

        try {
            $loaded = $this->modx->getService('mail', 'mail.modPHPMailer');
            if (!$loaded || !$this->modx->mail) {
                throw new Exception('Standard MODX mail service is unavailable.');
            }

            if (method_exists($this->modx->mail, 'reset')) {
                $this->modx->mail->reset();
            }

            $from = trim((string)$this->modx->getOption('emailsender', null, ''));
            $fromName = trim((string)$this->modx->getOption('site_name', null, ''));

            if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('MODX emailsender is empty or invalid.');
            }

            $this->modx->mail->set(modMail::MAIL_FROM, $from);
            $this->modx->mail->set(
                modMail::MAIL_FROM_NAME,
                $fromName !== '' ? $fromName : $from
            );
            $this->modx->mail->set(modMail::MAIL_SUBJECT, $subject);
            $this->modx->mail->set(modMail::MAIL_BODY, $body);

            foreach ($recipients as $email) {
                $this->modx->mail->address('to', $email);
            }

            if (method_exists($this->modx->mail, 'setHTML')) {
                $this->modx->mail->setHTML(true);
            }

            $sent = (bool)$this->modx->mail->send();
            $error = $this->mailError();

            if (method_exists($this->modx->mail, 'reset')) {
                $this->modx->mail->reset();
            }

            if (!$sent) {
                throw new Exception($error !== '' ? $error : 'modPHPMailer::send() returned false.');
            }

            $this->updateLog($logId, 'sent', '');

            $this->modx->log(
                modX::LOG_LEVEL_INFO,
                '[training completion notify] sent; course=' . $courseId
                . '; user=' . $userId
                . '; user_course=' . $userCourseId
            );

            return array(
                'status' => 'sent',
                'notification_id' => $logId,
                'recipients' => $recipients,
            );
        } catch (Throwable $e) {
            if (
                isset($this->modx->mail)
                && is_object($this->modx->mail)
                && method_exists($this->modx->mail, 'reset')
            ) {
                try {
                    $this->modx->mail->reset();
                } catch (Throwable $ignored) {
                }
            }

            $this->updateLog($logId, 'failed', $e->getMessage());

            $this->modx->log(
                modX::LOG_LEVEL_ERROR,
                '[training completion notify] send failed; course=' . $courseId
                . '; user=' . $userId
                . '; user_course=' . $userCourseId
                . '; error=' . $e->getMessage()
            );

            return array(
                'status' => 'failed',
                'notification_id' => $logId,
                'error' => $e->getMessage(),
            );
        }
    }
}
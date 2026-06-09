<?php
require_once dirname(dirname(__FILE__)) . '/_helper.php';

class TrainingPracticeMgrMessagesGetListProcessor extends modProcessor
{
    public function process()
    {
        $attemptId = trainingPracticeMgrGetInt($this, 'attempt_id');
        if ($attemptId <= 0) {
            $attemptId = trainingPracticeMgrGetInt($this, 'attempt');
        }
        if ($attemptId <= 0) {
            $attemptId = trainingPracticeMgrGetInt($this, 'id');
        }
        if ($attemptId <= 0) {
            return $this->outputArray(array(), 0);
        }

        $messagesTable = trainingPracticeMgrTable($this->modx, 'training_practice_messages');
        $filesTable = trainingPracticeMgrTable($this->modx, 'training_practice_files');
        $usersTable = trainingPracticeMgrOptionalTable($this->modx, 'users');
        $attributesTable = trainingPracticeMgrOptionalTable($this->modx, 'user_attributes');

        $joins = array();
        $selectExtra = array(
            "'' AS `username`",
            "'' AS `email`",
            "'' AS `fullname`",
        );

        if ($usersTable !== '') {
            $joins[] = "LEFT JOIN {$usersTable} u ON u.`id` = m.`author_id`";
            $selectExtra[0] = "u.`username` AS `username`";
            // В MODX Revolution email хранится в modUserProfile (user_attributes), не в modUser.
            $selectExtra[1] = "'' AS `email`";
        }

        if ($usersTable !== '' && $attributesTable !== '') {
            $joins[] = "LEFT JOIN {$attributesTable} ua ON ua.`internalKey` = u.`id`";
            $selectExtra[1] = "ua.`email` AS `email`";
            $selectExtra[2] = "ua.`fullname` AS `fullname`";
        }

        $sql = "
            SELECT
                m.*,
                " . implode(",\n                ", $selectExtra) . "
            FROM {$messagesTable} m
            " . implode("\n", $joins) . "
            WHERE m.`attempt_id` = :attempt_id
            ORDER BY m.`createdon` ASC, m.`id` ASC
        ";
        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute(array(':attempt_id' => $attemptId))) {
            $err = $stmt ? $stmt->errorInfo() : array('', '', 'prepare failed');
            return $this->failure('Не удалось загрузить переписку: ' . (isset($err[2]) ? $err[2] : 'SQL error'));
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $messageIds = array();
        foreach ($rows as $row) {
            $messageIds[] = (int)$row['id'];
        }

        $filesByMessage = array();
        if ($messageIds) {
            $ids = implode(',', array_map('intval', $messageIds));
            $stmt = $this->modx->query("SELECT * FROM {$filesTable} WHERE `message_id` IN ({$ids}) ORDER BY `id` ASC");
            if ($stmt) {
                while ($file = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $mid = (int)$file['message_id'];
                    if (!isset($filesByMessage[$mid])) {
                        $filesByMessage[$mid] = array();
                    }
                    $filesByMessage[$mid][] = $file;
                }
            }
        }

        $results = array();
        foreach ($rows as $row) {
            $fullname = trim((string)$row['fullname']);
            if ($fullname === '') {
                $fullname = trim((string)$row['username']);
            }
            if ($fullname === '') {
                $fullname = ($row['author_type'] === 'user' || $row['sender_role'] === 'employee') ? 'Пользователь #' . (int)$row['author_id'] : 'Ментор';
            }

            $filesHtml = '';
            $fileNames = array();
            $mid = (int)$row['id'];
            if (!empty($filesByMessage[$mid])) {
                foreach ($filesByMessage[$mid] as $file) {
                    $name = trim((string)$file['original_name']);
                    if ($name === '') {
                        $name = trim((string)$file['name']);
                    }
                    $url = trim((string)$file['url']);
                    $fileNames[] = $name;
                    if ($url !== '') {
                        $filesHtml .= '<a href="' . trainingPracticeMgrEsc($url) . '" target="_blank" rel="noopener">' . trainingPracticeMgrEsc($name) . '</a><br>';
                    }
                }
            }

            $row['author_display'] = $fullname;
            $row['createdon_formatted'] = trainingPracticeMgrDate($row['createdon']);
            $row['message_html'] = nl2br(trainingPracticeMgrEsc($row['message']));
            $row['files_html'] = $filesHtml;
            $row['files_text'] = implode(', ', $fileNames);
            $results[] = $row;
        }

        return $this->outputArray($results, count($results));
    }
}

return 'TrainingPracticeMgrMessagesGetListProcessor';

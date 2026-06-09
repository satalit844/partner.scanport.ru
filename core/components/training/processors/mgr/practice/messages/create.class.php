<?php
require_once dirname(dirname(__FILE__)) . '/_helper.php';

class TrainingPracticeMgrMessagesCreateProcessor extends modProcessor
{
    public function process()
    {
        $attemptId = trainingPracticeMgrGetInt($this, 'attempt_id');
        $message = trim((string)$this->getProperty('message', ''));
        $markDone = trainingPracticeMgrGetInt($this, 'mark_done', 0) === 1;
        $sendRevision = trainingPracticeMgrGetInt($this, 'send_revision', 0) === 1;

        if ($attemptId <= 0) {
            return $this->failure('Выберите попытку');
        }
        if ($message === '') {
            return $this->failure('Напишите сообщение');
        }

        try {
            $attemptsTable = trainingPracticeMgrTable($this->modx, 'training_practice_attempts');
            $messagesTable = trainingPracticeMgrTable($this->modx, 'training_practice_messages');

            $stmt = $this->modx->prepare("SELECT * FROM {$attemptsTable} WHERE `id` = :id LIMIT 1");
            if (!$stmt || !$stmt->execute(array(':id' => $attemptId))) {
                return $this->failure('Не удалось прочитать попытку');
            }

            $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$attempt) {
                return $this->failure('Попытка не найдена');
            }

            $mentorId = (int)$this->modx->user->get('id');

            $stmt = $this->modx->prepare("
                INSERT INTO {$messagesTable}
                (`attempt_id`, `practice_id`, `author_id`, `author_type`, `user_id`, `sender_role`, `message`, `is_system`, `createdon`)
                VALUES
                (:attempt_id, :practice_id, :author_id, 'mentor', :user_id, 'mentor', :message, 0, NOW())
            ");
            if (!$stmt || !$stmt->execute(array(
                ':attempt_id' => $attemptId,
                ':practice_id' => (int)$attempt['practice_id'],
                ':author_id' => $mentorId,
                ':user_id' => (int)$attempt['user_id'],
                ':message' => $message,
            ))) {
                $error = $stmt ? $stmt->errorInfo() : array('', '', 'prepare failed');
                $this->modx->log(modX::LOG_LEVEL_ERROR, '[training] practice manager message create failed: ' . print_r($error, true));
                return $this->failure('Не удалось отправить ответ');
            }

            $newStatus = '';
            if ($markDone) {
                $newStatus = 'approved';
            } elseif ($sendRevision) {
                $newStatus = 'revision';
            } elseif ((string)$attempt['status'] === 'submitted') {
                $newStatus = 'in_review';
            }

            if ($newStatus !== '') {
                $stmt = $this->modx->prepare("
                    UPDATE {$attemptsTable}
                    SET
                        `status` = :status,
                        `review_comment` = :review_comment,
                        `reviewedon` = NOW(),
                        `reviewedby` = :reviewedby,
                        `reviewer_user_id` = :reviewedby,
                        `updatedon` = NOW()
                    WHERE `id` = :id
                ");
                if ($stmt) {
                    $stmt->execute(array(
                        ':status' => $newStatus,
                        ':review_comment' => $message,
                        ':reviewedby' => $mentorId,
                        ':id' => $attemptId,
                    ));
                    $attempt['status'] = $newStatus;
                }
            }

            $this->recalculateProgress($attempt);

            $status = $newStatus !== '' ? $newStatus : (string)$attempt['status'];
            return $this->success('Ответ отправлен', array(
                'attempt_id' => $attemptId,
                'status' => $status,
                'status_text' => trainingPracticeMgrStatusText($status),
            ));
        } catch (Exception $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[training] practice manager message create exception: ' . $e->getMessage());
            return $this->failure('Ошибка отправки ответа: ' . $e->getMessage());
        }
    }

    protected function recalculateProgress(array $attempt)
    {
        $corePath = $this->modx->getOption('training.core_path', null, $this->modx->getOption('core_path') . 'components/training/');
        $trainingClass = $corePath . 'model/training/training.class.php';
        $progressClass = $corePath . 'model/training/services/trainingprogress.class.php';

        if (!is_file($trainingClass) || !is_file($progressClass)) {
            return;
        }

        try {
            require_once $trainingClass;
            require_once $progressClass;
            $training = new Training($this->modx);
            $service = new TrainingProgressService($this->modx, $training);
            $service->recalculateModuleProgressFromLessons((int)$attempt['course_id'], (int)$attempt['module_id'], (int)$attempt['user_id']);
            $service->recalculateUserCourse((int)$attempt['course_id'], (int)$attempt['user_id']);
        } catch (Exception $e) {
            $this->modx->log(modX::LOG_LEVEL_WARN, '[training] practice manager message progress recalc failed: ' . $e->getMessage());
        }
    }
}

return 'TrainingPracticeMgrMessagesCreateProcessor';

<?php
require_once dirname(dirname(__FILE__)) . '/_helper.php';

class TrainingPracticeMgrAttemptsStatusProcessor extends modProcessor
{
    public function process()
    {
        $id = trainingPracticeMgrGetInt($this, 'id');
        $status = trainingPracticeMgrGetString($this, 'status');
        $comment = trim((string)$this->getProperty('comment', ''));

        if ($id <= 0) {
            return $this->failure('Не передан ID попытки');
        }

        $allowed = array('submitted', 'in_review', 'revision', 'approved', 'rejected');
        if (!in_array($status, $allowed, true)) {
            return $this->failure('Недопустимый статус');
        }

        $attemptsTable = trainingPracticeMgrTable($this->modx, 'training_practice_attempts');
        $messagesTable = trainingPracticeMgrTable($this->modx, 'training_practice_messages');

        $stmt = $this->modx->prepare("SELECT * FROM {$attemptsTable} WHERE `id` = :id LIMIT 1");
        $stmt->execute(array(':id' => $id));
        $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$attempt) {
            return $this->failure('Попытка не найдена');
        }

        $mentorId = (int)$this->modx->user->get('id');

        $stmt = $this->modx->prepare("\n            UPDATE {$attemptsTable}\n            SET\n                `status` = :status,\n                `review_comment` = :review_comment,\n                `reviewedon` = NOW(),\n                `reviewedby` = :reviewedby,\n                `reviewer_user_id` = :reviewedby,\n                `updatedon` = NOW()\n            WHERE `id` = :id\n        ");
        $stmt->execute(array(
            ':id' => $id,
            ':status' => $status,
            ':review_comment' => $comment,
            ':reviewedby' => $mentorId,
        ));

        $attempt['status'] = $status;
        $systemMessage = 'Статус изменен: ' . trainingPracticeMgrStatusText($status);
        if ($comment !== '') {
            $systemMessage .= "\n\n" . $comment;
        }

        $stmt = $this->modx->prepare("\n            INSERT INTO {$messagesTable}\n            (`attempt_id`, `practice_id`, `author_id`, `author_type`, `user_id`, `sender_role`, `message`, `is_system`, `createdon`)\n            VALUES\n            (:attempt_id, :practice_id, :author_id, 'mentor', :user_id, 'mentor', :message, 1, NOW())\n        ");
        $stmt->execute(array(
            ':attempt_id' => $id,
            ':practice_id' => (int)$attempt['practice_id'],
            ':author_id' => $mentorId,
            ':user_id' => (int)$attempt['user_id'],
            ':message' => $systemMessage,
        ));

        $this->recalculateProgress($attempt);

        return $this->success('Статус изменен');
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
            $this->modx->log(modX::LOG_LEVEL_WARN, '[training] practice manager status progress recalc failed: ' . $e->getMessage());
        }
    }
}

return 'TrainingPracticeMgrAttemptsStatusProcessor';

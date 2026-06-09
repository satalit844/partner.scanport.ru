<?php
require_once dirname(dirname(__FILE__)) . '/_helper.php';

class TrainingPracticeMgrPracticeRemoveProcessor extends modProcessor
{
    public function process()
    {
        $id = trainingPracticeMgrGetInt($this, 'id');
        if ($id <= 0) {
            return $this->failure('Не передан ID задания');
        }

        $practicesTable = trainingPracticeMgrTable($this->modx, 'training_practices');
        $attemptsTable = trainingPracticeMgrTable($this->modx, 'training_practice_attempts');

        $stmt = $this->modx->prepare("SELECT COUNT(*) FROM {$attemptsTable} WHERE `practice_id` = :id");
        $stmt->execute(array(':id' => $id));
        $attempts = (int)$stmt->fetchColumn();

        if ($attempts > 0) {
            $stmt = $this->modx->prepare("UPDATE {$practicesTable} SET `active` = 0, `editedon` = NOW() WHERE `id` = :id");
            $stmt->execute(array(':id' => $id));
            return $this->success('У задания уже есть попытки, поэтому оно отключено, а не удалено');
        }

        $stmt = $this->modx->prepare("DELETE FROM {$practicesTable} WHERE `id` = :id");
        $stmt->execute(array(':id' => $id));

        return $this->success('Практическое задание удалено');
    }
}

return 'TrainingPracticeMgrPracticeRemoveProcessor';

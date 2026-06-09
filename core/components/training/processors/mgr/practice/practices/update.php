<?php
require_once dirname(dirname(__FILE__)) . '/_helper.php';

class TrainingPracticeMgrPracticeUpdateProcessor extends modProcessor
{
    public function process()
    {
        $id = trainingPracticeMgrGetInt($this, 'id');
        if ($id <= 0) {
            return $this->failure('Не передан ID задания');
        }

        $title = trainingPracticeMgrGetString($this, 'title');
        if ($title === '') {
            return $this->failure('Укажите название практического задания');
        }

        $courseId = trainingPracticeMgrGetInt($this, 'course_id');
        $moduleId = trainingPracticeMgrGetInt($this, 'module_id');

        if ($courseId <= 0) {
            return $this->failure('Укажите курс');
        }
        if ($moduleId <= 0) {
            return $this->failure('Укажите модуль');
        }

        $table = trainingPracticeMgrTable($this->modx, 'training_practices');
        $deadlineAt = trainingPracticeMgrNormalizeDate($this->getProperty('deadline_at', ''));
        $rank = trainingPracticeMgrGetInt($this, 'rank');
        $active = trainingPracticeMgrGetInt($this, 'active', 1);

        $stmt = $this->modx->prepare("\n            UPDATE {$table}\n            SET\n                `course_id` = :course_id,\n                `module_id` = :module_id,\n                `title` = :title,\n                `description` = :description,\n                `template_file` = :template_file,\n                `template_file_name` = :template_file_name,\n                `image` = :image,\n                `deadline_at` = :deadline_at,\n                `deadline_days` = :deadline_days,\n                `allowed_extensions` = :allowed_extensions,\n                `max_file_size` = :max_file_size,\n                `active` = :active,\n                `rank` = :rank,\n                `editedon` = NOW()\n            WHERE `id` = :id\n        ");

        $stmt->execute(array(
            ':id' => $id,
            ':course_id' => $courseId,
            ':module_id' => $moduleId,
            ':title' => $title,
            ':description' => (string)$this->getProperty('description', ''),
            ':template_file' => trainingPracticeMgrGetString($this, 'template_file'),
            ':template_file_name' => trainingPracticeMgrGetString($this, 'template_file_name'),
            ':image' => trainingPracticeMgrGetString($this, 'image'),
            ':deadline_at' => $deadlineAt,
            ':deadline_days' => trainingPracticeMgrGetInt($this, 'deadline_days'),
            ':allowed_extensions' => trainingPracticeMgrGetString($this, 'allowed_extensions', 'pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip'),
            ':max_file_size' => trainingPracticeMgrGetInt($this, 'max_file_size', 52428800),
            ':active' => $active,
            ':rank' => $rank,
        ));

        if ($active === 1) {
            trainingPracticeMgrEnsureModuleLink($this->modx, $courseId, $moduleId, $id, $rank, 1);
        }

        return $this->success('Практическое задание сохранено');
    }
}

return 'TrainingPracticeMgrPracticeUpdateProcessor';

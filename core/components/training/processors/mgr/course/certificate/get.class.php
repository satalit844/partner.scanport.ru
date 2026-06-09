<?php

$corePath = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/';
require_once $corePath . 'model/training/training.class.php';
require_once $corePath . 'model/training/services/trainingcertificate.class.php';

class TrainingCourseCertificateGetProcessor extends modProcessor
{
    public function process()
    {
        $courseId = (int)$this->getProperty('course_id', $this->getProperty('id', 0));
        if ($courseId <= 0) {
            return $this->failure('Не указан курс');
        }

        $service = new TrainingCertificateService($this->modx, new Training($this->modx));
        $row = $service->getTemplate($courseId);
        if (!$row) {
            $row = array(
                'id' => 0,
                'course_id' => $courseId,
                'template_pdf' => '',
                'template_preview' => '',
                'output_dir' => '/assets/training/certificates/course_' . $courseId . '/',
                'page_no' => 1,
                'fullname_x' => '96.00',
                'fullname_y' => '690.00',
                'fullname_max_width' => '560.00',
                'fullname_font_size' => '28.00',
                'fullname_color' => '#7B4F92',
                'fullname_align' => 'left',
                'course_title_x' => '96.00',
                'course_title_y' => '340.00',
                'course_title_max_width' => '760.00',
                'course_title_font_size' => '24.00',
                'course_title_color' => '#FFFFFF',
                'course_title_align' => 'left',
                'completed_date_x' => '150.00',
                'completed_date_y' => '900.00',
                'completed_date_max_width' => '170.00',
                'completed_date_font_size' => '20.00',
                'completed_date_color' => '#7B4F92',
                'completed_date_align' => 'left',
                'date_format' => 'd.m.Y',
                'is_active' => 1,
            );
        }

        $row['preview_link'] = !empty($row['template_preview']) ? $row['template_preview'] : '—';
        $row['template_pdf_link'] = !empty($row['template_pdf']) ? $row['template_pdf'] : '—';
        $row['is_active'] = !empty($row['is_active']) ? 1 : 0;
        return $this->success('', $row);
    }
}
return 'TrainingCourseCertificateGetProcessor';

<?php
$allFormFields = $hook->getValues();
$emails = $hook->formit->config['emailTo'];
$base = MODX_BASE_PATH;
$core = MODX_CORE_PATH;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require $core .'vendor/autoload.php';

$filePath = $base . $allFormFields['questions_in_file'];

$spreadsheet = IOFactory::load($filePath);

if ($spreadsheet->getSheetCount() < 2) {
    $sheet = new Worksheet($spreadsheet, 'Лист 2');
    $spreadsheet->addSheet($sheet, 1);
} else {
    $sheet = $spreadsheet->getSheet(1);
}
$row = 1;
foreach ($allFormFields as $question => $response) {
    $sheet->setCellValue("A$row", $question);
    if (!is_array($response)) {
        $sheet->setCellValue("B$row", $response);
    }
    $row++;
}

$writer = new Xlsx($spreadsheet);
$writer->save($filePath);

$modx->getService('mail', 'mail.modPHPMailer');
$modx->mail->set(modMail::MAIL_FROM, $modx->getOption('emailsender'));
$modx->mail->set(modMail::MAIL_FROM_NAME, $modx->getOption('site_name'));

$emails = explode(',', $emails);

foreach ($emails as $email) {
    $modx->mail->address('to', $email);
}

$modx->mail->set(modMail::MAIL_SUBJECT, $allFormFields['page_name']);
$modx->mail->set(modMail::MAIL_BODY, $allFormFields['title_quiz']);

$modx->mail->setHTML(true);

if (file_exists($filePath)) {
    $modx->mail->attach($filePath);
}

if (!$modx->mail->send()) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: '.$modx->mail->mailer->ErrorInfo);
    return false;
}

$modx->mail->reset();

return true;
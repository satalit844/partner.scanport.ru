<?php
$output = true;
$allowedExt = array('svg','jpg','png','jpeg');
$maxFileSize = 10 * 1024 * 1024;

// $modx->log(1, '[formit2checkfiles $_FILES] '. print_r($_FILES[$key],1));

if(isset($_FILES[$key]["error"])) {
  foreach ($_FILES[$key]["error"] as $fkey => $error) {
    if ($error == UPLOAD_ERR_OK) {
      $fileName = basename($_FILES[$key]['name'][$fkey]);
      $fileExt = mb_strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
      $fileSize = filesize($_FILES[$key]['tmp_name'][$fkey]);
      if(!in_array($fileExt, $allowedExt)) {
        $errorMsg = 'Файл ' . $fileName . ' имеет не разрешённый тип.';
        $validator->addError($key, $errorMsg);
        $output = false;
        break;
      }
      if($fileSize > $maxFileSize) {
        $errorMsg = 'Файл '. $fileName .' имеет не разрешённый размер.';
        $validator->addError($key,$errorMsg);
        $output = false; 
        break;
      }
    } elseif ($error == UPLOAD_ERR_NO_FILE) {
        $output = true;
        break;
    } else {
      $errorMsg = 'Произошла ошибка при загрузке файла ' . $fileName .' на сервер.';
      $validator->addError($key,$errorMsg);
      $output = false;
      break;
    }
  }
}

// $modx->log(1, '[formit2checkfiles $output] '. print_r($output,1));

return $output;
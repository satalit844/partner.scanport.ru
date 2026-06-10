<?php
$file_path = MODX_BASE_PATH.$input;
if(file_exists($file_path)){
    return $input."?v=".md5_file($file_path);
}else{
    $external_file = file_get_contents($input);
    if($external_file){
        if(strpos($input, "?") !== false){
            return $input."&fileCashFix=".md5($external_file);
        }else{
            return $input."?v=".md5($external_file);
        }
    }else{
        return $input;
    }
}
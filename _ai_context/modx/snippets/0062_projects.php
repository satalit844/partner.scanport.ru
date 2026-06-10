<?php
$cache = 60 * 60 * 24;
$time = time();

if (empty($inn)) return;
if ($time > $_SESSION['user_progect']['time_cache']) {
    $inn = explode(';', $inn);
    $inn_tmp = [];
    foreach ($inn as $item) {
        $item_tmp = explode(':', $item);
        $inn_tmp[] = $item_tmp[0];
    }
    $inn = implode(',', $inn_tmp);
    $data = array(
        'action' => 'get_projects',
        'token' => md5(date('d.m.Y')),
        'inn' => $inn
    );
    $json_data = json_encode($data);
    
    $url = 'https://scanport.ru/project/requests.php';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data); // Отправляем данные как JSON-строку
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json', // Устанавливаем заголовок Content-Type на application/json
        'Content-Length: ' . strlen($json_data)
    ));
    
    $result = curl_exec($ch);
    
    curl_close($ch);
    
    $result = json_decode($result, true);
    
    $_SESSION['user_progect']['time_cache'] = $time + $cache;
    $_SESSION['user_progect']['res'] = $result;
} else {
    $result = $_SESSION['user_progect']['res'];
    $modx->log(1,'[projects $result] '. print_r($result,1));
}

if ($result === false) {
    $modx->log(1,'Ошибка CURL: ' . curl_error($ch));
} else {
    if ($result['message'] == 'no Resources') {
        $result['sucsess'] = false;
    }
    return $result;
}
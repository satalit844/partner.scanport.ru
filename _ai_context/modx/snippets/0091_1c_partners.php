<?php
/**
 * Парсер партнёров sovmestimo.1c.ru для MODX Console (шаговый, как пример с miniShop2)
 *
 * Логика:
 *  1) При первом запуске:
 *      - обходит страницы /partners/
 *      - собирает список партнёров (partcode, city, name)
 *      - кладёт его в $_SESSION['partners_parser_1c']['list']
 *      - считает total
 *      - создаёт/перезаписывает assets/1c/partners.csv с заголовком
 *
 *  2) При каждом следующем запуске:
 *      - берёт из списка очередные $step партнёров (по offset)
 *      - по каждому тянет JSON /fetch/partners/partners.php?partcode=XXX
 *      - парсит phone/site/address/lat/lng
 *      - дозаписывает в CSV
 *      - выводит прогресс-бар, % и количество
 *
 *  Console сама будет гонять этот код, пока $_SESSION['Console']['completed'] не станет true
 */

$step       = 10; // Сколько партнёров обрабатывать за один запуск
$maxPages   = 200; // Защита от бесконечных страниц
$sessionKey = 'partners_parser_1c';

/**
 * РЕЖИМ СБРОСА:
 *  true  — сбросить прогресс, удалить CSV и собрать всё заново
 *  false — продолжать с того места, где остановились
 */
$resetOnStart = false;

$baseListUrl  = 'https://sovmestimo.1c.ru/partners/';
$detailUrlTpl = 'https://sovmestimo.1c.ru/fetch/partners/partners.php?partcode=%d';

$basePath = rtrim(MODX_BASE_PATH, '/\\') . DIRECTORY_SEPARATOR;
$dirPath  = $basePath . 'assets' . DIRECTORY_SEPARATOR . '1c' . DIRECTORY_SEPARATOR;
$filePath = $dirPath . 'partners.csv';

$modx->setLogLevel(MODX_LOG_LEVEL_ERROR);

echo "== Парсер партнёров 1С (шаговый) ==\n";
echo "Файл CSV: {$filePath}\n\n";

if ($resetOnStart) {
    echo "== Режим сброса включён ==\n";
    // Сбрасываем сессию
    unset($_SESSION[$sessionKey]);

    // Удаляем старый CSV, если есть
    $basePath = rtrim(MODX_BASE_PATH, '/\\') . DIRECTORY_SEPARATOR;
    $dirPath  = $basePath . 'assets' . DIRECTORY_SEPARATOR . '1c' . DIRECTORY_SEPARATOR;
    $filePath = $dirPath . 'partners.csv';

    if (file_exists($filePath)) {
        if (@unlink($filePath)) {
            echo "Удалён старый файл CSV: {$filePath}\n";
        } else {
            echo "Не удалось удалить CSV (проверь права): {$filePath}\n";
        }
    } else {
        echo "CSV ещё не было: {$filePath}\n";
    }

    // На этом запуске только сбрасываем, работу не делаем
    $_SESSION['Console']['completed'] = true;
    return;
}


// --- Подготовка сессии ---
if (empty($_SESSION[$sessionKey]) || !is_array($_SESSION[$sessionKey])) {
    $_SESSION[$sessionKey] = [
        'initialized' => false,
        'list'        => [],
        'total'       => 0,
        'offset'      => 0,
        'pages_built' => 0,
    ];
}
$state =& $_SESSION[$sessionKey];


// ================= ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ =================

if (!function_exists('pp1c_httpGet')) {
    function pp1c_httpGet($url)
    {
        $maxAttempts = 3;

        // --- Пытаемся через cURL несколько раз ---
        if (function_exists('curl_init')) {
            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {

                echo "  [HTTP] GET (try {$attempt}/{$maxAttempts} via cURL): {$url}\n";

                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL            => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT        => 30,
                    CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; PartnerParser/1.0)',
                    // каждый запрос — новое соединение
                    CURLOPT_FORBID_REUSE   => true,
                    CURLOPT_FRESH_CONNECT  => true,
                    // иногда помогает при странных SSL-глюках
                    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                ]);

                $body = curl_exec($ch);

                if ($body === false) {
                    $err  = curl_error($ch);
                    $code = curl_errno($ch);
                    echo "  [HTTP] cURL error ({$code}): {$err}\n";
                    curl_close($ch);

                    if ($attempt < $maxAttempts) {
                        echo "  [HTTP] Ждём и пробуем ещё раз...\n";
                        usleep(300000); // 0.3 сек
                        continue;
                    } else {
                        echo "  [HTTP] Все попытки cURL исчерпаны.\n";
                        $body = null;
                    }
                } else {
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    echo "  [HTTP] HTTP code: {$httpCode}\n";

                    if ($httpCode >= 400) {
                        echo "  [HTTP] HTTP {$httpCode}";
                        if ($attempt < $maxAttempts) {
                            echo ", пробуем ещё раз...\n";
                            usleep(300000);
                            continue;
                        } else {
                            echo ", все попытки исчерпаны.\n";
                            $body = null;
                        }
                    }
                }

                if (!empty($body)) {
                    return $body;
                }
            }
        }

        // --- fallback: file_get_contents ---
        echo "  [HTTP] Переходим на file_get_contents: {$url}\n";

        $context = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'timeout' => 30,
                'header'  => "User-Agent: Mozilla/5.0 (compatible; PartnerParser/1.0)\r\n",
            ],
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            echo "  [HTTP] Ошибка file_get_contents, данных нет.\n";
            return null;
        }

        echo "  [HTTP] Ответ получен через file_get_contents.\n";
        return $body;
    }
}

if (!function_exists('pp1c_parseListPage')) {
    function pp1c_parseListPage($html)
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query("//div[contains(@class, 'partners_item')]");

        $partners = [];

        /** @var DOMElement $node */
        foreach ($nodes as $node) {
            $code = $node->getAttribute('data-partcode');
            if (!$code) {
                continue;
            }

            $cityNode = $xpath->query(".//span[contains(@class, 'partners_list__city')]", $node)->item(0);
            $nameNode = $xpath->query(".//span[contains(@class, 'partners_list__name')]", $node)->item(0);

            $city = $cityNode ? trim($cityNode->textContent) : '';
            $name = $nameNode ? trim($nameNode->textContent) : '';

            $partners[] = [
                'code' => $code,
                'city' => $city,
                'name' => $name,
            ];
        }

        return $partners;
    }
}

if (!function_exists('pp1c_parsePartnerInfoHtml')) {
    function pp1c_parsePartnerInfoHtml($html)
    {
        // Фикс кодировки
        if (function_exists('mb_convert_encoding')) {
            $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        } else {
            $html = '<?xml encoding="utf-8" ?>' . $html;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $result = [
            'legal_name' => '',
            'phone'      => '',
            'site'       => '',
            'address'    => '',
        ];

        $rows = $xpath->query("//div[contains(@class, 'partner_info')]/div[contains(@class, 'partner_info__row')]");
        if (!$rows || $rows->length === 0) {
            return $result;
        }

        /** @var DOMElement $row */
        foreach ($rows as $index => $row) {
            $text = trim($row->textContent);

            if ($index === 0) {
                $span = $xpath->query(".//span[contains(@class, 'partner_info__data')]", $row)->item(0);
                $result['legal_name'] = $span ? trim($span->textContent) : $text;
                continue;
            }

            if (mb_stripos($text, 'Телефон') !== false) {
                $span = $xpath->query(".//span[contains(@class, 'partner_info__data')]", $row)->item(0);
                $result['phone'] = $span ? trim($span->textContent) : trim(str_replace('Телефон:', '', $text));
                continue;
            }

            if (mb_stripos($text, 'Сайт') !== false) {
                $a = $xpath->query(".//a", $row)->item(0);
                if ($a && $a->hasAttribute('href')) {
                    $result['site'] = trim($a->getAttribute('href'));
                } else {
                    $result['site'] = trim(str_replace('Сайт:', '', $text));
                }
                continue;
            }

            if (mb_stripos($text, 'Адрес') !== false) {
                $span = $xpath->query(".//span[contains(@class, 'partner_info__data')]", $row)->item(0);
                $result['address'] = $span ? trim($span->textContent) : trim(str_replace('Адрес:', '', $text));
                continue;
            }
        }

        return $result;
    }
}

if (!function_exists('pp1c_fetchPartnerDetail')) {
    function pp1c_fetchPartnerDetail($partcode, $detailUrlTpl)
    {
        $url = sprintf($detailUrlTpl, $partcode);
        echo "    [DETAIL] partcode={$partcode} → {$url}\n";

        $json = pp1c_httpGet($url);
        if ($json === null) {
            echo "    [DETAIL] JSON не получен\n";
            return [
                'lat'        => '',
                'lng'        => '',
                'legal_name' => '',
                'phone'      => '',
                'site'       => '',
                'address'    => '',
            ];
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            echo "    [DETAIL] Ошибка JSON\n";
            return [
                'lat'        => '',
                'lng'        => '',
                'legal_name' => '',
                'phone'      => '',
                'site'       => '',
                'address'    => '',
            ];
        }

        $lat = isset($data['geometry']['coordinates'][0]) ? trim($data['geometry']['coordinates'][0]) : '';
        $lng = isset($data['geometry']['coordinates'][1]) ? trim($data['geometry']['coordinates'][1]) : '';

        $balloonHtml = $data['properties']['balloonContentBody'] ?? '';
        $info        = $balloonHtml
            ? pp1c_parsePartnerInfoHtml($balloonHtml)
            : [
                'legal_name' => '',
                'phone'      => '',
                'site'       => '',
                'address'    => '',
            ];

        return array_merge([
            'lat' => $lat,
            'lng' => $lng,
        ], $info);
    }
}

// =============== ЭТАП 1: инициализация и сбор списка партнёров ===============

if (!$state['initialized']) {
    echo "Инициализация: сбор списка партнёров...\n";

    // создаём папку и CSV с заголовком
    if (!is_dir($dirPath)) {
        if (!mkdir($dirPath, 0775, true) && !is_dir($dirPath)) {
            echo "ОШИБКА: не удалось создать директорию {$dirPath}\n";
            $_SESSION['Console']['completed'] = true;
            return;
        }
    }

    $fp = fopen($filePath, 'w');
    if ($fp === false) {
        echo "ОШИБКА: не удалось открыть файл для записи: {$filePath}\n";
        $_SESSION['Console']['completed'] = true;
        return;
    }
    
    // BOM для корректного UTF-8 в Excel
    fwrite($fp, "\xEF\xBB\xBF");
    
    // Заголовки как на скрине
    $header = ['город', 'партнёр', 'телефон', 'сайт', 'адрес'];
    fputcsv($fp, $header, ';'); // разделитель — точка с запятой
    
    fclose($fp);
    echo "Создан файл CSV с заголовком (город; партнёр; телефон; сайт; адрес).\n";

    $list       = [];
    $page       = 1;
    $pagesBuilt = 0;

    while ($page <= $maxPages) {
        $url = $page === 1
            ? $baseListUrl
            : $baseListUrl . $page . '/';

        echo "  [INIT] Страница {$page}: {$url}\n";

        $html = pp1c_httpGet($url);
        if ($html === null) {
            echo "  [INIT] Нет HTML, прекращаем обход.\n";
            break;
        }

        $items = pp1c_parseListPage($html);
        $count = count($items);
        echo "  [INIT] Найдено на странице: {$count}\n";

        if ($count === 0) {
            echo "  [INIT] Партнёры закончились.\n";
            break;
        }

        $list = array_merge($list, $items);
        $pagesBuilt++;
        $page++;
    }

    $state['list']        = $list;
    $state['total']       = count($list);
    $state['offset']      = 0;
    $state['pages_built'] = $pagesBuilt;
    $state['initialized'] = true;

    echo "Инициализация завершена.\n";
    echo "Страниц просмотрено: {$pagesBuilt}\n";
    echo "Всего партнёров найдено: {$state['total']}\n\n";

    $_SESSION['Console']['completed'] = false;

    // Прогресс (0%)
    for ($i = 0; $i <= 100; $i++) {
        echo ($i === 0) ? '|' : '_';
    }
    echo "|\n";
    echo "0% (0 из {$state['total']})\n\n";

    return;
}

// =============== ЭТАП 2: обработка очередной порции партнёров ===============

$total  = (int)$state['total'];
$offset = (int)$state['offset'];

echo "Обработка партнёров...\n";
echo "Всего партнёров: {$total}\n";
echo "Текущий offset : {$offset}\n\n";

if ($total === 0) {
    echo "Список пуст, завершаем.\n";
    $_SESSION['Console']['completed'] = true;
    unset($_SESSION[$sessionKey]);
    return;
}

if ($offset >= $total) {
    echo "Все партнёры уже обработаны.\n";
    $_SESSION['Console']['completed'] = true;
    unset($_SESSION[$sessionKey]);

    for ($i = 0; $i <= 100; $i++) {
        echo '=';
    }
    echo "\n100% ({$total})\n\n";
    return;
}

// открываем CSV в append-режиме
$fp = fopen($filePath, 'a');
if ($fp === false) {
    echo "ОШИБКА: не удалось открыть CSV для дозаписи: {$filePath}\n";
    $_SESSION['Console']['completed'] = true;
    return;
}

$end = min($offset + $step, $total);

for ($i = $offset; $i < $end; $i++) {
    $item = $state['list'][$i];

    $code = (int)$item['code'];
    $city = $item['city'];
    $name = $item['name'];

    echo "---- #".($i + 1)." / {$total} ----\n";
    echo "  partcode: {$code}\n";
    echo "  город   : {$city}\n";
    echo "  имя     : {$name}\n";

    $detail = pp1c_fetchPartnerDetail($code, $detailUrlTpl);

    // Партнёр — берём из детальной инфы, если есть,
    // иначе короткое имя из списка
    $partnerName = $detail['legal_name'] ?: $name;
    
    // Формат как в Excel:
    // город; партнёр; телефон; сайт; адрес
    $row = [
        $city,
        $partnerName,
        $detail['phone']   ?? '',
        $detail['site']    ?? '',
        $detail['address'] ?? '',
    ];
    
    fputcsv($fp, $row, ';');
    
    echo "  [CSV] Записано. партнёр=\"{$partnerName}\" телефон=\"{$row[2]}\"\n\n";

    // если переживаешь за throttling — можешь тут увеличить паузу
    usleep(100000); // 0.1 сек
}

fclose($fp);

// Обновляем offset
$state['offset'] = $end;

// Прогресс
$sucsess = $total > 0 ? round($state['offset'] / $total, 2) * 100 : 100;
if ($sucsess > 100) {
    $sucsess = 100;
}

if ($state['offset'] >= $total) {
    $_SESSION['Console']['completed'] = true;
} else {
    $_SESSION['Console']['completed'] = false;
}

// Рисуем прогресс-бар
for ($i = 0; $i <= 100; $i++) {
    if ($i <= $sucsess) {
        echo '=';
    } else {
        echo '_';
    }
}
echo "\n";

$current = $state['offset'];
echo "{$sucsess}% ({$current} из {$total})\n\n";
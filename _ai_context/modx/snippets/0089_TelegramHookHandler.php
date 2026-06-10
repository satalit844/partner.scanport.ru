<?php
/** @var \TelegramManager\Hook $Hook */
/** @var TelegramBot $Bot */
/** @var InlineKeyboard $InlineKeyboard */

/*
 * $Hook->command(); - команда
 * $Hook->query(); - текст после комманды
 * $Hook->getProperties(); - весь post запрос от telegram
 * $Hook->user(); - объект пользователя telegram отправившего запрос
 *      - $Hook->user()->typing(); // Имитирует "Печатает...."
 * */

// Отправляет сообщение пользователю
// $Hook->user()->typing();
// $Hook->user()->message('Привет, я прислал сообщение из сниппета');
// $modx->log(1, '[TelegramHookHandler hook properties] ' . print_r($Hook->command(), true));


// $Hook->user()->typing();
// $Hook->user()->message('Тест, смотрим все свойства Hook');

// // Весь update от Telegram (массив)
$modx->log(modX::LOG_LEVEL_ERROR, '[TelegramHookHandler $Hook->getProperties()] ' . print_r($Hook->getProperties(), true));

// // Дополнительно можешь посмотреть какие методы есть у объекта $Hook
$modx->log(modX::LOG_LEVEL_ERROR, '[TelegramHookHandler методы Hook] ' . print_r(get_class_methods($Hook), true));

// // То же для объекта пользователя
$modx->log(modX::LOG_LEVEL_ERROR, '[TelegramHookHandler методы User] ' . print_r(get_class_methods($Hook->user()), true));
// require_once MODX_CORE_PATH . 'components/telegram/vendor/autoload.php'; // лишнее — автолоад уже есть

$hook = $Hook;

// === /start ===
if ($hook->command() === 'start') {

    // inline-клавиатура
    $kb = new \Longman\TelegramBot\Entities\InlineKeyboard(
        [
            ['text' => '➡ Далее', 'callback_data' => 'next:1'],
        ]
    );

    // "печатает..." + сообщение с кнопкой через ваш сахар
    $Hook->user()->typing();
    $Hook->user()->message('Привет! Нажми кнопку ниже 👇', [
        'reply_markup' => $kb, // важно: объект, без json_encode
        // 'parse_mode' => 'markdown', // по желанию
    ]);
    return;
}

// === обработка нажатий ===
$update = $hook->getProperties();
$hasCallback = method_exists($hook, 'isCallback') ? $hook->isCallback() : !empty($update['callback_query']);

if ($hasCallback) {
    // Унифицированный доступ к данным callback (через методы, если есть)
    $cbId       = method_exists($hook, 'callbackId')        ? $hook->callbackId()        : ($update['callback_query']['id'] ?? null);
    $cbData     = method_exists($hook, 'callbackData')      ? $hook->callbackData()      : ($update['callback_query']['data'] ?? '');
    $chatId     = method_exists($hook, 'chatId')            ? $hook->chatId()            : ($update['callback_query']['message']['chat']['id'] ?? null);
    $messageId  = method_exists($hook, 'callbackMessageId') ? $hook->callbackMessageId() : ($update['callback_query']['message']['message_id'] ?? null);

    // Снять "часики"
    if ($cbId) {
        if (method_exists($Hook->user(), 'answerCallback')) {
            $Hook->user()->answerCallback($cbId);
        } else {
            \Longman\TelegramBot\Request::answerCallbackQuery([
                'callback_query_id' => $cbId,
            ]);
        }
    }

    if (strpos((string)$cbData, 'next:') === 0 && $chatId && $messageId) {
        // заменить текст у того же сообщения
        if (method_exists($Hook->user(), 'editMessageText')) {
            $Hook->user()->editMessageText((int)$messageId, "Ок, идём дальше ✅");
        } else {
            \Longman\TelegramBot\Request::editMessageText([
                'chat_id'    => $chatId,
                'message_id' => $messageId,
                'text'       => "Ок, идём дальше ✅",
            ]);
        }
    }
    return;
}

// фолбэк
$Hook->user()->message('Напиши /start');
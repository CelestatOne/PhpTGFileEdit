<?php
$bot_token = 'токин';
$fileph = 'templates/CarService/modules/contacts/phone.tpl';
$filenm = 'templates/CarService/modules/contacts/text.tpl';
$fileins = 'templates/CarService/modules/contacts/insta.tpl';
$update = json_decode(file_get_contents('php://input'), true);
$inline_keyboard = [
    [
        ['text' => '📝Изменить имя📝', 'callback_data' => 'change_name'],
        ['text' => '☎️Изменить номер☎️', 'callback_data' => 'change_number'],
    ],
    [
        ['text' => '🚧Ссылку insta🚧', 'callback_data' => 'change_inst'],
        ['text' => '👣Включить/Выключить Сайт👣', 'callback_data' => 'stat_s'],
    ]
];
            $keyboard = [
                [
                    ['text' => '🔈Выключить сайт🔈', 'callback_data' => '1'],
                    ['text' => '🔊Включить сайт🔊', 'callback_data' => '0']
                ]
            ];

function checkAccess($chat_id) {
    $filename = "users/{$chat_id}.txt";
    if (!file_exists($filename)) {
        return false;
    }
    return true;
}

if (isset($update['message'])) {
    $message = $update['message'];
    if (isset($message['text'])) {
        if ($message['text'] == '/start') {
            $chat_id = $message['chat']['id'];
            if (!checkAccess($chat_id)) {
                $response = 'Доступ закрыт';
                send_message($chat_id, $response);
                exit;
            }
            $response = 'Привет, я бот для изменения имени и номера. Пожалуйста, нажми на одну из кнопок ниже:';
            send_message($chat_id, $response, $inline_keyboard);
        }
    }
}

else if(isset($update['callback_query'])) {

   $callback_query = $update['callback_query'];
   $chat_id = $callback_query['message']['chat']['id'];
   $data = $callback_query['data'];

   if($data == 'change_name') {
        	$name = get_name();
            $response = '⬇️Сейчас имя на сайте :⬇️
' . $name;
            send_message($chat_id, $response);
      $response = 'Какое новое название сайта ?';
      $force_reply = true;
      send_message($chat_id, $response, [], $force_reply);
   }
   else if($data == 'change_number') {
   	$num = get_num();
   	$response = '⬇️Сейчас номер на сайте :⬇️
' . $num;
   	send_message($chat_id, $response);
      $response = 'Какой твой номер телефона?';
      $force_reply = true;
      send_message($chat_id, $response, [], $force_reply);
   }
   else if($data == 'change_inst') {
   	$nums = get_name_inst();
   	$response = '⬇️Сейчас ссылка инсты на сайте :⬇️
' . $nums;
   	send_message($chat_id, $response);
      $response = 'Новая ссылка на группу инстаграм';
 
      $force_reply = true;
      send_message($chat_id, $response, [], $force_reply);
   }
   else if($data == 'stat_s') {
   	$numx = site_stat();
   	$response = '⬇️Сейчас статус сайта :⬇️
' . $numx;
   	send_message($chat_id, $response,$keyboard);
   }
       // Выполняем нужное действие в зависимости от значения кнопки
    else if ($data == '1') {
        // Выключено
        site_offline(1);
    } else if ($data == '0') {
        // Включено
        site_offline(0);
    }

    // Отправляем ответ на инлайн кнопку
    answerCallbackQuery($update['callback_query']['id'], 'Сайт : ' . ($data == '0' ? 'Включен' : 'Выключен'));

}
function send_message($chat_id, $text, $reply_markup = [], $force_reply = false) {
	global $bot_token;
   $url = 'https://api.telegram.org/bot' . $bot_token . '/sendMessage';
   $data = [
      'chat_id' => $chat_id,
      'text' => $text
   ];
   if(!empty($reply_markup)) {
      $data['reply_markup'] = json_encode(['inline_keyboard' => $reply_markup]);
   }
   if($force_reply) {
      $data['reply_markup'] = json_encode(['force_reply' => true]);
   }
   file_get_contents($url . '?' . http_build_query($data));
}

// Функция для ответа на инлайн кнопку
function answerCallbackQuery($callback_query_id, $text) {
  global $bot_token;
    $data = [
        'callback_query_id' => $callback_query_id,
        'text' => $text
    ];

    file_get_contents('https://api.telegram.org/bot' . $bot_token . '/answerCallbackQuery?' . http_build_query($data));
    
    // Отправляем также обычное сообщение
    send_message($GLOBALS['chat_id'], $text);
}

if(isset($update['message']['reply_to_message'])) {

   $reply_to_message = $update['message']['reply_to_message'];
   $chat_id = $reply_to_message['chat']['id'];
   $text = $update['message']['text'];

   if($reply_to_message['text'] == 'Какое новое название сайта ?') {
      $response = 'Спасибо, я запомнил. Теперь имя сайта
'.$text;
      file_put_contents($filenm, $text);
      short_title($text);
      }
   else if($reply_to_message['text'] == 'Какой твой номер телефона?') {
      $response = 'Спасибо, я запомнил. Изменен на
'.$text;
      file_put_contents($fileph, $text);
   }
   else if($reply_to_message['text'] == 'Новая ссылка на группу инстаграм') {
      $response = 'Спасибо, я запомнил. Изменен на
'.$text;
      file_put_contents($fileins, $text);
   }
 //  send_message(-1001918971570, $response);
    send_message($chat_id, $response, $inline_keyboard);
}
function get_num(){
 global $fileph;
 $reqest = file_get_contents($fileph);
return $reqest;
}
function get_name() {
    global $filenm;
    $reqest=file_get_contents($filenm);
    return $reqest;
}
function get_name_inst() {
    global $fileins;
    $reqest=file_get_contents($fileins);
    return $reqest;
}
function site_stat() {
    // Задайте имя файла, в котором хранится значение site_offline
    $filename = 'engine/data/config.php';

    // Считайте содержимое файла в строку
    $fileContent = file_get_contents($filename);

    // Используйте регулярное выражение для извлечения значения site_offline
    preg_match("/'site_offline'\s*=>\s*'(\d)'/", $fileContent, $matches);

    // Получите значение site_offline из регулярного выражения
    $siteOfflineValue = isset($matches[1]) ? intval($matches[1]) : 0;

    // Верните значение site_offline
    return 'Состояние сайта: ' . ($siteOfflineValue === 1 ? 'Выключен' : 'Включен');
}


function site_offline($new_num) {
    // Задайте имя файла, в котором нужно произвести поиск и замену
    $filename = 'engine/data/config.php';

    // Считайте содержимое файла в строку
    $fileContent = file_get_contents($filename);

    // Создайте регулярное выражение для поиска переменной 'short_title' и её значения
    $pattern = "/'site_offline'\s*=>\s*'[^']*'/";

    // Замените значение переменной 'short_title' на новое значение
    $replacement = "'site_offline' => '$new_num'";
    $newContent = preg_replace($pattern, $replacement, $fileContent);

    // Запишите изменённое содержимое обратно в файл
    file_put_contents($filename, $newContent); 
}
function short_title($new_title) {
    // Задайте имя файла, в котором нужно произвести поиск и замену
    $filename = 'engine/data/config.php';

    // Считайте содержимое файла в строку
    $fileContent = file_get_contents($filename);

    // Создайте регулярное выражение для поиска переменной 'short_title' и её значения
    $pattern = "/'short_title'\s*=>\s*'[^']*'/";

    // Замените значение переменной 'short_title' на новое значение
    $replacement = "'short_title' => '$new_title'";
    $newContent = preg_replace($pattern, $replacement, $fileContent);

    // Запишите изменённое содержимое обратно в файл
    file_put_contents($filename, $newContent);
    
}

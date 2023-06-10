<?php
$bot_token = 'Бот Токен';
$fileph = 'templates/CarService/modules/contacts/phone.tpl';
$filenm = 'templates/CarService/modules/contacts/text.tpl';
$update = json_decode(file_get_contents('php://input'), true);
$inline_keyboard = [
            [
               ['text' => '📝Изменить имя📝', 'callback_data' => 'change_name'],
               ['text' => '☎️Изменить номер☎️', 'callback_data' => 'change_number'],
            ]
         ];

if(isset($update['message'])) {
    $message = $update['message'];
   if(isset($message['text'])) {

      if($message['text'] == '/start') {
         $chat_id = $message['chat']['id'];
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

if(isset($update['message']['reply_to_message'])) {

   $reply_to_message = $update['message']['reply_to_message'];
   $chat_id = $reply_to_message['chat']['id'];
   $text = $update['message']['text'];

   if($reply_to_message['text'] == 'Какое новое название сайта ?') {
      $response = 'Спасибо, я запомнил. Теперь имя сайта
'.$text;
 
      file_put_contents($filenm, $text);
   }
   else if($reply_to_message['text'] == 'Какой твой номер телефона?') {
      $response = 'Спасибо, я запомнил. Изменен на
'.$text;
      file_put_contents($fileph, $text);
   }
   send_message(-1001918971570, $response);
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
<?php

function processInlineResult(&$chosen_inline_result, REDIS &$redis, PDO &$pdo) {
    $chat_id = &$chosen_inline_result['from']['id'];
    $id_article = &$chosen_inline_result['result_id'];
    $inline_message_id = &$chosen_inline_result['inline_message_id'];
    $language = &getLanguage($chat_id, $redis, $pdo);
    $share =  $redis->get('share');
    $redis->hMSet('share:' . $share, $redis->hGetAll($chat_id . ':temp' . $id_article));
    //$redis->expireAt('share:' . $share, 604800);
    $redis->incr('share');
    //apiRequest('sendMessage', ['chat_id' => 24203883, 'text' => $chat_id . ':temp' . $id_article]);
    //apiRequest('sendMessage', ['chat_id' => 24203883, 'text' => json_encode($redis->hGetAll($chat_id . ':temp' . $id_article))]);
    apiRequest('editMessageReplyMarkup', ['inline_message_id' => $inline_message_id, 'reply_markup' => json_encode(getShareInlineKeyboard($share, $language))]);
    //apiRequest('sendMessage', ['chat_id' => 24203883, 'text' => $id_article . $chat_id . $share . json_encode($redis->hGetAll($chat_id . ':temp:' . $id_article))]);
}

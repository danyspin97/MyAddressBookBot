<?php

function processInlineQuery(&$inline_query, REDIS &$redis, PDO &$pdo) {
    $chat_id = &$inline_query['from']['id'];
    $id = &$inline_query['id'];
    $text = &$inline_query['query'];
    $language = &getLanguage($chat_id, $redis, $pdo);
    $order = &getOrder($chat_id, $redis, $pdo);
    $isregistred = isUserRegistered($chat_id, $pdo);
    if ($isregistred) {
        if (isset($text) && $text !== '') {
            $sth = $pdo->prepare("SELECT \"username\", \"first_name\", \"last_name\", \"desc\", \"id\", \"id_contact\" FROM (SELECT \"username\", \"first_name\", \"last_name\", \"desc\", \"id\", \"id_contact\" FROM \"Contact\" WHERE \"id_owner\" = :chat_id) AS T WHERE \"first_name\" LIKE '$text%'  OR \"first_name\" LIKE '%$text%' OR \"last_name\" LIKE '$text%' OR \"last_name\" LIKE '%$text%' OR  CONCAT_WS(' ', \"first_name\", \"last_name\") LIKE '$text%' OR username LIKE '$text%' OR username LIKE '%$text%' OR username LIKE '@$text%' OR username LIKE '%@$text%' OR CONCAT_WS(' ', \"first_name\", \"last_name\") LIKE '%$text' OR CONCAT_WS(' ', \"last_name\", \"first_name\") LIKE '$text%' OR CONCAT_WS(' ', \"last_name\", \"first_name\") LIKE '%$text' ORDER BY $order LIMIT 50;");
        } else {
            $sth = $pdo->prepare("SELECT \"username\", \"first_name\", \"last_name\", \"desc\", \"id\", \"id_contact\" FROM \"Contact\" WHERE \"id_owner\" = :chat_id ORDER BY $order LIMIT 50");
        }
        $sth->bindParam(':chat_id', $chat_id);
        $sth->execute();
        $results = [];
        $id_article = 1;
        while ($row = $sth->fetch()) {
            $string = $row['first_name'];
            if (isset($row['last_name']) && ($row['last_name'] !== 'NULL')) {
                $string = $string . ' ' . $row['last_name'];
            }
            $message_text = &getASInfoByRowForInline($row, $language);
            $redis->hMSet($chat_id . ':temp' . $id_article, $row);
            //apiRequest('sendMessage', ['chat_id' => 24203883, 'text' => $chat_id . ':temp' . $id_article]);
            //$redis->expireAt($chat_id . ':temp' . $id_article, 300);
            $keyboard = &getSaveFromInlineInlineKeyboard($language);
            array_push($results, [
                'type' => 'article',
                'id' => (string)$id_article,
                'title' => $string,
                'message_text' => $message_text,
                'description' => $row['username'],
                'reply_markup' => $keyboard,
                'parse_mode' => 'HTML',
            ]);
            $id_article++;
        }
        $sth = null;
        $results = json_encode($results);
        apiAnswerInlineQuery($chat_id, $id, $results, $language);
    } else {
        apiRequest('answerInlineQuery', [
            'inline_query_id' => $id,
            'switch_pm_text' => getMessage('Register_InlineQuery', $language),
            'cache_time' => 10
        ]);
    }
}

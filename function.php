<?php

require_once  'languages.php';

// Define utilities and special string to use within the bot
define('SPACEPERVIEW', 3);
define('NEWLINE', '
');
define('ROUNDS_UP', '¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯');
define('ROUNDS_DOWN', '__________________');

// Define status for the bot
define('NOSTATUS', -1);
define('MENU', 0);
define('ADDING_USERNAME_MENU', 1);
define('ADDING_FIRSTNAME_MENU', 2);
define('ADDING_LASTNAME_MENU', 3);
define('ADDING_DESC_MENU', 4);
define('ADDING_USERNAME_AB', 5);
define('ADDING_FIRSTNAME_AB', 6);
define('ADDING_LASTNAME_AB', 7);
define('ADDING_DESC_AB', 8);
define('SHOW_AB', 9);
define('SHOW_CONTACT', 10);
define('SHOW_CONTACT_NOTVALID', 11);
define('EDITUSERNAME', 12);
define('EDITFIRSTNAME', 13);
define('EDITLASTNAME', 14);
define('EDITDESC', 15);
define('ADDING_LASTNAME_CONTACT', 16);
define('ADDING_DESC_CONTACT', 17);
define('DELETE_AS_PROMPT', 18);
define('DELETE_AB_PROMPT', 19);
define('OPTIONS', 20);
define('CHOOSE_ORDER', 21);
define('CHOOSE_LANGUAGE', 22);
define('SAVING_USER_PROMPT', 23);
define('ADDING_SEARCH_QUERY', 24);
define('SHOW_RESULTS', 24);
define('ADDING_DESC_SAVE', 25);


// Order of contacts costant
define('FIRSTNAME', 0);
define('LASTNAME', 1);
define('USERNAME', 2);
define('FIRSTNAME_ORDER', 'first_name, last_name');
define('LASTNAME_ORDER', 'last_name, first_name');
define('USERNAME_ORDER', 'username');

// The is that is sent for the easter egg
define('ADDING_HISSELF_STICKER_ID', 'BQADBAADdgADJkm4A4DKE1aO0rUFAg');

/*
 * This function get the message requested in the languange selected. Localization function that take string in language.php
 * @param:
 * $message  The index of the message requested, e.g. 'AddUsername_Msg'
 * $language The language which the message has to be sent
 */
function &getMessage($message, &$language) {
    return $GLOBALS['messages'][$language][$message];
}

function &getABIndexForContact(&$chat_id, &$selected_contact, &$order, PDO &$pdo) {
    $sth = $pdo->prepare("SELECT \"id\" FROM \"Contact\" WHERE \"id_owner\" = :chat_id ORDER BY $order");
    $sth->bindParam(':chat_id', $chat_id);
    $sth->execute();
    $cont = 1;
    $position = 1;
    while ($row = $sth->fetch()) {
        if ($row['id'] == $selected_contact){
            $position = $cont;
            break;
        }
        $cont++;
    }
    $sth = null;
    return (($position % SPACEPERVIEW) == 0 ? 0 : 1) + intval($position / SPACEPERVIEW);
}

function &getASInfoByID(&$chat_id, &$index_contact, &$language, PDO &$pdo) {
    $sth = $pdo->prepare('SELECT "username", "first_name", "last_name", "desc" FROM "Contact" WHERE "id" = :id_as AND "id_owner" = :id_owner');
    $sth->bindParam(':id_as', $index_contact);
    $sth->bindParam(':id_owner', $chat_id);
    $sth->execute();
    $row = $sth->fetch();
    if (!isset($row['username'])) {
        return null;
    }
    $string = getMessage('Username_Msg', $language) . '@' . $row['username'] . NEWLINE . getMessage('FirstName_Msg', $language) . $row['first_name'] . NEWLINE;
    if (isset($row['last_name']) && ($row['last_name'] !== 'NULL')) {
        $string = $string . getMessage('LastName_Msg', $language) . $row['last_name'] . NEWLINE;
    }
    if (isset($row['desc']) && ($row['desc'] !== 'NULL')) {
        $string = $string . getMessage('Description_Msg', $language) . $row['desc'] . NEWLINE;
    }
    $sth = null;
    $string = $string . '/' . $row['username'] . ' ' . NEWLINE;
    return $string;
}

function &getASInfoByRow(&$row, &$language) {
    $string = ROUNDS_DOWN . NEWLINE . getMessage('Username_Msg', $language) . '@' . $row['username'] . NEWLINE . getMessage('FirstName_Msg', $language). $row['first_name'] . NEWLINE;
    if (isset($row['last_name']) && ($row['last_name'] !== 'NULL')) {
        $string = $string . getMessage('LastName_Msg', $language) . $row['last_name'] . NEWLINE;
    }
    if (isset($row['desc']) && ($row['desc'] !== 'NULL')) {
    $string = $string . getMessage('Description_Msg', $language) . '<i>' . $row['desc'] . '</i>' . NEWLINE;
    }
    $string = $string . '/' . $row['username'] . ' ' . NEWLINE . ROUNDS_UP . NEWLINE;
    return $string;
}

function &getASInfoByRowForInline(&$row, &$language) {
    $string = ROUNDS_DOWN . NEWLINE . getMessage('Username_Msg', $language) . '@' . $row['username'] . NEWLINE . getMessage('FirstName_Msg', $language). $row['first_name'] . NEWLINE;
    if (isset($row['last_name']) && ($row['last_name'] !== 'NULL')) {
    $string = $string . getMessage('LastName_Msg', $language) . $row['last_name'] . NEWLINE;
    }
    if (isset($row['desc']) && ($row['desc'] !== 'NULL')) {
        $string = $string . getMessage('Description_Msg', $language) . '<i>' . $row['desc'] . '</i>' . NEWLINE;
    }
    $string = $string . ROUNDS_UP;
    return $string;
}

function &getABList(&$chat_id, &$index, &$language, &$order, PDO &$pdo) {
    $string = getMessage('Bot_Title', $language) . NEWLINE;
    $id = ($index - 1) * SPACEPERVIEW + 1;
    $maxid = $id + SPACEPERVIEW;
    $sth = $pdo->prepare("SELECT \"username\", \"first_name\", \"last_name\", \"desc\", \"id\" FROM \"Contact\" WHERE \"id_owner\" = :chat_id ORDER BY $order;");
    $sth->bindParam(':chat_id', $chat_id);
    $sth->execute();
    $cont = 1;
    $displayedrow = 0;
    while($row = $sth->fetch()) {
        if ($displayedrow === 0 && ($cont == (($index - 1) * SPACEPERVIEW + 1))) {
            $usernames = [
                [
                    'text' => '@' . $row['username'],
                    'callback_data' => 'id/' . $row['id'],
                ]
            ];
            $string = $string . getASInfoByRow($row, $language);
            $displayedrow++;
        } elseif ($displayedrow > 0 && $displayedrow < SPACEPERVIEW) {
            array_push($usernames, [
                'text' => '@' . $row['username'],
                'callback_data' => 'id/' . $row['id'],
            ]);
            $string = $string . getASInfoByRow($row, $language);
            $displayedrow++;
        } elseif ($displayedrow == SPACEPERVIEW) {
            break;
        } else {
            $cont++;
        }
    }
    $sth = null;
    $container = [
        'string' => &$string,
        'usernames' => &$usernames
    ];
    return $container;
}

function &getListResults(&$chat_id, &$query, PDO &$pdo) {
    $sth = $pdo->prepare("SELECT COUNT(\"username\") FROM (SELECT \"username\", \"first_name\", \"last_name\" FROM \"Contact\" WHERE \"id_owner\" = :chat_id) AS T WHERE \"first_name\" LIKE '$query%'  OR \"first_name\" LIKE '%$query%' OR \"last_name\" LIKE '$query%' OR \"last_name\" LIKE '%$query%' OR  CONCAT_WS(' ', \"first_name\", \"last_name\") LIKE '$query%' OR username LIKE '$query%' OR username LIKE '%$query%' OR username LIKE '@$query%' OR username LIKE '%@$query%' OR CONCAT_WS(' ', \"first_name\", \"last_name\") LIKE '%$query' OR CONCAT_WS(' ', \"last_name\", \"first_name\") LIKE '$query%' OR CONCAT_WS(' ', \"last_name\", \"first_name\") LIKE '%$query';");
    $sth->bindParam(':chat_id', $chat_id);
    $sth->execute();
    $results = $sth->fetchColumn();
    $list = intval($results/ SPACEPERVIEW);
    // Add one list for the remaing one if there are any
    if (($results % SPACEPERVIEW) > 0)
        $list++;
    return $list;
}

function &getSearchResults(&$chat_id, &$query, &$index, &$order, &$language, PDO &$pdo) {
    $string = getMessage('ShowResults_Msg', $language) . "\"<b>$query</b>\"" . NEWLINE;
    $sth = $pdo->prepare("SELECT \"username\", \"first_name\", \"last_name\", \"desc\", \"id\" FROM (SELECT \"username\", \"first_name\", \"last_name\", \"desc\", \"id\" FROM \"Contact\" WHERE \"id_owner\" = :chat_id) AS T WHERE \"first_name\" LIKE '$query%'  OR \"first_name\" LIKE '%$query%' OR \"last_name\" LIKE '$query%' OR \"last_name\" LIKE '%$query%' OR  CONCAT_WS(' ', \"first_name\", \"last_name\") LIKE '$query%' OR username LIKE '$query%' OR username LIKE '%$query%' OR username LIKE '@$query%' OR username LIKE '%@$query%' OR CONCAT_WS(' ', \"first_name\", \"last_name\") LIKE '%$query' OR CONCAT_WS(' ', \"last_name\", \"first_name\") LIKE '$query%' OR CONCAT_WS(' ', \"last_name\", \"first_name\") LIKE '%$query' ORDER BY $order;");
    $sth->bindParam(':chat_id', $chat_id);
    $sth->execute();
    $cont = 1;
    $displayedrow = 0;
    $usernames = [[]];
    while($row = $sth->fetch()) {
        if ($displayedrow === 0 && ($cont == (($index - 1) * SPACEPERVIEW + 1))) {
            $usernames = [
                [
                    'text' => '@' . $row['username'],
                    'callback_data' => 'id/' . $row['id'],
                ]
            ];
            $string = $string . getASInfoByRow($row, $language);
            $displayedrow++;
        } elseif ($displayedrow > 0 && $displayedrow < SPACEPERVIEW) {
            array_push($usernames, [
                'text' => '@' . $row['username'],
                'callback_data' => 'id/' . $row['id'],
            ]);
            $string = $string . getASInfoByRow($row, $language);
            $displayedrow++;
        } elseif ($displayedrow == SPACEPERVIEW) {
            break;
        } else {
            $cont++;
        }
    }
    $sth = null;
    $container = [
        'string' => &$string,
        'usernames' => &$usernames,
    ];
    return $container;
}

function saveContact(&$chat_id, &$row, PDO &$pdo) {
    if (isset($row['id_contact']) && $row['id_contact'] !== 'NULL') {
        $sth = $pdo->prepare('INSERT INTO "Contact" ("id", "id_owner", "id_contact", "username", "first_name", "last_name", "desc") VALUES (:id, :chat_id, :id_contact, :username, :first_name, :last_name, :description)');
        $sth->bindValue(':id_contact', $row['id_contact'], PDO::PARAM_INT);
    } else {
        $sth = $pdo->prepare('INSERT INTO "Contact" ("id", "id_owner", "username", "first_name", "last_name", "desc") VALUES (:id, :chat_id, :username, :first_name, :last_name, :description)');
    }
    $sth->bindParam(':id', $row['id']);
    $sth->bindParam(':chat_id', $chat_id);
    $sth->bindParam(':username', $row['username']);
    $sth->bindParam(':first_name', $row['first_name']);
    $sth->bindParam(':last_name', $row['last_name']);
    $sth->bindParam(':description', $row['last_name']);
    $sth->execute();
    $sth = null;
}

function &getList(&$chat_id, PDO &$pdo) {
    // Count how many Contact does this user own by doing a SELECT COUNT query
    $sth = $pdo->prepare('SELECT COUNT("id") FROM "Contact" WHERE "id_owner" = :chat_id');
    $sth->bindParam(':chat_id', $chat_id);
    $sth->execute();
    $addressspacecount = $sth->fetchColumn();
    $sth = null;
    // Calculate how many menu's lists do we have to create by divind the number of spaces of the addressbook for the number of the address space we want to be listed in a single menu's list
    $list = intval($addressspacecount / SPACEPERVIEW);
    // Add one list for the remaing one if there are any
    if (($addressspacecount % SPACEPERVIEW) > 0)
        $list++;
    return $list;
}

function &getOrderString(&$order_number) {
    switch ($order_number) {
        case FIRSTNAME:
            $order_string = FIRSTNAME_ORDER;
            break;
        case LASTNAME:
            $order_string = LASTNAME_ORDER;
            break;
        case USERNAME:
            $order_string = USERNAME_ORDER;
            break;
    }
    return $order_string;
}

function &getLanguage(&$chat_id, REDIS &$redis, PDO &$pdo) {
    $is_language_set = $redis->exists($chat_id . ':language');
    if ($is_language_set) {
        return $redis->get($chat_id . ':language');
    } else {
        $sth = $pdo->prepare('SELECT "language" FROM "User" WHERE "chat_id" = :chat_id');
        $sth->bindParam(':chat_id', $chat_id);
        $sth->execute();
        $row = $sth->fetch();
        $sth = null;
        if (isset($row['language'])) {
            $redis->setEx($chat_id . ':language', 86400, $row['language']);
            return $row['language'];
        }
        else {
            $text = 'en';
            return $text;
        }
    }
}

function setLanguage(&$chat_id, &$language, REDIS &$redis, PDO &$pdo) {
    $sth = $pdo->prepare('UPDATE "User" SET "language" = :language WHERE "chat_id" = :chat_id');
    $sth->bindParam(':language', $language);
    $sth->bindParam(':chat_id', $chat_id);
    $sth->execute();
    $sth = null;
    $redis->setEx($chat_id . ':language', 86400, $language);
}

function &getOrder(&$chat_id, REDIS &$redis, PDO &$pdo) {
    $is_order_set = $redis->exists($chat_id . ':order');
    if ($is_order_set) {
        return getOrderString($redis->get($chat_id . ':order'));
    } else {
        $sth = $pdo->prepare('SELECT "order" FROM "User" WHERE "chat_id" = :chat_id');
        $sth->bindParam(':chat_id', $chat_id);
        $sth->execute();
        $row = $sth->fetch();
        $sth = null;
        $redis->setEx($chat_id . ':order', 86400, $row['order']);
        return getOrderString($row['order']);
    }
}

function setOrder(&$chat_id, $order, REDIS &$redis, PDO &$pdo) {
    $sth = $pdo->prepare('UPDATE "User" SET "order" = :order WHERE "chat_id" = :chat_id');
    $sth->bindParam(':order', $order);
    $sth->bindParam(':chat_id', $chat_id);
    $sth->execute();
    $sth = null;
    $redis->setEx($chat_id . ':order', 86400, $order);
}

function &getStatus(&$chat_id, &$redis) {
    $is_status_set = $redis->exists($chat_id . ':status');
    if ($is_status_set) {
        return $redis->get($chat_id . ':status');
    } else {
        $redis->set($chat_id . ':status', 0);
        $redis->set($chat_id . ':easter_egg', 1);
        return -1;
    }
}

function &getIndexAddressbook(&$chat_id, &$redis) {
    $is_index_set = $redis->exists($chat_id . ':index_addressbook');
    if ($is_index_set) {
        return $redis->get($chat_id . ':index_addressbook');
    } else {
        $redis->set($chat_id . ':index_addressbook', 1);
        return 1;
    }
}

<?php


// Process a callback_query from a private chat with the bot
function processCallbackQuery(&$callback_query, REDIS &$redis, PDO &$pdo) {
    $id = $callback_query['id'];
    $chat_id = $callback_query['from']['id'];
    $message_id = isset($callback_query['message']['message_id']) ? $callback_query['message']['message_id'] : null;
    $inline_message_id = isset($callback_query['inline_message_id']) ? $callback_query['inline_message_id'] : null;
    $data = $callback_query['data'];
    if (isset($data) && isset($chat_id) && isset($id)) {
        $language = getLanguage($chat_id, $redis, $pdo);
        if (isset($message_id)) {
            switch ($data) {
                case 'show/ab':
                    if($redis->exists($chat_id . ':search_query')) {
                        $redis->delete($chat_id . ':search_query');
                        $redis->delete($chat_id . ':index_search');
                    }
                    if (getASRowOwnedByUser($chat_id, $pdo) > 0) {
                        $order = getOrder($chat_id, $redis, $pdo);
                        $index_addressbook = getIndexAddressbook($chat_id, $redis);
                        $list = getList($chat_id, $pdo);
                        $container = &getABList($chat_id, $index_addressbook, $language, $order, $pdo);
                        apiEditMessageInlineKeyboard($chat_id, $message_id, $container['string'], getListInlineKeyboard($index_addressbook, $list, $container['usernames'], $language));
                    } else {
                        apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('AddressBookEmty_Msg', $language), getABEmptyInlineKeyboard($language));
                    }
                    $redis->set($chat_id . ':status', SHOW_AB);
                    $redis->delete($chat_id . ':selected_contact');
                    apiAnswerlCallbackQueryEmpty($id);
                    break;
                case 'menu':
                    if($redis->exists($chat_id . ':search_query')) {
                        $redis->delete($chat_id . ':search_query');
                        $redis->delete($chat_id . ':index_search');
                    }
                    $spaces = getASRowOwnedByUser($chat_id, $pdo);
                    if ($spaces > 0) {
                        $keyboard = &getMenuInlineKeyboard($language);
                    } else {
                        $keyboard = &getAddInlineKeyboard($language);
                    }
                    apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('Menu_Msg', $language), $keyboard);
                    $redis->set($chat_id . ':status', SHOW_AB);
                    $redis->delete($chat_id . ':selected_contact');
                    apiAnswerlCallbackQueryEmpty($id);
                    break;
                case 'add':
                    apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('AddUsername_Msg', $language), getBackInlineKeyboard($language));
                    switch (getStatus($chat_id, $redis)) {
                        case SHOW_AB:
                        $redis->set($chat_id . ':status', ADDING_USERNAME_AB);
                        break;
                    case MENU:
                        $redis->set($chat_id . ':status', ADDING_USERNAME_MENU);
                        break;
                    default:
                        $redis->set($chat_id . ':status', ADDING_USERNAME_AB);
                        break;
                    }
                    apiAnswerlCallbackQueryEmpty($id);
                    break;
                case 'back':
                    switch (getStatus($chat_id, $redis)) {
                        case OPTIONS:
                        case NOSTATUS:
                        // No break
                        case ADDING_USERNAME_MENU:
                            $rows = getASRowOwnedByUser($chat_id, $pdo);
                            if($rows > 0) {
                                $keyboard = &getMenuInlineKeyboard($language);
                            } else {
                                $keyboard = &getAddInlineKeyboard($language);
                            }
                            apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('Menu_Msg', $language), $keyboard);
                            $redis->set($chat_id . ':status', MENU);
                            apiAnswerlCallbackQueryEmpty($id);
                            break;
                        case ADDING_USERNAME_AB:
                            if (getASRowOwnedByUser($chat_id, $pdo) > 0) {
                                $index_addressbook = getIndexAddressbook($chat_id, $redis);
                                $order = getOrder($chat_id, $redis, $pdo);
                                $list = getList($chat_id, $pdo);
                                $container = &getABList($chat_id, $index_addressbook, $language, $order, $pdo);
                                apiEditMessageInlineKeyboard($chat_id, $message_id, $container['string'], getListInlineKeyboard($index_addressbook, $list, $container['usernames'], $language));
                            } else {
                                apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('AddressBookEmty_Msg', $language), getABEmptyInlineKeyboard($language));
                            }
                            $redis->set($chat_id . ':status', SHOW_AB);
                            apiAnswerlCallbackQueryEmpty($id);
                            break;
                        case ADDING_FIRSTNAME_MENU;
                            $status2 = ADDING_USERNAME_MENU;
                            // No break
                        case ADDING_FIRSTNAME_AB:
                            apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('AddUsername_Msg', $language), getBackInlineKeyboard($language));
                            $redis->delete($chat_id . ':contact');
                            $status = isset($status2) ? $status2 : ADDING_USERNAME_AB;
                            $redis->set($chat_id . ':status', $status);
                            apiAnswerlCallbackQueryEmpty($id);
                            break;
                        case ADDING_LASTNAME_MENU:
                            $status2 = ADDING_FIRSTNAME_MENU;
                            // No break
                        case ADDING_LASTNAME_AB:
                            apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('AddFirstName_Msg', $language), getBackInlineKeyboard($language));
                            $redis->delete($chat_id . ':contact', 'first_name');
                            $status = isset($status2) ? $status2 : ADDING_FIRSTNAME_AB;
                            $redis->set($chat_id . ':status', $status);
                            apiAnswerlCallbackQueryEmpty($id);
                            break;
                        case ADDING_DESC_MENU:
                            $status2 = ADDING_LASTNAME_MENU;
                            // No break
                        case ADDING_DESC_AB:
                            apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('AddLastName_Msg', $language), getBackSkipInlineKeyboard($language));
                            $redis->delete($chat_id . ':contact', 'last_name');
                            $status = isset($status2) ? $status2 : ADDING_LASTNAME_AB;
                            $redis->set($chat_id . ':status', $status);
                            apiAnswerlCallbackQueryEmpty($id);
                            break;
                        case DELETE_AB_PROMPT:
                        case CHOOSE_ORDER:
                            $suffix2 = 'show/ab';
                            // No break
                        case CHOOSE_LANGUAGE:
                            $suffix = isset($suffix2) ? $suffix2 : 'menu';
                            apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('Options_Msg', $language), getOptionsInlineKeyboard($language, $suffix));
                            apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('Options_AnswerCallback', $language)]);
                            $redis->set($chat_id . ':status', OPTIONS);
                            break;
                        case EDITUSERNAME:
                        case EDITFIRSTNAME:
                        case EDITLASTNAME:
                        case EDITDESC:
                        case ADDING_LASTNAME_CONTACT:
                        case ADDING_DESC_CONTACT:
                        case DELETE_AS_PROMPT:
                            $selected_contact = $redis->get($chat_id . ':selected_contact');
                            $row = &getASRowByID($chat_id, $selected_contact, $pdo);
                            apiEditMessageInlineKeyboard($chat_id, $message_id, getASInfoByRow($row, $language), getEditASInlineKeyboard($chat_id, $row, $language, $redis));
                            $redis->set($chat_id . ':status', $status);
                            apiAnswerlCallbackQueryEmpty($id);
                            break;
                        case ADDING_DESC_SAVE:
                        case SAVING_USER_PROMPT:
                            if($redis->exists($chat_id . ':search_query')) {
                                $redis->delete($chat_id . ':search_query');
                                $redis->delete($chat_id . ':index_search');
                            }
                            // If the index is 0 (it means this is the first time the user see the list) set the index to 1
                            if (getASRowOwnedByUser($chat_id, $pdo) > 0) {
                                $order = getOrder($chat_id, $redis, $pdo);
                                $index_addressbook = getIndexAddressbook($chat_id, $redis);
                                $list = getList($chat_id, $pdo);
                                $container = &getABList($chat_id, $index_addressbook, $language, $order, $pdo);
                                apiEditMessageInlineKeyboard($chat_id, $message_id, $container['string'], getListInlineKeyboard($index_addressbook, $list, $container['usernames'], $language));
                            } else {
                                apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('AddressBookEmty_Msg', $language), getABEmptyInlineKeyboard($language));
                            }
                            $redis->set($chat_id . ':status', SHOW_AB);
                            $redis->delete($chat_id . ':forward');
                            apiAnswerlCallbackQueryEmpty($id);
                            break;
                    }
                    break;
                case 'skip':
                    switch (getStatus($chat_id, $redis)) {
                        // If the user want to skip the insertion of the last name or the description just put the status to the next step according to which one is currently
                        case ADDING_LASTNAME_MENU:
                            $status2 = ADDING_DESC_MENU;
                            // No break
                        case ADDING_LASTNAME_AB:
                            apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('LastName_Msg', $language) . getMessage('Skipped_Msg', $language) . NEWLINE . getMessage('AddDescription_Msg', $language), getBackSkipInlineKeyboard($language));
                            $status = isset($status2) ? $status2 : ADDING_DESC_AB;
                            $redis->set($chat_id . ':status', $status);
                            $redis->hset($chat_id . ':contact', 'last_name', 'NULL');
                            apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('LastName_AnswerCallback', $language) . getMessage('Skipped_AnswerCallback', $language)]);
                            break;
                        case ADDING_DESC_SAVE:
                        case ADDING_DESC_AB:
                        // No break
                        case ADDING_DESC_MENU:
                            $new_id = getASRowOwnedByUser($chat_id, $pdo);
                            $new_id++;
                            $sth = $pdo->prepare('INSERT INTO "Contact" ("id", "id_owner", "username", "first_name", "last_name", "desc") VALUES (:id, :chat_id, :username, :first_name, :last_name, \'NULL\')');
                            $sth->bindParam(':id', $new_id);
                            $sth->bindParam(':chat_id', $chat_id);
                            $sth->bindParam(':username', $redis->hget($chat_id . ':contact', 'username'));
                            $sth->bindParam(':first_name', $redis->hget($chat_id . ':contact', 'first_name'));
                            $sth->bindParam(':last_name', $redis->hget($chat_id . ':contact', 'last_name'));
                            $sth->execute();
                            $sth = null;
                            $order = getOrder($chat_id, $redis, $pdo);
                            $index_addressbook = getABIndexForContact($chat_id, $new_id, $order, $pdo);
                            $list = getList($chat_id, $pdo);
                            $container = &getABList($chat_id, $index_addressbook, $language, $order, $pdo);
                            apiEditMessageInlineKeyboard($chat_id, $message_id, $container['string'], getListInlineKeyboard($index_addressbook, $list, $container['usernames'], $language));
                            $redis->set($chat_id . ':status', SHOW_AB);
                            $redis->delete($chat_id . ':contact');
                            apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('ContactAdded_AnswerCallback', $language)]);
                            break;
                        default:
                        $id = getASRowOwnedByUser($chat_id, $pdo);
                        if($id > 0) {
                            $keyboard = &getMenuInlineKeyboard($language);
                        } else {
                            $keyboard = &getAddInlineKeyboard($language);
                        }
                        apiSendDefaultMessage($chat_id, getMessage($messagetoshow, $language), $keyboard);
                        apiAnswerlCallbackQueryEmpty($id);
                        break;
                    }
                    break;
                case 'options/menu':
                    $status2 = OPTIONS;
                    $suffix2 = 'menu';
                    // No break
                case 'options/ab':
                    if($redis->exists($chat_id . ':search_query')) {
                        $redis->delete($chat_id . ':search_query');
                        $redis->delete($chat_id . ':index_search');
                    }
                    $suffix = isset($suffix2) ? $suffix2 : 'show/ab';
                    apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('Options_Msg', $language), getOptionsInlineKeyboard($language, $suffix));
                    $status = isset($status2) ? $status2 : OPTIONS;
                    $redis->set($chat_id . ':status', $status);
                    apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('Options_AnswerCallback', $language)]);
                    break;
                case 'language':
                    apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('ChooseLanguage_Msg', $language), getChooseLanguageInlineKeyboard($language));
                    $redis->set($chat_id . ':status', CHOOSE_LANGUAGE);
                    apiAnswerlCallbackQueryEmpty($id);
                    break;
                case 'same/language':
                    apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('SameLanguage_AnswerCallback', $language)]);
                    break;
                case 'search':
                    apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('EnterSearchQuery_Msg', $language), getBackSearchInlineKeyboard($language));
                    $redis->set($chat_id . ':status', ADDING_SEARCH_QUERY);
                    apiAnswerlCallbackQueryEmpty($id);
                    break;
                case 'back/search':
                    $index_search = $redis->get($chat_id . ':index_search');
                    $search_query = $redis->get($chat_id . ':search_query');
                    $order = getOrder($chat_id, $redis, $pdo);
                    $container = &getSearchResults($chat_id, $search_query, $index_search, $order, $language, $pdo);
                    $list = getListResults($chat_id, $index_search, $pdo);
                    if($list > 0) {
                        apiEditMessageInlineKeyboard($chat_id, $message_id, $container['string'], getListInlineKeyboard($index_search, $list, $container['usernames'], $language, 'search'));
                    } else {
                        apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('ResultsNull_Msg', $language) . "\"$search_query\"", getASNotValidInlineKeyboard($language));
                    }
                    $redis->set($chat_id . ':status', SHOW_RESULTS);
                    $redis->delete($chat_id . ':selected_contact');
                    apiAnswerlCallbackQueryEmpty($id);
                    break;
                case 'update/username':
                    $selected_contact = $redis->get($chat_id . ':selected_contact');
                    $row = &getASRowByID($chat_id, $selected_contact, $pdo);
                    $chat = apiRequest('getChat', ['chat_id' => $row['id_contact']]);
                    $new_username = $chat['username'];
                    if ($newusername !== $row['username']) {
                        $sth = $pdo->prepare('UPDATE "Contact" SET "username" = :username WHERE "id_contact" = :id_contact');
                        $sth->bindParam(':username', $new_username);
                        $sth->bindValue(':id_contact', $row['id_contact'], PDO::PARAM_INT);
                        $sth->execute();
                        $sth = null;
                        apiEditMessageInlineKeyboard($chat_id, $message_id, getASInfoByRow($row, $language), getEditASInlineKeyboard($chat_id, $row, $language, $redis));
                        apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('UsernameUpdated_AsnwerCallback', $language)]);
                    } else {
                        apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('UsernameAlreadyUpdated_AsnwerCallback', $language)]);
                    }
                    break;
                case 'update/all':
                    $sth = $pdo->prepare('SELECT DISTINCT "id_contact", "username" FROM (SELECT "id_contact", "username" FROM "Contact" WHERE "id_owner" = :chat_id) AS T WHERE NOT ("id_contact" IS NULL)');
                    $sth->bindParam(':chat_id', $chat_id);
                    $sth->execute();
                    $sth2 = $pdo->prepare('UPDATE "Contact" SET "username" = :username WHERE "id_contact" = :id_contact');
                    $updates = [['newdata' => []]];
                    while($row = $sth->fetch()) {
                        $chat = apiRequest('getChat', ['chat_id' => $row['id_contact']]);
                        if (isset($chat['username']) && ($chat['username'] !== $row['username'])) {
                            $sth2->bindParam(':username', $chat['username']);
                            $sth2->bindValue(':id_contact', $row['id_contact'], PDO::PARAM_INT);
                            $sth2->execute();
                        } elseif(!isset($chat['username']) && !isset($chat['error_code'])) {
                            $username = 'NoUsername';
                            $sth2->bindParam(':username', $username);
                            $sth2->bindValue(':id_contact', $row['id_contact'], PDO::PARAM_INT);
                            $sth2->execute();
                        }
                    }
                    $sth2 = null;
                    $sth = null;
                    $pdo = null;
                    apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('UsernamesUpdated_AsnwerCallback', $language)]);
                    break;
                case 'save':
                    $new_id = getASRowOwnedByUser($chat_id, $pdo);
                    $new_id++;
                    $row = $redis->hGetAll($chat_id . ':forward');
                    $row['id'] = &$new_id;
                    saveContact($chat_id, $row, $pdo);
                    $order = &getOrder($chat_id, $redis, $pdo);
                    $index_addressbook = &getABIndexForContact($chat_id, $new_id, $order, $pdo);
                    $list = &getList($chat_id, $pdo);
                    $container = &getABList($chat_id, $index_addressbook, $language, $order, $pdo);
                    apiEditMessageInlineKeyboard($chat_id, $message_id,  $container['string'], getListInlineKeyboard($index_addressbook, $list, $container['usernames'], $language));
                    apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('Saved_AnswerCallback', $language)]);
                    $redis->set($chat_id . ':index_addressbook', $index_addressbook);
                    $redis->set($chat_id . ':status', SHOW_AB);
                    $redis->delete($chat_id . ':forward');
                    break;
                case 'order':
                    apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('Order_Msg', $language), getOrderInlineKeyboard($language));
                    $redis->set($chat_id . ':status', CHOOSE_ORDER);
                    apiAnswerlCallbackQueryEmpty($id);
                    break;
                default:
                    $string = explode('/', $data);
                    if (strpos($string[0], 'id') !== false) {
                        $selected_contact = $string[1];
                        $suffix = $string[2];
                        $redis->set($chat_id . ':status', SHOW_CONTACT);
                        $redis->set($chat_id . ':selected_contact', $selected_contact);
                        $row = &getASRowByID($chat_id, $selected_contact, $pdo);
                        apiEditMessageInlineKeyboard($chat_id, $message_id, getASInfoByRow($row, $language), getEditASInlineKeyboard($chat_id, $row, $language, $redis));
                        apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('ShowingContact_AsnwerCallback', $language) . $row['first_name'] .  (($row['last_name'] !== 'NULL') ? (' ' . $row['last_name']) : '')]);
                    } elseif (strpos($string[0], 'ab') !== false) {
                        $index_addressbook = $string[1];
                        $redis->set($chat_id . ':index_addressbook', $index_addressbook);
                        $list = getList($chat_id, $pdo);
                        $order = getOrder($chat_id, $redis, $pdo);
                        $container = &getABList($chat_id, $index_addressbook, $language, $order, $pdo);
                        apiEditMessageInlineKeyboard($chat_id, $message_id, $container['string'], getListInlineKeyboard($index_addressbook, $list, $container['usernames'], $language));
                        apiAnswerlCallbackQueryEmpty($id);
                    } elseif (strpos($string[0], 'edit') !== false) {
                        $info = $string[1];;
                        switch ($info) {
                            case 'username':
                                apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('EditUsername_Msg', $language), getBackInlineKeyboard($language));
                                $redis->set($chat_id . ':message_id', $message_id);
                                $redis->set($chat_id . ':status', EDITUSERNAME);
                                break;
                            case 'firstname':
                                apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('EditFirstName_Msg', $language), getBackInlineKeyboard($language));
                                $redis->set($chat_id . ':message_id', $message_id);
                                $redis->set($chat_id . ':status', EDITFIRSTNAME);
                                break;
                            case 'lastname':
                                apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('EditLastName_Msg', $language), getBackDeleteInlineKeyboard(true, $language));
                                $redis->set($chat_id . ':message_id', $message_id);
                                $redis->set($chat_id . ':status', EDITLASTNAME);
                                break;
                            case 'desc':
                                apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('EditDescription_Msg', $language), getBackDeleteInlineKeyboard(false, $language));
                                $redis->set($chat_id . ':message_id', $message_id);
                                $redis->set($chat_id . ':status', EDITDESC);
                                break;
                            case 'contact':
                                $new_id = getASRowOwnedByUser($chat_id, $pdo);
                                $new_id++;
                                $row = $redis->hGetAll($chat_id . ':forward');
                                $row['id'] = &$new_id;
                                saveContact($chat_id, $row, $pdo);
                                apiEditMessageInlineKeyboard($chat_id, $message_id, getASInfoByRow($row, $language), getEditASInlineKeyboard($chat_id, $row, $language, $redis));
                                $order = getOrder($chat_id, $redis, $pdo);
                                $index_addressbook = getABIndexForContact($chat_id, $new_id, $order, $pdo);
                                $redis->set($chat_id . ':status',  SHOW_CONTACT);
                                $redis->set($chat_id . ':index_addressbook', $index_addressbook);
                                $redis->set($chat_id . ':selected_contact', $new_id);
                                $redis->delete($chat_id . ':forward');
                                break;
                            default:
                            break;
                        }
                        apiAnswerlCallbackQueryEmpty($id);
                    } elseif (strpos($string[0], 'add') !== false) {
                        $info = $string[1];
                        switch ($info) {
                            case 'lastname':
                                apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('AddLastName_Msg', $language), getBackInlineKeyboard($language));
                                $redis->set($chat_id . ':status', ADDING_LASTNAME_CONTACT);
                                $redis->set($chat_id . ':message_id', $message_id);
                                break;
                            case 'desc':
                                apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('AddDescription_Msg', $language), getBackInlineKeyboard($language));
                                $redis->set($chat_id . ':status', ADDING_DESC_CONTACT);
                                $redis->set($chat_id . ':message_id', $message_id);
                                break;
                            case 'desc&save':
                                $row = $redis->hGetAll($chat_id . ':forward');
                                apiEditMessageInlineKeyboard($chat_id, $message_id, getASInfoByRow($row, $language) . getMessage('AddDescription_Msg', $language), getCancelSkipInlineKeyBoard($language));
                                $redis->set($chat_id . ':status', ADDING_DESC_SAVE);
                                $redis->set($chat_id . ':message_id', $message_id);
                                break;
                            default:
                                break;
                        }
                        apiAnswerlCallbackQueryEmpty($id);
                    } elseif (strpos($string[0], 'delete') !== false) {
                        $info = $string[1];
                        switch ($info) {
                            case 'asprompt':
                                apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('DeleteASPrompt_Msg', $language), getDeleteASPromptInlineKeyboard($language));
                                $redis->set($chat_id . ':status', DELETE_AS_PROMPT);
                                apiAnswerlCallbackQueryEmpty($id);
                                break;
                            case 'as':
                                $max = getASRowOwnedByUser($chat_id, $pdo);
                                $sth = $pdo->prepare('DELETE FROM "Contact" WHERE "id" = :selected_contact AND "id_owner" = :id_owner');
                                $selected_contact = $redis->get($chat_id . ':selected_contact');
                                $sth->bindParam(':selected_contact', $selected_contact);
                                $sth->bindParam(':id_owner', $chat_id);
                                $sth->execute();
                                $sth = null;
                                if ($max !== $selected_contact) {
                                    updateASInfo($chat_id, $max, 'id', $selected_contact, $pdo);
                                }
                                if(!$redis->exists($chat_id . ':search_query')) {
                                    $spaces = getASRowOwnedByUser($chat_id, $pdo);
                                    if ($spaces > 0) {
                                        $list = getList($chat_id, $pdo);
                                        $index_addressbook = getIndexAddressbook($chat_id, $redis);
                                        if ($index_addressbook > $list) {
                                            $index_addressbook--;
                                            $redis->set($chat_id . ':index_addressbook', $index_addressbook);
                                        }
                                        $order = getOrder($chat_id, $redis, $pdo);
                                        $container = &getABList($chat_id, $index_addressbook, $language, $order, $pdo);
                                        apiEditMessageInlineKeyboard($chat_id, $message_id, $container['string'], getListInlineKeyboard($index_addressbook, $list, $container['usernames'], $language));
                                    } else {
                                        apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('AddressBookEmty_Msg', $language), getABEmptyInlineKeyboard($language));
                                    }
                                    $redis->set($chat_id . ':status', SHOW_AB);
                                } else {
                                    $index_search = $redis->get($chat_id . ':index_search');
                                    $search_query = $redis->get($chat_id . ':search_query');
                                    $list = getListResults($chat_id, $search_query, $pdo);
                                    if ($index_search > $list) {
                                        $index_search--;
                                    }
                                    $order = getOrder($chat_id, $redis, $pdo);
                                    $container = &getSearchResults($chat_id, $search_query, $index_search, $order, $language, $pdo);
                                    if($list > 0) {
                                        apiEditMessageInlineKeyboard($chat_id, $message_id, $container['string'], getListInlineKeyboard($index_search, $list, $container['usernames'], $language, 'search'));
                                    } else {
                                        apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('ResultsNull_Msg', $language) . "\"$search_query\"", getASNotValidInlineKeyboard($language));
                                    }
                                    $redis->set($chat_id . ':status', SHOW_RESULTS);
                                    $redis->set($chat_id . ':index_search', $index_search);
                                }
                                apiAnswerlCallbackQueryEmpty($id);
                                break;
                            case 'info':
                                $selected_contact = $redis->get($chat_id . ':selected_contact');
                                $status = getStatus($chat_id, $redis);
                                switch ($status) {
                                    case EDITLASTNAME:
                                        updateASInfo($chat_id, $selected_contact, 'last_name', 'NULL', $pdo);
                                        $row = &getASRowByID($chat_id, $selected_contact, $pdo);
                                        apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('DeletedLastName_Msg', $language) . NEWLINE . getASInfoByRow($row, $language),  getEditASInlineKeyboard($chat_id, $row, $language, $redis));
                                        $redis->set($chat_id . ':index_addressbook', getABIndexForContact($chat_id, $selected_contact, getOrder($chat_id, $redis, $pdo), $pdo));
                                        $redis->set($chat_id . ':status', SHOW_CONTACT);
                                        break;
                                    case EDITDESC:
                                        updateASInfo($chat_id, $selected_contact, 'desc', 'NULL', $pdo);
                                        $row = &getASRowByID($chat_id, $selected_contact, $pdo);
                                        apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('DeletedDescription_Msg', $language) . NEWLINE . getASInfoByRow($row, $language),  getEditASInlineKeyboard($chat_id, $row, $language, $redis));
                                        $redis->set($chat_id . ':status', SHOW_CONTACT);
                                        break;
                                    default:
                                        break;
                                }
                                apiAnswerlCallbackQueryEmpty($id);
                                break;
                            case 'allprompt':
                                apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('DeleteAllContactsPrompt_Msg', $language), getDeleteAllPromptInlineKeyboard($language));
                                $redis->set($chat_id . ':status', DELETE_AB_PROMPT);
                                apiAnswerlCallbackQueryEmpty($id);
                                break;
                            case 'all':
                                if (getASRowOwnedByUser($chat_id, $pdo) > 0) {
                                    $sth = $pdo->prepare('DELETE FROM "Contact" WHERE "id_owner" = :chat_id');
                                    $sth->bindParam(':chat_id', $chat_id);
                                    $sth->execute();
                                    $sth = null;
                                    apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('AllContactsDeleted_AnswerCallback', $language)]);
                                } else {
                                    apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('NoContact_AnswerCallback', $language)]);
                                }
                                apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('Options_Msg', $language), getOptionsInlineKeyboard($language, 'show/ab'));
                                apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('Options_AnswerCallback', $language)]);
                                $redis->set($chat_id . ':status', OPTIONS);
                                break;
                            default:
                                break;
                        }
                    } elseif (strpos($string[0], 'cls') !== false) {
                        $language = $string[1];
                        $sth = $pdo->prepare('INSERT INTO "User" ("chat_id", "language") VALUES (:chat_id, :language)');
                        $sth->bindParam(':chat_id', $chat_id);
                        $sth->bindParam(':language', $language);
                        $sth->execute();
                        $sth = null;
                        apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('Menu_Msg', $language),  getAddInlineKeyboard($language));
                        apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('Registered_AnswerCallback', $language)]);
                        $redis->setEx($chat_id . ':language', 86400, $language);
                        $redis->set($chat_id . ':status', MENU);
                        $redis->set($chat_id . ':index_addressbook', 1);
                        $redis->set($chat_id . ':easter_egg', 1);
                    } elseif (strpos($string[0], 'cl') !== false) {
                        $language = $string[1];
                        setLanguage($chat_id, $language, $redis, $pdo);
                        apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('Options_Msg', $language), getOptionsInlineKeyboard($language, 'menu'));
                        apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('LanguageChanged_AnswerCallback', $language)]);
                        $redis->set($chat_id . ':status', OPTIONS);
                    } elseif (strpos($string[0], 'search') !== false) {
                        apiRequest('sendChatAction',  ['chat_id' => $chat_id, 'action' => 'typing']);
                        $index_search = $string[1];
                        $search_query = $redis->get($chat_id . ':search_query');
                        $order = getOrder($chat_id, $redis, $pdo);
                        $container = &getSearchResults($chat_id, $search_query, $index_search, $order, $language, $pdo);
                        $list = getListResults($chat_id, $search_query, $pdo);
                        apiEditMessageInlineKeyboard($chat_id, $message_id, $index_search . $container['string'], getListInlineKeyboard($index_search, $list, $container['usernames'], $language, 'search'));
                        $redis->set($chat_id . ':index_search', $index_search);
                    } elseif (strpos($string[0], 'order') !== false) {
                        apiEditMessageInlineKeyboard($chat_id, $message_id, getMessage('Options_Msg', $language), getOptionsInlineKeyboard($language, 'show/ab'));
                        apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('Options_AnswerCallback', $language)]);
                        setOrder($chat_id, $string[1], $redis, $pdo);
                        $redis->set($chat_id . ':status', OPTIONS);
                    }
                    apiAnswerlCallbackQueryEmpty($id);
                    break;
            }
        } else if (isset($data) && isset($chat_id) && isset($id) && isset($inline_message_id)) {
            if (strpos($data, 'shared') !== false) {
                if (isUserRegistered($chat_id, $pdo)) {
                    $string = explode('/', $data);
                    if($redis->exists($chat_id . ':search_query')) {
                        $redis->delete($chat_id . ':search_query');
                        $redis->delete($chat_id . ':index_search');
                    }
                    $row = $redis->hGetAll('share:' . $string[1]);
                    if (!isset($callback_query['from']['username']) || ($row['username'] !== ($callback_query['from']['username']))) {
                        $selected_contact = checkContactExist($chat_id, $row['username'], $pdo, $row['id_contact']);
                        if ($selected_contact === 0) {
                            $redis->hMSet($chat_id . ':forward', $row);
                            $string = getASInfoByRowForInline($row, $language) . NEWLINE . getMessage('SaveContact_Msg', $language);
                            apiSendDefaultMessage($chat_id, $string, getSaveInlineKeyboard($language));
                            $redis->set($chat_id . ':status', SAVING_USER_PROMPT);
                        } else {
                            $username = getUsernameFromID($chat_id, $selected_contact, $pdo);
                            $string = '/' . $username . getMessage('ContactAlreadyExist_Msg', $language);
                            apiSendDefaultMessage($chat_id, $string, getASNotValidInlineKeyboard($language));
                            $redis->set($chat_id . ':status', SHOW_CONTACT_NOTVALID);
                        }
                    } elseif(isset($callback_query['from']['username']) && ($row['username'] === ($callback_query['from']['username']))) {
                        $new_message = &apiSendDefaultMessage($chat_id, getMessage('AddHisSelf_Msg', $language), getASNotValidInlineKeyboard($language));
                        $redis->set($chat_id . ':status', SHOW_CONTACT_NOTVALID);
                    }
                } else {
                    apiSendDefaultMessage($chat_id, getMessage('Welcome_Msg', $language), getChooseLanguageStartInlineKeyboard());
                }
            }
            apiRequest('answerCallbackQuery', ['callback_query_id' => $id, 'text' => getMessage('CheckPrivate_Msg', $language), 'show_alert' => true]);
        }
    }
}

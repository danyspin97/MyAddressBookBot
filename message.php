<?php

/*
 *  This function process every message that the bot receive
 *  param:
 *  $message     Message string took from the JSON (php://input)
 *  $pdo         Connecction with the database (it is not possible to open it inside a function, see https://phpdelusions.net/pdo)
 */
function processMessage(&$message, REDIS &$redis, PDO &$pdo) {
    // process incoming message
    $chat_id = &$message['chat']['id'];
    // Did the user forwarded a message from another user?
    if (isset($message['forward_from']['username']) && ($message['forward_from']['id'] !== $message['from']['id'])) {
        $language = &getLanguage($chat_id, $redis, $pdo);
        // If the user was searching
        if($redis->exists($chat_id . ':search_query')) {
            // he isn't any more so delete search keys
            $redis->delete($chat_id . ':search_query');
            $redis->delete($chat_id . ':index_search');
        }
        // Does this contact not exist in the user's address book?
        $id = checkContactExist($chat_id, $message['forward_from']['username'], $pdo, $message['forward_from']['id']);
        if ($id === 0) {
            // Send it to the user
            $first_name = &$message['forward_from']['first_name'];
            $first_name = iconv(mb_detect_encoding($first_name, mb_detect_order(), true), "UTF-8", $first_name);
            $last_name = isset($message['forward_from']['last_name']) ? $message['forward_from']['last_name'] : 'NULL';
            $last_name = iconv(mb_detect_encoding($last_name, mb_detect_order(), true), "UTF-8", $last_name);
            $row = [
                'id_contact' => $message['forward_from']['id'],
                'username' => $message['forward_from']['username'],
                'first_name' => (($first_name !== '') ? $first_name : 'First Name'),
                'last_name' => ($last_name !== '') ? $last_name : 'NULL',
                'desc' => 'NULL'
            ];
            $redis->hMSet($chat_id . ':forward', $row);
            $string = getASInfoByRowForInline($row, $language) . NEWLINE . getMessage('SaveContact_Msg', $language);
            apiSendDefaultMessage($chat_id, $string, getSaveInlineKeyboard($language));
            $redis->set($chat_id . ':status', SAVING_USER_PROMPT);
        } else {
            $username = getUsernameFromID($chat_id, $id, $pdo);
            // Send the reference to the contact so the user can see it
            $string = '/' . $username . getMessage('ContactAlreadyExist_Msg', $language) . NEWLINE . getMessage('ForwardAgain_Msg', $language);
            apiSendDefaultMessage($chat_id, $string, getASNotValidInlineKeyboard($language));
            $redis->set($chat_id . ':status', SHOW_CONTACT_NOTVALID);
        }
        $redis->delete($chat_id . ':message_id', $chat_id . 'selected_contact');
        // If the message has been forwarded but who sent it doesn't have a username
    } elseif (isset($message['forward_from']['id']) && !isset($message['forward_from']['username']) && ($message['forward_from']['id'] !== $message['from']['id'])) {
        $language = getLanguage($chat_id, $redis, $pdo);
        apiSendDefaultMessage($chat_id,  getMessage('NoUsername_Msg', $language), getASNotValidInlineKeyboard($language));
        $redis->delete($chat_id . ':message_id', $chat_id . 'selected_contact');
        $redis->set($chat_id . ':status', SHOW_CONTACT_NOTVALID);
    } else if (isset($message['text'])) {
        // Text sent by the user
        $text = $message['text'];
        $language = &getLanguage($chat_id, $redis, $pdo);
        $sth = null;
        if (strpos($text, '/start') === 0) {
            if($redis->exists($chat_id . ':search_query')) {
                $redis->delete($chat_id . ':search_query');
                $redis->delete($chat_id . ':index_search');
            }
            $string = explode(' ', $text);
            if (!isset($string[1])) {
                $isregistred = isUserRegistered($chat_id, $pdo);
                // Choose the message to show after the welcome
                $messagetoshow = $isregistred ? 'Menu_Msg' : 'Welcome_Msg';
                $id = getASRowOwnedByUser($chat_id, $pdo);
                // Choose the inline keyboard to insert
                if ($isregistred) {
                    if($id > 0) {
                        $keyboard = &getMenuInlineKeyboard($language);
                    } else {
                        $keyboard = &getAddInlineKeyboard($language);
                    }
                } else {
                    $keyboard = getChooseLanguageStartInlineKeyboard();
                }
                // Send a new message
                apiSendDefaultMessage($chat_id, getMessage($messagetoshow, $language), $keyboard);
                // If the user is already registred we need to update the message_id in this user's row
                if ($isregistred) {
                    $redis->delete($chat_id . ':message_id', $chat_id . ':selected_contact');
                    $redis->set($chat_id . ':status', MENU);
                }
                // Inline query button send /start show_ab
            } elseif (strpos($string[1], 'show_ab') === 0) {
                if (getASRowOwnedByUser($chat_id, $pdo) > 0) {
                    $order = getOrder($chat_id, $redis, $pdo);
                    $index_addressbook = getIndexAddressbook($chat_id, $redis);
                    $list = getList($chat_id, $pdo);
                    $container = &getABList($chat_id, $index_addressbook, $language, $order, $pdo);
                    apiSendDefaultMessage($chat_id, $container['string'], getListInlineKeyboard($index_addressbook, $list, $container['usernames'], $language));
                } else {
                    apiSendDefaultMessage($chat_id, getMessage('AddressBookEmty_Msg', $language), getABEmptyInlineKeyboard($language));
                }
                $redis->delete($chat_id . ':message_id', $chat_id . ':selected_contact');
                $redis->set($chat_id . ':status', SHOW_AB);
            }
        } elseif (strpos($text, '/help') === 0) {
            // Send help message
            apiRequest('sendMessage', ['chat_id' => $chat_id, 'text' => getMessage('Help_Msg', $language), 'parse_mode' => 'HTML']);
        } elseif (strpos($text, '/about') === 0) {
            // Send about message
            apiRequest('sendMessage', ['chat_id' => $chat_id, 'text' => getMessage('About_Msg', $language), 'parse_mode' => 'HTML']);
        } elseif (strpos($text, '/') === 0 && strlen($text) > 5) {
            if($redis->exists($chat_id . ':search_query')) {
                $redis->delete($chat_id . ':search_query');
                $redis->delete($chat_id . ':index_search');
            }
            $username = str_replace('/', '', $text);
            $selected_contact = checkContactExist($chat_id, $username, $pdo);
            if($selected_contact > 0) {
                $row = &getASRowByID($chat_id, $selected_contact, $pdo);
                apiSendDefaultMessage($chat_id, getASInfoByRow($row, $language), getEditASInlineKeyboard($chat_id, $row, $language, $redis));
                $redis->delete($chat_id . ':message_id');
                $redis->set($chat_id . ':status', SHOW_CONTACT);
                $redis->set($chat_id . ':selected_contact', $selected_contact);
            } else {
                cleanStringFromEmoj($text);
                $string = $text . getMessage('ContactNotExist_Msg', $language);
                apiSendDefaultMessage($chat_id, $string, getASNotValidInlineKeyboard($language));
                $redis->delete($chat_id . ':message_id');
                $redis->set($chat_id . ':status', SHOW_CONTACT_NOTVALID);
            }
        } else {
            // If the message has been forwarded and the user who originally sent it isn't the user that is using the bot
            $status = getStatus($chat_id, $redis);
            $message_id = $redis->get($chat_id . ':message_id');
            //$text = iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text);
            // If the message isn't a command then the user sent data about adding or modifying the contacts of his addressbook
            switch ($status) {
                case ADDING_USERNAME_MENU:
                    $status2 = ADDING_FIRSTNAME_MENU;
                    // no break
                case ADDING_USERNAME_AB:
                    if (!strncmp($text, '@', 1)) {
                        // Count how many addressspace the user has in his addressbook so that incrementing this number we have the index for the new row
                        $string = explode(' ', $text);
                        $text = $string[0];
                        $text = preg_replace('/[^\w]/', '', $text);
                        if (strlen($text) >= 5 && strlen($text) <= 32) {
                            $contact = checkContactExist($chat_id, $text, $pdo);
                            if ($contact === 0) {
                                // If the owner of the address book has a username check it is different from the username he is adding
                                if ((isset($message['from']['username']) && ($text !== ($message['from']['username']))) || !isset($message['from']['username'])) {
                                    $redis->hset($chat_id . ':contact', 'username', $text);
                                    apiEditMessageStandard($chat_id, $message_id, getMessage('Username_Msg', $language) . '@' . $text);
                                    $new_message = &apiSendReplyMessage($chat_id, getMessage('AddFirstName_Msg', $language),  getBackInlineKeyboard($language), $message['message_id']);
                                    $status = (isset($status2) ? $status2 : ADDING_FIRSTNAME_AB);
                                    $redis->set($chat_id . ':status', $status);
                                    $redis->set($chat_id . ':message_id', $new_message['message_id']);
                                } else {
                                    if ($redis->exists($chat_id . ':easter_egg')) {
                                        apiRequest('sendMessage', ['chat_id' => $chat_id, 'text' => getMessage('AddHisSelf_EasterEgg', $language), 'parse_mode' => 'HTML']);
                                        apiRequest('sendSticker', ['chat_id' => $chat_id, 'sticker' => ADDING_HISSELF_STICKER_ID, 'disable_notification' => true]);
                                        $new_message = &apiSendDefaultMessage($chat_id, getMessage('AddUsername_Msg', $language), getBackInlineKeyboard($language));
                                        $redis->set($chat_id . ':message_id', $newmmessage['message_id']);
                                        $redis->delete($chat_id . ':easter_egg');
                                    } else {
                                        $string = getMessage('AddHisSelf_Msg', $language) . ' ' . getMessage('AddUsername_Msg', $language);
                                        $new_message = apiSendReplyMessage($chat_id, $string, getBackInlineKeyboard($language), $message['message_id']);
                                        $redis->set($chat_id . ':message_id', $new_message['message_id']);
                                    }
                                }
                            } else {
                                $username = getUsernameFromID($chat_id, $contact, $pdo);
                                $string = '/' . $username . getMessage('ContactAlreadyExist_Msg', $language) . NEWLINE . getMessage('ResendUsername_Msg', $language);
                                $new_message = &apiSendReplyMessage($chat_id, $string, getASNotValidInlineKeyboard($language), $message['message_id']);
                                $redis->set($chat_id . ':message_id', $new_message['message_id']);
                                $redis->set($chat_id . ':status', SHOW_CONTACT_NOTVALID);
                            }
                        } else {
                            $string = getMessage('UsernameLenght_Msg', $language) . NEWLINE . getMessage('ResendUsername_Msg', $language);
                            $new_message = &apiSendReplyMessage($chat_id, $string, getBackInlineKeyboard($language), $message['message_id']);
                            $redis->set($chat_id . ':message_id', $new_message['message_id']);
                        }
                    } else {
                        $new_message = &apiSendReplyMessage($chat_id, getMessage('ResendUsername_Msg', $language), getBackInlineKeyboard($language), $message['message_id']);
                        $redis->set($chat_id . ':message_id', $new_message['message_id']);
                    }
                    break;
                case ADDING_FIRSTNAME_MENU:
                    $status2 = ADDING_LASTNAME_MENU;
                    // No break
                case ADDING_FIRSTNAME_AB:
                    $redis->hset($chat_id . ':contact', 'first_name', $text);
                    apiEditMessageStandard($chat_id, $message_id, getMessage('FirstName_Msg', $language) . $text);
                    //$reply_markup = ['force_reply' => true];
                    $new_message = &apiSendReplyMessage($chat_id, getMessage('AddLastName_Msg', $language), getBackSkipInlineKeyboard($language), $message['message_id']);
                    //apiRequest('editMessageReplyMarkup', ['chat_id' => $chat_id, 'message_id' => $new_message['message_id'], 'reply_markup' => getBackSkipInlineKeyboard($language)]);
                    $status = isset($status2) ? $status2 : ADDING_LASTNAME_AB;
                    $redis->set($chat_id . ':status', $status);
                    $redis->set($chat_id . ':message_id', $new_message['message_id']);
                    break;
                case ADDING_LASTNAME_MENU:
                    $status2 = ADDING_DESC_MENU;
                    // No break
                case ADDING_LASTNAME_AB:
                    $redis->hset($chat_id . ':contact', 'last_name', $text);
                    apiEditMessageStandard($chat_id, $message_id, getMessage('LastName_Msg', $language) . $text);
                    $new_message = &apiSendReplyMessage($chat_id, getMessage('AddDescription_Msg', $language), getBackSkipInlineKeyboard($language), $message['message_id']);
                    $status = isset($status2) ? $status2 : ADDING_DESC_AB;
                    $redis->set($chat_id . ':status', $status);
                    $redis->set($chat_id . ':message_id', $new_message['message_id']);
                    break;
                case ADDING_DESC_MENU:
                case ADDING_DESC_AB:
                    $new_id = getASRowOwnedByUser($chat_id, $pdo);
                    $new_id++;
                    $row = $redis->hGetAll($chat_id . ':contact');
                    $sth = $pdo->prepare('INSERT INTO "Contact" ("id", "id_owner", "username", "first_name", "last_name", "desc") VALUES (:id, :chat_id, :username, :first_name, :last_name, :description)');
                    $sth->bindParam(':id', $new_id);
                    $sth->bindParam(':chat_id', $chat_id);
                    $sth->bindParam(':username', $row['username']);
                    $sth->bindParam(':first_name', $row['first_name']);
                    $sth->bindParam(':last_name', $row['last_name']);
                    $sth->bindParam(':description', $text);
                    $sth->execute();
                    $sth = null;
                    $order = &getOrder($chat_id, $redis, $pdo);
                    $index_addressbook = &getABIndexForContact($chat_id, $new_id, $order, $pdo);
                    $list = &getList($chat_id, $pdo);
                    $container = &getABList($chat_id, $index_addressbook, $language, $order, $pdo);
                    apiEditMessageStandard($chat_id, $message_id, getMessage('Description_Msg', $language) . $text);
                    $new_message = &apiSendDefaultMessage($chat_id, $container['string'], getListInlineKeyboard($index_addressbook, $list, $container['usernames'], $language));
                    $redis->delete($chat_id . ':contact');
                    $redis->set($chat_id . ':index_addressbook', $index_addressbook);
                    $redis->delete($chat_id . ':message_id');
                    $redis->set($chat_id . ':status', SHOW_AB);
                    break;
                case EDITUSERNAME:
                    if (!strncmp($text, '@', 1)) {
                        $selected_contact = $redis->get($chat_id . ':selected_contact');
                        $string = explode(' ', $text);
                        $text = $string[0];
                        $text = preg_replace('/[^\w]/', '', $text);
                        if (strlen($text) > 5 && strlen($text) <= 32) {
                            updateASInfo($chat_id, $index_contact, 'username', $text, $pdo);
                            $row = &getASRowByID($chat_id, $selected_contact, $pdo);
                            apiEditMessageStandard($chat_id, $message_id, getMessage('EditedUsername_Msg', $language) . '@' . $text);
                            $new_message = apiRequest('sendMessage', ['chat_id' => $chat_id, 'text' => getASInfoByRow($row, $language), 'reply_markup' => getEditASInlineKeyboard($chat_id, $row, $language, $redis), 'parse_mode' => 'HTML']);
                            $redis->set($chat_id . ':message_id', $new_message['message_id']);
                            $redis->set($chat_id . ':status', SHOW_CONTACT);
                            $order = getOrder($chat_id, $redis, $pdo);
                            if ($order === 'username') {
                                $redis->set($chat_id . ':index_addressbook', getABIndexForContact($chat_id, $selected_contact, $order, $pdo));
                            }
                        } else {
                            $string = getMessage('UsernameLenght_Msg', $language) . NEWLINE . getMessage('ResendUsername_Msg', $language);
                            $new_message = &apiSendDefaultMessage($chat_id, $string, getBackInlineKeyboard($language));
                            $redis->set($chat_id . ':message_id', $new_message['message_id']);
                        }
                    } else {
                        $new_message = &apiSendDefaultMessage($chat_id, getMessage('ResendUsername_Msg', $language), getBackInlineKeyboard($language));
                        $redis->set($chat_id . ':message_id', $new_message['message_id']);
                    }
                    break;
                case EDITFIRSTNAME:
                    $selected_contact = $redis->get($chat_id . ':selected_contact');
                    updateASInfo($chat_id, $selected_contact, 'first_name', $text, $pdo);
                    $row = &getASRowByID($chat_id, $selected_contact, $pdo);
                    apiEditMessageStandard($chat_id, $message_id, getMessage('EditedFirstName_Msg', $language) . $text);
                    $new_message = &apiSendDefaultMessage($chat_id, getASInfoByRow($row, $language), getEditASInlineKeyboard($chat_id, $row, $language, $redis));
                    $redis->set($chat_id . ':status', SHOW_CONTACT);
                    $redis->set($chat_id . ':message_id', $new_message['message_id']);
                    $redis->set($chat_id . ':index_addressbook', getABIndexForContact($chat_id, $selected_contact, getOrder($chat_id, $redis, $pdo), $pdo));
                    break;
                case EDITLASTNAME:
                    $selected_contact = $redis->get($chat_id . ':selected_contact');
                    updateASInfo($chat_id, $selected_contact, 'last_name', $text, $pdo);
                    $row = &getASRowByID($chat_id, $selected_contact, $pdo);
                    apiEditMessageStandard($chat_id, $message_id, getMessage('EditedLastName_Msg', $language) . $text);
                    $new_message = &apiSendDefaultMessage($chat_id, getASInfoByRow($row, $language), getEditASInlineKeyboard($chat_id, $row, $language, $redis));
                    $redis->set($chat_id . ':status', SHOW_CONTACT);
                    $redis->set($chat_id . ':message_id', $new_message['message_id']);
                    $redis->set($chat_id . ':index_addressbook', getABIndexForContact($chat_id, $selected_contact, getOrder($chat_id, $redis, $pdo), $pdo));
                    break;
                case EDITDESC:
                    $selected_contact = $redis->get($chat_id . ':selected_contact');
                    updateASInfo($chat_id, $selected_contact, 'desc', $text, $pdo);
                    $row = &getASRowByID($chat_id, $selected_contact, $pdo);
                    apiEditMessageStandard($chat_id, $message_id, getMessage('EditedDescription_Msg', $language) . $text);
                    $new_message = &apiSendDefaultMessage($chat_id, getASInfoByRow($row, $language), getEditASInlineKeyboard($chat_id, $row, $language, $redis));
                    $redis->set($chat_id . ':status', SHOW_CONTACT);
                    $redis->set($chat_id . ':message_id', $new_message['message_id']);
                    break;
                case ADDING_LASTNAME_CONTACT:
                    $selected_contact = $redis->get($chat_id . ':selected_contact');
                    updateASInfo($chat_id, $selected_contact, 'last_name', $text, $pdo);
                    $row = &getASRowByID($chat_id, $selected_contact, $pdo);
                    apiEditMessageStandard($chat_id, $message_id, getMessage('EditedLastName_Msg', $language) . $text);
                    $new_message = &apiSendDefaultMessage($chat_id, getASInfoByRow($row, $language), getEditASInlineKeyboard($chat_id, $row, $language, $redis));
                    $redis->set($chat_id . ':status', SHOW_CONTACT);
                    $redis->set($chat_id . ':message_id', $new_message['message_id']);
                    $redis->set($chat_id . ':index_addressbook', getABIndexForContact($chat_id, $selected_contact, getOrder($chat_id, $redis, $pdo), $pdo));
                    break;
                case ADDING_DESC_CONTACT:
                    $selected_contact = $redis->get($chat_id . ':selected_contact');
                    updateASInfo($chat_id, $selected_contact, 'desc', $text, $pdo);
                    $row = &getASRowByID($chat_id, $selected_contact, $pdo);
                    apiEditMessageStandard($chat_id, $message_id, getMessage('EditedDescription_Msg', $language) . $text);
                    $new_message = &apiSendDefaultMessage($chat_id, getASInfoByRow($row, $language), getEditASInlineKeyboard($chat_id, $row, $language, $redis));
                    $redis->set($chat_id . ':status', SHOW_CONTACT);
                    $redis->set($chat_id . ':message_id', $new_message['message_id']);
                    break;
                case ADDING_DESC_SAVE:
                    $row = $redis->hGetAll($chat_id . ':forward');
                    if (isset($row)) {
                        $new_id = getASRowOwnedByUser($chat_id, $pdo);
                        $new_id++;
                        $row['id'] = &$new_id;
                        $row['desc'] = &$text;
                        saveContact($chat_id, $row, $pdo);
                        $order = &getOrder($chat_id, $redis, $pdo);
                        $index_addressbook = &getABIndexForContact($chat_id, $selected_contact, $order, $pdo);
                        $list = &getList($chat_id, $pdo);
                        $container = &getABList($chat_id, $index_addressbook, $language, $order, $pdo);
                        apiEditMessageStandard($chat_id, $message_id, getMessage('Description_Msg', $language) . $text);
                        $new_message = &apiSendDefaultMessage($chat_id, $container['string'], getListInlineKeyboard($index_addressbook, $list, $container['usernames'], $language));
                        $redis->set($chat_id . ':status', SHOW_AB);
                        $redis->delete($chat_id . ':selected_contact');
                        $redis->set($chat_id . ':index_addressbook', $index_addressbook);
                        $redis->set($chat_id . ':message_id', $new_message['message_id']);
                        $redis->delete($chat_id . ':forward');
                    } else {
                        $isregistred = isUserRegistered($chat_id, $pdo);
                        // Choose the message to show after the welcome
                        $messagetoshow = $isregistred ? 'Menu_Msg' : 'Welcome_Msg';
                        $id = getASRowOwnedByUser($chat_id, $pdo);
                        // Choose the inline keyboard to insert
                        if ($isregistred) {
                            if($id > 0) {
                                $keyboard = &getMenuInlineKeyboard($language);
                            } else {
                                $keyboard = &getAddInlineKeyboard($language);
                            }
                        } else {
                            $keyboard = getChooseLanguageStartInlineKeyboard();
                        }
                        // Send a new message
                        apiSendDefaultMessage($chat_id, getMessage($messagetoshow, $language), $keyboard);
                        // If the user is already registred we need to update the message_id in this user's row
                        if ($isregistred) {
                            $redis->delete($chat_id . ':message_id', $chat_id . ':selected_contact');
                            $redis->set($chat_id . ':status', MENU);
                        }
                    }
                    break;
                case ADDING_SEARCH_QUERY:
                    $page = 1;
                    $order = &getOrder($chat_id, $redis, $pdo);
                    apiEditMessageStandard($chat_id, $message_id, getMessage('EnterSearchQuery_Msg', $language) . "\"<b>$text</b>\"");
                    $container = &getSearchResults($chat_id, $text, $page, $order, $language, $pdo);
                    $list = &getListResults($chat_id, $text, $pdo);
                    if($list > 0) {
                        $new_index_search = 1;
                        apiSendReplyMessage($chat_id, $container['string'], getListInlineKeyboard($new_index_search, $list, $container['usernames'], $language, 'search'), $message['message_id']);
                        $redis->setEx($chat_id . ':search_query', 10800, $text);
                        $redis->setEx($chat_id . ':index_search', 10800, $new_index_search);
                    } else {
                        $string = getMessage('ResultsNull_Msg', $language) . "\"<b>$text</b>\"";
                        apiSendReplyMessage($chat_id, $string, getSearchNullInlineKeyboard($language), $message['message_id']);
                    }
                    $redis->delete($chat_id . ':message_id');
                    $redis->set($chat_id . ':status', SHOW_RESULTS);
                    break;
                case NOSTATUS:
                    $id = getASRowOwnedByUser($chat_id, $pdo);
                    if($id > 0) {
                        $keyboard = &getMenuInlineKeyboard($language);
                    } else {
                        $keyboard = &getAddInlineKeyboard($language);
                    }
                    apiSendDefaultMessage($chat_id, getMessage($messagetoshow, $language), $keyboard);
                    break;
                default:
                    break;
            }
        }
    }
}

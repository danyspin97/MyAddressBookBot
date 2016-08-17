<?php

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

class MyAddressBookBot extends WiseDragonStd\HadesWrapper\Bot {
    public $order;
    public $index_addressbook;
    public $selected_contact;

    protected function processMessage() {
        $message = &$this->update['message'];
        $this->chat_id = &$message['from']['id'];
        $this->getLanguage();
        // Did the user forwarded a message from another user?
        if (isset($message['forward_from']['username']) && ($message['forward_from']['id'] !== $message['from']['id'])) {
            // If the user was searching
            if($this->redis->exists($this->chat_id . ':search_query')) {
                // he isn't any more so delete search keys
                $this->redis->delete($this->chat_id . ':search_query');
                $this->redis->delete($this->chat_id . ':index_search');
            }
            // Does this contact not exist in the user's address book?
            $id = $this->database->checkContactExist($message['forward_from']['username'], $message['forward_from']['id']);
            if ($id === 0) {
                // Send it to the user
                $first_name = &$message['forward_from']['first_name'];
                $last_name = $message['forward_from']['last_name'] ?? 'NULL';
                $row = [
                    'id_contact' => &$message['forward_from']['id'],
                    'username' => &$message['forward_from']['username'],
                    'first_name' => (($first_name !== '') ? $first_name : 'First Name'),
                    'last_name' => ($last_name !== '') ? $last_name : 'NULL',
                    'desc' => 'NULL'
                ];
                $this->redis->hMSet($this->chat_id . ':forward', $row);
                $string = $this->getContactInfoByRowForInline($row) . NEWLINE . $this->localization[$this->language]['SaveContact_Msg'];
                $this->sendMessage($string, $this->inline_keyboard->getSaveInlineKeyboard());
                $this->redis->set($this->chat_id . ':status', SAVING_USER_PROMPT);
            } else {
                // Send the reference to the contact so the user can see it
                $string = '/' . $message['forward_from']['username'] . $this->localization[$this->language]['ContactAlreadyExist_Msg'] . NEWLINE . $this->localization[$this->language]['ForwardAgain_Msg'];
                $this->sendMessage($string, $this->inline_keyboard->getContactNotValidInlineKeyboard());
                $this->redis->set($this->chat_id . ':status', SHOW_CONTACT_NOTVALID);
            }
            $this->redis->delete($this->chat_id . ':message_id', $this->chat_id . 'selected_contact');
            // If the message has been forwarded but who sent it doesn't have a username
        } elseif (isset($message['forward_from']['id']) && !isset($message['forward_from']['username']) && ($message['forward_from']['id'] !== $message['from']['id'])) {
            $this->sendMessage($this->localization[$this->language]['NoUsername_Msg'], $this->inline_keyboard->getContactNotValidInlineKeyboard());
            $this->redis->delete($this->chat_id . ':message_id', $this->chat_id . 'selected_contact');
            $this->redis->set($this->chat_id . ':status', SHOW_CONTACT_NOTVALID);
        } else if (isset($message['text'])) {
            // Text sent by the user
            $text = &$message['text'];
            if (strpos($text, '/start') === 0) {
                if($this->redis->exists($this->chat_id . ':search_query')) {
                    $this->redis->delete($this->chat_id . ':search_query');
                    $this->redis->delete($this->chat_id . ':index_search');
                }
                $string = explode(' ', $text);
                if (!isset($string[1])) {
                    $isregistred = $this->database->isUserRegistered();
                    // Choose the message to show after the welcome
                    $messagetoshow = $isregistred ? 'Menu_Msg' : 'Welcome_Msg';
                    $id = $this->database->getContactRowOwnedByUser();
                    // Choose the inline keyboard to insert
                    if ($isregistred) {
                        if($id > 0) {
                            $keyboard = &$this->inline_keyboard->getMenuInlineKeyboard();
                        } else {
                            $keyboard = &$this->inline_keyboard->getAddInlineKeyboard();
                        }
                    } else {
                        $keyboard = &$this->inline_keyboard->getChooseLanguageStartInlineKeyboard();
                    }
                    // Send a new message
                    $this->sendMessage($this->localization[$this->language][$messagetoshow], $keyboard);
                    // If the user is already registred we need to update the message_id in this user's row
                    if ($isregistred) {
                        $this->redis->delete($this->chat_id . ':message_id', $this->chat_id . ':selected_contact');
                        $this->redis->set($this->chat_id . ':status', MENU);
                    }
                    // Inline query button send /start show_ab
                } elseif (strpos($string[1], 'show_ab') === 0) {
                    if ($this->database->getContactRowOwnedByUser() > 0) {
                        $this->getOrder();
                        $this->getIndexAddressbook();
                        $list = $this->database->getList();
                        $container = &$this->database->getABList();
                        $this->sendMessage($container['string'], $this->inline_keyboard->database->getListInlineKeyboard($list, $container['usernames']));
                    } else {
                        $this->sendMessage($this->localization[$this->language]['AddressBookEmty_Msg'], $this->inline_keyboard->getABEmptyInlineKeyboard());
                    }
                    $this->redis->delete($this->chat_id . ':message_id', $this->chat_id . ':selected_contact');
                    $this->redis->set($this->chat_id . ':status', SHOW_AB);
                }
            } elseif (strpos($text, '/help') === 0) {
                // Send help message
                $this->sendMessage($this->localization[$this->language]['Help_Msg']);
            } elseif (strpos($text, '/about') === 0) {
                // Send about message
                $this->inline_keyboard->addLevelButtons(['text' => $this->localization[$this->language]['Vote_Button'], 'url' => 'https://telegram.me/storebot?start=myaddressbookbot'], ['text' => 'HadesWrapper', 'url' => 'https://gitlab.com/WiseDragonStd/HadesWrapper']);
                $this->sendMessageKeyboard($this->localization[$this->language]['About_Msg'], $this->inline_keyboard->getKeyboard());
            } elseif (strpos($text, '/') === 0 && strlen($text) > 5) {
                if($this->redis->exists($this->chat_id . ':search_query')) {
                    $this->redis->delete($this->chat_id . ':search_query');
                    $this->redis->delete($this->chat_id . ':index_search');
                }
                $username = explode(' ', $text);
                $username = str_replace('/', '', $username[0]);
                $this->selected_contact = $this->database->checkContactExist($username);
                if($this->selected_contact > 0) {
                    $row = &$this->database->getContactRowByID();
                    $this->sendMessage($this->getContactInfoByRow($row), $this->inline_keyboard->getEditContactInlineKeyboard($row));
                    $this->redis->delete($this->chat_id . ':message_id');
                    $this->redis->set($this->chat_id . ':status', SHOW_CONTACT);
                    $this->redis->set($this->chat_id . ':selected_contact', $this->selected_contact);
                } else {
                    $string = $text . $this->localization[$this->language]['ContactNotExist_Msg'];
                    $this->sendMessage($string, $this->inline_keyboard->getContactNotValidInlineKeyboard());
                    $this->redis->delete($this->chat_id . ':message_id');
                    $this->redis->set($this->chat_id . ':status', SHOW_CONTACT_NOTVALID);
                }
            } else {
                // If the message has been forwarded and the user who originally sent it isn't the user that is using the bot
                $this->getStatus();
                $message_id = $this->redis->get($this->chat_id . ':message_id');
                // If the message isn't a command then the user sent data about adding or modifying the contacts of his addressbook
                switch ($this->status) {
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
                                $contact = $this->database->checkContactExist($text);
                                if ($contact === 0) {
                                    // If the owner of the address book has a username check it is different from the username he is adding
                                    if ((isset($message['from']['username']) && ($text !== ($message['from']['username']))) || !isset($message['from']['username'])) {
                                        $this->redis->hset($this->chat_id . ':contact', 'username', $text);
                                        $this->editMessageText($this->localization[$this->language]['Username_Msg'] . '@' . $text, $message_id);
                                        $new_message = &$this->sendReplyMessageKeyboard($this->localization[$this->language]['AddFirstName_Msg'],  $this->inline_keyboard->getBackInlineKeyboard(), $message['message_id']);
                                        $this->status = $status2 ?? ADDING_FIRSTNAME_AB;
                                        $this->redis->set($this->chat_id . ':status', $this->status);
                                        $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                                    } else {
                                        if ($this->redis->exists($this->chat_id . ':easter_egg')) {
                                            $this->sendMessageRef($this->localization[$this->language]['AddHisSelf_EasterEgg']);
                                            $this->sendStickerRef(ADDING_HISSELF_STICKER_ID);
                                            $new_message = &$this->sendMessage($this->localization[$this->language]['AddUsername_Msg'], $this->inline_keyboard->getBackInlineKeyboard());
                                            $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                                            $this->redis->delete($this->chat_id . ':easter_egg');
                                        } else {
                                            $string = $this->localization[$this->language]['AddHisSelf_Msg'] . ' ' . $this->localization[$this->language]['AddUsername_Msg'];
                                            $new_message = $this->sendReplyMessageKeyboard($string, $this->inline_keyboard->getBackInlineKeyboard(), $message['message_id']);
                                            $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                                        }
                                    }
                                } else {
                                    $username = $this->database->getUsernameFromID($this->chat_id, $contact, $this->pdo);
                                    $string = '/' . $username . $this->localization[$this->language]['ContactAlreadyExist_Msg'] . NEWLINE . $this->localization[$this->language]['ResendUsername_Msg'];
                                    $new_message = &$this->sendReplyMessageKeyboard($string, $this->inline_keyboard->getContactNotValidInlineKeyboard(), $message['message_id']);
                                    $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                                    $this->redis->set($this->chat_id . ':status', SHOW_CONTACT_NOTVALID);
                                }
                            } else {
                                $string = $this->localization[$this->language]['UsernameLenght_Msg'] . NEWLINE . $this->localization[$this->language]['ResendUsername_Msg'];
                                $new_message = $this->sendReplyMessageKeyboard($string, $this->inline_keyboard->getBackInlineKeyboard($language), $message['message_id']);
                                $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                            }
                        } else {
                            $new_message = $this->sendReplyMessageKeyboard($this->localization[$this->language]['ResendUsername_Msg'], $this->inline_keyboard->getBackInlineKeyboard($language), $message['message_id']);
                            $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                        }
                        break;
                    case ADDING_FIRSTNAME_MENU:
                        $status2 = ADDING_LASTNAME_MENU;
                        // No break
                    case ADDING_FIRSTNAME_AB:
                        $this->redis->hset($this->chat_id . ':contact', 'first_name', $text);
                        $this->editMessageText($this->localization[$this->language]['FirstName_Msg'] . $text, $message_id);
                        $new_message = &$this->sendReplyMessageKeyboard($this->localization[$this->language]['AddLastName_Msg'], $this->inline_keyboard->getBackSkipInlineKeyboard(), $message['message_id']);
                        $this->status = $status2 ?? ADDING_LASTNAME_AB;
                        $this->redis->set($this->chat_id . ':status', $this->status);
                        $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                        break;
                    case ADDING_LASTNAME_MENU:
                        $status2 = ADDING_DESC_MENU;
                        // No break
                    case ADDING_LASTNAME_AB:
                        $this->redis->hset($this->chat_id . ':contact', 'last_name', $text);
                        $this->editMessageText($this->localization[$this->language]['LastName_Msg'] . $text, $message_id);
                        $new_message = &$this->sendReplyMessageKeyboard($this->localization[$this->language]['AddDescription_Msg'], $this->inline_keyboard->getBackSkipInlineKeyboard(), $message['message_id']);
                        $this->status = $status2 ?? ADDING_DESC_AB;
                        $this->redis->set($this->chat_id . ':status', $this->status);
                        $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                        break;
                    case ADDING_DESC_MENU:
                    case ADDING_DESC_AB:
                        $this->selected_contact = $this->database->getContactRowOwnedByUser();
                        $this->selected_contact++;
                        $row = $this->redis->hGetAll($this->chat_id . ':contact');
                        $row['id'] = &$this->selected_contact;
                        $row['description'] = &$text;
                        $this->database->saveContact($row);
                        $this->getOrder();
                        $this->getABIndexForContact();
                        $list = &$this->database->getList();
                        $container = &$this->database->getABList();
                        $this->editMessageText($this->localization[$this->language]['Description_Msg'] . $text, $message_id);
                        $new_message = &$this->sendMessage($container['string'], $this->inline_keyboard->getListInlineKeyboard($list, $container['usernames']));
                        $this->redis->delete($this->chat_id . ':contact');
                        $this->redis->set($this->chat_id . ':index_addressbook', $this->index_addressbook);
                        $this->redis->delete($this->chat_id . ':message_id');
                        $this->redis->set($this->chat_id . ':status', SHOW_AB);
                        break;
                    case EDITUSERNAME:
                        if (!strncmp($text, '@', 1)) {
                            $this->selected_contact = $this->redis->get($this->chat_id . ':selected_contact');
                            $string = explode(' ', $text);
                            $text = $string[0];
                            $text = preg_replace('/[^\w]/', '', $text);
                            if (strlen($text) > 5 && strlen($text) <= 32) {
                                $this->database->updateContactInfo('username', $text);
                                $row = &$this->database->getContactRowByID();
                                $this->editMessageText($this->localization[$this->language]['EditedUsername_Msg'] . '@' . $text, $message_id);
                                $new_message = &$this->sendMessage($this->getContactInfoByRow($row), $this->inline_keyboard->getEditContactInlineKeyboard($row));
                                $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                                $this->redis->set($this->chat_id . ':status', SHOW_CONTACT);
                                $this->getOrder();
                                if ($order === 'username') {
                                    $this->redis->set($this->chat_id . ':index_addressbook', $this->getABIndexForContact());
                                }
                            } else {
                                $string = $this->localization[$this->language]['UsernameLenght_Msg'] . NEWLINE . $this->localization[$this->language]['ResendUsername_Msg'];
                                $new_message = &$this->sendReplyMessageKeyboard($string, $this->inline_keyboard->getBackInlineKeyboard(), $message['message_id']);
                                $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                            }
                        } else {
                            $new_message = &$this->sendReplyMessageKeyboard($this->localization[$this->language]['ResendUsername_Msg'], $this->inline_keyboard->getBackInlineKeyboard(), $message['message_id']);
                            $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                        }
                        break;
                    case EDITFIRSTNAME:
                        $this->selected_contact = $this->redis->get($this->chat_id . ':selected_contact');
                        $this->database->updateContactInfo('first_name', $text);
                        $row = &$this->database->getContactRowByID();
                        $this->editMessageText($this->localization[$this->language]['EditedFirstName_Msg'] . $text, $message_id);
                        $new_message = &$this->sendMessage($this->getContactInfoByRow($row), $this->inline_keyboard->getEditContactInlineKeyboard($row));
                        $this->redis->set($this->chat_id . ':status', SHOW_CONTACT);
                        $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                        $this->redis->set($this->chat_id . ':index_addressbook', $this->getABIndexForContact());
                        break;
                    case EDITLASTNAME:
                        $this->selected_contact = $this->redis->get($this->chat_id . ':selected_contact');
                        $this->database->updateContactInfo('last_name', $text);
                        $row = &$this->database->getContactRowByID();
                        $this->editMessageText($this->localization[$this->language]['EditedLastName_Msg'] . $text, $message_id);
                        $new_message = &$this->sendMessage($this->getContactInfoByRow($row), $this->inline_keyboard->getEditContactInlineKeyboard($row));
                        $this->redis->set($this->chat_id . ':status', SHOW_CONTACT);
                        $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                        $this->redis->set($this->chat_id . ':index_addressbook', $this->getABIndexForContact());
                        break;
                    case EDITDESC:
                        $this->selected_contact = $this->redis->get($this->chat_id . ':selected_contact');
                        $this->database->updateContactInfo('desc', $text);
                        $row = &$this->database->getContactRowByID($this->chat_id, $this->selected_contact, $this->pdo);
                        $this->editMessageText($this->localization[$this->language]['EditedDescription_Msg'] . $text, $message_id);
                        $new_message = &$this->sendMessage($this->getContactInfoByRow($row), $this->inline_keyboard->getEditContactInlineKeyboard($row));
                        $this->redis->set($this->chat_id . ':status', SHOW_CONTACT);
                        $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                        break;
                    case ADDING_LASTNAME_CONTACT:
                        $this->selected_contact = $this->redis->get($this->chat_id . ':selected_contact');
                        $this->database->updateContactInfo('last_name', $text);
                        $row = &$this->database->getContactRowByID();
                        $this->editMessageText($this->localization[$this->language]['EditedLastName_Msg'] . $text, $message_id);
                        $new_message = &$this->sendMessage($this->getContactInfoByRow($row), $this->inline_keyboard->getEditContactInlineKeyboard($row));
                        $this->redis->set($this->chat_id . ':status', SHOW_CONTACT);
                        $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                        $this->redis->set($this->chat_id . ':index_addressbook', $this->getABIndexForContact());
                        break;
                    case ADDING_DESC_CONTACT:
                        $this->selected_contact = $this->redis->get($this->chat_id . ':selected_contact');
                        $this->database->updateContactInfo('desc', $text);
                        $row = &$this->database->getContactRowByID($this->chat_id, $selected_contact, $this->pdo);
                        $this->editMessageText($message_id, $this->localization[$this->language]['EditedDescription_Msg'] . $text);
                        $new_message = &$this->sendMessage($this->getContactInfoByRow($row), $this->inline_keyboard->getEditContactInlineKeyboard($row));
                        $this->redis->set($this->chat_id . ':status', SHOW_CONTACT);
                        $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                        break;
                    case ADDING_DESC_SAVE:
                        $row = $this->redis->hGetAll($this->chat_id . ':forward');
                        if (isset($row)) {
                            $this->selected_contact = $this->database->getContactRowOwnedByUser();
                            $this->selected_contact++;
                            $row['id'] = &$this->selected_contact;
                            $row['desc'] = &$text;
                            $this->database->saveContact($row);
                            $this->getOrder();
                            $this->getABIndexForContact();
                            $list = &$this->database->getList();
                            $container = &$this->database->getABList();
                            $this->editMessageText($this->localization[$this->language]['Description_Msg'] . $text, $message_id);
                            $new_message = &$this->sendMessage($container['string'], $this->inline_keyboard->getListInlineKeyboard($list, $container['usernames']));
                            $this->redis->set($this->chat_id . ':status', SHOW_AB);
                            $this->redis->delete($this->chat_id . ':selected_contact');
                            $this->redis->set($this->chat_id . ':index_addressbook', $this->index_addressbook);
                            $this->redis->set($this->chat_id . ':message_id', $new_message['message_id']);
                            $this->redis->delete($this->chat_id . ':forward');
                        } else {
                            $isregistred = $this->database->isUserRegistered();
                            // Choose the message to show after the welcome
                            $messagetoshow = $isregistred ? 'Menu_Msg' : 'Welcome_Msg';
                            $id = $this->database->getContactRowOwnedByUser();
                            // Choose the inline keyboard to insert
                            if ($isregistred) {
                                if($id > 0) {
                                    $keyboard = &$this->inline_keyboard->getMenuInlineKeyboard();
                                } else {
                                    $keyboard = &$this->inline_keyboard->getAddInlineKeyboard();
                                }
                            } else {
                                $keyboard = &$this->inline_keyboard->getChooseLanguageStartInlineKeyboard();
                            }
                            // Send a new message
                            $this->sendMessage($this->localization[$this->language][$messagetoshow], $keyboard);
                            // If the user is already registred we need to update the message_id in this user's row
                            if ($isregistred) {
                                $this->redis->delete($this->chat_id . ':message_id', $this->chat_id . ':selected_contact');
                                $this->redis->set($this->chat_id . ':status', MENU);
                            }
                        }
                        break;
                    case ADDING_SEARCH_QUERY:
                        $page = 1;
                        $this->getOrder();
                        $this->editMessageText($this->localization[$this->language]['EnterSearchQuery_Msg'] . "\"<b>$text</b>\"", $message_id);
                        $container = &$this->database->getSearchResults($text, $page);
                        $list = &$this->database->getListResults($text);
                        if($list > 0) {
                            $this->index_addressbook = 1;
                            $this->sendReplyMessageKeyboard($container['string'], $this->inline_keyboard->getListInlineKeyboard($page, $container['usernames'], 'search'), $message['message_id']);
                            $this->redis->setEx($this->chat_id . ':search_query', 10800, $text);
                            $this->redis->setEx($this->chat_id . ':index_search', 10800, $page);
                        } else {
                            $string = $this->localization[$this->language]['ResultsNull_Msg'] . "\"<b>$text</b>\"";
                            $this->sendReplyMessageKeyboard($string, $this->inline_keyboard->getSearchNullInlineKeyboard(), $message['message_id']);
                        }
                        $this->redis->delete($this->chat_id . ':message_id');
                        $this->redis->set($this->chat_id . ':status', SHOW_RESULTS);
                        break;
                    case NOSTATUS:
                        $id = $this->database->getContactRowOwnedByUser();
                        if($id > 0) {
                            $keyboard = &$this->inline_keyboard->getMenuInlineKeyboard();
                        } else {
                            $keyboard = &$this->inline_keyboard->getAddInlineKeyboard();
                        }
                        $this->sendMessage($this->localization[$this->language][$messagetoshow], $keyboard);
                        break;
                    default:
                        break;
                }
            }
        }
    }

    protected function processCallbackQuery() {
        $callback_query = &$this->update['callback_query'];
        $this->chat_id = &$callback_query['from']['id'];
        $message_id = $callback_query['message']['message_id'] ?? null;
        $inline_message_id = $callback_query['inline_message_id'] ?? null;
        $data = $callback_query['data'];
        if (isset($data) && isset($this->chat_id)) {
            $this->getLanguage();
            echo $data;
            if (isset($message_id)) {
                switch ($data) {
                    case 'show/ab':
                        if($this->redis->exists($this->chat_id . ':search_query')) {
                            $this->redis->delete($this->chat_id . ':search_query');
                            $this->redis->delete($this->chat_id . ':index_search');
                        }
                        if ($this->database->getContactRowOwnedByUser() > 0) {
                            $this->getOrder();
                            $this->getIndexAddressbook();
                            $list = $this->database->getList();
                            $container = &$this->database->getABList();
                            $this->editMessageTextKeyboard($container['string'], $this->inline_keyboard->getListInlineKeyboard($list, $container['usernames']), $message_id);
                        } else {
                            $this->editMessageTextKeyboard($this->localization[$this->language]['AddressBookEmty_Msg'], $this->inline_keyboard->getABEmptyInlineKeyboard(), $message_id);
                        }
                        $this->redis->set($this->chat_id . ':status', SHOW_AB);
                        $this->redis->delete($this->chat_id . ':selected_contact');
                        $this->answerEmptyCallbackQuery();
                        break;
                    case 'menu':
                        if($this->redis->exists($this->chat_id . ':search_query')) {
                            $this->redis->delete($this->chat_id . ':search_query');
                            $this->redis->delete($this->chat_id . ':index_search');
                        }
                        $spaces = $this->database->getContactRowOwnedByUser();
                        if ($spaces > 0) {
                            $keyboard = &$this->inline_keyboard->getMenuInlineKeyboard();
                        } else {
                            $keyboard = &$this->inline_keyboard->getAddInlineKeyboard();
                        }
                        $this->editMessageTextKeyboard($this->localization[$this->language]['Menu_Msg'], $keyboard, $message_id);
                        $this->redis->set($this->chat_id . ':status', SHOW_AB);
                        $this->redis->delete($this->chat_id . ':selected_contact');
                        $this->answerEmptyCallbackQuery();
                        break;
                    case 'add':
                        $this->editMessageTextKeyboard($this->localization[$this->language]['AddUsername_Msg'], $this->inline_keyboard->getBackInlineKeyboard(), $message_id);
                        switch ($this->getStatus()) {
                            case SHOW_AB:
                            $this->redis->set($this->chat_id . ':status', ADDING_USERNAME_AB);
                            break;
                        case MENU:
                            $this->redis->set($this->chat_id . ':status', ADDING_USERNAME_MENU);
                            break;
                        default:
                            $this->redis->set($this->chat_id . ':status', ADDING_USERNAME_AB);
                            break;
                        }
                        $this->answerEmptyCallbackQuery();
                        break;
                    case 'back':
                        switch ($this->getStatus()) {
                            case OPTIONS:
                            case NOSTATUS:
                            // No break
                            case ADDING_USERNAME_MENU:
                                $rows = $this->database->getContactRowOwnedByUser();
                                if($rows > 0) {
                                    $keyboard = &$this->inline_keyboard->getMenuInlineKeyboard();
                                } else {
                                    $keyboard = &$this->inline_keyboard->getAddInlineKeyboard();
                                }
                                $this->editMessageTextKeyboard($this->localization[$this->language]['Menu_Msg'], $keyboard, $message_id);
                                $this->redis->set($this->chat_id . ':status', MENU);
                                $this->answerEmptyCallbackQuery();
                                break;
                            case ADDING_USERNAME_AB:
                                if ($this->database->getContactRowOwnedByUser() > 0) {
                                    $this->getIndexAddressbook();
                                    $this->getOrder();
                                    $list = $this->database->getList();
                                    $container = &$this->database->getABList();
                                    $this->editMessageTextKeyboard($container['string'], $this->inline_keyboard->getListInlineKeyboard($list, $container['usernames']), $message_id);
                                } else {
                                    $this->editMessageTextKeyboard($this->localization[$this->language]['AddressBookEmty_Msg'], $this->inline_keyboard->getABEmptyInlineKeyboard(), $message_id);
                                }
                                $this->redis->set($this->chat_id . ':status', SHOW_AB);
                                $this->answerEmptyCallbackQuery();
                                break;
                            case ADDING_FIRSTNAME_MENU;
                                $status2 = ADDING_USERNAME_MENU;
                                // No break
                            case ADDING_FIRSTNAME_AB:
                                $this->editMessageTextKeyboard($this->localization[$this->language]['AddUsername_Msg'], $this->inline_keyboard->getBackInlineKeyboard(), $message_id);
                                $this->redis->delete($this->chat_id . ':contact');
                                $this->status = $status2 ?? ADDING_USERNAME_AB;
                                $this->redis->set($this->chat_id . ':status', $this->status);
                                $this->answerEmptyCallbackQuery();
                                break;
                            case ADDING_LASTNAME_MENU:
                                $status2 = ADDING_FIRSTNAME_MENU;
                                // No break
                            case ADDING_LASTNAME_AB:
                                $this->editMessageTextKeyboard($this->localization[$this->language]['AddFirstName_Msg'], $this->inline_keyboard->getBackInlineKeyboard(), $message_id);
                                $this->redis->delete($this->chat_id . ':contact', 'first_name');
                                $this->status = $status2 ?? ADDING_FIRSTNAME_AB;
                                $this->redis->set($this->chat_id . ':status', $this->status);
                                $this->answerEmptyCallbackQuery();
                                break;
                            case ADDING_DESC_MENU:
                                $status2 = ADDING_LASTNAME_MENU;
                                // No break
                            case ADDING_DESC_AB:
                                $this->editMessageTextKeyboard($this->localization[$this->language]['AddLastName_Msg'], $this->inline_keyboard->getBackSkipInlineKeyboard(), $message_id);
                                $this->redis->delete($this->chat_id . ':contact', 'last_name');
                                $this->status = $status2 ?? ADDING_LASTNAME_AB;
                                $this->redis->set($this->chat_id . ':status', $this->status);
                                $this->answerEmptyCallbackQuery();
                                break;
                            case DELETE_AB_PROMPT:
                            case CHOOSE_ORDER:
                                $suffix2 = 'show/ab';
                                // No break
                            case CHOOSE_LANGUAGE:
                                $suffix = $suffix2 ?? 'menu';
                                $this->editMessageTextKeyboard($this->localization[$this->language]['Options_Msg'], $this->inline_keyboard->getOptionsInlineKeyboard($suffix), $message_id);
                                $this->answerCallbackQueryRef($this->localization[$this->language]['Options_AnswerCallback']);
                                $this->redis->set($this->chat_id . ':status', OPTIONS);
                                break;
                            case EDITUSERNAME:
                            case EDITFIRSTNAME:
                            case EDITLASTNAME:
                            case EDITDESC:
                            case ADDING_LASTNAME_CONTACT:
                            case ADDING_DESC_CONTACT:
                            case DELETE_AS_PROMPT:
                                $this->selected_contact = $this->redis->get($this->chat_id . ':selected_contact');
                                $row = &$this->database->getContactRowByID();
                                $this->editMessageTextKeyboard($this->getContactInfoByRow($row), $this->inline_keyboard->getEditContactInlineKeyboard($row), $message_id);
                                $this->redis->set($this->chat_id . ':status', $this->status);
                                $this->answerEmptyCallbackQuery();
                                break;
                            case ADDING_DESC_SAVE:
                            case SAVING_USER_PROMPT:
                                if($this->redis->exists($this->chat_id . ':search_query')) {
                                    $this->redis->delete($this->chat_id . ':search_query');
                                    $this->redis->delete($this->chat_id . ':index_search');
                                }
                                // If the index is 0 (it means this is the first time the user see the list) set the index to 1
                                if ($this->database->getContactRowOwnedByUser() > 0) {
                                    $this->getOrder();
                                    $this->getIndexAddressbook();
                                    $list = $this->database->getList();
                                    $container = &$this->database->getABList();
                                    $this->editMessageTextKeyboard($container['string'], $this->inline_keyboard->getListInlineKeyboard($list, $container['usernames']), $message_id);
                                } else {
                                    $this->editMessageTextKeyboard($this->localization[$this->language]['AddressBookEmty_Msg'], $this->inline_keyboard->getABEmptyInlineKeyboard(), $message_id);
                                }
                                $this->redis->set($this->chat_id . ':status', SHOW_AB);
                                $this->redis->delete($this->chat_id . ':forward');
                                $this->answerEmptyCallbackQuery();
                                break;
                        }
                        break;
                    case 'skip':
                        switch ($this->getStatus()) {
                            // If the user want to skip the insertion of the last name or the description just put the status to the next step according to which one is currently
                            case ADDING_LASTNAME_MENU:
                                $status2 = ADDING_DESC_MENU;
                                // No break
                            case ADDING_LASTNAME_AB:
                                $this->editMessageText($this->localization[$this->language]['LastName_Msg'] . $this->localization[$this->language]['Skipped_Msg'] . NEWLINE . $this->localization[$this->language]['AddDescription_Msg'],  $message_id, $this->inline_keyboard->getBackSkipInlineKeyboard());
                                $this->status = $status2 ?? ADDING_DESC_AB;
                                $this->redis->set($this->chat_id . ':status', $this->status);
                                $this->redis->hset($this->chat_id . ':contact', 'last_name', 'NULL');
                                $this->answerCallbackQuery($this->localization[$this->language]['LastName_AnswerCallback'] . $this->localization[$this->language]['Skipped_AnswerCallback']);
                                break;
                            case ADDING_DESC_SAVE:
                            case ADDING_DESC_AB:
                            // No break
                            case ADDING_DESC_MENU:
                                $this->selected_contact = $this->database->getContactRowOwnedByUser();
                                $this->selected_contact++;
                                $row = $this->redis->hGetAll($this->chat_id . ':contact');
                                $row['id'] = &$this->selected_contact;
                                $row['description'] = 'NULL';
                                $this->database->saveContact($row);
                                $this->getOrder();
                                $this->getABIndexForContact();
                                $list = $this->database->getList();
                                $container = &$this->database->getABList();
                                $this->editMessageText($container['string'], $message_id, $this->inline_keyboard->getListInlineKeyboard($list, $container['usernames']));
                                $this->redis->set($this->chat_id . ':status', SHOW_AB);
                                $this->redis->delete($this->chat_id . ':contact');
                                $this->answerCallbackQueryRef($this->localization[$this->language]['ContactAdded_AnswerCallback']);
                                break;
                            default:
                            $id = $this->database->getContactRowOwnedByUser();
                            if($id > 0) {
                                $keyboard = &$this->getMenuInlineKeyboard();
                            } else {
                                $keyboard = &$this->getAddInlineKeyboard();
                            }
                            $this->sendMessage($this->localization[$this->language][$messagetoshow], $keyboard);
                            $this->answerEmptyCallbackQuery();
                            break;
                        }
                        break;
                    case 'options/menu':
                        $status2 = OPTIONS;
                        $suffix2 = 'menu';
                        // No break
                    case 'options/ab':
                        if($this->redis->exists($this->chat_id . ':search_query')) {
                            $this->redis->delete($this->chat_id . ':search_query');
                            $this->redis->delete($this->chat_id . ':index_search');
                        }
                        $suffix = $suffix2 ?? 'show/ab';
                        $this->editMessageTextKeyboard($this->localization[$this->language]['Options_Msg'], $this->inline_keyboard->getOptionsInlineKeyboard($suffix), $message_id);
                        $this->status = $status2 ?? OPTIONS;
                        $this->redis->set($this->chat_id . ':status', $this->status);
                        $this->answerCallbackQuery($this->localization[$this->language]['Options_AnswerCallback']);
                        break;
                    case 'language':
                        $this->editMessageTextKeyboard($this->localization[$this->language]['ChooseLanguage_Msg'], $this->inline_keyboard->getChooseLanguageInlineKeyboard(), $message_id);
                        $this->redis->set($this->chat_id . ':status', CHOOSE_LANGUAGE);
                        $this->answerEmptyCallbackQuery();
                        break;
                    case 'same/language':
                        $this->answerCallbackQueryRef($this->localization[$this->language]['SameLanguage_AnswerCallback']);
                        break;
                    case 'search':
                        $this->editMessageTextKeyboard($this->localization[$this->language]['EnterSearchQuery_Msg'], $this->inline_keyboard->getBackSearchInlineKeyboard(), $message_id);
                        $this->redis->set($this->chat_id . ':status', ADDING_SEARCH_QUERY);
                        $this->answerEmptyCallbackQuery();
                        break;
                    case 'back/search':
                        $this->index_addressbook = $this->redis->get($this->chat_id . ':index_search');
                        $search_query = $this->redis->get($this->chat_id . ':search_query');
                        $this->getOrder();
                        $container = &$this->database->getSearchResults($search_query);
                        $list = $this->database->getListResults($search_query);
                        if($list > 0) {
                            $this->editMessageTextKeyboard($container['string'], $this->inline_keyboard->getListInlineKeyboard($list, $container['usernames'], 'search'), $message_id);
                        } else {
                            $this->editMessageTextKeyobard($this->localization[$this->language]['ResultsNull_Msg'] . "\"$search_query\"", $this->inline_keyboard->getContactNotValidInlineKeyboard(), $message_id);
                        }
                        $this->redis->set($this->chat_id . ':status', SHOW_RESULTS);
                        $this->redis->delete($this->chat_id . ':selected_contact');
                        $this->answerEmptyCallbackQuery();
                        break;
                    case 'update/username':
                        $this->selected_contact = $this->redis->get($this->chat_id . ':selected_contact');
                        $row = &$this->database->getContactRowByID();
                        if (isset($row['id_contact'])) {
                            $chat = &$this->getChatRef($row['id_contact']);
                            $new_username = $chat['username'];
                        }
                        if (isset($new_username) && isset($chat) && !empty($chat) && $new_username !== $row['username'] && !isset($chat['error_code'])) {
                            $sth = $this->pdo->prepare('UPDATE "Contact" SET "username" = :username WHERE "id_contact" = :id_contact');
                            $sth->bindParam(':username', $new_username);
                            $sth->bindValue(':id_contact', $row['id_contact'], PDO::PARAM_INT);
                            $sth->execute();
                            $sth = null;
                            $this->editMessageTextKeyboard($this->getContactInfoByRow($row), $this->inline_keyboard->getEditContactInlineKeyboard($row), $messge_id);
                            $this->answerCallbackQueryRef($this->localization[$this->language]['UsernameUpdated_AsnwerCallback']);
                        } else {
                            $this->answerCallbackQueryRef($this->localization[$this->language]['UsernameAlreadyUpdated_AsnwerCallback']);
                        }
                        break;
                    case 'update/all':
                        $sth = $this->pdo->prepare('SELECT DISTINCT "id_contact", "username" FROM (SELECT "id_contact", "username" FROM "Contact" WHERE "id_owner" = :chat_id) AS T WHERE NOT ("id_contact" IS NULL)');
                        $sth->bindParam(':chat_id', $this->chat_id);
                        $sth->execute();
                        $sth2 = $this->pdo->prepare('UPDATE "Contact" SET "username" = :username WHERE "id_contact" = :id_contact');
                        $updates = [['newdata' => []]];
                        while($row = $sth->fetch()) {
                            $chat = $this->getChatRef($row['id_contact']);
                            if (isset($chat) && !empty($chat) && isset($chat['username']) && ($chat['username'] !== $row['username'])) {
                                $sth2->bindParam(':username', $chat['username']);
                                $sth2->bindValue(':id_contact', $row['id_contact'], PDO::PARAM_INT);
                                $sth2->execute();
                            } elseif(isset($chat) && !empty($chat) && !isset($chat['username']) && !isset($chat['error_code'])) {
                                $username = 'NoUsername';
                                $sth2->bindParam(':username', $username);
                                $sth2->bindValue(':id_contact', $row['id_contact'], PDO::PARAM_INT);
                                $sth2->execute();
                            }
                        }
                        $sth2 = null;
                        $sth = null;
                        $this->answerCallbackQueryRef($this->localization[$this->language]['UsernamesUpdated_AsnwerCallback']);
                        break;
                    case 'save':
                        $this->selected_contact = $this->database->getContactRowOwnedByUser();
                        $this->selected_contact++;
                        $row = $this->redis->hGetAll($this->chat_id . ':forward');
                        $row['id'] = &$this->selected_contact;
                        $row['desc'] = 'NULL';
                        $this->database->saveContact($row);
                        $this->getOrder();
                        $this->getABIndexForContact();
                        $list = &$this->database->getList();
                        $container = &$this->database->getABList();
                        $this->editMessageTextKeyboard($container['string'], $this->inline_keyboard->getListInlineKeyboard($list, $container['usernames']), $message_id);
                        $this->answerCallbackQueryRef($this->localization[$this->language]['Saved_AnswerCallback']);
                        $this->redis->set($this->chat_id . ':index_addressbook', $this->index_addressbook);
                        $this->redis->set($this->chat_id . ':status', SHOW_AB);
                        $this->redis->delete($this->chat_id . ':forward');
                        break;
                    case 'order':
                        $this->editMessageTextKeyboard($this->localization[$this->language]['Order_Msg'], $this->inline_keyboard->getOrderInlineKeyboard(), $message_id);
                        $this->redis->set($this->chat_id . ':status', CHOOSE_ORDER);
                        $this->answerEmptyCallbackQuery();
                        break;
                    default:
                        $string = explode('/', $data);
                        if (strpos($string[0], 'id') !== false) {
                            $this->selected_contact = $string[1];
                            $suffix = $string[2];
                            $this->redis->set($this->chat_id . ':status', SHOW_CONTACT);
                            $this->redis->set($this->chat_id . ':selected_contact', $this->selected_contact);
                            $row = &$this->database->getContactRowByID();
                            $this->editMessageTextKeyboard($this->getContactInfoByRow($row), $this->inline_keyboard->getEditContactInlineKeyboard($row), $message_id);
                            $this->answerCallbackQuery($this->localization[$this->language]['ShowingContact_AsnwerCallback'] . $row['first_name'] .  (($row['last_name'] !== 'NULL') ? (' ' . $row['last_name']) : ''));
                        } elseif (strpos($string[0], 'ab') !== false) {
                            $this->index_addressbook = $string[1];
                            $this->redis->set($this->chat_id . ':index_addressbook', $this->index_addressbook);
                            $list = $this->database->getList();
                            $this->getOrder();
                            $container = &$this->database->getABList();
                            $this->editMessageTextKeyboard($container['string'], $this->inline_keyboard->getListInlineKeyboard($list, $container['usernames']), $message_id);
                            $this->answerEmptyCallbackQuery();
                        } elseif (strpos($string[0], 'edit') !== false) {
                            $info = $string[1];;
                            switch ($info) {
                                case 'username':
                                    $this->editMessageTextKeyboard($this->localization[$this->language]['EditUsername_Msg'], $this->inline_keyboard->getBackInlineKeyboard(), $message_id);
                                    $this->redis->set($this->chat_id . ':message_id', $message_id);
                                    $this->redis->set($this->chat_id . ':status', EDITUSERNAME);
                                    break;
                                case 'firstname':
                                    $this->editMessageTextKeyboard($this->localization[$this->language]['EditFirstName_Msg'], $this->inline_keyboard->getBackInlineKeyboard(), $message_id);
                                    $this->redis->set($this->chat_id . ':message_id', $message_id);
                                    $this->redis->set($this->chat_id . ':status', EDITFIRSTNAME);
                                    break;
                                case 'lastname':
                                    $this->editMessageTextKeyboard($this->localization[$this->language]['EditLastName_Msg'], $this->inline_keyboard->getBackDeleteInlineKeyboard(true), $message_id);
                                    $this->redis->set($this->chat_id . ':message_id', $message_id);
                                    $this->redis->set($this->chat_id . ':status', EDITLASTNAME);
                                    break;
                                case 'desc':
                                    $this->editMessageTextKeyboard($this->localization[$this->language]['EditDescription_Msg'], $this->inline_keyboard->getBackDeleteInlineKeyboard(false), $message_id);
                                    $this->redis->set($this->chat_id . ':message_id', $message_id);
                                    $this->redis->set($this->chat_id . ':status', EDITDESC);
                                    break;
                                case 'contact':
                                    $this->selected_contact = $this->database->getContactRowOwnedByUser();
                                    $this->selected_contact++;
                                    $row = $this->redis->hGetAll($this->chat_id . ':forward');
                                    $row['id'] = &$this->selected_contact;
                                    $row['desc'] = 'NULL';
                                    $this->database->saveContact($row);
                                    $this->editMessageTextKeyboard($this->getContactInfoByRow($row), $this->inline_keyboard->getEditContactInlineKeyboard($row), $message_id);
                                    $this->getOrder();
                                    $this->getABIndexForContact();
                                    $this->redis->set($this->chat_id . ':status',  SHOW_CONTACT);
                                    $this->redis->set($this->chat_id . ':index_addressbook', $this->index_addressbook);
                                    $this->redis->set($this->chat_id . ':selected_contact', $this->selected_contact);
                                    $this->redis->delete($this->chat_id . ':forward');
                                    break;
                                default:
                                break;
                            }
                            $this->answerEmptyCallbackQuery();
                        } elseif (strpos($string[0], 'add') !== false) {
                            $info = $string[1];
                            switch ($info) {
                                case 'lastname':
                                    $this->editMessageTextKeyboard($this->localization[$this->language]['AddLastName_Msg'], $this->inline_keyboard->getBackInlineKeyboard(), $message_id);
                                    $this->redis->set($this->chat_id . ':status', ADDING_LASTNAME_CONTACT);
                                    $this->redis->set($this->chat_id . ':message_id', $message_id);
                                    break;
                                case 'desc':
                                    $this->editMessageTextKeyboard($this->localization[$this->language]['AddDescription_Msg'], $this->inline_keyboard->getBackInlineKeyboard($language), $message_id);
                                    $this->redis->set($this->chat_id . ':status', ADDING_DESC_CONTACT);
                                    $this->redis->set($this->chat_id . ':message_id', $message_id);
                                    break;
                                case 'desc&save':
                                    $row = $this->redis->hGetAll($this->chat_id . ':forward');
                                    $this->editMessageText($this->getContactInfoByRow($row) . $this->localization[$this->language]['AddDescription_Msg'], $message_id, $this->inline_keyboard->getCancelSkipInlineKeyBoard());
                                    $this->redis->set($this->chat_id . ':status', ADDING_DESC_SAVE);
                                    $this->redis->set($this->chat_id . ':message_id', $message_id);
                                    break;
                                default:
                                    break;
                            }
                            $this->answerEmptyCallbackQuery();
                        } elseif (strpos($string[0], 'delete') !== false) {
                            $info = $string[1];
                            switch ($info) {
                                case 'asprompt':
                                    $this->editMessageTextKeyboard($this->localization[$this->language]['DeleteASPrompt_Msg'], $this->inline_keyboard->getDeleteContactPromptInlineKeyboard(), $message_id);
                                    $this->redis->set($this->chat_id . ':status', DELETE_AS_PROMPT);
                                    $this->answerEmptyCallbackQuery();
                                    break;
                                case 'as':
                                    $max = $this->database->getContactRowOwnedByUser();
                                    $sth = $this->pdo->prepare('DELETE FROM "Contact" WHERE "id" = :selected_contact AND "id_owner" = :id_owner');
                                    $this->selected_contact = $this->redis->get($this->chat_id . ':selected_contact');
                                    $sth->bindParam(':selected_contact', $this->selected_contact);
                                    $sth->bindParam(':id_owner', $this->chat_id);
                                    $sth->execute();
                                    $sth = null;
                                    if ($max !== $this->selected_contact) {
                                        $temp = $this->selected->contact;
                                        $this->selected->contact = $max;
                                        $this->database->updateContactInfo('id', $temp);
                                    }
                                    if(!$this->redis->exists($this->chat_id . ':search_query')) {
                                        $spaces = $this->database->getContactRowOwnedByUser();
                                        if ($spaces > 0) {
                                            $list = $this->database->getList();
                                            $this->getIndexAddressbook();
                                            if ($this->index_addressbook > $list) {
                                                $this->index_addressbook--;
                                                $this->redis->set($this->chat_id . ':index_addressbook', $this->index_addressbook);
                                            }
                                            $this->getOrder();
                                            $container = &$this->database->getABList();
                                            $this->editMessageTextKeyboard($container['string'], $this->inline_keyboard->getListInlineKeyboard($list, $container['usernames']), $message_id);
                                        } else {
                                            $this->editMessageTextKeyboard($this->localization[$this->language]['AddressBookEmty_Msg'], $this->inline_keyboard->getABEmptyInlineKeyboard(), $message_id);
                                        }
                                        $this->redis->set($this->chat_id . ':status', SHOW_AB);
                                    } else {
                                        $this->index_addressbook = $this->redis->get($this->chat_id . ':index_search');
                                        $search_query = $this->redis->get($this->chat_id . ':search_query');
                                        $list = $this->database->getListResults($search_query);
                                        if ($this->index_addressbook > $list) {
                                            $this->index_addressbook--;
                                        }
                                        $this->getOrder();
                                        $container = &$this->database->getSearchResults($search_query);
                                        if($list > 0) {
                                           $this->editMessageTextKeyboard($container['string'], $this->inline_keyboard->getListInlineKeyboard($list, $container['usernames'], 'search'), $message_id);
                                        } else {
                                            $this->editMessageText($this->localization[$this->language]['ResultsNull_Msg'] . "\"$search_query\"", $message_id, $this->inline_keyboard->getContactNotValidInlineKeyboard());
                                        }
                                        $this->redis->set($this->chat_id . ':status', SHOW_RESULTS);
                                        $this->redis->set($this->chat_id . ':index_search', $this->index_addressbook);
                                    }
                                    $this->answerEmptyCallbackQuery();
                                    break;
                                case 'info':
                                    $this->selected_contact = $this->redis->get($this->chat_id . ':selected_contact');
                                    $this->getStatus();
                                    $this->getOrder();
                                    switch ($this->status) {
                                        case EDITLASTNAME:
                                            $this->database->updateContactInfo('last_name', 'NULL');
                                            $row = &$this->database->getContactRowByID($this->chat_id, $selected_contact, $this->pdo);
                                            $this->editMessageText($this->localization[$this->language]['DeletedLastName_Msg'] . NEWLINE . $this->getContactInfoByRow($row), $message_id, $this->inline_keyboard->getEditContactInlineKeyboard($row));
                                            $this->redis->set($this->chat_id . ':index_addressbook', $this->getABIndexForContact());
                                            $this->redis->set($this->chat_id . ':status', SHOW_CONTACT);
                                            break;
                                        case EDITDESC:
                                            $this->database->updateContactInfo('desc', 'NULL');
                                            $row = &$this->database->getContactRowByID();
                                            $this->editMessageText($this->localization[$this->language]['DeletedDescription_Msg'] . NEWLINE . $this->getContactInfoByRow($row), $message_id, $this->inline_keyboard->getEditContactInlineKeyboard($row));
                                            $this->redis->set($this->chat_id . ':status', SHOW_CONTACT);
                                            break;
                                        default:
                                            break;
                                    }
                                    $this->answerEmptyCallbackQuery();
                                    break;
                                case 'allprompt':
                                    $this->editMessageTextKeyboard($this->localization[$this->language]['DeleteAllContactsPrompt_Msg'], $this->inline_keyboard->getDeleteAllPromptInlineKeyboard(), $message_id);
                                    $this->redis->set($this->chat_id . ':status', DELETE_AB_PROMPT);
                                    $this->answerEmptyCallbackQuery();
                                    break;
                                case 'all':
                                    if ($this->database->getContactRowOwnedByUser() > 0) {
                                        $sth = $this->pdo->prepare('DELETE FROM "Contact" WHERE "id_owner" = :chat_id');
                                        $sth->bindParam(':chat_id', $this->chat_id);
                                        $sth->execute();
                                        $sth = null;
                                        $this->answerCallbackQueryRef($this->localization[$this->language]['AllContactsDeleted_AnswerCallback']);
                                    } else {
                                        $this->answerCallbackQueryRef($this->localization[$this->language]['NoContact_AnswerCallback']);
                                    }
                                    $suffix = 'show/ab';
                                    $this->editMessageTextKeyboard($this->localization[$this->language]['Options_Msg'], $this->inline_keyboard->getOptionsInlineKeyboard($suffix), $message_id);
                                    $this->answerCallbackQueryRef($this->localization[$this->language]['Options_AnswerCallback']);
                                    $this->redis->set($this->chat_id . ':status', OPTIONS);
                                    break;
                                default:
                                    break;
                            }
                        } elseif (strpos($string[0], 'cls') !== false) {
                            if(isset($this->language)) {
                                break;
                            }
                            $this->language = $string[1];
                            $sth = $this->pdo->prepare('INSERT INTO "User" ("chat_id", "language") VALUES (:chat_id, :language)');
                            $sth->bindParam(':chat_id', $this->chat_id);
                            $sth->bindParam(':language', $this->language);
                            $sth->execute();
                            $sth = null;
                            $this->editMessageTextKeyboard($this->localization[$this->language]['Menu_Msg'], $this->inline_keyboard->getAddInlineKeyboard(), $message_id);
                            $this->answerCallbackQueryRef($this->localization[$this->language]['Registered_AnswerCallback']);
                            $this->redis->setEx($this->chat_id . ':language', 86400, $this->language);
                            $this->redis->set($this->chat_id . ':status', MENU);
                            $this->redis->set($this->chat_id . ':index_addressbook', 1);
                            $this->redis->set($this->chat_id . ':easter_egg', 1);
                        } elseif (strpos($string[0], 'cl') !== false) {
                            $this->language = $string[1];
                            $this->setLanguage($string[1]);
                            $suffix = 'menu';
                            $this->editMessageTextKeyboard($this->localization[$this->language]['Options_Msg'], $this->inline_keyboard->getOptionsInlineKeyboard($suffix), $message_id);
                            $this->answerCallbackQueryRef($this->localization[$this->language]['LanguageChanged_AnswerCallback']);
                            $this->redis->set($this->chat_id . ':status', OPTIONS);
                        } elseif (strpos($string[0], 'search') !== false) {
                            $this->sendChatAction('typing');
                            $this->index_addressbook = $string[1];
                            $search_query = $this->redis->get($this->chat_id . ':search_query');
                            $this->getOrder();
                            $container = &$this->database->getSearchResults($search_query);
                            $list = $this->database->getListResults($search_query);
                            $this->editMessageText($this->index_addressbook . $container['string'], $message_id, $this->inline_keyboard->getListInlineKeyboard($list, $container['usernames'], 'search'));
                            $this->redis->set($this->chat_id . ':index_search', $this->index_addressbook);
                        } elseif (strpos($string[0], 'order') !== false) {
                            $suffix = 'show/ab';
                            $this->editMessageTextKeyboard($this->localization[$this->language]['Options_Msg'], $this->inline_keyboard->getOptionsInlineKeyboard($suffix), $message_id);
                            $this->answerCallbackQueryRef($this->localization[$this->language]['Options_AnswerCallback']);
                            $this->setOrder($string[1]);
                            $this->redis->set($this->chat_id . ':status', OPTIONS);
                        }
                        $this->answerEmptyCallbackQuery();
                        break;
                }
            } else if (isset($data) && isset($this->chat_id) && isset($id) && isset($inline_message_id)) {
                if (strpos($data, 'shared') !== false) {
                    if ($this->database->isUserRegistered($this->chat_id, $this->pdo)) {
                        $string = explode('/', $data);
                        if($this->redis->exists($this->chat_id . ':search_query')) {
                            $this->redis->delete($this->chat_id . ':search_query');
                            $this->redis->delete($this->chat_id . ':index_search');
                        }
                        $row = $this->redis->hGetAll('share:' . $string[1]);
                        if (!isset($callback_query['from']['username']) || ($row['username'] !== ($callback_query['from']['username']))) {
                            $this->selected_contact = $this->database->checkContactExist($row['username'], $row['id_contact']);
                            if ($htis->selected_contact === 0) {
                                $this->redis->hMSet($this->chat_id . ':forward', $row);
                                $string = getContactInfoByRowForInline($row) . NEWLINE . $this->localization[$this->language]['SaveContact_Msg'];
                                $this->sendMessage($string, $this->inline_keybboard->getSaveInlineKeyboard());
                                $this->redis->set($this->chat_id . ':status', SAVING_USER_PROMPT);
                            } else {
                                $username = $this->database->getUsernameFromID();
                                $string = '/' . $username . $this->localization[$this->language]['ContactAlreadyExist_Msg'];
                                $this->sendMessage($string, $this->inline_keyboard->getContactNotValidInlineKeyboard());
                                $this->redis->set($this->chat_id . ':status', SHOW_CONTACT_NOTVALID);
                            }
                        } elseif(isset($callback_query['from']['username']) && ($row['username'] === ($callback_query['from']['username']))) {
                            $new_message = &$this->sendMessage($this->localization[$this->language]['AddHisSelf_Msg'], $this->inline_keyboard->getContactNotValidInlineKeyboard());
                            $this->redis->set($this->chat_id . ':status', SHOW_CONTACT_NOTVALID);
                        }
                    } else {
                        $this->sendMessage($this->localization[$this->language]['Welcome_Msg'], $this->inline_keyboard->getChooseLanguageStartInlineKeyboard());
                    }
                }
                $this->answerCallbackQuertRef($this->localization[$this->language]['CheckPrivate_Msg'], true);
            }
        }
    }

    protected function processInlineQuery() {
        $inline_query = &$this->update['inline_query'];
        $this->chat_id = &$inline_query['from']['id'];
        $text = &$inline_query['query'];
        $this->getLanguage();
        $this->getOrder();
        $isregistred = $this->database->isUserRegistered();
        if ($isregistred) {
            if (isset($text) && $text !== '') {
                $sth = $this->pdo->prepare("SELECT \"username\", \"first_name\", \"last_name\", \"desc\", \"id\", \"id_contact\" FROM (SELECT \"username\", \"first_name\", \"last_name\", \"desc\", \"id\", \"id_contact\" FROM \"Contact\" WHERE \"id_owner\" = :chat_id) AS T WHERE \"first_name\" LIKE '$text%'  OR \"first_name\" LIKE '%$text%' OR \"last_name\" LIKE '$text%' OR \"last_name\" LIKE '%$text%' OR  CONCAT_WS(' ', \"first_name\", \"last_name\") LIKE '$text%' OR username LIKE '$text%' OR username LIKE '%$text%' OR username LIKE '@$text%' OR username LIKE '%@$text%' OR CONCAT_WS(' ', \"first_name\", \"last_name\") LIKE '%$text' OR CONCAT_WS(' ', \"last_name\", \"first_name\") LIKE '$text%' OR CONCAT_WS(' ', \"last_name\", \"first_name\") LIKE '%$text' ORDER BY " . $this->order . " LIMIT 50;");
            } else {
                $sth = $this->pdo->prepare("SELECT \"username\", \"first_name\", \"last_name\", \"desc\", \"id\", \"id_contact\" FROM \"Contact\" WHERE \"id_owner\" = :chat_id ORDER BY " . $this->order . " LIMIT 50");
            }
            $sth->bindParam(':chat_id', $this->chat_id);
            $sth->execute();
            $results = new \WiseDragonStd\HadesWrapper\InlineQueryResults();
            while ($row = $sth->fetch()) {
                $string = $row['first_name'];
                if (isset($row['last_name']) && ($row['last_name'] !== 'NULL')) {
                    $string = $string . ' ' . $row['last_name'];
                }
                $message_text = &$this->getContactInfoByRowForInline($row);
                $this->redis->hMSet($this->chat_id . ':temp' . $results->newArticleKeyboard($string, $message_text, $row['username'], $this->inline_keyboard->getSaveFromInlineInlineKeyboard()), $row);
            }
            $sth = null;
            $this->answerInlineQuerySwitchPMRef($results->getResults(), $this->localization[$this->language]['SwitchPM_InlineQuery'], 'show/ab');
        } else {
            $this->answerEmptyInlineQuerySwitchPMRef($this->localization[$this->language]['Register_InlineQuery']);
        }
    }

    protected function processInlineResult() {
        echo "FHBAUHFIu";
        $chosen_inline_result = &$this->update['chosen_inline_result'];
        $this->chat_id = &$inline_result['from']['id'];
        $this->getLanguage();
        $share =  $this->redis->get('share');
        $this->redis->incr('share');
        $share++;
        $this->redis->hMSet('share:' . $share, $this->redis->hGetAll($this->chat_id . ':temp' . $chosen_inline_result['result_id']));
        $this->editInlineMessageReplyMarkupRef($chosen_inline_result['inline_message_id'], $this->inline_keyboard->getShareInlineKeyboard($share));
    }

    public function &getABIndexForContact() {
        $sth = $this->pdo->prepare("SELECT \"id\" FROM \"Contact\" WHERE \"id_owner\" = :chat_id ORDER BY " . $this->order);
        $sth->bindParam(':chat_id', $this->chat_id);
        $sth->execute();
        $cont = 1;
        $position = 1;
        while ($row = $sth->fetch()) {
            if ($row['id'] == $this->selected_contact){
                $position = $cont;
                break;
            }
            $cont++;
        }
        $sth = null;
        $this->index_addressbook = (($position % SPACEPERVIEW) == 0 ? 0 : 1) + intval($position / SPACEPERVIEW);
        return $this->index_addressbook;
    }

    public function &getASInfoByID() {
        $sth = $this->pdo->prepare('SELECT "username", "first_name", "last_name", "desc" FROM "Contact" WHERE "id" = :id_as AND "id_owner" = :id_owner');
        $sth->bindParam(':id_as', $this->selected_contact);
        $sth->bindParam(':id_owner', $this->chat_id);
        $sth->execute();
        $row = $sth->fetch();
        if (!isset($row['username'])) {
            return null;
        }
        $string = $this->localization[$this->language]['Username_Msg'] . '@' . $row['username'] . NEWLINE . $this->localization[$this->language]['FirstName_Msg'] . $row['first_name'] . NEWLINE;
        if (isset($row['last_name']) && ($row['last_name'] !== 'NULL')) {
            $string = $string . $this->localization[$this->language]['LastName_Msg'] . $row['last_name'] . NEWLINE;
        }
        if (isset($row['desc']) && ($row['desc'] !== 'NULL')) {
            $string = $string . $this->localization[$this->language]['Description_Msg'] . $row['desc'] . NEWLINE;
        }
        $sth = null;
        $string = $string . '/' . $row['username'] . ' ' . NEWLINE;
        return $string;
    }

    public function &getContactInfoByRow(&$row) {
        $string = ROUNDS_DOWN . NEWLINE . $this->localization[$this->language]['Username_Msg'] . '@' . $row['username'] . NEWLINE . $this->localization[$this->language]['FirstName_Msg'] . $row['first_name'] . NEWLINE;
        if (isset($row['last_name']) && ($row['last_name'] !== 'NULL')) {
            $string = $string . $this->localization[$this->language]['LastName_Msg'] . $row['last_name'] . NEWLINE;
        }
        if (isset($row['desc']) && ($row['desc'] !== 'NULL')) {
        $string = $string . $this->localization[$this->language]['Description_Msg'] . '<i>' . $row['desc'] . '</i>' . NEWLINE;
        }
        $string = $string . '/' . $row['username'] . ' ' . NEWLINE . ROUNDS_UP . NEWLINE;
        return $string;
    }

    public function &getContactInfoByRowForInline(&$row) {
        $string = ROUNDS_DOWN . NEWLINE . $this->localization[$this->language]['Username_Msg'] . '@' . $row['username'] . NEWLINE . $this->localization[$this->language]['FirstName_Msg'] . $row['first_name'] . NEWLINE;
        if (isset($row['last_name']) && ($row['last_name'] !== 'NULL')) {
        $string = $string . $this->localization[$this->language]['LastName_Msg'] . $row['last_name'] . NEWLINE;
        }
        if (isset($row['desc']) && ($row['desc'] !== 'NULL')) {
            $string = $string . $this->localization[$this->language]['Description_Msg'] . '<i>' . $row['desc'] . '</i>' . NEWLINE;
        }
        $string = $string . ROUNDS_UP;
        return $string;
    }



    private function &getOrderString($order_number) {
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

    public function &getOrder() {
        $is_order_set = $this->redis->exists($this->chat_id . ':order');
        if ($is_order_set) {
            $this->order = $this->getOrderString($this->redis->get($this->chat_id . ':order'));
            return $this->order;
        } else {
            $sth = $this->pdo->prepare('SELECT "order" FROM "User" WHERE "chat_id" = :chat_id');
            $sth->bindParam(':chat_id', $this->chat_id);
            $sth->execute();
            $row = $sth->fetch();
            $sth = null;
            $this->redis->setEx($this->chat_id . ':order', 86400, $row['order']);
            $this->order = &$row['order'];
            return $this->getOrderString($row['order']);
        }
    }

    public function setOrder($new_order) {
        $sth = $this->pdo->prepare('UPDATE "User" SET "order" = :order WHERE "chat_id" = :chat_id');
        $sth->bindParam(':order', $new_order);
        $sth->bindParam(':chat_id', $this->chat_id);
        $sth->execute();
        $sth = null;
        $this->redis->setEx($this->chat_id . ':order', 86400, $new_order);
        $this->order = $new_order;
    }

    public function &getStatus() {
        $is_status_set = $this->redis->exists($this->chat_id . ':status');
        if ($is_status_set) {
            $this->status = $this->redis->get($this->chat_id . ':status');
            return $this->status;
        } else {
            $this->redis->set($this->chat_id . ':status', 0);
            $this->redis->set($this->chat_id . ':easter_egg', 1);
            $this->status = -1;
            return -1;
        }
    }

    public function &getIndexAddressbook() {
        $is_index_set = $this->redis->exists($this->chat_id . ':index_addressbook');
        if ($is_index_set) {
            $this->index_addressbook = $this->redis->get($this->chat_id . ':index_addressbook');
            return $this->index_addressbook;
        } else {
            $this->redis->set($this->chat_id . ':index_addressbook', 1);
            $this->index_addressbook = 1;
            return 1;
        }
    }
}

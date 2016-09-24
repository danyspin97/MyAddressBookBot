<?php

require './vendor/autoload.php';
require 'lib/myaddressbookbot/myaddressbookbot.php';
require 'lib/myaddressbookbot/database.php';
require 'lib/myaddressbookbot/inline_keyboard.php';
require 'lib/myaddressbookbot/languages.php';
require 'lib/myaddressbookbot/data.php';

/*
 * Main script of the Bot
 * Each request sent ny a telegram client will be parsed here and
 * the respective function will be called
 */

define('COMMANDLIST', "start - Start me, help - Get help, suggestion and tips to how to use me, about - About me, just to know me and my creator better");

// Set error reporting to skip PHP_NOTICE: http://php.net/manual/en/function.error-reporting.php
error_reporting(E_ALL & ~E_NOTICE);

$bot = new MyAddressBookBot($token);
$bot->setLocalization($localization);
$bot->setDatabase(new Database($driver, $dbname, $user, $password, $bot));
$bot->connectToRedis();
$bot->inline_keyboard = new InlineKeyboard($bot);
//while(true) {
 //  $bot->getUpdatesRedis(100, 60);
//}
$bot->getUpdatesLocal();
$bot = null;

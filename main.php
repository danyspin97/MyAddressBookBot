<?php

require './vendor/autoload.php';
require 'myaddressbookbot.php';
require 'database.php';
require 'inline_keyboard.php';
require 'languages.php';
/*
 * Main script of the Bot
 * Each request sent ny a telegram client will be parsed here and
 * the respective function will be called
 */

define('COMMANDLIST', "start - Start me, help - Get help, suggestion and tips to how to use me, about - About me, just to know me and my creator better");

// Set error reporting to skip PHP_NOTICE: http://php.net/manual/en/function.error-reporting.php
error_reporting(E_ALL & ~E_NOTICE);

$bot = new MyAddressBookBot('225771982:AAHrQ5-9DTh8MBUuVW7qOepW5srz9-HdH-E');
$bot->setLocalization($localization);
$bot->setDatabase(new Database('pgsql', 'MyAddressBookDB', 'user', 'pass', $bot));
$bot->connectToRedis();
$bot->inline_keyboard = new InlineKeyboard($bot);
while(true) {
$bot->getUpdatesRedis();
}
$bot = null;

<?php

require './vendor/autoload.php';
require 'myaddressbookbot.php';
require 'languages.php';
require 'inline_keyboard.php';
require 'database.php';
require 'data.php';

/*
 * Main script of the Bot using long polling
 * Each request sent ny a telegram client will be parsed here and
 * the respective function will be called
 */

// Set error reporting to skip PHP_NOTICE: http://php.net/manual/en/function.error-reporting.php
error_reporting(E_ALL & ~E_NOTICE);

$bot = new MyAddressBookBot($token);
$bot->setLocalization($localization);
$bot->setDatabase(new Database($driver, $dbname, $user, $password, $bot));
$bot->connectToRedis();
$bot->inline_keyboard = new InlineKeyboard($bot);
try {
    $bot->adjustOffsetRedis();
} catch (Exception $e) {
    echo $e->getMessage();
}
$bot = null;

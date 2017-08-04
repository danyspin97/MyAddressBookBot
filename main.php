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

$bot = new MyAddressBookBot($token);
$bot->database->connect(['username' => $user,
                         'password' => $password,
                         'dbname' => $dbname,
                         'adapter' => $driver]);
// Create redis object
$bot->redis = new Redis();
// Connect to redis database
$bot->redis->connect('127.0.0.1');
$bot->getUpdatesLocal();

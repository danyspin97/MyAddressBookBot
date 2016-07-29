<?php

include "../autoload.php";

/*
 * Main script of the Bot
 * Each request sent ny a telegram client will be parsed here and
 * the respective function will be called
 */

define('COMMANDLIST', "start - Start me, help - Get help, suggestion and tips to how to use me, about - About me, just to know me and my creator better");

// Set error reporting to skip PHP_NOTICE: http://php.net/manual/en/function.error-reporting.php
error_reporting(E_ALL & ~E_NOTICE);

$bot = new Bot('token');
$bot->setLocalization($localization);
$bot->database = new Database('pgsql', 'dbname', 'user', 'name');
$bot->inline_keyboard = new Inline_keyboard();
$bot->getUpdateRedis();
$bot = null;

<?php

/*
 * Main script of the Bot
 * Each request sent ny a telegram client will be parsed here and
 * the respective function will be called
 */

require_once 'core.php';
require_once 'message.php';
require_once 'callback.php';
require_once 'inline_query.php';
require_once 'inline_result.php';
require_once 'database.php';
require_once 'inline_keyboard.php';
require_once 'function.php';

define('COMMANDLIST', "start - Start me, help - Get help, suggestion and tips to how to use me, about - About me, just to know me and my creator better");

// Set error reporting to skip PHP_NOTICE: http://php.net/manual/en/function.error-reporting.php
error_reporting(E_ALL & ~E_NOTICE);

$content = file_get_contents('php://input');
$update = json_decode($content, true);

if (!$update) {
    // receive wrong update, must not happen
    exit;
}

// Open connection with the database using PDO-mysql exenstion
$pdo = new PDO('pgsql:host=localhost;dbname=dbname', 'user', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
/*
 * Default fetch mode will be FETCH_ASSOC that returns an array indexed by column name as returned in your result set
 * Checkout http://php.net/manual/en/pdostatement.fetch.php for more
 */
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Opedn Redis connection in local
$redis = new Redis();
$redis->connect('127.0.0.1');

if (isset($update['message'])) {
    processMessage($update['message'], $redis, $pdo);
} elseif (isset($update['callback_query'])) {
    processCallbackQuery($update['callback_query'], $redis, $pdo);
} elseif (isset($update['inline_query'])) {
    processInlineQuery($update['inline_query'], $redis, $pdo);
} elseif (isset($update['chosen_inline_result'])) {
    processInlineResult($update['chosen_inline_result'], $redis, $pdo);
}
// Close Mysql connection
$pdo = null;
// Close Redis connection
$redis->close();

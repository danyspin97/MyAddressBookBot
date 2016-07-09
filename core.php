<?php

define('BOT_TOKEN', 'token');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

/*
 * exec_curl_request(), apiRequest() and apiRequestJson() taken from
 * https://core.telegram.org/bots/samples/hellobot
 */

function &exec_curl_request(&$handle) {
    $response = curl_exec($handle);

    if ($response === false) {
        $errno = curl_errno($handle);
        $error = curl_error($handle);
        error_log("Curl returned error $errno: $error\n");
        curl_close($handle);
        return false;
    }

    $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
    curl_close($handle);

    if ($http_code >= 500) {
        // do not wat to DDOS server if something goes wrong
        sleep(10);
        return false;
    } else if ($http_code != 200) {
        $response = json_decode($response, true);
        error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
        if ($http_code == 401) {
            throw new Exception('Invalid access token provided');
        }
        return false;
    } else {
        $response = json_decode($response, true);
        if (isset($response['desc'])) {
            error_log("Request was successfull: {$response['description']}\n");
        }
        $response = $response['result'];
    }
  return $response;
}

function &apiRequest($method, $parameters) {
    if (!is_string($method)) {
        error_log("Method name must be a string\n");
        return false;
    }

    if (!$parameters) {
        $parameters = array();
    } else if (!is_array($parameters)) {
        error_log("Parameters must be an array\n");
        return false;
    }

    $url = API_URL . $method.'?'.http_build_query($parameters);

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);

    return exec_curl_request($handle);
}

function apiRequestJson($method, $parameters) {
    if (!is_string($method)) {
        error_log("Method name must be a string\n");
        return false;
    }

    if (!$parameters) {
        $parameters = array();
    } else if (!is_array($parameters)) {
        error_log("Parameters must be an array\n");
        return false;
    }

    $parameters['method'] = $method;

    $handle = curl_init(API_URL);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);
    curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
    curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

    return exec_curl_request($handle);
}

function &apiSendDefaultMessage(&$chat_id, &$text, &$reply_markup) {
    $parameters = [
        'chat_id' => &$chat_id,
        'text' => &$text,
        'reply_markup' => &$reply_markup,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true,
    ];
    $url = API_URL . 'sendMessage?' . http_build_query($parameters);

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);

    return exec_curl_request($handle);
}

function &apiSendReplyMessage(&$chat_id, &$text, &$reply_markup, &$message_id) {
    $parameters = [
        'chat_id' => &$chat_id,
        'text' => &$text,
        'reply_markup' => &$reply_markup,
        'reply_to_message_id' => &$message_id,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true,
    ];
    $url = API_URL . 'sendMessage?' . http_build_query($parameters);

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);

    return exec_curl_request($handle);
}

function apiEditMessageStandard(&$chat_id, &$message_id, $text) {
    $parameters = [
        'chat_id' => &$chat_id,
        'message_id' => &$message_id,
        'text' => &$text,
        'parse_mode' => 'HTML',
    ];
    $url = API_URL . 'editMessageText?' . http_build_query($parameters);

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);

    return exec_curl_request($handle);
}

function apiEditMessageInlineKeyboard(&$chat_id, &$message_id, $text, &$reply_markup) {
    $parameters = [
        'chat_id' => &$chat_id,
        'message_id' => &$message_id,
        'text' => &$text,
        'reply_markup' => &$reply_markup,
        'parse_mode' => 'HTML',
    ];
    $url = API_URL . 'editMessageText?' . http_build_query($parameters);

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);

    return exec_curl_request($handle);
}

function apiAnswerInlineQuery(&$chat_id, &$id, &$results, &$language) {
    $parameters = [
        'inline_query_id' => $id,
        'switch_pm_text' => getMessage('SwitchPM_InlineQuery', $language),
        'is_personal' => true,
        'switch_pm_parameter' => 'show_ab',
        'results' => $results,
        'cache_time' => 30
    ];

    $url = API_URL . 'answerInlineQuery?' . http_build_query($parameters);

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);

    return exec_curl_request($handle);
}

function apiAnswerlCallbackQueryEmpty(&$id) {
    $parameters = [
        'callback_query_id' => $id,
        'text' => ''
    ];

    $url = API_URL . 'answerCallbackQuery?' . http_build_query($parameters);

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);

    return exec_curl_request($handle);
}

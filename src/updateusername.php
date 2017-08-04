<?php

/*
 * This script runs every night to update user usernames
 */

require_once 'core.php';

$pdo = new PDO('pgsql:host=127.0.0.1;dbname=dbname', 'user', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$sth = $pdo->prepare('SELECT DISTINCT "id_contact", "username" FROM "Contact" WHERE NOT ("id_contact" IS NULL)');
$sth->execute();
$sth2 = $pdo->prepare('UPDATE "Contact" SET "username" = :username WHERE "id_contact" = :id_contact');
$updates = [['newdata' => []]];
while ($row = $sth->fetch()) {
    $chat = apiRequest('getChat', ['chat_id' => $row['id_contact']]);
    // Has the username changed?
    if (isset($chat['username']) && ($chat['username'] !== $row['username'])) {
        $sth2->bindParam(':username', $chat['username']);
        $sth2->bindValue(':id_contact', $row['id_contact'], PDO::PARAM_INT);
        // Update it
        $sth2->execute();
    //  Has the username been deleted?
    } elseif(!isset($chat['username']) && !isset($chat['error_code'])) {
        $username = 'empty';
        $sth2->bindParam(':username', $username);
        $sth2->bindValue(':id_contact', $row['id_contact'], PDO::PARAM_INT);
        // Change it with NoUsername
        $sth2->execute();
    }
}
$sth2 = null;
$sth = null;
$pdo = null;

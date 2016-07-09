<?php

/*
 * Functions that just update or request data from the database
 * without doing anything else will be declared here
 */

/*
 * Update a variable of a Contact row
 * @param:
 * $id_contact The id of the contact
 * $info       The info to update, e.g. 'username' to update username
 * $text       The text that will replace the info data
 */
function updateASInfo(&$chat_id, &$id_contact, $info, $text, PDO &$pdo) {
    $sth = $pdo->prepare("UPDATE \"Contact\" SET \"$info\" = :$info WHERE \"id\" = :id_as AND \"id_owner\" = :chat_id");
    $sth->bindParam(":$info", $text);
    $sth->bindParam(':id_as', $id_contact);
    $sth->bindParam(':chat_id', $chat_id);
    $sth->execute();
    $sth = null;
}

function &getASRowByID(&$chat_id, &$id_contact, PDO &$pdo) {
    $sth = $pdo->prepare('SELECT "username", "first_name", "last_name", "desc", "id", "id_contact" FROM "Contact" WHERE "id" = :id_as AND "id_owner" = :id_owner');
    $sth->bindParam(':id_as', $id_contact);
    $sth->bindParam(':id_owner', $chat_id);
    $sth->execute();
    $row = $sth->fetch();
    $sth = null;
    return $row;
}

// Get the number of the contact owned by a user
function getASRowOwnedByUser(&$chat_id, PDO &$pdo) {
    $sth = $pdo->prepare('SELECT COUNT("id") FROM "Contact" WHERE "id_owner" = :chat_id');
    $sth->bindParam(':chat_id', $chat_id);
    $sth->execute();
    $id = $sth->fetchColumn();
    $sth = null;
    if ($id !== false) {
        return $id;
    } else {
        return 0;
    }
}

function isUserRegistered(&$chat_id, PDO &$pdo) {
    $sth = $pdo->prepare('SELECT COUNT("chat_id") FROM "User" WHERE "chat_id" = :chat_id');
    $sth->bindParam(':chat_id', $chat_id);
    $sth->execute();
    if ($sth->fetchColumn() > 0) {
        return true;
    } else {
        return false;
    }
}

/*
 * Check if in the address book of the user (identified by $chat_id) there is a
 * contact that has $username as username, or, if there isn't, check if there is a user with the same $id_contact (it means he is the same user and he just changed username)
 */
function checkContactExist(&$chat_id, &$username, PDO &$pdo, $id_contact = -3) {
    $sth = $pdo->prepare('SELECT "id", "username" FROM (SELECT "id", "id_contact", "username" FROM "Contact" WHERE "id_owner" = :chat_id) AS T WHERE "id_contact" = :id_contact OR "username" LIKE :username');
    $sth->bindParam(':id_contact', $id_contact);
    $sth->bindParam(':username', $username);
    $sth->bindParam(':chat_id', $chat_id);
    $sth->execute();
    $row = $sth->fetch();
    $sth = null;
    if(isset($row['id'])) {
        if ($row['username'] !== $username) {
            $chat = apiRequest('getChat', ['chat_id' => $id_contact]);
            $sth = $pdo->prepare('UPDATE "Contact" SET "username" = :username WHERE "id_contact" = :id_contact');
            $sth->bindParam(':username', $chat['username']);
            $sth->bindParam(':id_contact', $id_contact);
            $sth->execute();
            $sth = null;
        }
        return $row['id'];
    } else {
        return 0;
    }
}

// Return the username of a contact by passing the $chat_id of the owner and the $id of the contact
function getUsernameFromID(&$chat_id, &$selected_contact, PDO &$pdo) {
    $sth = $pdo->prepare('SELECT "username" FROM "Contact" WHERE "id_owner" = :chat_id AND "id" = :selected_contact');
    $sth->bindParam(':selected_contact', $selected_contact);
    $sth->bindParam(':chat_id', $chat_id);
    $sth->execute();
    $row = $sth->fetch();
    $sth = null;
    if(isset($row['username'])) {
        return $row['username'];
    } else {
        return 'NULL';
    }
}

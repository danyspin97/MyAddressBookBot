<?php

class Database extends \WiseDragonStd\HadesWrapper\Database {
    /*
    * Update a variable of a Contact row
    * @param:
    * $id_contact The id of the contact
    * $info       The info to update, e.g. 'username' to update username
    * $text       The text that will replace the info data
    */
   public function updateContactInfo($info, $text) {
       switch ($info) {
           case 'description':
                $end = 49;
                break;
           default:
                $end = 31;
                break;
       }
       $sth = $this->pdo->prepare("UPDATE \"Contact\" SET \"$info\" = :info WHERE \"id\" = :id_as AND \"id_owner\" = :chat_id");
       $sth->bindParam(":info", substr($text, 0, $end));
       $sth->bindParam(':id_as', $this->bot->selected_contact);
       $sth->bindParam(':chat_id', $this->bot->getChatID());
       $sth->execute();
       $sth = null;
   }

   public function &getContactRowByID() {
       $sth = $this->pdo->prepare('SELECT "username", "first_name", "last_name", "desc", "id", "id_contact" FROM "Contact" WHERE "id" = :id_as AND "id_owner" = :id_owner');
       $sth->bindParam(':id_as', $this->bot->selected_contact);
       $sth->bindParam(':id_owner', $this->bot->getChatID());
       $sth->execute();
       $row = $sth->fetch();
       $sth = null;
       return $row;
   }

   // Get the number of the contact owned by a user
   public function getContactRowOwnedByUser() {
       $sth = $this->pdo->prepare('SELECT COUNT("id") FROM "Contact" WHERE "id_owner" = :chat_id');
       $sth->bindParam(':chat_id', $this->bot->getChatID());
       $sth->execute();
       $id = $sth->fetchColumn();
       $sth = null;
       if ($id !== false) {
           return $id;
       } else {
           return 0;
       }
   }

   public function isUserRegistered() {
       $sth = $this->pdo->prepare('SELECT COUNT("chat_id") FROM "User" WHERE "chat_id" = :chat_id');
       $sth->bindParam(':chat_id', $this->bot->getChatID());
       $sth->execute();
       if ($sth->fetchColumn() > 0) {
           return true;
       } else {
           return false;
       }
   }

   /*
    * Check if in the address book of the user (identified by $this->bot->chat_id) there is a
    * contact that has $username as username, or, if there isn't, check if there is a user with the same $id_contact (it means he is the same user and he just changed username)
    */
   public function checkContactExist(&$username, $id_contact = -3) {
       $sth = $this->pdo->prepare('SELECT "id", "username" FROM (SELECT "id", "id_contact", "username" FROM "Contact" WHERE "id_owner" = :chat_id) AS T WHERE "id_contact" = :id_contact OR LOWER("username") LIKE LOWER(:username)');
       $sth->bindParam(':id_contact', $id_contact);
       $sth->bindParam(':username', $username);
       $sth->bindParam(':chat_id', $this->bot->getChatID());
       $sth->execute();
       $row = $sth->fetch();
       $sth = null;
       if(isset($row['id'])) {
           return $row['id'];
       } else {
           return 0;
       }

   }

   // Return the username of a contact by passing the $this->bot->chat_id of the owner and the $id of the contact
   public function getUsernameFromID() {
       $sth = $this->pdo->prepare('SELECT "username" FROM "Contact" WHERE "id_owner" = :chat_id AND "id" = :selected_contact');
       $sth->bindParam(':selected_contact', $this->bot->selected_contact);
       $sth->bindParam(':chat_id', $this->bot->getChatID());
       $sth->execute();
       $row = $sth->fetch();
       $sth = null;
       if(isset($row['username'])) {
           return $row['username'];
       } else {
           return 'NULL';
       }
   }

    public function saveContact(&$row) {
        if (isset($row['id_contact']) && $row['id_contact'] !== 'NULL') {
            $sth = $this->pdo->prepare('INSERT INTO "Contact" ("id", "id_owner", "id_contact", "username", "first_name", "last_name", "desc") VALUES (:id, :chat_id, :id_contact, :username, :first_name, :last_name, :desc)');
            $sth->bindValue(':id_contact', $row['id_contact'], PDO::PARAM_INT);
        } else {
            $sth = $this->pdo->prepare('INSERT INTO "Contact" ("id", "id_owner", "username", "first_name", "last_name", "desc") VALUES (:id, :chat_id, :username, :first_name, :last_name, :desc)');
        }
        $sth->bindParam(':id', $row['id']);
        $sth->bindParam(':chat_id', $this->bot->getChatID());
        $sth->bindParam(':username', $row['username']);
        $sth->bindParam(':first_name', substr($row['first_name'], 0, 31));
        $sth->bindParam(':last_name', substr($row['last_name'], 0, 31));
        $sth->bindParam(':desc', substr($row['desc'], 0, 49));
        $sth->execute();
        $sth = null;
    }

    public function &getSearchResults($query) {
        $string = $this->localization[$this->language]['ShowResults_Msg'] . "\"<b>$query</b>\"" . NEWLINE;
        $query = strtolower($query);
        $sth = $this->pdo->prepare("SELECT \"username\", \"first_name\", \"last_name\", \"desc\", \"id\" FROM (SELECT \"username\", \"first_name\", \"last_name\", \"desc\", \"id\" FROM \"Contact\" WHERE \"id_owner\" = :chat_id) AS T WHERE LOWER(\"first_name\") LIKE '$query%'  OR LOWER(\"first_name\") LIKE '%$query%' OR LOWER(\"last_name\") LIKE '$query%' OR LOWER(\"last_name\") LIKE '%$query%' OR  LOWER(CONCAT_WS(' ', \"first_name\", \"last_name\")) LIKE '$query%' OR LOWER(username) LIKE '$query%' OR LOWER(username) LIKE '%$query%' OR LOWER(username) LIKE '@$query%' OR LOWER(username) LIKE '%@$query%' OR LOWER(CONCAT_WS(' ', \"first_name\", \"last_name\")) LIKE '%$query' OR LOWER(CONCAT_WS(' ', \"last_name\", \"first_name\")) LIKE '$query%' OR LOWER(CONCAT_WS(' ', \"last_name\", \"first_name\")) LIKE '%$query' ORDER BY " . $this->bot->order);
        $sth->bindParam(':chat_id', $this->bot->getChatID());
        $sth->execute();
        $cont = 1;
        $displayedrow = 0;
        $usernames = [[]];
        while($row = $sth->fetch()) {
            if ($displayedrow === 0 && ($cont == (($this->bot->index_addressbook - 1) * SPACEPERVIEW + 1))) {
                $usernames = [
                    [
                        'text' => '@' . $row['username'],
                        'callback_data' => 'id/' . $row['id'],
                    ]
                ];
                $string = $string . $this->bot->getContactInfoByRow($row, false);
                $displayedrow++;
            } elseif ($displayedrow > 0 && $displayedrow < SPACEPERVIEW) {
                array_push($usernames, [
                    'text' => '@' . $row['username'],
                    'callback_data' => 'id/' . $row['id'],
                ]);
                $string = $string . $this->bot->getContactInfoByRow($row, false);
                $displayedrow++;
            } elseif ($displayedrow == SPACEPERVIEW) {
                break;
            } else {
                $cont++;
            }
        }
        print_r($usernames);
        $sth = null;
        $container = [
            'string' => &$string,
            'usernames' => &$usernames,
        ];
        return $container;
    }

    public function &getABList() {
        $string = $this->bot->localization[$this->language]['Bot_Title'] . NEWLINE;
        $id = ($this->bot->index_addressbook - 1) * SPACEPERVIEW + 1;
        $maxid = $id + SPACEPERVIEW;
        $sth = $this->pdo->prepare("SELECT \"username\", \"first_name\", \"last_name\", \"desc\", \"id\" FROM \"Contact\" WHERE \"id_owner\" = :chat_id ORDER BY " . $this->bot->order);
        $sth->bindParam(':chat_id', $this->bot->getChatID());
        $sth->execute();
        $cont = 1;
        $displayedrow = 0;
        while($row = $sth->fetch()) {
            if ($displayedrow === 0 && ($cont == (($this->bot->index_addressbook - 1) * SPACEPERVIEW + 1))) {
                $usernames = [
                    [
                        'text' => '@' . $row['username'],
                        'callback_data' => 'id/' . $row['id'],
                    ]
                ];
                $string = $string . $this->bot->getContactInfoByRow($row, false);
                $displayedrow++;
            } elseif ($displayedrow > 0 && $displayedrow < SPACEPERVIEW) {
                array_push($usernames, [
                    'text' => '@' . $row['username'],
                    'callback_data' => 'id/' . $row['id'],
                ]);
                $string = $string . $this->bot->getContactInfoByRow($row, false);
                $displayedrow++;
            } elseif ($displayedrow == SPACEPERVIEW) {
                break;
            } else {
                $cont++;
            }
        }
        $sth = null;
        $container = [
            'string' => &$string,
            'usernames' => &$usernames
        ];
        return $container;
    }

    public function &getListResults($query) {
        $sth = $this->pdo->prepare("SELECT COUNT(\"username\") FROM (SELECT \"username\", \"first_name\", \"last_name\" FROM \"Contact\" WHERE \"id_owner\" = :chat_id) AS T WHERE \"first_name\" LIKE '$query%'  OR \"first_name\" LIKE '%$query%' OR \"last_name\" LIKE '$query%' OR \"last_name\" LIKE '%$query%' OR  CONCAT_WS(' ', \"first_name\", \"last_name\") LIKE '$query%' OR username LIKE '$query%' OR username LIKE '%$query%' OR username LIKE '@$query%' OR username LIKE '%@$query%' OR CONCAT_WS(' ', \"first_name\", \"last_name\") LIKE '%$query' OR CONCAT_WS(' ', \"last_name\", \"first_name\") LIKE '$query%' OR CONCAT_WS(' ', \"last_name\", \"first_name\") LIKE '%$query';");
        $sth->bindParam(':chat_id', $this->bot->getChatID());
        $sth->execute();
        $results = $sth->fetchColumn();
        $list = intval($results/ SPACEPERVIEW);
        // Add one list for the remaing one if there are any
        if (($results % SPACEPERVIEW) > 0)
            $list++;
        return $list;
    }

    public function &getList() {
        // Count how many Contact does this user own by doing a SELECT COUNT query
        $sth = $this->pdo->prepare('SELECT COUNT("id") FROM "Contact" WHERE "id_owner" = :chat_id');
        $sth->bindParam(':chat_id', $this->bot->getChatID());
        $sth->execute();
        $addressspacecount = $sth->fetchColumn();
        $sth = null;
        // Calculate how many menu's lists do we have to create by divind the number of spaces of the addressbook for the number of the address space we want to be listed in a single menu's list
        $list = intval($addressspacecount / SPACEPERVIEW);
        // Add one list for the remaing one if there are any
        if (($addressspacecount % SPACEPERVIEW) > 0)
            $list++;
        return $list;
    }

    public function checkIDUsed() {
        $sth = $this->pdo->prepare('SELECT DINSTICT ("id") FROM "Contact") WHERE "chat_id" = :chat_id');
        $sth->bindParam(':chat_id', $this->bot->chat_id);
        $sth->execute();
        $ids = $sth->fetch();
        while(in_array($this->bot->selected_contact, $ids)) {
            $this->bot->selected_contact++;
        }
    }
}

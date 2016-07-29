<?php

$pdo = new PDO('pgsql:host=127.0.0.1;dbname=dbname', 'user', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$redis = new Redis();
$redis->connect('127.0.0.1');
$offset = $redis->get('offset');

$sth = $pdo->prepare('UPDATE "BOT" SET "offset" = :offset');
$sth->bindParam(':offset', $offset);
$sth->execute();
$pdo = null;
$redis->close();

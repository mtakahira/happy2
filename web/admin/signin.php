<?php

require_once __DIR__ . '/functions.php';
$username = require_basic_auth();

header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<title>会員限定ページ</title>
<h1>ようこそ,<?=h($username)?>さん</h1>
<a href="http://dummy@localhost:8080/">ログアウト</a>

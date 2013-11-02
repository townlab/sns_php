<?php

// 初期化
require_once 'init.php';

// ログインされている状態を要求
require_login();

// セッション変数を全て解除する
$_SESSION = array();

// ユーザー側のセッションクッキーを削除する
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// サーバー側のセッションを破壊する
session_destroy();

// ログインページにリダイレクト
redirect('/login.php');
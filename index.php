<?php

// 初期化
require 'init.php';

// ログインされている状態を要求
require_login();

try {
    
    // ログイン中のユーザー情報を取得
    $me = DB::connect()->getUser($_SESSION['user_id']);
    // 全てのユーザー情報を取得
    $all = DB::connect()->getAllUsers();
    
} catch (Exception $e) {
    
    // エラーページに遷移
    error_page($e);
    
}

// 出力を開始
start_output();

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <title><?=h(SITE_NAME)?> - ホーム</title>
  </head>
  <body>
    <h1>こんにちは！</h1>
      <p>
        <?=h($me['name'])?>としてログインしています。
        [<a href="logout.php">ログアウト</a>]
      </p>
    <h1>ユーザー一覧</h1>
    <ul>
<?php foreach ($all as $user): ?>
      <li><a href="profile.php?id=<?=h($user['id'])?>"><?=h($user['name'])?></li>
<?php endforeach; ?>
    </ul>
    <p></p>
  </body>
</html>
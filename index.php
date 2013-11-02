<?php

// 初期化
require_once 'init.php';

// ログインされている状態を要求
require_login();

try {
    
    // ログイン中のユーザー情報を取得
    $me = DB::connect()->getUser($_SESSION['user_id']);
    
    // 全てのユーザー情報を取得
    $all = DB::connect()->getAllUsers();
    
} catch (PDOException $e) {
    
    // DISPLAY_SQL_STATEがFalseのときは代替メッセージをセット
    $errors = DISPLAY_SQL_STATE ? $e->getMessage() : 'データベースでエラーが発生しました。';
    
} catch (RuntimeException $e) {
    
    // 例外スタックを配列に変換
    $errors = exception_to_array($e);
    
}

// $errorsが空でなければエラーページに遷移
if (!empty($errors)) {
    redirect('/error.php', array('errors' => $errors));
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
      <li><a href="profile.php?id=<?=h($user['id'])?>"><?=$user['name']?></li>
<?php endforeach; ?>
    </ul>
    <p></p>
  </body>
</html>
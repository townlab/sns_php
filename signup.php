<?php

// 初期化
require 'init.php';

// ログインされていない状態を要求
require_unlogin();

// POSTで受け取ったパラメータを変数として展開
extract(filter_struct_utf8(INPUT_POST, array(
    'name' => FILTER_STRUCT_FULL_TRIM, // トリミングする
    'email' => FILTER_STRUCT_FULL_TRIM, // トリミングする
    'password' => '',
    'token' => '',
)));

// POSTされたときのみ実行
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        
        // トークンをチェック
        Token::check($token);
        // 登録
        DB::connect()->signup($name, $email, $password);
        // ログインページに遷移
        redirect('/login.php');
        
    } catch (Exception $e) {
        
        // 例外スタックを配列に変換
        $errors = exception_to_array($e);
        
    }
    
}

// 新しいトークンを生成
$token = Token::generate();

// 出力開始
start_output();

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <title><?=h(SITE_NAME)?> - 新規ユーザー登録</title>
  </head>
  <body>
    <h1>新規ユーザー登録</h1>
<?php if (!empty($errors)): ?>
    <ul>
<?php foreach ($errors as $e): ?>
      <li><?=h($e)?></li>
<?php endforeach; ?>
    </ul>
<?php endif; ?>
    <form action="" method="post">
      <label style="display:block;">
        お名前： <input type="text" name="name" value="<?=h($name)?>">
      </label>
      <label style="display:block;">
        メールアドレス： <input type="text" name="email" value="<?=h($email)?>">
      </label>
      <label style="display:block;">
        パスワード： <input type="password" name="password" value="<?=h($password)?>">
      </label>
      <div>
        <input type="submit" value="新規登録"> <a href=".">戻る</a>
        <input type="hidden" name="token" value="<?=h($token)?>">
      </div>
    </form>
  </body>
</html>
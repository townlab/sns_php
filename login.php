<?php

// 初期化
require 'init.php';

// ログインされていない状態を要求
require_unlogin();

// POSTで受け取ったパラメータを変数として展開
extract(filter_struct_utf8(INPUT_POST, array(
    'email' => FILTER_STRUCT_FULL_TRIM, // トリミングする
    'password' => '',
    'token' => '',
)));

// POSTされたときのみ実行
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        
        // トークンをチェック
        Token::check($token);
        // IDをセッションに取得
        $_SESSION['user_id'] = DB::connect()->login($email, $password);
        // セッション固定攻撃対策
        session_regenerate_id(true);
        // ホームに遷移
        redirect();
        
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
    <title><?=h(SITE_NAME)?> - ログイン</title>
  </head>
  <body>
    <h1>ログイン</h1>
<?php if (!empty($errors)): ?>
    <ul>
<?php foreach ($errors as $e): ?>
      <li><?=h($e)?></li>
<?php endforeach; ?>
    </ul>
<?php endif; ?>
    <form action="" method="post">
      <label style="display:block;">
        メールアドレス： <input type="text" name="email" value="<?=h($email)?>">
      </label>
      <label style="display:block;">
        パスワード： <input type="password" name="password" value="<?=h($password)?>">
      </label>
      <div>
        <input type="submit" value="ログイン"> <a href="signup.php">新規登録はこちら！</a>
        <input type="hidden" name="token" value="<?=h($token)?>">
      </div>
    </form>
  </body>
</html>
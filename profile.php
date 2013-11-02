<?php

// 初期化
require 'init.php';

// ログインされている状態を要求
require_login();

// GETで受け取ったパラメータを変数として展開
extract(filter_struct_utf8(INPUT_GET, array(
    'id' => '', 
)));

try {
    
    // 表示したいユーザーの情報を取得
    $user = DB::connect()->getUser($id);
    
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
    <title><?=h(SITE_NAME)?> - <?=h($user['name'])?>さんのプロフィール</title>
  </head>
  <body>
    <h1><?=h($user['name'])?>さんのプロフィール</h1>
    <table border="1">
      <tr>
        <th>お名前</th><td><?=h($user['name'])?></td>
      </tr>
      <tr>
        <th>メールアドレス</th><td><?=h($user['email'])?></td>
      </tr>
    </table>
    <p><a href=".">一覧へ</a></p>
  </body>
</html>
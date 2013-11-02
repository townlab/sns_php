<?php

// 初期化
require_once 'init.php';

// GETで受け取ったパラメータを変数として展開
extract(filter_struct_utf8(INPUT_GET, array(
    'errors' => FILTER_STRUCT_FORCE_ARRAY, // 1次元配列を要求
)));

// 500 Internal Server Error を返す
header('HTTP', true, 500);

// 出力を開始
start_output();

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <title><?=h(SITE_NAME)?> - エラー発生</title>
  </head>
  <body>
    <h1>おやっ？何かがおかしいです</h1>
<?php if (!empty($errors)): ?>
    <ul>
<?php foreach ($errors as $e): ?>
      <li><?=h($e)?></li>
<?php endforeach; ?>
    </ul>
<?php endif; ?>
  </body>
</html>
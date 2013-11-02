<?php

/* 設定・クラス・汎用関数を読み込む */
require 'config.php';
require 'DB.php';
require 'Token.php';
require 'Module.php';

/* エラーレポート設定 */
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT); // E_NOTICEまでキッチリ表示。

/* セッション有効期限設定 */
session_set_cookie_params(0, '/sns_php');

/* セッションキャッシュ設定 */
session_cache_limiter('none'); // 「戻る」を押しても大丈夫なようにする。

/* セッション開始 */
session_start();

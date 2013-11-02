<?php

/**
 * htmlspecialcharsのラッパー関数。
 * 
 * @param string $s
 * @return string
 */
function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/**
 * RuntimeExceptionを生成。
 * 
 * @param string $message
 * @param RuntimeException $previous
 * @return RuntimeException
 */
function e($message, RuntimeException $previous = null) {
    return new RuntimeException($message, 0, $previous);
}

/**
 * 出力開始時の送信ヘッダ。
 * 文字化けを防ぐために不可欠。
 */
function start_output() {
    header('Content-Type: text/html; charset=utf-8');
}

/**
 * exitも含めたリダイレクト。
 * 
 * @param string $path SITE_URLに続く部分。
 * @param array $param http_build_query関数に渡される連想配列。
 */
function redirect($path = '', array $params = array()) {
    $q = $params ? '?' . http_build_query($params, '', '&') : '';
    header('Location: ' . SITE_URL . $path . $q);
    exit;
}

/**
 * ログインしている状態を要求。
 * ログインしていなければログインページに遷移。
 */
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        redirect('/login.php');
    }
}

/**
 * ログインしていない状態を要求。
 * ログインしていればホームに遷移。
 */
function require_unlogin() {
    if (isset($_SESSION['user_id'])) {
        redirect('');
    }
}

/**
 * パスワードのハッシュを取得。
 * 
 * @param $password
 * @return string
 */
function get_password_hash($password) {
    return sha1(PASSWORD_KEY . $password);
}

/**
 * 例外スタックを逆順にして配列に変換。
 * 
 * @param Exception $e
 */
function exception_to_array(Exception $e) {
    do {
        // DISPLAY_SQL_STATEがFalseのとき、
        // PDOの例外に関しては代替メッセージをセット
        $errors[] = $e instanceof PDOException && !DISPLAY_SQL_STATE ?
            'データベースでエラーが発生しました。' :
            $e->getMessage()
        ;
    } while ($e = $e->getPrevious());
    return array_reverse($ret);
}

// http://qiita.com/items/c39b9ee695a5c2e74627 より引用。
// 長くて複雑な関数だけど汎用性が非常に高くて便利。
define('FILTER_STRUCT_FORCE_ARRAY', 1);
define('FILTER_STRUCT_TRIM', 2);
define('FILTER_STRUCT_FULL_TRIM', 4);
function filter_struct_utf8($type, array $default) {
    static $is_recursive_static = false;
    $is_recursive = $is_recursive_static;
    if (!$is_recursive) {
        $types = array(
            INPUT_GET => $_GET,
            INPUT_POST => $_POST,
            INPUT_COOKIE => $_COOKIE,
            INPUT_REQUEST => $_REQUEST,
        );
        $type = (int)$type;
        if (!isset($types[$type])) {
            throw new LogicException('unknown super global var type');
        }
        $var = $types[$type];
        $is_recursive_static = true;
    } else {
        $var = $type;
    }
    $trim_chars = "\\0\x20\x09\x0a\x0d\x0b";
    $full_trim_chars = "{$trim_chars}\xc2\xa0\xe3\x80\x80";
    $trim_pattern = "/\A[{$trim_chars}]++|[{$trim_chars}]++\z/u";
    $full_trim_pattern = "/\A[{$full_trim_chars}]++|[{$full_trim_chars}]++\z/u";
    $ret = array();
    foreach ($default as $key => $value) {
        if (is_int($value) && !($value & (
            FILTER_STRUCT_FORCE_ARRAY |
            FILTER_STRUCT_FULL_TRIM | 
            FILTER_STRUCT_TRIM
        ))) {
            if (!$is_recursive) {
                $is_recursive_static = false;
            }
            throw new LogicException('unknown bitmask');
        }
        if (is_int($value) && $value & FILTER_STRUCT_FORCE_ARRAY) {
            $tmp = array();
            if (isset($var[$key])) {
                foreach ((array)$var[$key] as $k => $v) {
                    if (!preg_match('//u', $k)) {
                        continue;
                    }
                    $value &= FILTER_STRUCT_FULL_TRIM | FILTER_STRUCT_TRIM;
                    $tmp += array($k => $value ? $value : '');
                }
            }
            $value = $tmp;
        }
        if (isset($var[$key]) && is_array($value)) {
            $ret[$key] = filter_struct_utf8($var[$key], $value);
        } elseif (!isset($var[$key]) || is_array($var[$key])) {
            $ret[$key] = is_int($value) ? '' : $value;
        } else {
            if (!isset($var[$key]) || is_array($var[$key])) {
                $var[$key] = null;
            } elseif (is_int($value) && $value & FILTER_STRUCT_FULL_TRIM) {
                $var[$key] = preg_replace($full_trim_pattern, '', $var[$key]);
            } elseif (is_int($value) && $value & FILTER_STRUCT_TRIM) {
                $var[$key] = preg_replace($trim_pattern, '', $var[$key]);
            } else {
                $var[$key] = preg_replace('//u', '', $var[$key]);
            }
            if ($var[$key] === null) {
                $var[$key] = is_int($value) ? '' : $value;
            }
            $ret[$key] = $var[$key];
        }
    }
    if (!$is_recursive) {
        $is_recursive_static = false;
    }
    return $ret;
}


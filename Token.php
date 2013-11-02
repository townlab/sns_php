<?php

/**
 * トークンクラス
 */
class Token {
    
    /**
     * セッションのキーに使う定数
     */
    const KEY = "\0__TOKENS__\0";
    
    /**
     * トークンを生成。
     * 複数のウィンドウで作業することを考慮して、
     * 最大10個までの保持を許す。
     *
     * @static
     * @access public
     * @return string 生成されたトークン。
     */
    public static function generate() {
        self::initialize();
        $_SESSION[self::KEY] =
            array_slice($_SESSION[self::KEY], -9, null, true)
            + array(($new = sha1(microtime() . mt_rand())) => 1)
        ;
        return $new;
    }
    
    /**
     * トークンをチェック。
     *
     * @static
     * @access public
     * @throw RuntimeException
     */
    public static function check($token) {
        self::initialize();
        if (is_scalar($token) && isset($_SESSION[self::KEY][$token])) {
            unset($_SESSION[self::KEY][$token]);
        } else {
            throw e('不正なアクセスが行われました。');
        }
    }
    
    /**
     * トークン格納配列が配列でなければ初期化する。
     *
     * @static
     * @access private
     */
    private static function initialize() {
        if (!isset($_SESSION[self::KEY]) || !is_array($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = array();
        }
    }
    
}
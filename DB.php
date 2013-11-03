<?php

/**
 * データベースクラス
 *
 * @throw PDOException
 */
class DB {
   
    /**
     * 静的プロパティ
     */
    private static $instance;
   
    /**
     * 動的プロパティ
     */
    private $pdo;
   
    /**
     * このクラス自身のインスタンスを生成して返す。
     * 2回目以降は1回目に生成したものを返すようにして、
     * 「シングルトン(singleton)」を実現している。
     *
     * @static
     * @access public
     * @return DB
     */
    public static function connect() {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }
   
    /**
     * PDOを使って実際にデータベースに接続する。
     * コンストラクタだがクラス内部のconnectメソッドからしか呼ばれないため、
     * アクセス権限はprivateに設定。
     *
     * @access private
     */
    private function __construct() {
        $this->pdo = new PDO(
            // DSN (Data Source Name)
            sprintf('mysql:dbname=%s;host=%s;charset=utf8',
                DB_NAME,
                DB_HOST
            ),
            DB_USER,
            DB_PASS,
            array(
                // SQL実行失敗時にPDOExceptionをスローさせる
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                // デフォルトフェッチを連想配列形式に指定
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // PDOStatement::rowCount()をSELECTに対しても使えるように設定
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            )
        );
    }
    
    /**
     * ユーザー登録を行う。
     *
     * @access public
     * @param string $name
     * @param string $email
     * @param string $password
     * @throw RuntimeException
     */
    public function signup($name, $email, $password) {
        // 例外初期化
        $e = null;
        // 名前チェック
        if ($name === '') {
            $e = e('お名前が入力されていません。', $e);
        }
        // メールアドレスチェック
        if ($email === '') {
            $e = e('メールアドレスが入力されていません。', $e);
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $e = e('メールアドレスの形式が不正です。', $e);
        } elseif ($this->emailExists($email)) {
            $e = e('このメールアドレスは既に登録されています。', $e);
        }
        // パスワードチェック
        if ($password === '') {
            $e = e('パスワードが入力されていません', $e);
        }
        // 1つでも例外が発生していればスローする
        if ($e) {
            throw $e;
        }
        // プリペアドステートメントを生成
        $stmt = $this->pdo->prepare(implode(' ', array(
            'INSERT',
            'INTO `users`(`name`, `email`, `password`, `created`, `modified`)',
            'VALUES (?, ?, ?, NOW(), NOW())',
        )));
        // 値をバインドして実行
        $stmt->execute(array($name, $email, get_password_hash($password)));
    }
    
    /**
     * メールアドレスとパスワードから該当するIDを返す。
     *
     * @access public
     * @param string $email
     * @param string $password
     * @return int idの値。
     * @throw RuntimeException
     */
    public function login($email, $password) {
        // 例外初期化
        $e = null;
        // メールアドレスチェック
        if ($email === '') {
            $e = e('メールアドレスが入力されていません。', $e);
        }
        // パスワードチェック
        if ($password === '') {
            $e = e('パスワードが入力されていません', $e);
        }
        // 1つでも例外が発生していればスローする
        if ($e) {
            throw $e;
        }
        // プリペアドステートメントを生成
        $stmt = $this->pdo->prepare(implode(' ', array(
            'SELECT `id`, `password`',
            'FROM `users`',
            'WHERE `email` = ?',
            'LIMIT 1',
        )));
        // 値をバインドして実行
        $stmt->execute(array($email));
        // 見つかったかどうかチェック
        if (!$stmt->rowCount()) {
            throw e('そのメールアドレスは登録されていません。');
        }
        // パスワード照合
        $row = $stmt->fetch();
        if ($row['password'] !== get_password_hash($password)) {
            throw e('パスワードが違います。');
        }
        // IDを返す
        return (int)$row['id'];
    }
    
    /**
     * IDから該当するユーザー情報を取得する。
     *
     * @access public
     * @param string $id
     * @return array 連想配列。
     * @throw RuntimeException
     */
    public function getUser($id) {
        $stmt = $this->pdo->prepare(implode(' ', array(
            'SELECT *',
            'FROM `users`',
            'WHERE `id` = ?',
            'LIMIT 1',
        )));
        $stmt->bindValue(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        if (!$stmt->rowCount()) {
            throw e('該当するユーザーが見つかりません。');
        }
        return $stmt->fetch();
    }
    
    /**
     * 全てのユーザー情報を取得する。
     *
     * @access public
     * @param string $id
     * @param string $table
     * @return array 2次元の連想配列。
     */
    public function getAllUsers() {
        return $this->pdo->query('SELECT * FROM `users`')->fetchAll();
    }
    
    /**
     * メールアドレスが既に登録されているかどうかを返す。
     *
     * @access private
     * @param string $email
     * @return bool 登録済みかどうか。
     */
    private function emailExists($email) {
        $stmt = $this->pdo->prepare(implode(' ', array(
            'SELECT `id`',
            'FROM `users`',
            'WHERE `email` = ?',
            'LIMIT 1',
        )));
        $stmt->execute(array($email));
        return (bool)$stmt->rowCount();
    }
    
}
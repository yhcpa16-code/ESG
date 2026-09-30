<?php
/**
 * 資料庫連接類
 */
class Database {
    private static $instance = null;
    private $pdo;
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $port = DB_PORT;

    private function __construct() {
        $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5,
        ];
        // 共享主機在瞬間大量連線時會暫時拒絕（2002 Operation not permitted、1040/1203 連線過多），稍候重試
        $retryable = [2002, 1040, 1203];
        $maxAttempts = 5;
        for ($attempt = 1; ; $attempt++) {
            try {
                $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
                return;
            } catch (PDOException $e) {
                $code = (int)($e->errorInfo[1] ?? $e->getCode());
                if ($attempt < $maxAttempts && in_array($code, $retryable, true)) {
                    usleep(random_int(100, 300) * 1000 * $attempt);
                    continue;
                }
                error_log('資料庫連接失敗（第 ' . $attempt . ' 次）: ' . $e->getMessage());
                http_response_code(503);
                die(APP_DEBUG ? '資料庫連接失敗: ' . $e->getMessage() : '系統暫時無法連線資料庫，請稍後再試');
            }
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPDO() {
        return $this->pdo;
    }

    /**
     * 執行查詢
     */
    public function query($sql, $params = []) {
        $sql = preg_replace('/\{([a-z_]+)\}/', TABLE_PREFIX . '$1', $sql);
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception('查詢執行失敗: ' . $e->getMessage());
        }
    }

    /**
     * 獲取單筆記錄
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * 獲取所有記錄
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * 插入記錄
     */
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $sql = 'INSERT INTO {' . $table . "} ($columns) VALUES ($placeholders)";

        try {
            $this->query($sql, array_values($data));
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception('插入失敗: ' . $e->getMessage());
        }
    }

    /**
     * 更新記錄
     */
    public function update($table, $data, $condition, $params = []) {
        $set = implode(',', array_map(fn($k) => "$k=?", array_keys($data)));
        $sql = 'UPDATE {' . $table . "} SET $set WHERE $condition";

        try {
            $stmt = $this->query($sql, array_merge(array_values($data), $params));
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception('更新失敗: ' . $e->getMessage());
        }
    }

    /**
     * 刪除記錄
     */
    public function delete($table, $condition, $params = []) {
        $sql = 'DELETE FROM {' . $table . "} WHERE $condition";

        try {
            $stmt = $this->query($sql, $params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception('刪除失敗: ' . $e->getMessage());
        }
    }

    /**
     * 開始事務
     */
    public function beginTransaction() {
        $this->pdo->beginTransaction();
    }

    /**
     * 提交事務
     */
    public function commit() {
        $this->pdo->commit();
    }

    /**
     * 回滾事務
     */
    public function rollback() {
        $this->pdo->rollback();
    }
}
?>

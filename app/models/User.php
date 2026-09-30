<?php
/**
 * 使用者模型
 */
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * 根據使用者名稱取得使用者
     */
    public function getUserByUsername($username) {
        $sql = "SELECT * FROM {users} WHERE username = ? AND is_active = 1";
        return $this->db->fetchOne($sql, [$username]);
    }

    /**
     * 根據ID取得使用者
     */
    public function getUserById($user_id) {
        $sql = "SELECT * FROM {users} WHERE user_id = ? AND is_active = 1";
        return $this->db->fetchOne($sql, [$user_id]);
    }

    /**
     * 驗證登入
     */
    public function authenticate($username, $password) {
        $user = $this->getUserByUsername($username);

        if (!$user) {
            return false;
        }

        if (password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    /**
     * 建立新使用者
     */
    public function create($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['created_at'] = date(DATETIME_FORMAT);

        try {
            $user_id = $this->db->insert('users', $data);
            return $user_id;
        } catch (Exception $e) {
            throw new Exception('建立使用者失敗: ' . $e->getMessage());
        }
    }

    /**
     * 更新使用者
     */
    public function update($user_id, $data) {
        $data['updated_at'] = date(DATETIME_FORMAT);

        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        try {
            return $this->db->update('users', $data, 'user_id = ?', [$user_id]);
        } catch (Exception $e) {
            throw new Exception('更新使用者失敗: ' . $e->getMessage());
        }
    }

    /**
     * 取得所有使用者
     */
    public function getAll() {
        $sql = "SELECT user_id, username, user_name, role, is_active, created_at FROM {users} ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    /**
     * 刪除使用者
     */
    public function delete($user_id) {
        try {
            return $this->db->update('users', ['is_active' => 0], 'user_id = ?', [$user_id]);
        } catch (Exception $e) {
            throw new Exception('刪除使用者失敗: ' . $e->getMessage());
        }
    }

    /**
     * 驗證密碼強度
     */
    public function validatePasswordStrength($password) {
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            return false;
        }
        return true;
    }
}
?>

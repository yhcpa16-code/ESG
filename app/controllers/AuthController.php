<?php
/**
 * 認證控制器 - 處理登入、登出等認證相關操作
 */
class AuthController {
    private $userModel;
    private $errors = [];
    private $messages = [];

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * 顯示登入表單
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($_SESSION['user_id'])) {
                header('Location: ' . SITE_URL . 'index.php?controller=dashboard');
                exit;
            }
            include SITE_ROOT . '/app/views/users/login.php';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
        }
    }

    /**
     * 處理登入
     */
    private function handleLogin() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->errors[] = '帳號和密碼不能為空';
        } else {
            $user = $this->userModel->authenticate($username, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['role'] = $user['role'];

                // 記錄審計日誌
                $this->logAudit($_SESSION['user_id'], '登入系統', 'auth');

                header('Location: ' . SITE_URL . 'index.php?controller=dashboard');
                exit;
            } else {
                $this->errors[] = '帳號或密碼錯誤';
            }
        }

        if (!empty($this->errors)) {
            include SITE_ROOT . '/app/views/users/login.php';
        }
    }

    /**
     * 登出
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logAudit($_SESSION['user_id'], '登出系統', 'auth');
        }

        session_destroy();
        header('Location: ' . SITE_URL . 'index.php?controller=auth&action=login');
        exit;
    }

    /**
     * 修改密碼
     */
    public function changePassword() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . SITE_URL . 'index.php?controller=auth&action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            include SITE_ROOT . '/app/views/users/change_password.php';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleChangePassword();
        }
    }

    /**
     * 處理密碼變更
     */
    private function handleChangePassword() {
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (DEMO_MODE && ($_SESSION['username'] ?? '') === DEMO_USERNAME) {
            $this->errors[] = '示範帳號無法修改密碼';
        } elseif (empty($old_password) || empty($new_password) || empty($confirm_password)) {
            $this->errors[] = '所有欄位都必填';
        } elseif ($new_password !== $confirm_password) {
            $this->errors[] = '新密碼和確認密碼不一致';
        } elseif (!$this->userModel->validatePasswordStrength($new_password)) {
            $this->errors[] = '新密碼強度不足';
        } else {
            $user = $this->userModel->getUserById($_SESSION['user_id']);
            if (!password_verify($old_password, $user['password'])) {
                $this->errors[] = '舊密碼錯誤';
            } else {
                try {
                    $this->userModel->update($_SESSION['user_id'], ['password' => $new_password]);
                    $this->messages[] = '密碼已成功修改';
                    $this->logAudit($_SESSION['user_id'], '修改密碼', 'auth');
                } catch (Exception $e) {
                    $this->errors[] = '修改密碼失敗: ' . $e->getMessage();
                }
            }
        }

        include SITE_ROOT . '/app/views/users/change_password.php';
    }

    /**
     * 記錄審計日誌
     */
    private function logAudit($user_id, $action, $module) {
        if (ENABLE_AUDIT_LOG) {
            $sql = "INSERT INTO {audit_logs} (user_id, action, module, ip_address) VALUES (?, ?, ?, ?)";
            $db = Database::getInstance();
            $db->query($sql, [$user_id, $action, $module, $_SERVER['REMOTE_ADDR'] ?? '']);
        }
    }

    /**
     * 取得錯誤訊息
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * 取得成功訊息
     */
    public function getMessages() {
        return $this->messages;
    }
}
?>

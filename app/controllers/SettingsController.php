<?php
/**
 * AI 設定控制器（僅限管理員）
 */
class SettingsController {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . SITE_URL . 'index.php?controller=auth&action=login');
            exit;
        }
        if (($_SESSION['role'] ?? '') !== USER_ROLE_ADMIN) {
            http_response_code(403);
            $_SESSION['flash_errors'] = ['只有系統管理員可以使用「AI 設定」'];
            header('Location: ' . SITE_URL . 'index.php?controller=dashboard');
            exit;
        }
    }

    public function index() {
        $keySource = OPENAI_KEY_SOURCE;
        $maskedKey = AiKeyStore::mask(OPENAI_API_KEY);
        $csrfToken = $this->csrfToken();
        $errors = $this->takeFlash('flash_errors');
        $messages = $this->takeFlash('flash_messages');
        $pageTitle = 'AI 設定';
        include SITE_ROOT . '/app/views/settings/index.php';
    }

    public function save() {
        $this->requirePostWithToken();
        $key = trim($_POST['api_key'] ?? '');

        if ($key === '') {
            $this->redirectWith('flash_errors', '請貼上 API 金鑰');
        }
        if (!AiKeyStore::isValidFormat($key)) {
            $this->redirectWith('flash_errors', '金鑰格式不正確，OpenAI 金鑰應以 sk- 開頭，且不含空格');
        }

        try {
            $this->verify($key);
            AiKeyStore::save($key);
        } catch (Exception $e) {
            $this->redirectWith('flash_errors', '未儲存：' . $e->getMessage());
        }

        $this->audit('更新 OpenAI API 金鑰（' . AiKeyStore::mask($key) . '）');
        $this->redirectWith('flash_messages', '金鑰已驗證並儲存，AI 記帳可以使用了');
    }

    public function test() {
        $this->requirePostWithToken();
        try {
            $this->verify(OPENAI_API_KEY);
        } catch (Exception $e) {
            $this->redirectWith('flash_errors', '連線測試失敗：' . $e->getMessage());
        }
        $this->redirectWith('flash_messages', '連線測試成功：可以使用 ' . OPENAI_TRANSCRIBE_MODEL . ' 與 ' . OPENAI_PARSE_MODEL);
    }

    public function clear() {
        $this->requirePostWithToken();
        try {
            AiKeyStore::clear();
        } catch (Exception $e) {
            $this->redirectWith('flash_errors', $e->getMessage());
        }
        $this->audit('清除 OpenAI API 金鑰');
        $this->redirectWith('flash_messages', '已清除網頁儲存的金鑰');
    }

    private function verify($key) {
        $client = new OpenAIClient($key);
        foreach (array_unique([OPENAI_TRANSCRIBE_MODEL, OPENAI_PARSE_MODEL]) as $model) {
            try {
                $client->checkModel($model);
            } catch (Exception $e) {
                throw new Exception('模型 ' . $model . ' 無法使用（' . $e->getMessage() . '）');
            }
        }
    }

    private function csrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    private function requirePostWithToken() {
        $token = $_POST['csrf_token'] ?? '';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($this->csrfToken(), $token)) {
            $this->redirectWith('flash_errors', '請求無效或已過期，請重新操作');
        }
    }

    private function redirectWith($flashKey, $message) {
        $_SESSION[$flashKey] = [$message];
        header('Location: ' . SITE_URL . 'index.php?controller=settings&action=index');
        exit;
    }

    private function takeFlash($flashKey) {
        $items = $_SESSION[$flashKey] ?? [];
        unset($_SESSION[$flashKey]);
        return $items;
    }

    private function audit($action) {
        if (ENABLE_AUDIT_LOG) {
            Database::getInstance()->query(
                'INSERT INTO {audit_logs} (user_id, action, module, ip_address) VALUES (?, ?, ?, ?)',
                [$_SESSION['user_id'], $action, 'settings', $_SERVER['REMOTE_ADDR'] ?? '']
            );
        }
    }
}
?>

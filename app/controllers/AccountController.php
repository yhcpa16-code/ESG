<?php
/**
 * 會計科目控制器
 */
class AccountController {
    private $accountModel;
    private $errors = [];
    private $messages = [];

    public function __construct() {
        $this->accountModel = new Account();
        $this->checkAuth();
    }

    /**
     * 檢查認證
     */
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . SITE_URL . 'index.php?controller=auth&action=login');
            exit;
        }
    }

    /**
     * 科目列表
     */
    public function index() {
        $type = $_GET['type'] ?? null;

        if ($type) {
            $accounts = $this->accountModel->getByType($type);
            $pageTitle = '會計科目 - ' . $this->getTypeName($type);
        } else {
            $accounts = $this->accountModel->getAll();
            $pageTitle = '所有會計科目';
        }

        include SITE_ROOT . '/app/views/accounts/list.php';
    }

    /**
     * 新增科目表單
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $types = [
                'asset' => '資產',
                'liability' => '負債',
                'equity' => '權益',
                'revenue' => '收入',
                'expense' => '支出'
            ];
            include SITE_ROOT . '/app/views/accounts/form.php';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        }
    }

    /**
     * 處理新增科目
     */
    private function handleCreate() {
        $data = [
            'account_code' => $_POST['account_code'] ?? '',
            'account_name' => $_POST['account_name'] ?? '',
            'account_type' => $_POST['account_type'] ?? '',
            'description' => $_POST['description'] ?? '',
            'balance' => 0
        ];

        if (empty($data['account_code'])) {
            $this->errors[] = '科目編碼必填';
        } elseif (empty($data['account_name'])) {
            $this->errors[] = '科目名稱必填';
        } elseif (empty($data['account_type'])) {
            $this->errors[] = '科目類別必填';
        } else {
            try {
                $this->accountModel->create($data);
                $this->messages[] = '科目已成功新增';
                header('Location: ' . SITE_URL . 'index.php?controller=account&action=index');
                exit;
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        $types = [
            'asset' => '資產',
            'liability' => '負債',
            'equity' => '權益',
            'revenue' => '收入',
            'expense' => '支出'
        ];
        include SITE_ROOT . '/app/views/accounts/form.php';
    }

    /**
     * 編輯科目表單
     */
    public function edit() {
        $id = $_GET['id'] ?? 0;
        $account = $this->accountModel->getAccountById($id);

        if (!$account) {
            $this->errors[] = '科目不存在';
            header('Location: ' . SITE_URL . 'index.php?controller=account&action=index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $types = [
                'asset' => '資產',
                'liability' => '負債',
                'equity' => '權益',
                'revenue' => '收入',
                'expense' => '支出'
            ];
            $pageTitle = '編輯科目';
            include SITE_ROOT . '/app/views/accounts/form.php';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($id);
        }
    }

    /**
     * 處理編輯科目
     */
    private function handleEdit($id) {
        $data = [
            'account_name' => $_POST['account_name'] ?? '',
            'account_type' => $_POST['account_type'] ?? '',
            'description' => $_POST['description'] ?? ''
        ];

        if (empty($data['account_name'])) {
            $this->errors[] = '科目名稱必填';
        } elseif (empty($data['account_type'])) {
            $this->errors[] = '科目類別必填';
        } else {
            try {
                $this->accountModel->update($id, $data);
                $this->messages[] = '科目已成功更新';
                header('Location: ' . SITE_URL . 'index.php?controller=account&action=index');
                exit;
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        $account = $this->accountModel->getAccountById($id);
        $types = [
            'asset' => '資產',
            'liability' => '負債',
            'equity' => '權益',
            'revenue' => '收入',
            'expense' => '支出'
        ];
        $pageTitle = '編輯科目';
        include SITE_ROOT . '/app/views/accounts/form.php';
    }

    /**
     * 刪除科目
     */
    public function delete() {
        $id = $_GET['id'] ?? 0;
        $account = $this->accountModel->getAccountById($id);

        if (!$account) {
            $this->errors[] = '科目不存在';
        } else {
            try {
                $this->accountModel->delete($id);
                $this->messages[] = '科目已成功刪除';
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        header('Location: ' . SITE_URL . 'index.php?controller=account&action=index');
        exit;
    }

    /**
     * 科目類別名稱轉換
     */
    private function getTypeName($type) {
        $types = [
            'asset' => '資產',
            'liability' => '負債',
            'equity' => '權益',
            'revenue' => '收入',
            'expense' => '支出'
        ];
        return $types[$type] ?? '未知';
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

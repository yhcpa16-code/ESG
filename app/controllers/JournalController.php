<?php
/**
 * 日記帳控制器
 */
class JournalController {
    private $journalModel;
    private $accountModel;
    private $errors = [];
    private $messages = [];

    public function __construct() {
        $this->journalModel = new JournalEntry();
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
     * 日記帳列表
     */
    public function index() {
        $page = $_GET['page'] ?? 1;
        $offset = ($page - 1) * ITEMS_PER_PAGE;

        $entries = $this->journalModel->getAll(ITEMS_PER_PAGE, $offset);

        // 取得總筆數（簡化版，可改為分頁查詢）
        $all_entries = $this->journalModel->getAll();
        $total_pages = ceil(count($all_entries) / ITEMS_PER_PAGE);

        $pageTitle = '日記帳';
        include SITE_ROOT . '/app/views/journal/list.php';
    }

    /**
     * 新增日記帳表單
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $accounts = $this->accountModel->getAll();
            $voucher_no = $this->journalModel->generateVoucherNo();
            $pageTitle = '新增日記帳';
            include SITE_ROOT . '/app/views/journal/form.php';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        }
    }

    /**
     * 處理新增日記帳
     */
    private function handleCreate() {
        $data = [
            'voucher_no' => $_POST['voucher_no'] ?? '',
            'entry_date' => $_POST['entry_date'] ?? '',
            'debit_account' => $_POST['debit_account'] ?? 0,
            'credit_account' => $_POST['credit_account'] ?? 0,
            'amount' => $_POST['amount'] ?? 0,
            'description' => $_POST['description'] ?? '',
            'created_by' => $_SESSION['user_id']
        ];

        // 驗證
        if (empty($data['voucher_no'])) {
            $this->errors[] = '憑證號碼必填';
        } elseif (empty($data['entry_date'])) {
            $this->errors[] = '日期必填';
        } elseif ($data['debit_account'] == 0) {
            $this->errors[] = '借方科目必選';
        } elseif ($data['credit_account'] == 0) {
            $this->errors[] = '貸方科目必選';
        } elseif ($data['debit_account'] == $data['credit_account']) {
            $this->errors[] = '借方和貸方科目不能相同';
        } elseif ($data['amount'] <= 0) {
            $this->errors[] = '金額必須大於零';
        } else {
            try {
                $this->journalModel->create($data);
                $this->messages[] = '日記帳已成功新增';
                header('Location: ' . SITE_URL . 'index.php?controller=journal&action=index');
                exit;
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        $accounts = $this->accountModel->getAll();
        $pageTitle = '新增日記帳';
        include SITE_ROOT . '/app/views/journal/form.php';
    }

    /**
     * 編輯日記帳表單
     */
    public function edit() {
        $id = $_GET['id'] ?? 0;
        $entry = $this->journalModel->getEntryById($id);

        if (!$entry) {
            $this->errors[] = '日記帳記錄不存在';
            header('Location: ' . SITE_URL . 'index.php?controller=journal&action=index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $accounts = $this->accountModel->getAll();
            $pageTitle = '編輯日記帳';
            include SITE_ROOT . '/app/views/journal/form.php';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($id);
        }
    }

    /**
     * 處理編輯日記帳
     */
    private function handleEdit($id) {
        $data = [
            'entry_date' => $_POST['entry_date'] ?? '',
            'debit_account' => $_POST['debit_account'] ?? 0,
            'credit_account' => $_POST['credit_account'] ?? 0,
            'amount' => $_POST['amount'] ?? 0,
            'description' => $_POST['description'] ?? ''
        ];

        if (empty($data['entry_date'])) {
            $this->errors[] = '日期必填';
        } elseif ($data['debit_account'] == 0) {
            $this->errors[] = '借方科目必選';
        } elseif ($data['credit_account'] == 0) {
            $this->errors[] = '貸方科目必選';
        } elseif ($data['debit_account'] == $data['credit_account']) {
            $this->errors[] = '借方和貸方科目不能相同';
        } elseif ($data['amount'] <= 0) {
            $this->errors[] = '金額必須大於零';
        } else {
            try {
                $this->journalModel->update($id, $data);
                $this->messages[] = '日記帳已成功更新';
                header('Location: ' . SITE_URL . 'index.php?controller=journal&action=index');
                exit;
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        $entry = $this->journalModel->getEntryById($id);
        $accounts = $this->accountModel->getAll();
        $pageTitle = '編輯日記帳';
        include SITE_ROOT . '/app/views/journal/form.php';
    }

    /**
     * 刪除日記帳
     */
    public function delete() {
        $id = $_GET['id'] ?? 0;
        $entry = $this->journalModel->getEntryById($id);

        if (!$entry) {
            $this->errors[] = '日記帳記錄不存在';
        } else {
            try {
                $this->journalModel->delete($id);
                $this->messages[] = '日記帳已成功刪除';
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        header('Location: ' . SITE_URL . 'index.php?controller=journal&action=index');
        exit;
    }

    /**
     * 查看日記帳詳情
     */
    public function view() {
        $id = $_GET['id'] ?? 0;
        $entry = $this->journalModel->getEntryById($id);

        if (!$entry) {
            $this->errors[] = '日記帳記錄不存在';
            header('Location: ' . SITE_URL . 'index.php?controller=journal&action=index');
            exit;
        }

        $pageTitle = '日記帳詳情';
        include SITE_ROOT . '/app/views/journal/view.php';
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

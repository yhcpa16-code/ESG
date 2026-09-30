<?php
/**
 * 儀表板控制器
 */
class DashboardController {
    private $journalModel;
    private $accountModel;

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
     * 儀表板首頁
     */
    public function index() {
        // 取得統計資料
        $today = date('Y-m-d');
        $start_of_month = date('Y-m-01');
        $end_of_month = date('Y-m-t');

        // 今日交易數
        $today_entries = $this->journalModel->getByDateRange($today, $today);
        $today_count = count($today_entries);
        $today_amount = 0;
        foreach ($today_entries as $entry) {
            $today_amount += $entry['amount'];
        }

        // 本月交易數
        $month_entries = $this->journalModel->getByDateRange($start_of_month, $end_of_month);
        $month_count = count($month_entries);
        $month_amount = 0;
        foreach ($month_entries as $entry) {
            $month_amount += $entry['amount'];
        }

        // 科目總數
        $all_accounts = $this->accountModel->getAll();
        $account_count = count($all_accounts);

        // 資產總額
        $total_assets = 0;
        foreach ($all_accounts as $account) {
            if ($account['account_type'] === 'asset') {
                $total_assets += $account['balance'];
            }
        }

        // 負債總額
        $total_liabilities = 0;
        foreach ($all_accounts as $account) {
            if ($account['account_type'] === 'liability') {
                $total_liabilities += $account['balance'];
            }
        }

        // 權益總額
        $total_equity = 0;
        foreach ($all_accounts as $account) {
            if ($account['account_type'] === 'equity') {
                $total_equity += $account['balance'];
            }
        }

        // 最近交易
        $recent_entries = array_slice($this->journalModel->getAll(null, 0), 0, 10);

        $pageTitle = '儀表板';
        include SITE_ROOT . '/app/views/dashboard/index.php';
    }
}
?>

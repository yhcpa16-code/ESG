<?php
/**
 * 報表控制器
 */
class ReportController {
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
     * 試算表
     */
    public function trialBalance() {
        $accounts = $this->accountModel->getAll();
        $total_debit = 0;
        $total_credit = 0;

        foreach ($accounts as &$account) {
            if (in_array($account['account_type'], ['asset', 'expense'])) {
                $total_debit += $account['balance'];
            } else {
                $total_credit += $account['balance'];
            }
        }

        $pageTitle = '試算表';
        include SITE_ROOT . '/app/views/reports/trial_balance.php';
    }

    /**
     * 分類帳
     */
    public function ledger() {
        $account_id = $_GET['account_id'] ?? null;
        $accounts = $this->accountModel->getAll();

        if ($account_id) {
            $account = $this->accountModel->getAccountById($account_id);
            if (!$account) {
                $account_id = null;
                $entries = [];
            } else {
                $entries = $this->journalModel->getByAccount($account_id);
            }
        } else {
            $account = null;
            $entries = [];
        }

        $pageTitle = '分類帳';
        include SITE_ROOT . '/app/views/reports/ledger.php';
    }

    /**
     * 損益表
     */
    public function incomeStatement() {
        $start_date = $_GET['start_date'] ?? date('Y-01-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');

        $revenue_accounts = $this->accountModel->getByType('revenue');
        $expense_accounts = $this->accountModel->getByType('expense');

        $total_revenue = 0;
        $total_expense = 0;

        foreach ($revenue_accounts as &$account) {
            $entries = $this->journalModel->getByDateRange($start_date, $end_date);
            $account_total = 0;
            foreach ($entries as $entry) {
                if ($entry['credit_account'] == $account['account_id']) {
                    $account_total += $entry['amount'];
                } elseif ($entry['debit_account'] == $account['account_id']) {
                    $account_total -= $entry['amount'];
                }
            }
            $account['period_amount'] = $account_total;
            $total_revenue += $account_total;
        }

        foreach ($expense_accounts as &$account) {
            $entries = $this->journalModel->getByDateRange($start_date, $end_date);
            $account_total = 0;
            foreach ($entries as $entry) {
                if ($entry['debit_account'] == $account['account_id']) {
                    $account_total += $entry['amount'];
                } elseif ($entry['credit_account'] == $account['account_id']) {
                    $account_total -= $entry['amount'];
                }
            }
            $account['period_amount'] = $account_total;
            $total_expense += $account_total;
        }

        $net_income = $total_revenue - $total_expense;

        $pageTitle = '損益表';
        include SITE_ROOT . '/app/views/reports/income_statement.php';
    }

    /**
     * 資產負債表
     */
    public function balanceSheet() {
        $asset_accounts = $this->accountModel->getByType('asset');
        $liability_accounts = $this->accountModel->getByType('liability');
        $equity_accounts = $this->accountModel->getByType('equity');

        $total_assets = 0;
        foreach ($asset_accounts as &$account) {
            $total_assets += $account['balance'];
        }

        $total_liabilities = 0;
        foreach ($liability_accounts as &$account) {
            $total_liabilities += $account['balance'];
        }

        $total_equity = 0;
        foreach ($equity_accounts as &$account) {
            $total_equity += $account['balance'];
        }

        $pageTitle = '資產負債表';
        include SITE_ROOT . '/app/views/reports/balance_sheet.php';
    }

    /**
     * 科目餘額表
     */
    public function accountBalance() {
        $accounts = $this->accountModel->getAll();

        $pageTitle = '科目餘額表';
        include SITE_ROOT . '/app/views/reports/account_balance.php';
    }
}
?>

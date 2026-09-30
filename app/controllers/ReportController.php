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

        foreach ($accounts as $account) {
            if (Account::isDebitNature($account['account_type'])) {
                $total_debit += $account['natural_balance'];
            } else {
                $total_credit += $account['natural_balance'];
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

        $entries = $this->journalModel->getByDateRange($start_date, $end_date);
        $periodAmount = function ($account) use ($entries) {
            $sign = Account::isDebitNature($account['account_type']) ? 1 : -1;
            $total = 0;
            foreach ($entries as $entry) {
                if ($entry['debit_account'] == $account['account_id']) {
                    $total += $sign * $entry['amount'];
                } elseif ($entry['credit_account'] == $account['account_id']) {
                    $total -= $sign * $entry['amount'];
                }
            }
            return $total;
        };

        $total_revenue = 0;
        foreach ($revenue_accounts as $i => $account) {
            $revenue_accounts[$i]['period_amount'] = $periodAmount($account);
            $total_revenue += $revenue_accounts[$i]['period_amount'];
        }

        $total_expense = 0;
        foreach ($expense_accounts as $i => $account) {
            $expense_accounts[$i]['period_amount'] = $periodAmount($account);
            $total_expense += $expense_accounts[$i]['period_amount'];
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

        $sum = fn($accounts) => array_sum(array_column($accounts, 'natural_balance'));
        $total_assets = $sum($asset_accounts);
        $total_liabilities = $sum($liability_accounts);
        // 尚未結帳前，本期損益（收入－費用）屬於權益的一部分
        $current_income = $sum($this->accountModel->getByType('revenue')) - $sum($this->accountModel->getByType('expense'));
        $total_equity = $sum($equity_accounts) + $current_income;

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

<?php
/**
 * 日記帳模型
 */
class JournalEntry {
    private $db;
    private $accountModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->accountModel = new Account();
    }

    /**
     * 根據ID取得日記帳記錄
     */
    public function getEntryById($entry_id) {
        $sql = "SELECT je.*, u.user_name, da.account_code as debit_code, da.account_name as debit_name,
                       ca.account_code as credit_code, ca.account_name as credit_name
                FROM {journal_entries} je
                LEFT JOIN {users} u ON je.created_by = u.user_id
                LEFT JOIN {accounts} da ON je.debit_account = da.account_id
                LEFT JOIN {accounts} ca ON je.credit_account = ca.account_id
                WHERE je.entry_id = ?";
        return $this->db->fetchOne($sql, [$entry_id]);
    }

    /**
     * 根據憑證號碼取得記錄
     */
    public function getEntryByVoucher($voucher_no) {
        $sql = "SELECT * FROM {journal_entries} WHERE voucher_no = ?";
        return $this->db->fetchOne($sql, [$voucher_no]);
    }

    /**
     * 取得所有日記帳記錄
     */
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT je.*, u.user_name, da.account_code as debit_code, da.account_name as debit_name,
                       ca.account_code as credit_code, ca.account_name as credit_name
                FROM {journal_entries} je
                LEFT JOIN {users} u ON je.created_by = u.user_id
                LEFT JOIN {accounts} da ON je.debit_account = da.account_id
                LEFT JOIN {accounts} ca ON je.credit_account = ca.account_id
                ORDER BY je.entry_date DESC, je.entry_id DESC";

        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        return $this->db->fetchAll($sql);
    }

    /**
     * 按日期範圍取得記錄
     */
    public function getByDateRange($start_date, $end_date) {
        $sql = "SELECT je.*, u.user_name, da.account_code as debit_code, da.account_name as debit_name,
                       ca.account_code as credit_code, ca.account_name as credit_name
                FROM {journal_entries} je
                LEFT JOIN {users} u ON je.created_by = u.user_id
                LEFT JOIN {accounts} da ON je.debit_account = da.account_id
                LEFT JOIN {accounts} ca ON je.credit_account = ca.account_id
                WHERE DATE(je.entry_date) BETWEEN ? AND ?
                ORDER BY je.entry_date ASC, je.entry_id ASC";
        return $this->db->fetchAll($sql, [$start_date, $end_date]);
    }

    /**
     * 按科目取得記錄
     */
    public function getByAccount($account_id) {
        $sql = "SELECT je.*, u.user_name, da.account_code as debit_code, da.account_name as debit_name,
                       ca.account_code as credit_code, ca.account_name as credit_name
                FROM {journal_entries} je
                LEFT JOIN {users} u ON je.created_by = u.user_id
                LEFT JOIN {accounts} da ON je.debit_account = da.account_id
                LEFT JOIN {accounts} ca ON je.credit_account = ca.account_id
                WHERE je.debit_account = ? OR je.credit_account = ?
                ORDER BY je.entry_date DESC";
        return $this->db->fetchAll($sql, [$account_id, $account_id]);
    }

    /**
     * 建立新的日記帳記錄
     */
    public function create($data) {
        try {
            $this->db->beginTransaction();

            // 驗證科目存在
            if (!$this->accountModel->getAccountById($data['debit_account'])) {
                throw new Exception('借方科目不存在');
            }
            if (!$this->accountModel->getAccountById($data['credit_account'])) {
                throw new Exception('貸方科目不存在');
            }

            // 驗證金額
            if ($data['amount'] <= 0) {
                throw new Exception('金額必須大於零');
            }

            $data['created_at'] = date(DATETIME_FORMAT);

            // 插入日記帳記錄
            $entry_id = $this->db->insert('journal_entries', $data);

            // 更新科目餘額
            $this->accountModel->updateBalance($data['debit_account'], $data['amount'], 'add');
            $this->accountModel->updateBalance($data['credit_account'], $data['amount'], 'subtract');

            $this->db->commit();

            return $entry_id;
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception('建立日記帳失敗: ' . $e->getMessage());
        }
    }

    /**
     * 更新日記帳記錄
     */
    public function update($entry_id, $data) {
        try {
            $this->db->beginTransaction();

            $entry = $this->getEntryById($entry_id);
            if (!$entry) {
                throw new Exception('日記帳記錄不存在');
            }

            // 如果金額改變，回沖舊的記錄
            if (isset($data['amount']) && $data['amount'] != $entry['amount']) {
                $this->accountModel->updateBalance($entry['debit_account'], $entry['amount'], 'subtract');
                $this->accountModel->updateBalance($entry['credit_account'], $entry['amount'], 'add');

                // 加上新的金額
                $this->accountModel->updateBalance($data['debit_account'], $data['amount'], 'add');
                $this->accountModel->updateBalance($data['credit_account'], $data['amount'], 'subtract');
            }

            $data['updated_at'] = date(DATETIME_FORMAT);

            $result = $this->db->update('journal_entries', $data, 'entry_id = ?', [$entry_id]);

            $this->db->commit();

            return $result;
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception('更新日記帳失敗: ' . $e->getMessage());
        }
    }

    /**
     * 刪除日記帳記錄
     */
    public function delete($entry_id) {
        try {
            $this->db->beginTransaction();

            $entry = $this->getEntryById($entry_id);
            if (!$entry) {
                throw new Exception('日記帳記錄不存在');
            }

            // 回沖金額
            $this->accountModel->updateBalance($entry['debit_account'], $entry['amount'], 'subtract');
            $this->accountModel->updateBalance($entry['credit_account'], $entry['amount'], 'add');

            // 刪除記錄
            $result = $this->db->delete('journal_entries', 'entry_id = ?', [$entry_id]);

            $this->db->commit();

            return $result;
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception('刪除日記帳失敗: ' . $e->getMessage());
        }
    }

    /**
     * 產生下一個憑證號碼
     */
    public function generateVoucherNo() {
        $date = date('Ymd');
        $sql = "SELECT COUNT(*) as count FROM {journal_entries} WHERE DATE(entry_date) = CURDATE()";
        $result = $this->db->fetchOne($sql);
        $seq = $result['count'] + 1;
        return $date . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * 取得日記帳統計
     */
    public function getStatistics($start_date, $end_date) {
        $sql = "SELECT
                    COUNT(*) as total_entries,
                    SUM(amount) as total_amount,
                    COUNT(DISTINCT entry_date) as transaction_days
                FROM {journal_entries}
                WHERE DATE(entry_date) BETWEEN ? AND ?";
        return $this->db->fetchOne($sql, [$start_date, $end_date]);
    }
}
?>

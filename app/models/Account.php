<?php
/**
 * 會計科目模型
 */
class Account {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // balance 一律以「借方為正」儲存；資產、費用為借方科目，負債、權益、收入為貸方科目
    public static function isDebitNature($type) {
        return in_array($type, [ACCOUNT_TYPE_ASSET, ACCOUNT_TYPE_EXPENSE], true);
    }

    // 依科目正常餘額方向顯示的餘額（貸方科目取反號）
    private static function withNaturalBalance($account) {
        if ($account) {
            $balance = (float)$account['balance'];
            $account['natural_balance'] = self::isDebitNature($account['account_type']) ? $balance : -$balance;
        }
        return $account;
    }

    /**
     * 根據ID取得科目
     */
    public function getAccountById($account_id) {
        $sql = "SELECT * FROM {accounts} WHERE account_id = ?";
        return self::withNaturalBalance($this->db->fetchOne($sql, [$account_id]));
    }

    /**
     * 根據科目編碼取得科目
     */
    public function getAccountByCode($account_code) {
        $sql = "SELECT * FROM {accounts} WHERE account_code = ?";
        return $this->db->fetchOne($sql, [$account_code]);
    }

    /**
     * 取得所有科目
     */
    public function getAll($type = null) {
        if ($type) {
            return $this->getByType($type);
        }
        $sql = "SELECT * FROM {accounts} ORDER BY account_code ASC";
        return array_map([self::class, 'withNaturalBalance'], $this->db->fetchAll($sql));
    }

    /**
     * 按類型取得科目
     */
    public function getByType($type) {
        $sql = "SELECT * FROM {accounts} WHERE account_type = ? ORDER BY account_code ASC";
        return array_map([self::class, 'withNaturalBalance'], $this->db->fetchAll($sql, [$type]));
    }

    /**
     * 建立新科目
     */
    public function create($data) {
        $data['created_at'] = date(DATETIME_FORMAT);

        try {
            $account_id = $this->db->insert('accounts', $data);
            return $account_id;
        } catch (Exception $e) {
            throw new Exception('建立科目失敗: ' . $e->getMessage());
        }
    }

    /**
     * 更新科目
     */
    public function update($account_id, $data) {
        $data['updated_at'] = date(DATETIME_FORMAT);

        try {
            return $this->db->update('accounts', $data, 'account_id = ?', [$account_id]);
        } catch (Exception $e) {
            throw new Exception('更新科目失敗: ' . $e->getMessage());
        }
    }

    /**
     * 刪除科目
     */
    public function delete($account_id) {
        try {
            return $this->db->delete('accounts', 'account_id = ?', [$account_id]);
        } catch (Exception $e) {
            throw new Exception('刪除科目失敗: ' . $e->getMessage());
        }
    }

    /**
     * 更新餘額
     */
    public function updateBalance($account_id, $amount, $type = 'add') {
        try {
            $account = $this->getAccountById($account_id);
            if (!$account) {
                throw new Exception('科目不存在');
            }

            if ($type === 'add') {
                $new_balance = $account['balance'] + $amount;
            } else {
                $new_balance = $account['balance'] - $amount;
            }

            return $this->db->update('accounts', ['balance' => $new_balance], 'account_id = ?', [$account_id]);
        } catch (Exception $e) {
            throw new Exception('更新餘額失敗: ' . $e->getMessage());
        }
    }

    /**
     * 取得科目餘額
     */
    public function getBalance($account_id) {
        $account = $this->getAccountById($account_id);
        return $account ? $account['balance'] : 0;
    }

    /**
     * 取得按類型的科目統計
     */
    public function getAccountsByTypeWithBalance($type = null) {
        if ($type) {
            $sql = "SELECT account_code, account_name, account_type, balance FROM {accounts} WHERE account_type = ? ORDER BY account_code ASC";
            return $this->db->fetchAll($sql, [$type]);
        } else {
            $sql = "SELECT account_code, account_name, account_type, balance FROM {accounts} ORDER BY account_code ASC";
            return $this->db->fetchAll($sql);
        }
    }
}
?>

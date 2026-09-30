-- 會計帳務管理系統資料庫初始化SQL
-- 建立資料庫
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS accounting_system DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE accounting_system;

-- 使用者表
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    is_active INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 會計科目表
CREATE TABLE IF NOT EXISTS accounts (
    account_id INT AUTO_INCREMENT PRIMARY KEY,
    account_code VARCHAR(20) UNIQUE NOT NULL,
    account_name VARCHAR(100) NOT NULL,
    account_type VARCHAR(20) NOT NULL,
    description TEXT,
    balance DECIMAL(15,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (account_code),
    INDEX idx_type (account_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 日記帳表
CREATE TABLE IF NOT EXISTS journal_entries (
    entry_id INT AUTO_INCREMENT PRIMARY KEY,
    voucher_no VARCHAR(30) UNIQUE NOT NULL,
    entry_date DATE NOT NULL,
    debit_account INT NOT NULL,
    credit_account INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (debit_account) REFERENCES accounts(account_id) ON DELETE RESTRICT,
    FOREIGN KEY (credit_account) REFERENCES accounts(account_id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
    INDEX idx_voucher (voucher_no),
    INDEX idx_date (entry_date),
    INDEX idx_accounts (debit_account, credit_account)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 審計日誌表
CREATE TABLE IF NOT EXISTS audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    module VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_date (created_at),
    INDEX idx_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 系統設定表
CREATE TABLE IF NOT EXISTS system_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI 辨識紀錄表（語音／拍照記帳）
CREATE TABLE IF NOT EXISTS ai_recognition_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    source_type VARCHAR(10) NOT NULL,
    source_text TEXT,
    ai_result TEXT,
    model VARCHAR(100),
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    entry_id INT NULL,
    error_message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (entry_id) REFERENCES journal_entries(entry_id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入預設使用者（帳號：admin，密碼：admin123）
INSERT INTO users (username, password, user_name, role) VALUES
('admin', '$2y$10$L6bDcGEmlU7wyV8eLt49FeCjm2vZUbvWCyr5HbQYRRDgEbrwBYGDq', '系統管理員', 'admin'),
('user1', '$2y$10$L6bDcGEmlU7wyV8eLt49FeCjm2vZUbvWCyr5HbQYRRDgEbrwBYGDq', '會計人員', 'user');

-- 插入預設會計科目
INSERT INTO accounts (account_code, account_name, account_type, description) VALUES
-- 資產類
('1001', '現金', 'asset', '庫存現金'),
('1002', '銀行存款', 'asset', '銀行帳戶'),
('1003', '應收帳款', 'asset', '應收帳款'),
('1004', '預付費用', 'asset', '預付費用'),
('1101', '固定資產', 'asset', '固定資產'),

-- 負債類
('2001', '應付帳款', 'liability', '應付帳款'),
('2002', '應付稅款', 'liability', '應付稅款'),
('2101', '短期借款', 'liability', '短期借款'),

-- 權益類
('3001', '股本', 'equity', '股本'),
('3002', '保留盈餘', 'equity', '保留盈餘'),

-- 收入類
('4001', '銷售收入', 'revenue', '銷售收入'),
('4002', '服務收入', 'revenue', '服務收入'),
('4101', '其他收入', 'revenue', '其他收入'),

-- 支出類
('5001', '進貨成本', 'expense', '進貨成本'),
('5002', '人事費用', 'expense', '薪資與獎金'),
('5003', '租賃費用', 'expense', '租金'),
('5004', '水電費', 'expense', '水電瓦斯費'),
('5005', '折舊費用', 'expense', '折舊費用'),
('5006', '其他費用', 'expense', '其他費用');

-- 插入預設系統設定
INSERT INTO system_settings (setting_key, setting_value) VALUES
('company_name', '宥鋐會計師事務所'),
('company_address', '台北市'),
('fiscal_year_start', '01-01'),
('fiscal_year_end', '12-31'),
('currency', 'TWD'),
('date_format', 'Y-m-d');

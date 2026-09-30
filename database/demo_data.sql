-- 示範資料：公開示範帳號與範例交易（需先匯入 init.sql）
SET NAMES utf8mb4;

INSERT INTO users (username, password, user_name, role) VALUES
('demo', '__DEMO_HASH__', '示範使用者', 'user');

INSERT INTO journal_entries (voucher_no, entry_date, debit_account, credit_account, amount, description, created_by)
SELECT v.voucher_no, v.entry_date, d.account_id, c.account_id, v.amount, v.description, u.user_id
FROM (
    SELECT '20260901-0001' AS voucher_no, '2026-09-01' AS entry_date, '1002' AS debit_code, '3001' AS credit_code, 500000 AS amount, '股東出資，存入公司銀行帳戶' AS description
    UNION ALL SELECT '20260902-0001', '2026-09-02', '1101', '1002', 120000, '購買辦公設備及電腦'
    UNION ALL SELECT '20260903-0001', '2026-09-03', '5003', '1002', 25000, '支付九月辦公室租金'
    UNION ALL SELECT '20260905-0001', '2026-09-05', '5001', '2001', 30000, '賒購商品一批'
    UNION ALL SELECT '20260910-0001', '2026-09-10', '1001', '4001', 80000, '門市現金銷售'
    UNION ALL SELECT '20260912-0001', '2026-09-12', '1003', '4002', 45000, '提供顧問服務，款項待收'
    UNION ALL SELECT '20260915-0001', '2026-09-15', '5004', '1001', 3200, '繳納水電費'
    UNION ALL SELECT '20260920-0001', '2026-09-20', '1002', '1003', 45000, '收回顧問服務款項'
    UNION ALL SELECT '20260925-0001', '2026-09-25', '5002', '1002', 60000, '發放九月薪資'
    UNION ALL SELECT '20260928-0001', '2026-09-28', '2001', '1002', 30000, '支付供應商貨款'
) v
JOIN accounts d ON d.account_code = v.debit_code
JOIN accounts c ON c.account_code = v.credit_code
JOIN users u ON u.username = 'admin';

UPDATE accounts a SET balance =
    (SELECT COALESCE(SUM(j.amount), 0) FROM journal_entries j WHERE j.debit_account = a.account_id)
  - (SELECT COALESCE(SUM(j.amount), 0) FROM journal_entries j WHERE j.credit_account = a.account_id);

<?php
/**
 * 資料庫安裝（僅限命令列）：php database/install.php
 * 依 config 的資料庫與 TABLE_PREFIX 建立資料表並匯入預設資料，並為預設帳號產生隨機密碼
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/config/config.php';

$tables = ['users', 'accounts', 'journal_entries', 'audit_logs', 'system_settings', 'ai_recognition_logs'];
$pdo = Database::getInstance()->getPDO();
$pdo->exec('SET NAMES utf8mb4');

$existing = $pdo->query("SHOW TABLES LIKE " . $pdo->quote(TABLE_PREFIX . 'users'))->fetchColumn();
if ($existing) {
    fwrite(STDERR, "資料表 " . TABLE_PREFIX . "users 已存在，為避免覆蓋資料已停止安裝。\n");
    exit(1);
}

$sql = file_get_contents(__DIR__ . '/init.sql');
$sql = preg_replace('/^\s*(CREATE DATABASE|USE)\b[^;]*;/mi', '', $sql);
$sql = preg_replace('/^\s*--.*$/m', '', $sql);
$names = implode('|', $tables);
$sql = preg_replace('/\b(TABLE(?: IF NOT EXISTS)?|INTO|REFERENCES)\s+(' . $names . ')\b/', '$1 ' . TABLE_PREFIX . '$2', $sql);

$statements = array_filter(array_map('trim', explode(';', $sql)));
foreach ($statements as $statement) {
    $pdo->exec($statement);
}

$passwords = [];
foreach (['admin', 'user1'] as $username) {
    $password = substr(strtr(base64_encode(random_bytes(12)), '+/', 'Kx'), 0, 14);
    $stmt = $pdo->prepare('UPDATE ' . TABLE_PREFIX . 'users SET password = ? WHERE username = ?');
    $stmt->execute([password_hash($password, PASSWORD_BCRYPT), $username]);
    $passwords[$username] = $password;
}

echo "已建立 " . count($statements) . " 個 SQL 敘述，資料表前綴：" . TABLE_PREFIX . "\n";
foreach ($pdo->query("SHOW TABLES LIKE " . $pdo->quote(TABLE_PREFIX . '%'))->fetchAll(PDO::FETCH_COLUMN) as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "  $t: $count 筆\n";
}
echo "預設帳號已設定隨機密碼（請登入後立即修改）：\n";
foreach ($passwords as $username => $password) {
    echo "  $username / $password\n";
}

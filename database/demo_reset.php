<?php
/**
 * 重建示範資料（僅限命令列）：php database/demo_reset.php
 * 僅允許在示範站（DEMO_MODE 且資料表前綴含 demo）或 GitHub Codespaces 中執行，避免誤刪正式資料
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/config/config.php';

$isDemoSite = DEMO_MODE && str_contains(TABLE_PREFIX, 'demo');
$isCodespace = getenv('CODESPACES') === 'true';
if (!$isDemoSite && !$isCodespace) {
    fwrite(STDERR, "拒絕執行：僅限示範站（DEMO_MODE 且前綴含 demo）或 Codespaces 環境\n");
    exit(1);
}

$tables = ['ai_recognition_logs', 'audit_logs', 'journal_entries', 'system_settings', 'accounts', 'users'];
$pdo = Database::getInstance()->getPDO();
$pdo->exec('SET NAMES utf8mb4');

$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach ($tables as $table) {
    $pdo->exec('DROP TABLE IF EXISTS `' . TABLE_PREFIX . $table . '`');
}
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

$demoPassword = $isDemoSite ? DEMO_PASSWORD : 'demo1234';
$sql = file_get_contents(__DIR__ . '/init.sql') . ";\n"
    . str_replace('__DEMO_HASH__', password_hash($demoPassword, PASSWORD_BCRYPT), file_get_contents(__DIR__ . '/demo_data.sql'));
$sql = preg_replace('/^\s*(CREATE DATABASE|USE)\b[^;]*;/mi', '', $sql);
$sql = preg_replace('/^\s*--.*$/m', '', $sql);
$names = implode('|', $tables);
$sql = preg_replace_callback(
    '/\b(TABLE(?: IF NOT EXISTS)?|INTO|REFERENCES|FROM|JOIN|UPDATE)\s+(' . $names . ')\b/',
    fn($m) => $m[1] . ' ' . TABLE_PREFIX . $m[2],
    $sql
);

foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
    $pdo->exec($statement);
}

// 公開示範站只開放示範帳號，預設帳號改為隨機密碼；Codespaces 為個人環境，沿用預設 admin / admin123
if ($isDemoSite) {
    $stmt = $pdo->prepare('UPDATE ' . TABLE_PREFIX . 'users SET password = ? WHERE username = ?');
    foreach (['admin', 'user1'] as $username) {
        $stmt->execute([password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT), $username]);
    }
}

$entries = $pdo->query('SELECT COUNT(*) FROM ' . TABLE_PREFIX . 'journal_entries')->fetchColumn();
echo date('Y-m-d H:i:s') . " 示範資料已重建（前綴 " . TABLE_PREFIX . "，範例交易 $entries 筆）\n";

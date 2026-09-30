<?php
/**
 * 會計帳務系統 - 系統配置文件
 */

// 各環境專屬設定（資料庫、網址、資料表前綴）放在 config/local.php，不隨程式碼複製；未設定者使用下方本機預設值
if (is_file(__DIR__ . '/local.php')) {
    require __DIR__ . '/local.php';
}

// 資料庫配置
defined('DB_HOST') || define('DB_HOST', 'localhost');
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');
defined('DB_NAME') || define('DB_NAME', 'accounting_system');
defined('DB_PORT') || define('DB_PORT', 3306);
defined('TABLE_PREFIX') || define('TABLE_PREFIX', '');

// OpenAI 設定：金鑰優先使用管理員在「AI 設定」頁面儲存的值（config/ai_key.php），其次為環境變數 OPENAI_API_KEY
define('OPENAI_KEY_FILE', __DIR__ . '/ai_key.php');
$storedAiKey = is_file(OPENAI_KEY_FILE) ? (string)(include OPENAI_KEY_FILE) : '';
define('OPENAI_API_KEY', $storedAiKey !== '' ? $storedAiKey : (getenv('OPENAI_API_KEY') ?: ''));
define('OPENAI_KEY_SOURCE', $storedAiKey !== '' ? 'page' : (getenv('OPENAI_API_KEY') ? 'env' : 'none'));
unset($storedAiKey);
define('OPENAI_API_BASE', 'https://api.openai.com/v1');
define('OPENAI_TRANSCRIBE_MODEL', 'gpt-4o-transcribe');
define('OPENAI_PARSE_MODEL', 'gpt-4o');
define('AI_MAX_UPLOAD_BYTES', 20 * 1024 * 1024);
define('AI_LOW_CONFIDENCE', 0.6);

// 系統配置
define('SITE_NAME', '會計帳務管理系統');
defined('SITE_URL') || define('SITE_URL', 'http://localhost/accounting_system/public/');
define('SITE_ROOT', dirname(dirname(__FILE__)));

// 公司資訊
define('COMPANY_NAME', '宥鋐會計師事務所');
define('COMPANY_ADDRESS', '台北市');

// 時區設定
date_default_timezone_set('Asia/Taipei');

// 錯誤報告：正式環境（APP_DEBUG=false）不在網頁顯示錯誤細節，只寫入伺服器錯誤日誌
defined('APP_DEBUG') || define('APP_DEBUG', true);
defined('SHOW_DEMO_ACCOUNT') || define('SHOW_DEMO_ACCOUNT', APP_DEBUG);
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

// 會話配置
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

// 常數定義
define('USER_ROLE_ADMIN', 'admin');
define('USER_ROLE_USER', 'user');

// 帳戶類型
define('ACCOUNT_TYPE_ASSET', 'asset');
define('ACCOUNT_TYPE_LIABILITY', 'liability');
define('ACCOUNT_TYPE_EQUITY', 'equity');
define('ACCOUNT_TYPE_REVENUE', 'revenue');
define('ACCOUNT_TYPE_EXPENSE', 'expense');

// 日期格式
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'Y年m月d日');

// 金額格式
define('CURRENCY_SYMBOL', 'NT$');
define('DECIMAL_PLACES', 2);

// 分頁設定
define('ITEMS_PER_PAGE', 10);

// 檔案上傳配置
define('UPLOAD_DIR', SITE_ROOT . '/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// 功能開關
define('ENABLE_MULTI_CURRENCY', false);
define('ENABLE_AUDIT_LOG', true);
define('ENABLE_AUTO_BACKUP', true);

// 安全設定
define('PASSWORD_MIN_LENGTH', 6);
define('SESSION_TIMEOUT', 3600); // 1小時

// 自動載入函數
function autoload($class) {
    $paths = [
        SITE_ROOT . '/app/models/',
        SITE_ROOT . '/app/controllers/',
        SITE_ROOT . '/app/lib/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
}

spl_autoload_register('autoload');
?>

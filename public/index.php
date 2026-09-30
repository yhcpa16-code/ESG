<?php
/**
 * 會計帳務管理系統 - 主入口檔案
 */

// 載入配置
require_once dirname(dirname(__FILE__)) . '/config/config.php';

// 獲取控制器和動作
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// 路由映射
$routes = [
    'auth' => 'AuthController',
    'dashboard' => 'DashboardController',
    'account' => 'AccountController',
    'journal' => 'JournalController',
    'report' => 'ReportController',
    'ai' => 'AiController',
    'settings' => 'SettingsController',
];

// 檢查控制器是否存在
if (!isset($routes[$controller])) {
    // 預設為儀表板
    $controllerClass = 'DashboardController';
} else {
    $controllerClass = $routes[$controller];
}

// 檢查認證 - 除了登入頁面外
if ($controller !== 'auth' || $action !== 'login') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . 'index.php?controller=auth&action=login');
        exit;
    }
}

try {
    // 實例化控制器
    $controllerInstance = new $controllerClass();

    // 檢查動作方法是否存在
    if (!is_callable([$controllerInstance, $action])) {
        $action = 'index';
    }

    // 將錯誤訊息和成功訊息暫存到全域變數，以便在視圖中使用
    if (method_exists($controllerInstance, 'getErrors')) {
        $GLOBALS['errors'] = $controllerInstance->getErrors();
    }
    if (method_exists($controllerInstance, 'getMessages')) {
        $GLOBALS['messages'] = $controllerInstance->getMessages();
    }

    // 執行動作
    $controllerInstance->$action();

} catch (Throwable $e) {
    error_log('[accounting] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo APP_DEBUG ? '錯誤: ' . htmlspecialchars($e->getMessage()) : '系統發生錯誤，請稍後再試或聯絡系統管理員';
}
?>

<?php
/**
 * OpenAI API 金鑰儲存（config/ai_key.php，該目錄禁止瀏覽器直接存取）
 */
class AiKeyStore {
    public static function isValidFormat($key) {
        return (bool)preg_match('/^sk-[A-Za-z0-9_\-]{20,300}$/', $key);
    }

    public static function save($key) {
        if (!self::isValidFormat($key)) {
            throw new Exception('金鑰格式不正確');
        }
        $content = "<?php\n// 由「AI 設定」頁面產生，請勿分享或提交至版本控制\nreturn " . var_export($key, true) . ";\n";
        $tmp = OPENAI_KEY_FILE . '.tmp';
        if (file_put_contents($tmp, $content, LOCK_EX) === false || !rename($tmp, OPENAI_KEY_FILE)) {
            @unlink($tmp);
            throw new Exception('無法寫入金鑰檔案，請確認 config 目錄的寫入權限');
        }
        self::invalidateCache();
    }

    public static function clear() {
        if (is_file(OPENAI_KEY_FILE) && !unlink(OPENAI_KEY_FILE)) {
            throw new Exception('無法刪除金鑰檔案');
        }
        self::invalidateCache();
    }

    public static function mask($key) {
        if ($key === '') {
            return '';
        }
        return substr($key, 0, 7) . '…' . substr($key, -4);
    }

    private static function invalidateCache() {
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate(OPENAI_KEY_FILE, true);
        }
    }
}
?>

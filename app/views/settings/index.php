<?php include SITE_ROOT . '/app/views/layouts/header.php'; ?>
<?php
$sourceLabels = [
    'page' => '網頁設定（config/ai_key.php）',
    'env' => 'Windows 環境變數 OPENAI_API_KEY',
    'none' => '尚未設定',
];
$action = SITE_URL . 'index.php?controller=settings&action=';
?>

<div class="content-container settings-page">
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-static">
        <?php foreach ($errors as $err): ?><p><?php echo htmlspecialchars($err); ?></p><?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($messages)): ?>
    <div class="alert alert-success alert-static">
        <?php foreach ($messages as $msg): ?><p><?php echo htmlspecialchars($msg); ?></p><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><h3>OpenAI API 金鑰</h3></div>
        <div class="card-body">
            <table class="table-bordered settings-status">
                <tr>
                    <th>狀態</th>
                    <td><?php echo $keySource === 'none' ? '<span class="status-off">未設定，AI 記帳無法使用</span>' : '<span class="status-on">已設定</span>'; ?></td>
                </tr>
                <tr>
                    <th>來源</th>
                    <td><?php echo htmlspecialchars($sourceLabels[$keySource]); ?></td>
                </tr>
                <?php if ($maskedKey !== ''): ?>
                <tr>
                    <th>目前金鑰</th>
                    <td><code><?php echo htmlspecialchars($maskedKey); ?></code></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>使用模型</th>
                    <td>語音：<code><?php echo htmlspecialchars(OPENAI_TRANSCRIBE_MODEL); ?></code>　分錄／照片：<code><?php echo htmlspecialchars(OPENAI_PARSE_MODEL); ?></code></td>
                </tr>
            </table>

            <?php if ($keySource !== 'none'): ?>
            <div class="settings-actions">
                <form method="POST" action="<?php echo $action; ?>test">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <button type="submit" class="btn-secondary">測試連線</button>
                </form>
                <?php if ($keySource === 'page'): ?>
                <form method="POST" action="<?php echo $action; ?>clear" onsubmit="return confirm('確定要清除金鑰嗎？清除後 AI 記帳將無法使用。');">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <button type="submit" class="btn-danger">清除金鑰</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3><?php echo $keySource === 'none' ? '設定金鑰' : '更換金鑰'; ?></h3></div>
        <div class="card-body">
            <form method="POST" action="<?php echo $action; ?>save" class="form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <div class="form-group">
                    <label for="api_key">OpenAI API 金鑰</label>
                    <input type="password" id="api_key" name="api_key" placeholder="sk-..." autocomplete="new-password" spellcheck="false" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">驗證並儲存</button>
                </div>
            </form>
            <ul class="settings-notes">
                <li>儲存前會先向 OpenAI 確認金鑰可以使用上述兩個模型，驗證失敗不會儲存。</li>
                <li>金鑰只存放在伺服器的 <code>config/ai_key.php</code>，網頁上不會顯示完整內容。</li>
                <li>網頁設定的金鑰優先於 Windows 環境變數。</li>
                <li>金鑰可在 OpenAI 平台（platform.openai.com）的 API keys 頁面建立或作廢；建議同時設定每月用量上限。</li>
            </ul>
        </div>
    </div>
</div>

<style>
.settings-page .card { max-width: 760px; }
.settings-status th { width: 120px; }
.status-on { color: #047857; font-weight: 600; }
.status-off { color: #b91c1c; font-weight: 600; }
.settings-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.settings-notes { margin: 20px 0 0 18px; color: #6b7280; font-size: 13px; line-height: 1.8; }
</style>

<?php include SITE_ROOT . '/app/views/layouts/footer.php'; ?>

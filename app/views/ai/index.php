<?php include SITE_ROOT . '/app/views/layouts/header.php'; ?>
<?php
$f = $form ?? [];
$showReview = !empty($f);
?>

<div class="content-container">
    <?php if (!$aiConfigured): ?>
    <div class="alert alert-warning alert-static">
        <?php if (DEMO_MODE): ?>
            示範站未開放 AI 語音／拍照辨識。想實際試用，可從 GitHub 下載本系統，或用 Codespaces 建立自己的環境並設定 OpenAI 金鑰。
        <?php elseif (($_SESSION['role'] ?? '') === USER_ROLE_ADMIN): ?>
            尚未設定 OpenAI API 金鑰，目前無法使用 AI 辨識。請至 <a href="<?php echo SITE_URL; ?>index.php?controller=settings&action=index">AI 設定</a> 貼上金鑰。
        <?php else: ?>
            尚未設定 OpenAI API 金鑰，目前無法使用 AI 辨識。請聯絡系統管理員至「AI 設定」頁面設定金鑰。
        <?php endif; ?>
        需要手動記帳時，請使用「日記帳」→「新增日記帳」。
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-static">
        <?php foreach ($errors as $err): ?>
            <p><?php echo htmlspecialchars($err); ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="ai-grid">
        <div class="card">
            <div class="card-header"><h3>🎙️ 語音記帳</h3></div>
            <div class="card-body">
                <p class="text-muted ai-hint">口述交易內容，例如：「今天用現金買文具 350 元」。</p>
                <div class="ai-actions">
                    <button type="button" id="ai-rec-start" class="btn-primary" <?php echo $aiConfigured ? '' : 'disabled'; ?>>開始錄音</button>
                    <button type="button" id="ai-rec-stop" class="btn-danger" disabled>停止並辨識</button>
                    <span id="ai-rec-timer" class="ai-timer"></span>
                </div>
                <label class="ai-upload">或上傳音檔
                    <input type="file" id="ai-audio-file" accept="audio/*" capture <?php echo $aiConfigured ? '' : 'disabled'; ?>>
                </label>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>📷 拍照記帳</h3></div>
            <div class="card-body">
                <p class="text-muted ai-hint">拍攝或上傳發票、收據，請讓金額與日期清楚入鏡。</p>
                <label class="btn-primary ai-file-btn <?php echo $aiConfigured ? '' : 'is-disabled'; ?>">拍照／選擇圖片
                    <input type="file" id="ai-image-file" accept="image/*" capture="environment" <?php echo $aiConfigured ? '' : 'disabled'; ?>>
                </label>
                <img id="ai-image-preview" class="ai-preview" alt="單據預覽" hidden>
            </div>
        </div>
    </div>

    <div id="ai-status" class="alert alert-info alert-static" hidden></div>

    <div class="card" id="ai-review" <?php echo $showReview ? '' : 'hidden'; ?>>
        <div class="card-header">
            <h3>覆核分錄草稿</h3>
            <span id="ai-confidence" class="text-muted"></span>
        </div>
        <div class="card-body">
            <div id="ai-warnings" class="alert alert-warning alert-static" hidden></div>

            <form method="POST" action="<?php echo SITE_URL; ?>index.php?controller=ai&action=save" class="form ai-form">
                <input type="hidden" name="log_id" id="ai-log-id" value="<?php echo (int)($f['log_id'] ?? 0); ?>">

                <div class="form-group">
                    <label for="ai-source-text">辨識內容</label>
                    <textarea id="ai-source-text" name="source_text" rows="3" readonly><?php echo htmlspecialchars($f['source_text'] ?? ''); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-col-6">
                        <div class="form-group">
                            <label for="ai-voucher-no">憑證號 *</label>
                            <input type="text" id="ai-voucher-no" name="voucher_no" value="<?php echo htmlspecialchars($voucher_no); ?>" required>
                        </div>
                    </div>
                    <div class="form-col-6">
                        <div class="form-group">
                            <label for="ai-entry-date">日期 *</label>
                            <input type="date" id="ai-entry-date" name="entry_date" value="<?php echo htmlspecialchars($f['entry_date'] ?? date('Y-m-d')); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <?php foreach (['debit_account' => '借方科目', 'credit_account' => '貸方科目'] as $field => $label): ?>
                    <div class="form-col-6">
                        <div class="form-group">
                            <label for="ai-<?php echo $field; ?>"><?php echo $label; ?> *</label>
                            <select id="ai-<?php echo $field; ?>" name="<?php echo $field; ?>" required>
                                <option value="">-- 請選擇 --</option>
                                <?php foreach ($accounts as $acc): ?>
                                <option value="<?php echo $acc['account_id']; ?>" <?php echo (int)($f[$field] ?? 0) === (int)$acc['account_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($acc['account_code'] . ' - ' . $acc['account_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-group">
                    <label for="ai-amount">金額 *</label>
                    <input type="number" id="ai-amount" name="amount" step="0.01" min="0.01" value="<?php echo htmlspecialchars((string)($f['amount'] ?? '')); ?>" required>
                </div>

                <div class="form-group">
                    <label for="ai-description">說明</label>
                    <textarea id="ai-description" name="description" rows="2"><?php echo htmlspecialchars($f['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">確認過帳</button>
                    <a href="<?php echo SITE_URL; ?>index.php?controller=ai&action=index" class="btn-secondary">放棄重來</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.ai-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
.ai-hint { margin-bottom: 15px; }
.ai-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 15px; }
.ai-timer { font-variant-numeric: tabular-nums; color: #ef4444; font-weight: 600; }
.ai-upload { display: block; font-size: 13px; color: #6b7280; }
.ai-upload input { display: block; margin-top: 6px; }
.ai-file-btn { position: relative; overflow: hidden; }
.ai-file-btn input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.ai-file-btn.is-disabled { opacity: .5; pointer-events: none; }
.ai-preview { display: block; max-width: 100%; max-height: 240px; margin-top: 15px; border: 1px solid #e5e7eb; border-radius: 6px; }
.ai-preview[hidden] { display: none; }
.ai-form { max-width: none; }
button[disabled] { opacity: .5; cursor: not-allowed; }
</style>

<script>
(function () {
    const recognizeUrl = <?php echo json_encode(SITE_URL . 'index.php?controller=ai&action=recognize'); ?>;
    const $ = (id) => document.getElementById(id);
    const statusEl = $('ai-status');
    const startBtn = $('ai-rec-start');
    const stopBtn = $('ai-rec-stop');
    const timerEl = $('ai-rec-timer');
    let recorder = null, chunks = [], timer = null, busy = false;

    function setStatus(message, type) {
        statusEl.hidden = !message;
        statusEl.className = 'alert alert-static alert-' + (type || 'info');
        statusEl.textContent = message || '';
    }

    function extensionFor(mimeType) {
        if (mimeType.includes('ogg')) return 'ogg';
        if (mimeType.includes('mp4')) return 'mp4';
        return 'webm';
    }

    startBtn && startBtn.addEventListener('click', async function () {
        if (!navigator.mediaDevices || !window.MediaRecorder) {
            setStatus('此瀏覽器或連線方式不支援錄音（需 HTTPS 或 localhost），請改用「上傳音檔」。', 'warning');
            return;
        }
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            chunks = [];
            recorder = new MediaRecorder(stream);
            recorder.ondataavailable = (e) => { if (e.data.size) chunks.push(e.data); };
            recorder.onstop = () => {
                stream.getTracks().forEach((t) => t.stop());
                const type = recorder.mimeType || 'audio/webm';
                send(new Blob(chunks, { type: type }), 'recording.' + extensionFor(type), 'audio');
            };
            recorder.start();
            const started = Date.now();
            timer = setInterval(() => {
                const s = Math.floor((Date.now() - started) / 1000);
                timerEl.textContent = '● 錄音中 ' + String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
            }, 250);
            startBtn.disabled = true;
            stopBtn.disabled = false;
            setStatus('錄音中，說完後請按「停止並辨識」。', 'info');
        } catch (err) {
            setStatus('無法使用麥克風：' + err.message + '。請允許瀏覽器存取麥克風，或改用上傳音檔。', 'danger');
        }
    });

    stopBtn && stopBtn.addEventListener('click', function () {
        clearInterval(timer);
        timerEl.textContent = '';
        stopBtn.disabled = true;
        startBtn.disabled = false;
        if (recorder && recorder.state !== 'inactive') recorder.stop();
    });

    $('ai-audio-file').addEventListener('change', function () {
        if (this.files[0]) send(this.files[0], this.files[0].name, 'audio');
        this.value = '';
    });

    $('ai-image-file').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const preview = $('ai-image-preview');
        preview.src = URL.createObjectURL(file);
        preview.hidden = false;
        send(file, file.name, 'image');
        this.value = '';
    });

    async function send(blob, fileName, sourceType) {
        if (busy) return;
        busy = true;
        setStatus(sourceType === 'audio' ? 'AI 正在辨識語音並產生分錄，約需 5 到 30 秒…' : 'AI 正在辨識單據並產生分錄，約需 5 到 30 秒…', 'info');

        const body = new FormData();
        body.append('source_type', sourceType);
        body.append('file', blob, fileName);

        try {
            const response = await fetch(recognizeUrl, { method: 'POST', body: body, credentials: 'same-origin' });
            let data;
            try {
                data = await response.json();
            } catch (e) {
                throw new Error('伺服器回應異常，可能是登入逾時，請重新整理頁面後再試');
            }
            if (!data.ok) throw new Error(data.error || '辨識失敗');
            fill(data);
            setStatus('辨識完成，請核對下方分錄後按「確認過帳」。', 'success');
        } catch (err) {
            setStatus(err.message, 'danger');
        } finally {
            busy = false;
        }
    }

    function fill(data) {
        const d = data.draft;
        $('ai-log-id').value = data.log_id;
        $('ai-source-text').value = data.source_text || '';
        $('ai-entry-date').value = d.entry_date;
        $('ai-debit_account').value = d.debit_account || '';
        $('ai-credit_account').value = d.credit_account || '';
        $('ai-amount').value = d.amount;
        $('ai-description').value = d.description;
        $('ai-confidence').textContent = 'AI 信心度：' + Math.round(d.confidence * 100) + '%';

        const warnings = $('ai-warnings');
        const items = d.warnings.slice();
        if (d.notes) items.push('AI 備註：' + d.notes);
        warnings.replaceChildren(...items.map((text) => {
            const p = document.createElement('p');
            p.textContent = text;
            return p;
        }));
        warnings.hidden = items.length === 0;

        const review = $('ai-review');
        review.hidden = false;
        review.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
})();
</script>

<?php include SITE_ROOT . '/app/views/layouts/footer.php'; ?>

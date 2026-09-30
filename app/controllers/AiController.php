<?php
/**
 * AI 語音／拍照記帳控制器
 */
class AiController {
    private $bookkeeper;
    private $journalModel;
    private $accountModel;
    private $errors = [];

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . SITE_URL . 'index.php?controller=auth&action=login');
            exit;
        }
        $this->bookkeeper = new AiBookkeeper();
        $this->journalModel = new JournalEntry();
        $this->accountModel = new Account();
    }

    public function index($form = null) {
        $accounts = $this->accountModel->getAll();
        $voucher_no = $form['voucher_no'] ?? $this->journalModel->generateVoucherNo();
        $aiConfigured = OPENAI_API_KEY !== '';
        $maxUploadMb = AI_MAX_UPLOAD_BYTES / 1024 / 1024;
        $errors = $this->errors;
        $pageTitle = 'AI 語音／拍照記帳';
        include SITE_ROOT . '/app/views/ai/index.php';
    }

    public function recognize() {
        header('Content-Type: application/json; charset=utf-8');
        set_time_limit(180);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('請使用 POST 上傳檔案', 405);
        }

        $sourceType = $_POST['source_type'] ?? '';
        if (!in_array($sourceType, ['audio', 'image'], true)) {
            $this->jsonError('辨識類型錯誤', 400);
        }

        $file = $_FILES['file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->jsonError('檔案上傳失敗，請重新選擇檔案', 400);
        }
        if ($file['size'] > AI_MAX_UPLOAD_BYTES) {
            $this->jsonError('檔案超過 ' . (AI_MAX_UPLOAD_BYTES / 1024 / 1024) . ' MB 上限', 413);
        }

        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if ($sourceType === 'audio' && !$this->bookkeeper->audioExtension($mimeType)) {
            $this->jsonError('不支援的音檔格式（' . $mimeType . '），請使用 webm、mp3、m4a、wav、ogg 或 flac', 415);
        }
        if ($sourceType === 'image' && !$this->bookkeeper->isSupportedImage($mimeType)) {
            $this->jsonError('不支援的圖片格式（' . $mimeType . '），請使用 JPG、PNG、WebP 或 GIF', 415);
        }

        try {
            $result = $sourceType === 'audio'
                ? $this->bookkeeper->fromAudio($file['tmp_name'], $mimeType)
                : $this->bookkeeper->fromImage($file['tmp_name'], $mimeType);

            $logId = $this->bookkeeper->logRecognition(
                $_SESSION['user_id'], $sourceType, $result['source_text'], $result['raw'], 'draft'
            );

            echo json_encode([
                'ok' => true,
                'log_id' => (int)$logId,
                'source_text' => $result['source_text'],
                'draft' => $result['draft'],
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            $this->bookkeeper->logRecognition($_SESSION['user_id'], $sourceType, null, null, 'failed', $e->getMessage());
            $this->jsonError($e->getMessage(), 502);
        }
        exit;
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . SITE_URL . 'index.php?controller=ai&action=index');
            exit;
        }

        $data = [
            'voucher_no' => trim($_POST['voucher_no'] ?? ''),
            'entry_date' => $_POST['entry_date'] ?? '',
            'debit_account' => (int)($_POST['debit_account'] ?? 0),
            'credit_account' => (int)($_POST['credit_account'] ?? 0),
            'amount' => (float)($_POST['amount'] ?? 0),
            'description' => trim($_POST['description'] ?? ''),
            'created_by' => $_SESSION['user_id'],
        ];
        $logId = (int)($_POST['log_id'] ?? 0);

        if ($data['voucher_no'] === '') {
            $this->errors[] = '憑證號碼必填';
        } elseif ($this->journalModel->getEntryByVoucher($data['voucher_no'])) {
            $this->errors[] = '憑證號碼已存在，請更換';
        }
        if ($data['entry_date'] === '') {
            $this->errors[] = '日期必填';
        }
        if ($data['debit_account'] === 0 || $data['credit_account'] === 0) {
            $this->errors[] = '借方與貸方科目皆須選擇';
        } elseif ($data['debit_account'] === $data['credit_account']) {
            $this->errors[] = '借方和貸方科目不能相同';
        }
        if ($data['amount'] <= 0) {
            $this->errors[] = '金額必須大於零';
        }

        if (empty($this->errors)) {
            try {
                $entryId = $this->journalModel->create($data);
                if ($logId > 0) {
                    $this->bookkeeper->markPosted($logId, $_SESSION['user_id'], $entryId);
                }
                header('Location: ' . SITE_URL . 'index.php?controller=journal&action=view&id=' . $entryId);
                exit;
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        $form = $data + ['log_id' => $logId, 'source_text' => $_POST['source_text'] ?? ''];
        $this->index($form);
    }

    private function jsonError($message, $status) {
        http_response_code($status);
        echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>

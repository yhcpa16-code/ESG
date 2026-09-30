<?php
/**
 * AI 記帳：將語音或單據照片轉為日記帳分錄草稿，並保存辨識紀錄
 */
class AiBookkeeper {
    private $db;
    private $accountModel;

    const AUDIO_TYPES = [
        'audio/webm' => 'webm', 'video/webm' => 'webm',
        'audio/ogg' => 'ogg', 'video/ogg' => 'ogg',
        'audio/mpeg' => 'mp3', 'audio/mp3' => 'mp3',
        'audio/mp4' => 'mp4', 'video/mp4' => 'mp4', 'audio/x-m4a' => 'm4a', 'audio/m4a' => 'm4a',
        'audio/wav' => 'wav', 'audio/x-wav' => 'wav', 'audio/wave' => 'wav',
        'audio/flac' => 'flac', 'audio/x-flac' => 'flac',
    ];

    const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    const DRAFT_SCHEMA = [
        'type' => 'object',
        'properties' => [
            'entry_date' => ['type' => 'string', 'description' => '交易日期，YYYY-MM-DD'],
            'debit_account_code' => ['type' => 'string', 'description' => '借方科目編碼'],
            'credit_account_code' => ['type' => 'string', 'description' => '貸方科目編碼'],
            'amount' => ['type' => 'number', 'description' => '新台幣含稅總額'],
            'description' => ['type' => 'string', 'description' => '交易摘要'],
            'recognized_text' => ['type' => 'string', 'description' => '從單據辨識出的重點文字'],
            'confidence' => ['type' => 'number', 'description' => '0 到 1 的信心度'],
            'notes' => ['type' => 'string', 'description' => '需人工確認之處'],
        ],
        'required' => ['entry_date', 'debit_account_code', 'credit_account_code', 'amount',
                       'description', 'recognized_text', 'confidence', 'notes'],
        'additionalProperties' => false,
    ];

    public function __construct() {
        $this->db = Database::getInstance();
        $this->accountModel = new Account();
    }

    public function audioExtension($mimeType) {
        return self::AUDIO_TYPES[$mimeType] ?? null;
    }

    public function isSupportedImage($mimeType) {
        return in_array($mimeType, self::IMAGE_TYPES, true);
    }

    public function fromAudio($filePath, $mimeType) {
        $client = new OpenAIClient();
        $text = $client->transcribe($filePath, $mimeType, 'audio.' . $this->audioExtension($mimeType));
        if ($text === '') {
            throw new Exception('未辨識到任何語音內容，請靠近麥克風重新錄音');
        }

        $raw = $this->requestDraft($client, [
            ['type' => 'text', 'text' => "以下是作業人員口述的交易內容（語音轉文字）：\n" . $text],
        ]);
        return ['source_text' => $text, 'raw' => $raw, 'draft' => $this->normalize($raw)];
    }

    public function fromImage($filePath, $mimeType) {
        $client = new OpenAIClient();
        $dataUrl = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($filePath));

        $raw = $this->requestDraft($client, [
            ['type' => 'text', 'text' => '以下是交易單據（發票、收據或帳單）的照片，請辨識並產生分錄草稿。'],
            ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
        ]);
        return ['source_text' => $raw['recognized_text'] ?? '', 'raw' => $raw, 'draft' => $this->normalize($raw)];
    }

    private function requestDraft(OpenAIClient $client, array $userContent) {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
            ['role' => 'user', 'content' => $userContent],
        ];
        return $client->chatJson($messages, 'journal_entry_draft', self::DRAFT_SCHEMA);
    }

    private function systemPrompt() {
        $typeNames = ['asset' => '資產', 'liability' => '負債', 'equity' => '權益', 'revenue' => '收入', 'expense' => '支出'];
        $lines = [];
        foreach ($this->accountModel->getAll() as $account) {
            $type = $typeNames[$account['account_type']] ?? $account['account_type'];
            $lines[] = $account['account_code'] . ' ' . $account['account_name'] . '（' . $type . '）';
        }
        $today = date('Y-m-d');

        return "你是台灣中小企業的記帳助理，負責把作業人員提供的交易資訊整理成一筆複式記帳分錄草稿（一借一貸），草稿會由人工覆核後才過帳。\n"
            . "規則：\n"
            . "1. 借方與貸方科目只能從下方科目清單中選擇，填入科目編碼，兩者不可相同。\n"
            . "2. amount 為新台幣含稅總額，正數，不含貨幣符號與千分位。\n"
            . "3. entry_date 使用 YYYY-MM-DD；民國年請加 1911 轉為西元年；無法判斷時使用今天 {$today}。\n"
            . "4. 付款方式不明時，視為現金交易。\n"
            . "5. description 以繁體中文簡述交易（30 字以內），可包含對象與品項。\n"
            . "6. recognized_text 摘錄單據上的關鍵文字（店名、統一編號、日期、品項、總額）；語音來源請填空字串。\n"
            . "7. confidence 為 0 到 1；資訊不完整、模糊或科目判斷不確定時請調低，並在 notes 用繁體中文說明需要人工確認之處，無則填空字串。\n"
            . "8. 若內容不是可記帳的交易，amount 填 0，並在 notes 說明原因。\n\n"
            . "科目清單：\n" . implode("\n", $lines);
    }

    private function normalize(array $raw) {
        $warnings = [];

        $debit = $this->accountModel->getAccountByCode((string)($raw['debit_account_code'] ?? ''));
        $credit = $this->accountModel->getAccountByCode((string)($raw['credit_account_code'] ?? ''));
        if (!$debit) {
            $warnings[] = '借方科目無法對應，請手動選擇';
        }
        if (!$credit) {
            $warnings[] = '貸方科目無法對應，請手動選擇';
        }
        if ($debit && $credit && $debit['account_id'] == $credit['account_id']) {
            $warnings[] = '借方與貸方科目相同，請修正';
        }

        $amount = round((float)($raw['amount'] ?? 0), 2);
        if ($amount <= 0) {
            $warnings[] = '未取得有效金額，請手動輸入';
        }

        $date = (string)($raw['entry_date'] ?? '');
        $parsed = DateTime::createFromFormat('Y-m-d', $date);
        if (!$parsed || $parsed->format('Y-m-d') !== $date) {
            $warnings[] = '日期無法辨識，已預設為今天';
            $date = date('Y-m-d');
        }

        $confidence = max(0, min(1, (float)($raw['confidence'] ?? 0)));
        if ($confidence < AI_LOW_CONFIDENCE) {
            $warnings[] = 'AI 信心度偏低，請仔細核對每個欄位';
        }

        return [
            'entry_date' => $date,
            'debit_account' => $debit ? (int)$debit['account_id'] : 0,
            'credit_account' => $credit ? (int)$credit['account_id'] : 0,
            'amount' => $amount > 0 ? $amount : '',
            'description' => mb_substr(trim((string)($raw['description'] ?? '')), 0, 200),
            'confidence' => $confidence,
            'notes' => trim((string)($raw['notes'] ?? '')),
            'warnings' => $warnings,
        ];
    }

    public function logRecognition($userId, $sourceType, $sourceText, $raw, $status, $error = null) {
        return $this->db->insert('ai_recognition_logs', [
            'user_id' => $userId,
            'source_type' => $sourceType,
            'source_text' => $sourceText,
            'ai_result' => $raw === null ? null : json_encode($raw, JSON_UNESCAPED_UNICODE),
            'model' => $sourceType === 'audio'
                ? OPENAI_TRANSCRIBE_MODEL . ' + ' . OPENAI_PARSE_MODEL
                : OPENAI_PARSE_MODEL,
            'status' => $status,
            'error_message' => $error,
        ]);
    }

    public function markPosted($logId, $userId, $entryId) {
        return $this->db->update(
            'ai_recognition_logs',
            ['status' => 'posted', 'entry_id' => $entryId],
            'log_id = ? AND user_id = ? AND status = ?',
            [$logId, $userId, 'draft']
        );
    }
}
?>

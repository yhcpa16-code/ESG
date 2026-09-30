<?php
/**
 * OpenAI API 用戶端（語音轉文字、結構化 JSON 輸出）
 */
class OpenAIClient {
    private $apiKey;

    public function __construct($apiKey = null) {
        $apiKey = $apiKey ?? OPENAI_API_KEY;
        if ($apiKey === '') {
            throw new Exception('尚未設定 OpenAI API 金鑰，請系統管理員至「AI 設定」頁面設定');
        }
        $this->apiKey = $apiKey;
    }

    public function checkModel($model) {
        $this->request('/models/' . rawurlencode($model), null, false, 'GET');
    }

    public function transcribe($filePath, $mimeType, $fileName) {
        $fields = [
            'file' => new CURLFile($filePath, $mimeType, $fileName),
            'model' => OPENAI_TRANSCRIBE_MODEL,
            'language' => 'zh',
            'response_format' => 'json',
        ];
        $result = $this->request('/audio/transcriptions', $fields, false);
        return trim($result['text'] ?? '');
    }

    public function chatJson(array $messages, $schemaName, array $schema) {
        $payload = [
            'model' => OPENAI_PARSE_MODEL,
            'messages' => $messages,
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => ['name' => $schemaName, 'strict' => true, 'schema' => $schema],
            ],
        ];
        $result = $this->request('/chat/completions', $payload, true);
        $message = $result['choices'][0]['message'] ?? [];

        if (!empty($message['refusal'])) {
            throw new Exception('AI 拒絕處理此內容：' . $message['refusal']);
        }
        $data = json_decode($message['content'] ?? '', true);
        if (!is_array($data)) {
            throw new Exception('AI 回傳內容無法解析，請重試');
        }
        return $data;
    }

    private function request($path, $body, $isJson, $method = 'POST') {
        $headers = ['Authorization: Bearer ' . $this->apiKey];
        if ($isJson) {
            $headers[] = 'Content-Type: application/json';
            $body = json_encode($body, JSON_UNESCAPED_UNICODE);
        }

        $ch = curl_init(OPENAI_API_BASE . $path);
        if ($method === 'POST') {
            curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $body]);
        }
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 90,
        ]);
        $raw = curl_exec($ch);

        if ($raw === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('無法連線至 OpenAI：' . $error);
        }
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($raw, true);
        if ($status >= 400) {
            $message = $data['error']['message'] ?? ('HTTP ' . $status);
            throw new Exception('OpenAI API 錯誤：' . $message);
        }
        if (!is_array($data)) {
            throw new Exception('OpenAI 回應格式無法解析');
        }
        return $data;
    }
}
?>

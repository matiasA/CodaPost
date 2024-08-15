<?php

require_once 'interface-ai-generator.php';

class OpenAI_Generator implements AI_Generator {
    private $api_key;

    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    public function generate_content($prompt) {
        $url = 'https://api.openai.com/v1/engines/davinci-codex/completions';

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ];

        $data = [
            'prompt' => $prompt,
            'max_tokens' => 500,
            'n' => 1,
            'stop' => null,
            'temperature' => 0.7,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['choices'][0]['text'])) {
            return $result['choices'][0]['text'];
        }

        return false;
    }
}
<?php

require_once 'interface-ai-generator.php';

class OpenAI_Generator implements AI_Generator {
    private $api_key;
    private $logger;

    public function __construct($api_key, $logger) {
        $this->api_key = $api_key;
        $this->logger = $logger;
    }

    public function generate_content($prompt) {
        echo "Iniciando generación de contenido con OpenAI\n";
        $url = 'https://api.openai.com/v1/chat/completions';

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ];

        $data = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'Eres un asistente útil que genera contenido para blogs.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 500,
            'temperature' => 0.7,
        ];

        echo "Enviando solicitud a OpenAI\n";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            echo "Error en la solicitud cURL: " . curl_error($ch) . "\n";
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        if ($http_status != 200) {
            echo "Error en la API de OpenAI. Código de estado: $http_status\n";
            return false;
        }

        $result = json_decode($response, true);

        if (isset($result['choices'][0]['message']['content'])) {
            echo "Contenido generado exitosamente\n";
            return $result['choices'][0]['message']['content'];
        }

        echo "Respuesta inesperada de la API de OpenAI\n";
        return false;
    }
}
<?php

require_once plugin_dir_path(__FILE__) . 'interface-ai-generator.php';

class OpenAI_Generator implements AI_Generator {
    private $api_key;
    private $logger;

    public function __construct($api_key, $logger) {
        $this->api_key = $api_key;
        $this->logger = $logger;
    }

    public function generate_content($prompt) {
        $this->logger->info("OpenAI: Iniciando generación de contenido");
        $url = 'https://api.openai.com/v1/chat/completions';

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ];

        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'Eres un asistente útil que genera contenido para blogs.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 500,
            'temperature' => 0.7,
        ];

        $this->logger->info("OpenAI: Enviando solicitud a la API");
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $this->logger->error("OpenAI: Error en la solicitud cURL: " . curl_error($ch));
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        if ($http_status != 200) {
            $this->logger->error("OpenAI: Error en la API. Código de estado: $http_status, Respuesta: $response");
            return false;
        }

        $result = json_decode($response, true);

        if (isset($result['choices'][0]['message']['content'])) {
            $this->logger->info("OpenAI: Contenido generado exitosamente");
            return $result['choices'][0]['message']['content'];
        }

        $this->logger->error("OpenAI: Respuesta inesperada de la API: " . print_r($result, true));
        return false;
    }
}
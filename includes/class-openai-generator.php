<?php

require_once plugin_dir_path(__FILE__) . 'interface-ai-generator.php';

class OpenAI_Generator implements AI_Generator {
    private $api_key;
    private $logger;
    private $model;

    public function __construct($api_key, $logger) {
        $this->api_key = $api_key;
        $this->logger = $logger;
        $this->model = get_option('coda_post_openai_model', 'gpt-4-0125-preview');
    }

    public function set_model($model) {
        $this->model = $model;
    }

    public function get_model() {
        return $this->model;
    }

    public function generate_content($prompt) {
        $this->logger->info("OpenAI: Iniciando generación de contenido con modelo {$this->model}");
        $url = 'https://api.openai.com/v1/chat/completions';

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ];

        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => 'Eres un periodista especializado que genera contenido para blogs.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 2000,
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

    public function get_available_models() {
        $url = 'https://api.openai.com/v1/models';

        $headers = [
            'Authorization: Bearer ' . $this->api_key
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $this->logger->error("OpenAI: Error al obtener modelos: " . curl_error($ch));
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        if ($http_status != 200) {
            $this->logger->error("OpenAI: Error al obtener modelos. Código de estado: $http_status, Respuesta: $response");
            return false;
        }

        $result = json_decode($response, true);

        if (isset($result['data'])) {
            $chat_models = array_filter($result['data'], function($model) {
                return strpos($model['id'], 'gpt-') === 0;
            });

            return array_column($chat_models, 'id');
        }

        $this->logger->error("OpenAI: Respuesta inesperada al obtener modelos: " . print_r($result, true));
        return false;
    }

    public function generate_image($prompt) {
        $response = wp_remote_post('https://api.openai.com/v1/images/generations', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'prompt' => $prompt,
                'n' => 1,
                'size' => '1024x1024',
            ]),
        ]);

        if (is_wp_error($response)) {
            $this->logger->error('Error al generar imagen: ' . $response->get_error_message());
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['data'][0]['url'])) {
            return $body['data'][0]['url'];
        }

        return false;
    }
}
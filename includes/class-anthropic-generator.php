<?php

require_once plugin_dir_path(__FILE__) . 'interface-ai-generator.php';

class Anthropic_Generator implements AI_Generator {
    private $api_key;
    private $logger;
    private $model = 'claude-2';
    private $max_tokens_to_sample = 1000;
    private $temperature = 0.7;
    private $top_p = 1;
    private $cache_expiration = 3600; // 1 hora en segundos

    public function __construct($api_key, $logger) {
        $this->api_key = $api_key;
        $this->logger = $logger;
    }

    public function generate_content($prompt) {
        $this->logger->info("Anthropic: Iniciando generación de contenido con modelo {$this->model}");

        // Verificar si hay una respuesta en caché
        $cached_response = $this->get_cached_response($prompt);
        if ($cached_response !== false) {
            $this->logger->info("Anthropic: Utilizando respuesta en caché");
            return $cached_response;
        }

        $url = 'https://api.anthropic.com/v1/complete';

        $headers = [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->api_key,
            'anthropic-version: 2023-06-01'
        ];

        $data = [
            'model' => $this->model,
            'prompt' => "\n\nHuman: {$prompt}\n\nAssistant:",
            'max_tokens_to_sample' => $this->max_tokens_to_sample,
            'temperature' => $this->temperature,
            'top_p' => $this->top_p
        ];

        $this->logger->info("Anthropic: Enviando solicitud a la API");
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $this->logger->error("Anthropic: Error en la solicitud cURL: " . curl_error($ch));
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        if ($http_status != 200) {
            $this->logger->error("Anthropic: Error en la API. Código de estado: $http_status, Respuesta: $response");
            return false;
        }

        $result = json_decode($response, true);

        if (isset($result['completion'])) {
            $this->logger->info("Anthropic: Contenido generado exitosamente");
            $content = trim($result['completion']);
            
            // Almacenar la respuesta en caché
            $this->cache_response($prompt, $content);
            
            return $content;
        }

        $this->logger->error("Anthropic: Respuesta inesperada de la API: " . print_r($result, true));
        return false;
    }

    private function get_cached_response($prompt) {
        $cache_key = 'anthropic_' . md5($prompt);
        $cached_data = get_transient($cache_key);
        
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        return false;
    }

    private function cache_response($prompt, $response) {
        $cache_key = 'anthropic_' . md5($prompt);
        set_transient($cache_key, $response, $this->cache_expiration);
    }

    public function set_model($model) {
        $this->model = $model;
    }

    public function get_model() {
        return $this->model;
    }
}
<?php

require_once plugin_dir_path(__FILE__) . 'interface-ai-generator.php';

class OpenAI_Generator implements AI_Generator {
    private $api_key;
    private $logger;
    private $timeout = 30; // Aumentamos el tiempo de espera a 30 segundos
    private $max_retries = 3; // Número máximo de intentos

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

    public function generate_image($prompt, $size = '1024x1024', $quality = 'standard', $style = 'vivid') {
        $this->logger->info("Iniciando generación de imagen con prompt: $prompt");
        $this->logger->info("Parámetros: size=$size, quality=$quality, style=$style");

        $attempt = 0;
        while ($attempt < $this->max_retries) {
            $response = wp_remote_post('https://api.openai.com/v1/images/generations', [
                'timeout' => $this->timeout,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'model' => 'dall-e-3',
                    'prompt' => $prompt,
                    'n' => 1,
                    'size' => $size,
                    'quality' => $quality,
                    'style' => $style,
                ]),
            ]);

            if (is_wp_error($response)) {
                $this->logger->error('Error en la solicitud a la API de OpenAI (Intento ' . ($attempt + 1) . '): ' . $response->get_error_message());
                $attempt++;
                if ($attempt < $this->max_retries) {
                    $this->logger->info('Reintentando en 5 segundos...');
                    sleep(5);
                }
                continue;
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
            $this->logger->info('Respuesta de la API de OpenAI: ' . print_r($body, true));

            if (isset($body['data'][0]['url'])) {
                $this->logger->info('URL de imagen generada: ' . $body['data'][0]['url']);
                return $body['data'][0]['url'];
            } else {
                $this->logger->error('No se encontró URL de imagen en la respuesta de la API');
                $attempt++;
                if ($attempt < $this->max_retries) {
                    $this->logger->info('Reintentando en 5 segundos...');
                    sleep(5);
                }
            }
        }

        $this->logger->error('No se pudo generar la imagen después de ' . $this->max_retries . ' intentos');
        return false;
    }
}
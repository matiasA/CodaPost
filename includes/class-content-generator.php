<?php

class Content_Generator {
    private $ai_generator;
    private $logger;
    private $max_content_length = 1500; // Aproximadamente 300 palabras

    public function __construct(AI_Generator $ai_generator, $logger) {
        $this->ai_generator = $ai_generator;
        $this->logger = $logger;
    }

    public function generate_content($structure, $content_type) {
        $this->logger->info("Content Generator: Iniciando generación de contenido");
        $current_year = date('Y');
        
        $prompt = "Eres un periodista especializado en $content_type. Quiero que escribas un artículo de máximo 1500 palabras en español al estilo del New York Times. El artículo debe tener obligatoriamente un título y al final 3 puntos clave para entender el tema. El artículo debe seguir esta estructura: $structure. Incluye datos recientes y tendencias actuales del año $current_year sobre $content_type.";

        $this->logger->info("Content Generator: Generando contenido completo");
        $full_content = $this->ai_generator->generate_content($prompt);

        if (!$full_content) {
            $this->logger->error("Content Generator: No se pudo generar el contenido");
            return false;
        }

        // Extraer el título, contenido principal y puntos clave
        $parts = explode("\n", $full_content, 2);
        $title = trim($parts[0]);
        $main_content = trim($parts[1]);

        // Extraer los puntos clave
        $key_points_start = strrpos($main_content, "Puntos clave:");
        $key_points = "";
        if ($key_points_start !== false) {
            $key_points = substr($main_content, $key_points_start);
            $main_content = trim(substr($main_content, 0, $key_points_start));
        }

        // Generar el extracto
        $excerpt_prompt = "Genera un resumen corto de 50 palabras para el siguiente artículo: $main_content";
        $excerpt = $this->ai_generator->generate_content($excerpt_prompt);

        if (!$excerpt) {
            $this->logger->error("Content Generator: No se pudo generar el resumen");
            return false;
        }

        return [
            'title' => $title,
            'content' => $main_content,
            'excerpt' => $excerpt,
            'key_points' => $key_points
        ];
    }
}
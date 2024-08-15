<?php

class Content_Generator {
    private $ai_generator;
    private $logger;
    private $max_content_length = 1500; // Aproximadamente 300 palabras

    public function __construct(AI_Generator $ai_generator, $logger) {
        $this->ai_generator = $ai_generator;
        $this->logger = $logger;
    }

    public function generate_content() {
        $title_prompt = "Genera un título interesante para un artículo de blog.";
        $title = $this->ai_generator->generate_content($title_prompt);

        if (!$title) {
            $this->logger->error("No se pudo generar el título");
            return false;
        }

        $content_prompt = "Escribe un artículo de blog detallado sobre el siguiente título: $title. El artículo debe tener aproximadamente 300 palabras.";
        $content = $this->ai_generator->generate_content($content_prompt);

        if (!$content) {
            $this->logger->error("No se pudo generar el contenido");
            return false;
        }

        // Truncar el contenido si excede el límite
        if (strlen($content) > $this->max_content_length) {
            $content = substr($content, 0, $this->max_content_length);
            $content = rtrim($content, ".\n") . "..."; // Asegurar que el contenido termine con una oración completa
        }

        $excerpt_prompt = "Genera un resumen corto de 50 palabras para el siguiente artículo: $content";
        $excerpt = $this->ai_generator->generate_content($excerpt_prompt);

        if (!$excerpt) {
            $this->logger->error("No se pudo generar el resumen");
            return false;
        }

        return [
            'title' => $title,
            'content' => $content,
            'excerpt' => $excerpt,
        ];
    }
}
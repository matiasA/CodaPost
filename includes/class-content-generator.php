<?php

class Content_Generator {
    private $ai_generator;

    public function __construct(AI_Generator $ai_generator) {
        $this->ai_generator = $ai_generator;
    }

    public function generate_content() {
        $title_prompt = "Genera un título interesante para un artículo de blog.";
        $title = $this->ai_generator->generate_content($title_prompt);

        $content_prompt = "Escribe un artículo de blog sobre el siguiente título: $title";
        $content = $this->ai_generator->generate_content($content_prompt);

        $excerpt_prompt = "Genera un resumen corto para el siguiente artículo: $content";
        $excerpt = $this->ai_generator->generate_content($excerpt_prompt);

        return [
            'title' => $title,
            'content' => $content,
            'excerpt' => $excerpt,
        ];
    }
}
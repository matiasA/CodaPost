<?php

class Content_Generator {
    private $ai_generator;
    private $logger;
    private $max_content_length = 1500; // Aproximadamente 300 palabras

    public function __construct(AI_Generator $ai_generator, $logger) {
        $this->ai_generator = $ai_generator;
        $this->logger = $logger;
    }

    public function set_ai_generator(AI_Generator $ai_generator) {
        $this->ai_generator = $ai_generator;
    }

    public function generate_content($structure, $content_type, $writing_style, $post_length) {
        $this->logger->info("Content Generator: Iniciando generación de contenido");
        $current_year = date('Y');
        
        $prompt = "Eres un periodista especializado en $content_type. Escribe un artículo en español con la siguiente estructura: $structure. 
                   El estilo de escritura debe ser $writing_style. La longitud del artículo debe ser $post_length. 
                   Incluye datos recientes y tendencias actuales sobre $content_type. 
                   El artículo debe comenzar directamente con un título atractivo y original (sin la palabra 'Título:' y sin mencionar el año actual), seguido del contenido detallado y una conclusión. 
                   Al final, proporciona 3 puntos clave para entender el tema.";

        $this->logger->info("Content Generator: Generando contenido completo");
        $full_content = $this->ai_generator->generate_content($prompt);

        if (!$full_content) {
            $this->logger->error("Content Generator: No se pudo generar el contenido");
            return false;
        }

        $processed_content = $this->process_content($full_content);

        return $processed_content;
    }

    private function process_content($content) {
        $lines = explode("\n", $content);
        $title = '';
        $body = '';
        $excerpt = '';
        $points = [];

        $in_points = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Procesar título
            if (empty($title)) {
                $title = $this->clean_title($this->clean_text($line));
                continue;
            }

            // Procesar puntos clave
            if (strpos(strtolower($line), 'puntos clave') !== false) {
                $in_points = true;
                continue;
            }

            if ($in_points) {
                if (preg_match('/^\d+\.?\s*(.*)/', $line, $matches)) {
                    $points[] = $this->clean_text($matches[1]);
                }
            } else {
                $body .= $this->clean_text($line) . "\n\n";
            }
        }

        // Si no se encontró un título, usar las primeras palabras del cuerpo
        if (empty($title)) {
            $words = explode(' ', strip_tags($body));
            $title = implode(' ', array_slice($words, 0, 8)) . '...';
        }

        // Extraer el excerpt de las primeras líneas del body
        $excerpt = $this->extract_excerpt($body);

        return [
            'title' => $title,
            'content' => $body,
            'excerpt' => $excerpt,
            'points' => $points
        ];
    }

    private function clean_text($text) {
        // Eliminar asteriscos para negrita
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
        
        // Eliminar guiones bajos para cursiva
        $text = preg_replace('/_(.*?)_/', '$1', $text);
        
        // Eliminar otros símbolos de Markdown si es necesario
        // Por ejemplo, para eliminar backticks:
        // $text = preg_replace('/`(.*?)`/', '$1', $text);

        return $text;
    }

    private function clean_title($title) {
        // Eliminar la palabra "Título:" al principio si existe
        $title = preg_replace('/^Título:\s*/i', '', $title);

        // Eliminar el año actual y el próximo del título
        $current_year = date('Y');
        $next_year = $current_year + 1;
        $title = str_replace([$current_year, $next_year], '', $title);
        
        // Eliminar cualquier año entre paréntesis
        $title = preg_replace('/\s*\(\d{4}\)\s*/', '', $title);
        
        // Limpiar espacios extra y puntuación al final
        $title = trim($title, " :-");
        
        return $title;
    }

    private function extract_excerpt($body) {
        $words = explode(' ', strip_tags($body));
        return implode(' ', array_slice($words, 0, 55)) . '...';
    }
}
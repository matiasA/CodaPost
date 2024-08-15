<?php

class Post_Publisher {
    private $logger;

    public function __construct($logger) {
        $this->logger = $logger;
    }

    public function publish_post($title, $content, $excerpt, $status = 'draft') {
        $this->logger->info("Post Publisher: Iniciando publicación de post");
        
        $post_data = array(
            'post_title'    => wp_strip_all_tags($title),
            'post_content'  => $content,
            'post_excerpt'  => $excerpt,
            'post_status'   => $status,
            'post_type'     => 'post',
        );

        $post_id = wp_insert_post($post_data);

        if ($post_id) {
            $this->logger->info("Post Publisher: Post creado exitosamente con ID: $post_id");
            return $post_id;
        } else {
            $this->logger->error("Post Publisher: Error al crear el post");
            return false;
        }
    }
}
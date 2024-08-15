<?php

class Post_Publisher {
    private $logger;

    public function __construct($logger) {
        $this->logger = $logger;
    }

    public function publish_post($content, $status = 'draft') {
        $post_data = array(
            'post_title'    => wp_strip_all_tags($content['title']),
            'post_content'  => $content['content'],
            'post_status'   => $status,
            'post_author'   => 1,
            'post_excerpt'  => wp_strip_all_tags($content['excerpt']),
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            $this->logger->error('Error al publicar el post: ' . $post_id->get_error_message());
            return false;
        }

        $this->logger->info('Post publicado exitosamente. ID: ' . $post_id);
        return $post_id;
    }
}
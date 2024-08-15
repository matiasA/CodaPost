<?php

class Post_Preview {
    private $logger;

    public function __construct($logger) {
        $this->logger = $logger;
    }

    public function display_preview() {
        $draft_posts = get_posts(array(
            'post_status' => 'draft',
            'meta_key' => '_coda_post_generated',
            'meta_value' => '1',
            'posts_per_page' => -1,
        ));

        echo '<div class="wrap">';
        echo '<h1>Revisar Posts Generados</h1>';

        if (empty($draft_posts)) {
            echo '<p>No hay posts generados para revisar.</p>';
        } else {
            foreach ($draft_posts as $post) {
                $this->display_post_preview($post);
            }
        }

        echo '</div>';
    }

    private function display_post_preview($post) {
        echo '<div class="coda-post-preview">';
        echo '<h2>' . esc_html($post->post_title) . '</h2>';
        echo '<div class="coda-post-content">' . wpautop($post->post_content) . '</div>';
        echo '<form method="post">';
        echo '<input type="hidden" name="post_id" value="' . $post->ID . '">';
        echo '<input type="submit" name="approve_post" class="button button-primary" value="Aprobar y Publicar">';
        echo '<input type="submit" name="delete_post" class="button" value="Eliminar">';
        echo '</form>';
        echo '</div>';

        if (isset($_POST['approve_post']) && $_POST['post_id'] == $post->ID) {
            wp_publish_post($post->ID);
            $this->logger->info("Post ID: {$post->ID} publicado");
            echo '<div class="updated"><p>Post publicado exitosamente.</p></div>';
        } elseif (isset($_POST['delete_post']) && $_POST['post_id'] == $post->ID) {
            wp_delete_post($post->ID, true);
            $this->logger->info("Post ID: {$post->ID} eliminado");
            echo '<div class="updated"><p>Post eliminado.</p></div>';
        }
    }
}
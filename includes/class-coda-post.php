<?php

class Coda_Post {
    public function run() {
        $this->load_dependencies();
        $this->set_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-content-generator.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-openai-generator.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-post-publisher.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-admin-page.php';
    }

    private function set_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('coda_post_create_post', array($this, 'create_automated_post'));
    }

    public function add_admin_menu() {
        $admin_page = new Admin_Page();
        $admin_page->add_menu();
    }

    public function create_automated_post() {
        $api_key = get_option('coda_post_openai_api_key', '');
        if (empty($api_key)) {
            error_log("Coda Post: API key no configurada.");
            return;
        }

        $openai_generator = new OpenAI_Generator($api_key);
        $generator = new Content_Generator($openai_generator);
        $content = $generator->generate_content();

        if ($content) {
            $publisher = new Post_Publisher();
            $post_id = $publisher->publish_post($content);
            if ($post_id) {
                error_log("Coda Post: Post creado exitosamente. ID: $post_id");
            } else {
                error_log("Coda Post: Error al crear el post.");
            }
        } else {
            error_log("Coda Post: No se pudo generar contenido.");
        }
    }
}
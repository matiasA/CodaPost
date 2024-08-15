<?php

class Coda_Post {
    private $logger;

    public function __construct() {
        $this->load_dependencies();
        $this->logger = new Coda_Logger();
    }

    public function run() {
        $this->set_hooks();
        $this->logger->info('Coda Post plugin initialized');
    }

    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-coda-logger.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-content-generator.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-openai-generator.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-post-publisher.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-admin-page.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-post-preview.php';
    }

    private function set_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('coda_post_create_draft', array($this, 'create_automated_draft'));
    }

    public function add_admin_menu() {
        $admin_page = new Admin_Page($this->logger);
        $admin_page->add_menu();
    }

    public function create_automated_draft() {
        $api_key = get_option('coda_post_openai_api_key', '');
        if (empty($api_key)) {
            $this->logger->error('API key no configurada');
            return;
        }

        $openai_generator = new OpenAI_Generator($api_key, $this->logger);
        $generator = new Content_Generator($openai_generator, $this->logger);
        $content = $generator->generate_content();

        if ($content) {
            $publisher = new Post_Publisher($this->logger);
            $post_id = $publisher->publish_post($content, 'draft');
            if ($post_id) {
                add_post_meta($post_id, '_coda_post_generated', '1', true);
                $this->logger->info("Borrador creado exitosamente. ID: $post_id");
            } else {
                $this->logger->error('Error al crear el borrador');
            }
        } else {
            $this->logger->error('No se pudo generar contenido');
        }
    }
}
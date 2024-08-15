<?php

class Coda_Post {
    private $logger;

    public function __construct() {
        $this->load_dependencies();
        $this->logger = new Coda_Logger();
        add_action('wp_ajax_generate_post_ajax', array($this, 'generate_post_ajax'));
        add_action('wp_ajax_nopriv_generate_post_ajax', array($this, 'generate_post_ajax'));
    }

    public function run() {
        $this->set_hooks();
        coda_post_log('Coda Post plugin initialized');
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
        coda_post_log('Coda Post: Hooks configurados');
    }

    public function add_admin_menu() {
        $admin_page = new Admin_Page($this->logger);
        $admin_page->add_menu();
    }

    public function create_automated_draft() {
        echo "Iniciando create_automated_draft\n";
        
        $api_key = get_option('coda_post_openai_api_key', '');
        if (empty($api_key)) {
            echo "API key no configurada\n";
            return;
        }

        echo "API key configurada, iniciando generación de contenido\n";
        $openai_generator = new OpenAI_Generator($api_key, $this->logger);
        $generator = new Content_Generator($openai_generator, $this->logger);
        $content = $generator->generate_content();

        if ($content) {
            echo "Contenido generado, intentando publicar\n";
            $publisher = new Post_Publisher($this->logger);
            $post_id = $publisher->publish_post($content, 'draft');
            if ($post_id) {
                add_post_meta($post_id, '_coda_post_generated', '1', true);
                echo "Borrador creado exitosamente. ID: $post_id\n";
            } else {
                echo "Error al crear el borrador\n";
            }
        } else {
            echo "No se pudo generar contenido\n";
        }
    }

    public function generate_post_ajax() {
        check_ajax_referer('generate_post_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permisos insuficientes.'));
        }

        ob_start();
        $this->create_automated_draft();
        $log = ob_get_clean();

        $lines = explode("\n", trim($log));
        foreach ($lines as $line) {
            if (!empty($line)) {
                wp_send_json_success(array('message' => $line));
            }
        }

        wp_send_json_error(array('message' => 'No se pudo generar el post.'));
    }
}
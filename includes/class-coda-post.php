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
        $this->logger->info('Coda Post: Iniciando create_automated_draft');
        
        $api_key = get_option('coda_post_openai_api_key', '');
        if (empty($api_key)) {
            $this->logger->error('Coda Post: API key no configurada');
            return false;
        }

        $this->logger->info('Coda Post: API key configurada, iniciando generación de contenido');
        $model = get_option('coda_post_openai_model', 'gpt-4-0125-preview');
        $openai_generator = new OpenAI_Generator($api_key, $this->logger);
        $openai_generator->set_model($model);
        $generator = new Content_Generator($openai_generator, $this->logger);
        
        // Obtener la estructura y el tipo de contenido
        $structure = isset($_POST['post_structure']) ? sanitize_text_field($_POST['post_structure']) : 'parrafos';
        $content_type = isset($_POST['content_type']) ? sanitize_text_field($_POST['content_type']) : 'tecnologia';
        
        $content = $generator->generate_content($structure, $content_type);

        if ($content) {
            $this->logger->info('Coda Post: Contenido generado, intentando publicar');
            $publisher = new Post_Publisher($this->logger);
            $post_id = $publisher->publish_post($content, 'draft');
            if ($post_id) {
                add_post_meta($post_id, '_coda_post_generated', '1', true);
                $this->logger->info("Coda Post: Borrador creado exitosamente. ID: $post_id");
                return $post_id;
            } else {
                $this->logger->error("Coda Post: Error al crear el borrador");
            }
        } else {
            $this->logger->error("Coda Post: No se pudo generar contenido");
        }

        return false;
    }

    public function generate_post_ajax() {
        check_ajax_referer('generate_post_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permisos insuficientes.'));
        }

        ob_start();
        $result = $this->create_automated_draft();
        $log = ob_get_clean();

        if ($result) {
            wp_send_json_success(array('message' => 'Post generado exitosamente', 'post_id' => $result));
        } else {
            wp_send_json_error(array('message' => 'No se pudo generar el post.'));
        }
    }
}
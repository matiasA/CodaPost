<?php

class Coda_Post {
    private $logger;

    public function __construct($logger) {
        $this->logger = $logger;
    }

    public function run() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('coda_post_create_draft', array($this, 'create_automated_draft'));
        add_action('wp_ajax_coda_post_publish', array($this, 'ajax_publish_post'));
        add_action('wp_ajax_coda_post_delete', array($this, 'ajax_delete_post'));
        add_action('wp_ajax_generate_post_ajax', array($this, 'generate_post_ajax'));
        add_action('wp_ajax_nopriv_generate_post_ajax', array($this, 'generate_post_ajax'));
        
        $this->logger->info('Coda Post: Hooks configurados');
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
        
        // Obtener los parámetros del formulario
        $structure = isset($_POST['post_structure']) ? sanitize_text_field($_POST['post_structure']) : 'parrafos';
        $content_type = isset($_POST['content_type']) ? sanitize_text_field($_POST['content_type']) : 'tecnologia';
        $writing_style = isset($_POST['writing_style']) ? sanitize_text_field($_POST['writing_style']) : 'Formal';
        $post_length = isset($_POST['post_length']) ? sanitize_text_field($_POST['post_length']) : 'medio';

        $generate_image = isset($_POST['generate_image']) ? true : false;
        $image_style = isset($_POST['image_style']) ? sanitize_text_field($_POST['image_style']) : 'vivid';
        $image_quality = isset($_POST['image_quality']) ? sanitize_text_field($_POST['image_quality']) : 'standard';

        $generated_content = $generator->generate_content($structure, $content_type, $writing_style, $post_length);

        if ($generated_content) {
            $this->logger->info('Coda Post: Contenido generado, intentando publicar');
            $publisher = new Post_Publisher($this->logger);
            $post_id = $publisher->publish_post($generated_content['title'], $generated_content['content'], $generated_content['excerpt'], 'draft');
            
            if ($post_id) {
                add_post_meta($post_id, '_coda_post_generated', '1', true);
                
                if ($generate_image) {
                    $image_url = $this->generate_and_attach_image($post_id, $generated_content['title'], $image_style, $image_quality);
                    if ($image_url) {
                        set_post_thumbnail($post_id, $image_url);
                    }
                }
                
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

    private function generate_and_attach_image($post_id, $title, $style, $quality) {
        $openai = new OpenAI_Generator(get_option('coda_post_openai_api_key'), $this->logger);
        $prompt = "Genera una imagen para un artículo con el siguiente título: $title";
        
        // Mapear estilos del formulario a los estilos de DALL-E 3
        $dalle_style = ($style == 'realista' || $style == 'fotografia') ? 'natural' : 'vivid';
        
        $image_url = $openai->generate_image($prompt, '1024x1024', $quality, $dalle_style);
        
        if ($image_url) {
            $upload = media_sideload_image($image_url, $post_id, $title, 'id');
            if (!is_wp_error($upload)) {
                return $upload;
            } else {
                $this->logger->error('Error al adjuntar la imagen: ' . $upload->get_error_message());
            }
        }
        
        return false;
    }

    public function generate_post_ajax() {
        check_ajax_referer('generate_post_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permisos insuficientes.'));
        }

        $post_id = $this->create_automated_draft();

        if ($post_id) {
            wp_send_json_success(array('message' => 'Post generado exitosamente', 'post_id' => $post_id));
        } else {
            wp_send_json_error(array('message' => 'No se pudo generar el post.'));
        }
    }

    public function ajax_publish_post() {
        check_ajax_referer('coda_post_publish_nonce', 'nonce');

        if (!current_user_can('publish_posts')) {
            wp_send_json_error(array('message' => 'No tienes permisos para publicar posts.'));
        }

        $post_id = intval($_POST['post_id']);
        $post = get_post($post_id);

        if (!$post || $post->post_status !== 'draft' || !get_post_meta($post_id, '_coda_post_generated', true)) {
            wp_send_json_error(array('message' => 'Post inválido o no generado por Coda Post.'));
        }

        $update_post = array(
            'ID' => $post_id,
            'post_status' => 'publish'
        );

        $result = wp_update_post($update_post);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } else {
            wp_send_json_success(array('message' => 'Post publicado exitosamente.'));
        }
    }

    public function ajax_delete_post() {
        check_ajax_referer('coda_post_delete_nonce', 'nonce');

        if (!current_user_can('delete_posts')) {
            wp_send_json_error(array('message' => 'No tienes permisos para eliminar posts.'));
        }

        $post_id = intval($_POST['post_id']);
        $post = get_post($post_id);

        if (!$post || !get_post_meta($post_id, '_coda_post_generated', true)) {
            wp_send_json_error(array('message' => 'Post inválido o no generado por Coda Post.'));
        }

        $result = wp_delete_post($post_id, true);

        if (!$result) {
            wp_send_json_error(array('message' => 'Error al eliminar el post.'));
        } else {
            wp_send_json_success(array('message' => 'Post eliminado exitosamente.'));
        }
    }
}
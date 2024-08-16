<?php
/**
 * Plugin Name: Coda Post
 * Description: Generador de posts automatizados para WordPress
 * Version: 1.0
 * Author: Cristian Aracena - CODA UNO
 */

set_time_limit(120); // Establece el límite de tiempo de ejecución a 120 segundos

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/class-coda-post-utils.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-coda-post.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-coda-logger.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-admin-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-openai-generator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-anthropic-generator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-content-generator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-post-publisher.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-style-settings.php';

function run_coda_post() {
    $logger = new Coda_Logger();
    $plugin = new Coda_Post($logger);
    $plugin->run();
}

run_coda_post();

// Añade esto después de run_coda_post();
add_action('admin_enqueue_scripts', function($hook) {
    if ('toplevel_page_coda-post' !== $hook) {
        return;
    }
    wp_enqueue_style('coda-post-admin-styles', plugin_dir_url(__FILE__) . 'admin/css/admin.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('coda-post-admin-scripts', plugin_dir_url(__FILE__) . 'admin/js/admin.js', array('jquery'), '1.0', true);
    wp_localize_script('coda-post-admin-scripts', 'coda_post_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('coda_post_ajax_nonce')
    ));
});

add_action('wp_ajax_generate_post_ajax', 'coda_post_generate_post_ajax');
add_action('wp_ajax_nopriv_generate_post_ajax', 'coda_post_generate_post_ajax');

function coda_post_generate_post_ajax() {
    error_log('Coda Post: Acción AJAX recibida');
    check_ajax_referer('generate_post_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permisos insuficientes.'));
    }

    $plugin = new Coda_Post();
    $result = $plugin->create_automated_draft();

    if ($result) {
        wp_send_json_success(array('message' => 'Post generado exitosamente', 'post_id' => $result));
    } else {
        wp_send_json_error(array('message' => 'No se pudo generar el post.'));
    }
}

// Añade esto después de run_coda_post();
add_action('init', function() {
    if (isset($_GET['test_coda_post'])) {
        coda_post_log('Iniciando test_coda_post');
        do_action('coda_post_create_draft');
        coda_post_log('Finalizando test_coda_post');
        die('Acción coda_post_create_draft ejecutada');
    }
});

// Añade esto para verificar si las tareas programadas se están ejecutando
add_action('init', function() {
    if (isset($_GET['check_cron'])) {
        $cron = _get_cron_array();
        coda_post_log('Tareas programadas: ' . print_r($cron, true));
        die('Tareas programadas registradas en el log');
    }
});

function coda_post_log($message) {
    if (WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}

// Añade esto para agregar favicon
add_action('admin_head', function() {
    echo '<link rel="icon" href="' . plugin_dir_url(__FILE__) . 'assets/icon-16x16.png" />';
});

// Añade esta función para obtener el generador de AI seleccionado
function get_selected_ai_generator($logger) {
    $selected_generator = get_option('coda_post_ai_generator', 'openai');
    $api_key = get_option('coda_post_api_key', '');

    if ($selected_generator === 'openai') {
        return new OpenAI_Generator($api_key, $logger);
    } elseif ($selected_generator === 'anthropic') {
        return new Anthropic_Generator($api_key, $logger);
    } else {
        // Por defecto, usa OpenAI
        return new OpenAI_Generator($api_key, $logger);
    }
}

// Modifica la clase Coda_Post para usar el generador seleccionado
class Coda_Post {
    private $logger;
    private $ai_generator;
    private $content_generator;

    public function __construct($logger) {
        $this->logger = $logger;
        $this->ai_generator = get_selected_ai_generator($logger);
        $this->content_generator = new Content_Generator($this->ai_generator, $logger);
    }

    // ... resto de la clase ...
}
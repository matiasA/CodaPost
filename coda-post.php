<?php
/**
 * Plugin Name: Coda Post
 * Description: Generador de posts automatizados para WordPress
 * Version: 1.0
 * Author: Tu Nombre
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/class-coda-post-utils.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-coda-post.php';

function run_coda_post() {
    $plugin = new Coda_Post();
    $plugin->run();
}

run_coda_post();

// Añade esto después de run_coda_post();
add_action('admin_enqueue_scripts', function($hook) {
    if ('toplevel_page_coda-post' !== $hook) {
        return;
    }
    wp_enqueue_script('jquery');
    error_log('Coda Post: Scripts cargados en la página de administración');
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
<?php
/**
 * Plugin Name: Coda Post
 * Description: Generador de posts automatizados para WordPress
 * Version: 1.0
 * Author: Tu Nombre
 */

if (!defined('ABSPATH')) exit;

// Añade esto al principio del archivo, después de las comprobaciones de seguridad
$log_file = plugin_dir_path(__FILE__) . 'coda-post.log';
if (!file_exists($log_file)) {
    touch($log_file);
    chmod($log_file, 0666);
}

require_once plugin_dir_path(__FILE__) . 'includes/class-coda-post.php';

function run_coda_post() {
    $plugin = new Coda_Post();
    $plugin->run();
}

run_coda_post();

// Añade esto después de run_coda_post();
add_action('init', function() {
    if (isset($_GET['test_coda_post'])) {
        error_log('Coda Post: Iniciando test_coda_post');
        do_action('coda_post_create_draft');
        error_log('Coda Post: Finalizando test_coda_post');
        die('Acción coda_post_create_draft ejecutada');
    }
});
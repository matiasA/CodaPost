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
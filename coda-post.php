<?php
/**
 * Plugin Name: Coda Post
 * Description: Generador de posts automatizados para WordPress
 * Version: 1.0
 * Author: Tu Nombre
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/class-coda-post.php';

function run_coda_post() {
    $plugin = new Coda_Post();
    $plugin->run();
}

run_coda_post();
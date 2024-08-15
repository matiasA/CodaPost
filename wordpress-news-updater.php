<?php
/**
 * Plugin Name: WordPress News Updater
 * Description: Busca y publica noticias actualizadas sobre un tema específico.
 * Version: 1.0
 * Author: Tu Nombre
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/class-news-updater.php';

function run_wordpress_news_updater() {
    $plugin = new News_Updater();
    $plugin->run();
}

run_wordpress_news_updater();
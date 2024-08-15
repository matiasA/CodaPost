<?php

class Admin_Page {
    public function add_menu() {
        add_menu_page(
            'News Updater',
            'News Updater',
            'manage_options',
            'news-updater',
            array($this, 'display_admin_page'),
            'dashicons-rss',
            20
        );
    }

    public function display_admin_page() {
        if (isset($_POST['news_updater_api_key'])) {
            update_option('news_updater_api_key', sanitize_text_field($_POST['news_updater_api_key']));
            update_option('news_updater_topic', sanitize_text_field($_POST['news_updater_topic']));
            echo '<div class="updated"><p>Configuración guardada.</p></div>';
        }

        $api_key = get_option('news_updater_api_key');
        $topic = get_option('news_updater_topic');

        echo '<div class="wrap">';
        echo '<h1>Configuración de News Updater</h1>';
        echo '<form method="post">';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="news_updater_api_key">Clave API de NewsAPI</label></th>';
        echo '<td><input type="text" id="news_updater_api_key" name="news_updater_api_key" value="' . esc_attr($api_key) . '" class="regular-text"></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="news_updater_topic">Tema de noticias</label></th>';
        echo '<td><input type="text" id="news_updater_topic" name="news_updater_topic" value="' . esc_attr($topic) . '" class="regular-text"></td>';
        echo '</tr>';
        echo '</table>';
        echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar cambios"></p>';
        echo '</form>';
        echo '</div>';
    }
}
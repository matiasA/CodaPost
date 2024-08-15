<?php

class Admin_Page {
    public function add_menu() {
        add_menu_page(
            'Coda Post',
            'Coda Post',
            'manage_options',
            'coda-post',
            array($this, 'display_admin_page'),
            'dashicons-admin-post',
            20
        );

        add_submenu_page(
            'coda-post',
            'Revisar Posts',
            'Revisar Posts',
            'manage_options',
            'coda-post-review',
            array($this, 'display_review_page')
        );
    }

    public function display_admin_page() {
        if (isset($_POST['coda_post_action'])) {
            if ($_POST['coda_post_action'] == 'generate') {
                $this->generate_post();
            } elseif ($_POST['coda_post_action'] == 'save_settings') {
                $this->save_settings();
            }
        }

        $api_key = get_option('coda_post_openai_api_key', '');

        echo '<div class="wrap">';
        echo '<h1>Coda Post - Generador de Posts</h1>';
        
        echo '<h2>Configuración</h2>';
        echo '<form method="post">';
        echo '<input type="hidden" name="coda_post_action" value="save_settings">';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="openai_api_key">OpenAI API Key</label></th>';
        echo '<td><input type="text" id="openai_api_key" name="openai_api_key" value="' . esc_attr($api_key) . '" class="regular-text"></td>';
        echo '</tr>';
        echo '</table>';
        echo '<p><input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar Configuración"></p>';
        echo '</form>';

        echo '<h2>Generar Post</h2>';
        echo '<form method="post">';
        echo '<input type="hidden" name="coda_post_action" value="generate">';
        echo '<p><input type="submit" name="submit" id="submit" class="button button-primary" value="Generar Nuevo Post"></p>';
        echo '</form>';
        echo '</div>';
    }

    public function display_review_page() {
        $post_preview = new Post_Preview();
        $post_preview->display_preview();
    }

    private function generate_post() {
        wp_schedule_single_event(time(), 'coda_post_create_draft');
        echo '<div class="updated"><p>Se ha programado la generación de un nuevo borrador. Por favor, revise la página "Revisar Posts" en unos momentos.</p></div>';
    }

    private function save_settings() {
        if (isset($_POST['openai_api_key'])) {
            update_option('coda_post_openai_api_key', sanitize_text_field($_POST['openai_api_key']));
            echo '<div class="updated"><p>Configuración guardada.</p></div>';
        }
    }
}
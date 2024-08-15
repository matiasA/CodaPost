<?php

class Admin_Page {
    private $logger;

    public function __construct($logger) {
        $this->logger = $logger;
    }

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
                coda_post_log('Acción de generación de post solicitada');
                $this->generate_post();
            } elseif ($_POST['coda_post_action'] == 'save_settings') {
                $this->save_settings();
            }
        }

        $api_key = get_option('coda_post_openai_api_key', '');
        coda_post_log('API Key actual: ' . ($api_key ? 'Configurada' : 'No configurada'));

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
        echo '<form method="post" id="generate-post-form">';
        echo '<input type="hidden" name="coda_post_action" value="generate">';
        echo '<p><input type="submit" name="submit" id="submit" class="button button-primary" value="Generar Nuevo Post"></p>';
        echo '</form>';

        // Añadir consola de depuración
        echo '<h3>Consola de Depuración</h3>';
        echo '<div id="debug-console" style="background-color: #f0f0f0; border: 1px solid #ccc; padding: 10px; height: 200px; overflow-y: scroll; font-family: monospace;"></div>';

        // Añadir script JavaScript
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var consoleDiv = $('#debug-console');

            function addConsoleMessage(message) {
                var timestamp = new Date().toLocaleTimeString();
                consoleDiv.append('<p>[' + timestamp + '] ' + message + '</p>');
                consoleDiv.scrollTop(consoleDiv[0].scrollHeight);
            }

            $('#generate-post-form').on('submit', function(e) {
                e.preventDefault();
                addConsoleMessage('Iniciando generación de post...');

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'generate_post_ajax',
                        nonce: '<?php echo wp_create_nonce('generate_post_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('Respuesta AJAX:', response);
                        if (response.success) {
                            addConsoleMessage('Post generado exitosamente. ID: ' + response.data.post_id);
                        } else {
                            addConsoleMessage('Error: ' + (response.data ? response.data.message : 'Respuesta inesperada del servidor'));
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log('Error AJAX:', jqXHR, textStatus, errorThrown);
                        addConsoleMessage('Error de conexión al servidor. Estado: ' + textStatus + ', Error: ' + errorThrown);
                    }
                });
            });
        });
        </script>
        <?php
        echo '</div>';
    }

    public function display_review_page() {
        $post_preview = new Post_Preview($this->logger);
        $post_preview->display_preview();
    }

    private function generate_post() {
        coda_post_log('Iniciando generate_post()');
        wp_schedule_single_event(time(), 'coda_post_create_draft');
        $this->logger->info('Se ha programado la generación de un nuevo borrador');
        coda_post_log('Se ha programado la generación de un nuevo borrador');
        
        // Ejecutar la acción inmediatamente después de programarla
        do_action('coda_post_create_draft');
        
        echo '<div class="updated"><p>Se ha iniciado la generación de un nuevo borrador. Por favor, revise la página "Revisar Posts" en unos momentos.</p></div>';
    }

    private function save_settings() {
        if (isset($_POST['openai_api_key'])) {
            update_option('coda_post_openai_api_key', sanitize_text_field($_POST['openai_api_key']));
            $this->logger->info('Configuración de API key actualizada');
            echo '<div class="updated"><p>Configuración guardada.</p></div>';
        }
    }
}
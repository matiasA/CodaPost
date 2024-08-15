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
            'dashicons-edit'
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

        $this->logger->info('API Key actual: ' . (get_option('coda_post_openai_api_key') ? 'Configurada' : 'No configurada'));

        echo '<div class="wrap">';
        echo '<h1>Coda Post</h1>';

        echo '<div class="coda-post-admin">';
        
        // Configuración de OpenAI
        echo '<div class="coda-post-section">';
        echo '<h2>Configuración de OpenAI</h2>';
        echo '<form method="post" class="coda-post-form">';
        echo '<input type="hidden" name="coda_post_action" value="save_settings">';
        
        $api_key = get_option('coda_post_openai_api_key', '');
        echo '<div class="coda-post-form-group">';
        echo '<label for="openai_api_key">API Key de OpenAI:</label>';
        echo '<input type="text" id="openai_api_key" name="openai_api_key" value="' . esc_attr($api_key) . '" class="regular-text">';
        echo '</div>';

        $model = get_option('coda_post_openai_model', 'gpt-4-1106-preview');
        echo '<div class="coda-post-form-group">';
        echo '<label for="openai_model">Modelo de OpenAI:</label>';
        echo '<select name="openai_model" id="openai_model">';
        echo '<option value="gpt-4-1106-preview"' . selected($model, 'gpt-4-1106-preview', false) . '>GPT-4 Turbo</option>';
        echo '<option value="gpt-4"' . selected($model, 'gpt-4', false) . '>GPT-4</option>';
        echo '<option value="gpt-3.5-turbo-1106"' . selected($model, 'gpt-3.5-turbo-1106', false) . '>GPT-3.5 Turbo</option>';
        echo '</select>';
        echo '</div>';

        echo '<p><input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar Configuración"></p>';
        echo '</form>';
        echo '</div>'; // Fin de la sección de configuración

        // Generación de Post
        echo '<div class="coda-post-section">';
        echo '<h2>Generar Post</h2>';
        echo '<form method="post" id="generate-post-form" class="coda-post-form">';
        echo '<input type="hidden" name="coda_post_action" value="generate">';
        
        echo '<div class="coda-post-form-group">';
        echo '<label for="post_structure">Estructura del post:</label>';
        echo '<select name="post_structure" id="post_structure">';
        echo '<option value="lista">Lista numerada</option>';
        echo '<option value="parrafos">Párrafos</option>';
        echo '<option value="preguntas">Preguntas y respuestas</option>';
        echo '</select>';
        echo '</div>';

        echo '<div class="coda-post-form-group">';
        echo '<label for="content_type">Tipo de contenido:</label>';
        echo '<select name="content_type" id="content_type">';
        echo '<option value="tecnologia">Tecnología</option>';
        echo '<option value="negocios">Negocios</option>';
        echo '<option value="salud">Salud</option>';
        echo '<option value="estilo_vida">Estilo de vida</option>';
        echo '</select>';
        echo '</div>';

        echo '<p><input type="submit" name="submit" id="submit" class="button button-primary" value="Generar Nuevo Post"></p>';
        echo '</form>';
        echo '</div>'; // Fin de la sección de generación de post

        // Consola de Depuración
        echo '<div class="coda-post-section">';
        echo '<h2>Consola de Depuración</h2>';
        echo '<div id="debug-console"></div>';
        echo '</div>'; // Fin de la sección de consola de depuración

        echo '</div>'; // Fin de coda-post-admin
        echo '</div>'; // Fin de wrap

        $this->add_admin_scripts();
    }

    private function save_settings() {
        if (isset($_POST['openai_api_key'])) {
            update_option('coda_post_openai_api_key', sanitize_text_field($_POST['openai_api_key']));
        }
        if (isset($_POST['openai_model'])) {
            update_option('coda_post_openai_model', sanitize_text_field($_POST['openai_model']));
        }
        echo '<div class="updated"><p>Configuración guardada.</p></div>';
    }

    private function generate_post() {
        $this->logger->info('Iniciando generate_post()');
        wp_schedule_single_event(time(), 'coda_post_create_draft');
        $this->logger->info('Se ha programado la generación de un nuevo borrador');
        
        do_action('coda_post_create_draft');
        
        echo '<div class="updated"><p>Se ha iniciado la generación de un nuevo borrador. Por favor, revise la página "Revisar Posts" en unos momentos.</p></div>';
    }

    private function add_admin_scripts() {
        ?>
        <style>
            .coda-post-admin {
                max-width: 800px;
                margin: 20px auto;
            }
            .coda-post-section {
                background: #fff;
                border: 1px solid #ccc;
                padding: 20px;
                margin-bottom: 20px;
                border-radius: 5px;
            }
            .coda-post-form-group {
                margin-bottom: 15px;
            }
            .coda-post-form-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            .coda-post-form-group input[type="text"],
            .coda-post-form-group select {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            #debug-console {
                background-color: #f0f0f0;
                border: 1px solid #ccc;
                padding: 10px;
                height: 200px;
                overflow-y: scroll;
                font-family: monospace;
            }
        </style>
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
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'generate_post_ajax',
                        nonce: '<?php echo wp_create_nonce('generate_post_nonce'); ?>',
                        structure: $('#post_structure').val(),
                        content_type: $('#content_type').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            addConsoleMessage('Post generado exitosamente. ID: ' + response.data.post_id);
                        } else {
                            addConsoleMessage('Error: ' + response.data.message);
                        }
                    },
                    error: function() {
                        addConsoleMessage('Error de conexión al servidor.');
                    }
                });
            });
        });
        </script>
        <?php
    }
}
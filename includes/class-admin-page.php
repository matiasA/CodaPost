<?php

require_once plugin_dir_path(__FILE__) . 'class-openai-generator.php';

class Admin_Page {
    private $logger;
    private $active_tab;
    private $openai_generator;

    public function __construct($logger) {
        $this->logger = $logger;
        $this->active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'generate';
        
        $api_key = get_option('coda_post_openai_api_key', '');
        $this->openai_generator = new OpenAI_Generator($api_key, $this->logger);
    }

    public function add_menu() {
        add_menu_page(
            'Coda Post',
            'Coda Post',
            'manage_options',
            'coda-post',
            array($this, 'display_admin_page'),
            plugin_dir_url(__FILE__) . '../assets/icon-16x16.png', // Ajusta la ruta según sea necesario
            30
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

        $this->display_tabs();

        echo '<div class="coda-post-admin">';
        
        switch ($this->active_tab) {
            case 'generate':
                $this->display_generate_tab();
                break;
            case 'review':
                $this->display_review_tab();
                break;
            case 'settings':
                $this->display_settings_tab();
                break;
        }

        echo '</div>'; // Fin de coda-post-admin
        echo '</div>'; // Fin de wrap

        $this->add_admin_scripts();
    }

    private function display_tabs() {
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="?page=coda-post&tab=generate" class="nav-tab ' . ($this->active_tab == 'generate' ? 'nav-tab-active' : '') . '">Generar Post</a>';
        echo '<a href="?page=coda-post&tab=review" class="nav-tab ' . ($this->active_tab == 'review' ? 'nav-tab-active' : '') . '">Revisar Posts</a>';
        echo '<a href="?page=coda-post&tab=settings" class="nav-tab ' . ($this->active_tab == 'settings' ? 'nav-tab-active' : '') . '">Configuración</a>';
        echo '</h2>';
    }

    private function display_generate_tab() {
        echo '<div class="coda-post-section">';
        echo '<h2>Generar Post</h2>';
        echo '<form method="post" id="generate-post-form" class="coda-post-form">';
        echo '<input type="hidden" name="coda_post_action" value="generate">';
        
        // Estructura del post
        echo '<div class="coda-post-form-group">';
        echo '<label for="post_structure">Estructura del post:</label>';
        echo '<select name="post_structure" id="post_structure">';
        echo '<option value="lista">Lista numerada</option>';
        echo '<option value="parrafos">Párrafos</option>';
        echo '<option value="preguntas">Preguntas y respuestas</option>';
        echo '<option value="guia">Guía paso a paso</option>';
        echo '<option value="comparacion">Comparación</option>';
        echo '</select>';
        echo '</div>';

        // Tipo de contenido
        echo '<div class="coda-post-form-group">';
        echo '<label for="content_type">Tipo de contenido:</label>';
        echo '<select name="content_type" id="content_type">';
        echo '<option value="tecnologia">Tecnología</option>';
        echo '<option value="negocios">Negocios</option>';
        echo '<option value="salud">Salud</option>';
        echo '<option value="estilo_vida">Estilo de vida</option>';
        echo '<option value="ciencia">Ciencia</option>';
        echo '<option value="entretenimiento">Entretenimiento</option>';
        echo '<option value="deportes">Deportes</option>';
        echo '<option value="educacion">Educación</option>';
        echo '</select>';
        echo '</div>';

        // Estilo de escritura
        $writing_styles = get_option('coda_post_writing_styles', ['Formal', 'Informal', 'Académico', 'Periodístico', 'Conversacional']);
        echo '<div class="coda-post-form-group">';
        echo '<label for="writing_style">Estilo de escritura:</label>';
        echo '<select name="writing_style" id="writing_style">';
        foreach ($writing_styles as $style) {
            echo '<option value="' . esc_attr($style) . '">' . esc_html($style) . '</option>';
        }
        echo '</select>';
        echo '</div>';

        // Longitud del post
        echo '<div class="coda-post-form-group">';
        echo '<label for="post_length">Longitud del post:</label>';
        echo '<select name="post_length" id="post_length">';
        echo '<option value="corto">Corto (300-500 palabras)</option>';
        echo '<option value="medio">Medio (500-800 palabras)</option>';
        echo '<option value="largo">Largo (800-1200 palabras)</option>';
        echo '<option value="muy_largo">Muy largo (1200-1500 palabras)</option>';
        echo '</select>';
        echo '</div>';

        echo '<p><input type="submit" name="submit" id="submit" class="button button-primary" value="Generar Nuevo Post"></p>';
        echo '</form>';
        echo '</div>';

        // Consola de Depuración
        echo '<div class="coda-post-section">';
        echo '<h2>Consola de Depuración</h2>';
        echo '<div id="debug-console"></div>';
        echo '</div>';
    }

    private function display_review_tab() {
        echo '<div class="coda-post-section">';
        echo '<h2>Revisar Posts Generados</h2>';
        
        $args = array(
            'post_type'   => 'post',
            'post_status' => 'draft',
            'meta_key'    => '_coda_post_generated',
            'meta_value'  => '1',
            'posts_per_page' => -1,
        );
        
        $generated_posts = new WP_Query($args);

        if ($generated_posts->have_posts()) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Título</th><th>Fecha</th><th>Acciones</th></tr></thead>';
            echo '<tbody>';
            while ($generated_posts->have_posts()) {
                $generated_posts->the_post();
                $post_id = get_the_ID();
                echo '<tr>';
                echo '<td>' . get_the_title() . '</td>';
                echo '<td>' . get_the_date() . '</td>';
                echo '<td>';
                echo '<a href="' . get_edit_post_link() . '" class="button button-small">Editar</a> ';
                echo '<a href="' . get_preview_post_link() . '" target="_blank" class="button button-small">Vista previa</a> ';
                echo '<button class="button button-small publish-post" data-post-id="' . $post_id . '">Publicar</button> ';
                echo '<button class="button button-small delete-post" data-post-id="' . $post_id . '">Eliminar</button>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>No hay posts generados para revisar.</p>';
        }
        
        wp_reset_postdata();
        
        echo '</div>';

        $this->add_review_scripts();
    }

    private function add_review_scripts() {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.publish-post').on('click', function() {
                var postId = $(this).data('post-id');
                if (confirm('¿Estás seguro de que quieres publicar este post?')) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'coda_post_publish',
                            post_id: postId,
                            nonce: '<?php echo wp_create_nonce('coda_post_publish_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('Post publicado exitosamente.');
                                location.reload();
                            } else {
                                alert('Error al publicar el post: ' + response.data.message);
                            }
                        }
                    });
                }
            });

            $('.delete-post').on('click', function() {
                var postId = $(this).data('post-id');
                if (confirm('¿Estás seguro de que quieres eliminar este post? Esta acción no se puede deshacer.')) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'coda_post_delete',
                            post_id: postId,
                            nonce: '<?php echo wp_create_nonce('coda_post_delete_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('Post eliminado exitosamente.');
                                location.reload();
                            } else {
                                alert('Error al eliminar el post: ' + response.data.message);
                            }
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }

    private function display_settings_tab() {
        echo '<div class="coda-post-section">';
        echo '<h2>Configuración de OpenAI</h2>';
        echo '<form method="post" class="coda-post-form">';
        echo '<input type="hidden" name="coda_post_action" value="save_settings">';
        
        $api_key = get_option('coda_post_openai_api_key', '');
        echo '<div class="coda-post-form-group">';
        echo '<label for="openai_api_key">API Key de OpenAI:</label>';
        echo '<input type="text" id="openai_api_key" name="openai_api_key" value="' . esc_attr($api_key) . '" class="regular-text">';
        echo '</div>';

        $current_model = get_option('coda_post_openai_model', 'gpt-4-0125-preview');
        $available_models = $this->openai_generator->get_available_models();

        echo '<div class="coda-post-form-group">';
        echo '<label for="openai_model">Modelo de OpenAI:</label>';
        echo '<select name="openai_model" id="openai_model">';
        
        if ($available_models) {
            foreach ($available_models as $model) {
                echo '<option value="' . esc_attr($model) . '"' . selected($current_model, $model, false) . '>' . esc_html($model) . '</option>';
            }
        } else {
            echo '<option value="gpt-4-0125-preview"' . selected($current_model, 'gpt-4-0125-preview', false) . '>GPT-4 Turbo (más reciente)</option>';
            echo '<option value="gpt-4"' . selected($current_model, 'gpt-4', false) . '>GPT-4</option>';
            echo '<option value="gpt-3.5-turbo-1106"' . selected($current_model, 'gpt-3.5-turbo-1106', false) . '>GPT-3.5 Turbo</option>';
        }
        
        echo '</select>';
        echo '</div>';

        echo '<p><input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar Configuración"></p>';
        echo '</form>';
        echo '</div>';
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
            /* Estilos para las pestañas */
            .nav-tab-wrapper {
                margin-bottom: 20px;
            }
            .nav-tab {
                display: inline-block;
                margin-right: 20px;
                text-decoration: none;
            }
            .nav-tab-active {
                background-color: #0073aa;
                color: #fff;
                padding: 5px 10px;
                border-radius: 5px;
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
                        content_type: $('#content_type').val(),
                        writing_style: $('#writing_style').val(),
                        post_length: $('#post_length').val()
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
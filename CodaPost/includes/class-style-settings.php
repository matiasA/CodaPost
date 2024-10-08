<?php

class Style_Settings {
    private $option_name = 'coda_post_custom_styles';

    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        register_setting($this->option_name, $this->option_name);

        add_settings_section(
            'coda_post_custom_styles_section',
            'Estilos CSS Personalizados',
            array($this, 'render_section_info'),
            'coda-post-styles'
        );

        add_settings_field(
            'custom_css',
            'CSS Personalizado',
            array($this, 'render_custom_css_field'),
            'coda-post-styles',
            'coda_post_custom_styles_section'
        );
    }

    public function render_section_info() {
        echo 'Ingrese sus estilos CSS personalizados para los posts generados:';
    }

    public function render_custom_css_field() {
        $options = get_option($this->option_name);
        $custom_css = isset($options['custom_css']) ? $options['custom_css'] : '';
        ?>
        <textarea name="<?php echo $this->option_name; ?>[custom_css]" rows="10" cols="50"><?php echo esc_textarea($custom_css); ?></textarea>
        <p class="description">Ingrese CSS personalizado para aplicar a los posts generados.</p>
        <?php
    }

    public function get_custom_styles() {
        $options = get_option($this->option_name);
        return isset($options['custom_css']) ? $options['custom_css'] : '';
    }
}
<?php

class News_Publisher {
    public function publish_news($news) {
        if (!$news) {
            return false;
        }

        $content = $news['content'] . "\n\n" . 'Fuente: <a href="' . esc_url($news['source_url']) . '" target="_blank">Leer más</a>';

        $post_id = wp_insert_post(array(
            'post_title'    => $news['title'],
            'post_content'  => $content,
            'post_excerpt'  => $news['description'],
            'post_status'   => 'draft',
            'post_type'     => 'post',
        ));

        if ($post_id) {
            $this->set_featured_image($post_id, $news['image_url']);
        }

        return $post_id;
    }

    private function set_featured_image($post_id, $image_url) {
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);
        $filename = basename($image_url);

        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);
        set_post_thumbnail($post_id, $attach_id);
    }
}
<?php

class Post_Publisher {
    public function publish_post($content, $status = 'publish') {
        $post_id = wp_insert_post(array(
            'post_title'    => $content['title'],
            'post_content'  => $content['content'],
            'post_excerpt'  => $content['excerpt'],
            'post_status'   => $status,
            'post_type'     => 'post',
        ));

        return $post_id;
    }
}
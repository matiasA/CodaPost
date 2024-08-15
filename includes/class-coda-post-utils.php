<?php

if (!function_exists('coda_post_log')) {
    function coda_post_log($message) {
        $log_file = plugin_dir_path(dirname(__FILE__)) . 'coda-post.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
    }
}
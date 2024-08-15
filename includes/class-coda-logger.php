<?php

class Coda_Logger {
    public function info($message) {
        $this->log('INFO', $message);
    }

    public function error($message) {
        $this->log('ERROR', $message);
    }

    private function log($level, $message) {
        $log_message = "[" . date('Y-m-d H:i:s') . "] [$level] $message\n";
        error_log($log_message, 3, WP_CONTENT_DIR . '/coda-post.log');
    }
}
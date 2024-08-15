<?php

if (!class_exists('Coda_Logger')) {
    class Coda_Logger {
        private $log_file;

        public function __construct() {
            $this->log_file = plugin_dir_path(dirname(__FILE__)) . 'coda-post.log';
        }

        public function log($message, $level = 'INFO') {
            $timestamp = date('Y-m-d H:i:s');
            $log_entry = "[$timestamp] [$level] $message" . PHP_EOL;
            
            file_put_contents($this->log_file, $log_entry, FILE_APPEND);
        }

        public function info($message) {
            $this->log($message, 'INFO');
        }

        public function error($message) {
            $this->log($message, 'ERROR');
        }

        public function warning($message) {
            $this->log($message, 'WARNING');
        }
    }
}
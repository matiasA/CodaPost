<?php

class News_Updater {
    public function run() {
        $this->load_dependencies();
        $this->set_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-news-fetcher.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-news-publisher.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-admin-page.php';
    }

    private function set_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_schedule_event', array($this, 'schedule_daily_update'));
    }

    public function add_admin_menu() {
        $admin_page = new Admin_Page();
        $admin_page->add_menu();
    }

    public function schedule_daily_update() {
        if (!wp_next_scheduled('daily_news_update')) {
            wp_schedule_event(time(), 'daily', 'daily_news_update');
        }
        add_action('daily_news_update', array($this, 'update_news'));
    }

    public function update_news() {
        $fetcher = new News_Fetcher();
        $news = $fetcher->fetch_news();

        $publisher = new News_Publisher();
        $publisher->publish_news($news);
    }
}
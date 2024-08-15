<?php

class News_Fetcher {
    private $api_key;
    private $topic;

    public function __construct() {
        $this->api_key = get_option('news_updater_api_key');
        $this->topic = get_option('news_updater_topic');
    }

    public function fetch_news() {
        $url = 'https://newsapi.org/v2/everything?' . http_build_query([
            'q' => $this->topic,
            'sortBy' => 'publishedAt',
            'language' => 'es',
            'apiKey' => $this->api_key
        ]);

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['articles'])) {
            return false;
        }

        $article = $data['articles'][0]; // Get the most recent article

        return [
            'title' => $article['title'],
            'description' => $article['description'],
            'image_url' => $article['urlToImage'],
            'content' => $article['content'],
            'source_url' => $article['url'],
        ];
    }
}
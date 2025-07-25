<?php

namespace App\Services\WyrdNews;

use Illuminate\Support\Facades\Http;

class WyrdNews
{
    /**
     * @return array<int, array<string, string>>
     */
    public static function fetchLatest(): array
    {
        $baseUrl = 'https://www.wyrd-games.net';
        $response = Http::get($baseUrl.'/news');
        $code = $response->body();
        preg_match_all('(<a href=(.*?) class="u-url" rel="bookmark">(.*?)</a>)', $code, $matches);
        $endpoints = $matches[1];
        $titles = $matches[2];

        $latestNews = [];
        foreach ($endpoints as $key => $endpoint) {
            $latestNews[] = [
                'title' => htmlspecialchars_decode($titles[$key]),
                'url' => $baseUrl.trim($endpoint, '"'),
            ];
        }

        return $latestNews;
    }
}

<?php

if (! function_exists('lume_normalize_video_url')) {
    function lume_normalize_video_url(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return $url;
        }

        $host = strtolower($parts['host']);
        $path = $parts['path'] ?? '';
        $query = $parts['query'] ?? '';

        $youtubeHosts = ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be'];
        if (in_array($host, $youtubeHosts, true)) {
            $videoId = '';

            if ($host === 'youtu.be' || $host === 'www.youtu.be') {
                $videoId = trim($path, '/');
            } elseif (str_starts_with($path, '/embed/')) {
                $videoId = basename($path);
            } elseif (str_starts_with($path, '/shorts/')) {
                $videoId = basename($path);
            } elseif ($path === '/watch' && $query !== '') {
                parse_str($query, $queryParams);
                $videoId = $queryParams['v'] ?? '';
            }

            if ($videoId !== '') {
                return 'https://www.youtube.com/embed/' . rawurlencode($videoId);
            }
        }

        $vimeoHosts = ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'];
        if (in_array($host, $vimeoHosts, true)) {
            $segments = array_values(array_filter(explode('/', trim($path, '/'))));
            $last = end($segments);
            if ($last && ctype_digit($last)) {
                return 'https://player.vimeo.com/video/' . $last;
            }
        }

        return $url;
    }
}

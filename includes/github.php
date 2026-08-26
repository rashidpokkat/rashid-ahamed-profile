<?php

declare(strict_types=1);

function github_username_from_url(string $url): string
{
    if (preg_match('~github\.com/([A-Za-z0-9-]+)~', $url, $match)) {
        return $match[1];
    }
    return trim($url, '@/ ');
}

function github_get(string $url): ?array
{
    $raw = false;
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_HTTPHEADER => [
                'User-Agent: rashid-ahamed-profile',
                'Accept: application/vnd.github+json',
            ],
        ]);
        $raw = curl_exec($curl);
        $code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        unset($curl);
        if ($raw === false || $code >= 400) {
            $raw = false;
        }
    }
    if ($raw === false) {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: rashid-ahamed-profile\r\nAccept: application/vnd.github+json\r\n",
                'timeout' => 3,
            ],
        ]);
        $raw = @file_get_contents($url, false, $context);
    }
    if ($raw === false) {
        return null;
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function load_github_profile(string $username): ?array
{
    $username = trim($username);
    if ($username === '') {
        return null;
    }

    $cache = dirname(__DIR__) . '/data/github-cache.json';
    if (is_readable($cache) && filemtime($cache) > time() - 1800) {
        $cached = json_decode((string) file_get_contents($cache), true);
        if (is_array($cached) && ($cached['login'] ?? '') === $username) {
            return $cached;
        }
    }

    $user = github_get('https://api.github.com/users/' . rawurlencode($username));
    if (!$user || empty($user['login'])) {
        if (is_readable($cache)) {
            $cached = json_decode((string) file_get_contents($cache), true);
            if (is_array($cached) && ($cached['login'] ?? '') === $username) {
                return $cached;
            }
        }
        return github_fallback_profile($username);
    }

    $repos = github_get('https://api.github.com/users/' . rawurlencode($username) . '/repos?sort=updated&per_page=8') ?? [];
    $publicRepos = [];
    foreach ($repos as $repo) {
        if (!is_array($repo) || !empty($repo['fork'])) {
            continue;
        }
        $publicRepos[] = [
            'name' => (string) ($repo['name'] ?? ''),
            'url' => (string) ($repo['html_url'] ?? ''),
            'description' => (string) ($repo['description'] ?? ''),
            'language' => (string) ($repo['language'] ?? ''),
            'stars' => (int) ($repo['stargazers_count'] ?? 0),
        ];
        if (count($publicRepos) >= 6) {
            break;
        }
    }

    $profile = [
        'login' => (string) $user['login'],
        'name' => (string) ($user['name'] ?? $user['login']),
        'bio' => trim(preg_replace('/\s+/', ' ', (string) ($user['bio'] ?? ''))),
        'company' => (string) ($user['company'] ?? ''),
        'location' => (string) ($user['location'] ?? ''),
        'html_url' => (string) ($user['html_url'] ?? ('https://github.com/' . $username)),
        'avatar_url' => (string) ($user['avatar_url'] ?? ''),
        'public_repos' => (int) ($user['public_repos'] ?? 0),
        'followers' => (int) ($user['followers'] ?? 0),
        'following' => (int) ($user['following'] ?? 0),
        'created_year' => substr((string) ($user['created_at'] ?? ''), 0, 4),
        'repos' => $publicRepos,
    ];

    @file_put_contents($cache, json_encode($profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    return $profile;
}

function github_fallback_profile(string $username): ?array
{
    $path = __DIR__ . '/github.fallback.php';
    if (!is_readable($path)) {
        return null;
    }
    $fallback = require $path;
    if (!is_array($fallback) || strcasecmp((string) ($fallback['login'] ?? ''), $username) !== 0) {
        return null;
    }
    return $fallback;
}

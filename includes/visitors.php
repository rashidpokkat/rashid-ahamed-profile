<?php

declare(strict_types=1);

function visitors_path(): string
{
    return dirname(__DIR__) . '/data/visitors.json';
}

function visitors_empty_store(): array
{
    return [
        'views' => 0,
        'unique' => 0,
        'daily' => [],
        'geo' => [],
        'known' => [],
        'visits' => [],
    ];
}

function visitors_normalize(mixed $data): array
{
    $store = visitors_empty_store();
    if (!is_array($data)) {
        return $store;
    }

    $store['views'] = max(0, (int) ($data['views'] ?? 0));
    $store['unique'] = max(0, (int) ($data['unique'] ?? 0));
    $store['daily'] = is_array($data['daily'] ?? null) ? $data['daily'] : [];
    $store['geo'] = is_array($data['geo'] ?? null) ? $data['geo'] : [];
    $store['known'] = is_array($data['known'] ?? null) ? $data['known'] : [];
    $store['visits'] = is_array($data['visits'] ?? null) ? $data['visits'] : [];

    return $store;
}

function visitors_mutate(callable $mutator): bool
{
    $path = visitors_path();
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
        return false;
    }

    $handle = fopen($path, 'c+');
    if ($handle === false) {
        return false;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return false;
        }

        $raw = stream_get_contents($handle);
        $store = visitors_normalize($raw === '' ? [] : json_decode((string) $raw, true));
        $store = visitors_normalize($mutator($store));

        $json = json_encode($store, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }

        rewind($handle);
        ftruncate($handle, 0);
        return fwrite($handle, $json . PHP_EOL) !== false;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

function visitor_client_ip(): string
{
    $candidates = [
        (string) ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''),
        (string) ($_SERVER['HTTP_X_REAL_IP'] ?? ''),
        (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
    ];

    foreach ($candidates as $ip) {
        $ip = trim($ip);
        if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    $forwarded = trim((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
    if ($forwarded !== '') {
        $first = trim(explode(',', $forwarded)[0]);
        if (filter_var($first, FILTER_VALIDATE_IP)) {
            return $first;
        }
    }

    return '';
}

function visitor_ip_is_private(string $ip): bool
{
    return $ip === '' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
}

function visitor_is_bot(?string $ua): bool
{
    $ua = strtolower((string) $ua);
    if ($ua === '') {
        return false;
    }

    return (bool) preg_match('/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|embedly|quora|redditbot|ahrefs|semrush|lighthouse|pagespeed|pingdom|uptime|monitor|wget|curl|python-requests|go-http-client|headless/i', $ua);
}

function visitor_device(?string $ua): string
{
    $ua = (string) $ua;
    if (preg_match('/iPad|Tablet|PlayBook/i', $ua)) {
        return 'Tablet';
    }
    if (preg_match('/Mobile|Android|iPhone|iPod|webOS|BlackBerry/i', $ua)) {
        return 'Mobile';
    }
    return 'Desktop';
}

function visitor_lookup_geo(string $ip, array $cached): array
{
    if (isset($cached[$ip]) && is_array($cached[$ip])) {
        return $cached[$ip];
    }

    $cfCountry = strtoupper(trim((string) ($_SERVER['HTTP_CF_IPCOUNTRY'] ?? '')));
    if (visitor_ip_is_private($ip)) {
        return [
            'country' => 'Local',
            'region' => '',
            'city' => '',
            'country_code' => '',
        ];
    }

    $geo = [
        'country' => $cfCountry !== '' && $cfCountry !== 'XX' ? $cfCountry : 'Unknown',
        'region' => '',
        'city' => '',
        'country_code' => $cfCountry !== 'XX' ? $cfCountry : '',
    ];

    $url = 'https://ipwho.is/' . rawurlencode($ip) . '?fields=success,country,region,city,country_code';
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 0.8,
            'header' => "Accept: application/json\r\nUser-Agent: rashid-ahamed-profile\r\n",
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);
    $raw = @file_get_contents($url, false, $context);
    if ($raw === false) {
        return $geo;
    }

    $data = json_decode($raw, true);
    if (!is_array($data) || empty($data['success'])) {
        return $geo;
    }

    return [
        'country' => (string) ($data['country'] ?: $geo['country']),
        'region' => (string) ($data['region'] ?? ''),
        'city' => (string) ($data['city'] ?? ''),
        'country_code' => strtoupper((string) ($data['country_code'] ?? $geo['country_code'])),
    ];
}

function visitor_place(array $geo): string
{
    $parts = array_values(array_filter([
        (string) ($geo['city'] ?? ''),
        (string) ($geo['region'] ?? ''),
        (string) ($geo['country'] ?? ''),
    ], static fn (string $part): bool => $part !== ''));

    return $parts !== [] ? implode(', ', $parts) : 'Unknown';
}

function track_public_visit(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        return;
    }
    if (!empty($_SESSION['admin_user'])) {
        return;
    }

    $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    if (visitor_is_bot($ua)) {
        return;
    }

    $purpose = strtolower((string) ($_SERVER['HTTP_SEC_PURPOSE'] ?? $_SERVER['HTTP_PURPOSE'] ?? ''));
    if (str_contains($purpose, 'prefetch') || strtolower((string) ($_SERVER['HTTP_X_MOZ'] ?? '')) === 'prefetch') {
        return;
    }

    $ip = visitor_client_ip();
    if ($ip === '') {
        return;
    }

    $now = time();
    $day = date('Y-m-d');
    $path = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $ref = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    $refHost = strtolower((string) (parse_url($ref, PHP_URL_HOST) ?? ''));
    if ($refHost === '' || $refHost === $host || str_ends_with($refHost, '.' . $host)) {
        $ref = '';
    }

    visitors_mutate(static function (array $store) use ($ip, $now, $day, $path, $ua, $ref): array {
        $last = $store['visits'][0] ?? null;
        if (is_array($last) && ($last['ip'] ?? '') === $ip) {
            $prev = strtotime((string) ($last['at'] ?? '')) ?: 0;
            if ($prev > 0 && ($now - $prev) < 20) {
                return $store;
            }
        }

        $geo = visitor_lookup_geo($ip, $store['geo']);
        $store['geo'][$ip] = $geo;
        if (count($store['geo']) > 2500) {
            $store['geo'] = array_slice($store['geo'], -2000, null, true);
        }

        $isNew = empty($store['known'][$ip]);
        if ($isNew) {
            $store['known'][$ip] = $day;
            $store['unique']++;
            if (count($store['known']) > 8000) {
                $store['known'] = array_slice($store['known'], -7000, null, true);
            }
        }

        $store['views']++;
        $row = $store['daily'][$day] ?? ['views' => 0, 'unique' => 0, 'ips' => []];
        $row['views'] = (int) $row['views'] + 1;
        $row['ips'] = is_array($row['ips'] ?? null) ? $row['ips'] : [];
        if (empty($row['ips'][$ip])) {
            $row['ips'][$ip] = 1;
            $row['unique'] = (int) $row['unique'] + 1;
        }
        $store['daily'][$day] = $row;

        $cutoffKeepIps = date('Y-m-d', $now - 14 * 86400);
        $cutoffDrop = date('Y-m-d', $now - 120 * 86400);
        foreach ($store['daily'] as $key => $daily) {
            if (!is_string($key) || $key < $cutoffDrop) {
                unset($store['daily'][$key]);
                continue;
            }
            if ($key < $cutoffKeepIps && is_array($daily)) {
                unset($daily['ips']);
                $store['daily'][$key] = $daily;
            }
        }
        ksort($store['daily']);

        array_unshift($store['visits'], [
            'id' => bin2hex(random_bytes(6)),
            'at' => date('c', $now),
            'ip' => $ip,
            'path' => $path,
            'device' => visitor_device($ua),
            'referrer' => $ref,
            'country' => (string) ($geo['country'] ?? ''),
            'region' => (string) ($geo['region'] ?? ''),
            'city' => (string) ($geo['city'] ?? ''),
        ]);
        $store['visits'] = array_values(array_slice($store['visits'], 0, 400));

        return $store;
    });
}

function load_visitor_stats(): array
{
    $path = visitors_path();
    $store = visitors_empty_store();
    if (is_readable($path)) {
        $store = visitors_normalize(json_decode((string) file_get_contents($path), true));
    }

    $today = date('Y-m-d');
    $weekStart = date('Y-m-d', time() - 6 * 86400);
    $todayRow = $store['daily'][$today] ?? ['views' => 0, 'unique' => 0];
    $weekViews = 0;
    $weekIps = [];
    $countries = [];
    $regions = [];

    foreach ($store['daily'] as $day => $row) {
        if (!is_string($day) || !is_array($row) || $day < $weekStart) {
            continue;
        }
        $weekViews += (int) ($row['views'] ?? 0);
        foreach ((array) ($row['ips'] ?? []) as $ip => $flag) {
            if ($flag) {
                $weekIps[$ip] = 1;
            }
        }
    }

    if ($weekIps === []) {
        foreach ($store['visits'] as $visit) {
            if (!is_array($visit)) {
                continue;
            }
            $at = (string) ($visit['at'] ?? '');
            $stamp = $at !== '' ? strtotime($at) : false;
            if (!$stamp || $stamp < time() - 7 * 86400) {
                continue;
            }
            $weekIps[(string) ($visit['ip'] ?? '')] = 1;
        }
        unset($weekIps['']);
    }

    foreach ($store['visits'] as $visit) {
        if (!is_array($visit)) {
            continue;
        }
        $country = (string) ($visit['country'] ?? '');
        if ($country !== '') {
            $countries[$country] = ($countries[$country] ?? 0) + 1;
        }
        $place = visitor_place($visit);
        if ($place !== 'Unknown') {
            $regions[$place] = ($regions[$place] ?? 0) + 1;
        }
    }

    arsort($countries);
    arsort($regions);

    $top = static function (array $map, int $limit): array {
        $out = [];
        $i = 0;
        foreach ($map as $label => $count) {
            $out[] = ['label' => (string) $label, 'count' => (int) $count];
            if (++$i >= $limit) {
                break;
            }
        }
        return $out;
    };

    return [
        'views' => $store['views'],
        'unique' => $store['unique'],
        'today_views' => (int) ($todayRow['views'] ?? 0),
        'today_unique' => (int) ($todayRow['unique'] ?? 0),
        'week_views' => $weekViews,
        'week_unique' => count($weekIps),
        'countries' => $top($countries, 8),
        'regions' => $top($regions, 8),
        'visits' => $store['visits'],
    ];
}

function clear_visitor_stats(): bool
{
    return visitors_mutate(static fn (): array => visitors_empty_store());
}

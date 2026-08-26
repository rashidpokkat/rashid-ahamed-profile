<?php

declare(strict_types=1);

function content_path(): string
{
    return dirname(__DIR__) . '/data/content.json';
}

function admin_path(): string
{
    return dirname(__DIR__) . '/data/admin.json';
}

function load_content(): array
{
    $path = content_path();
    if (is_readable($path)) {
        $data = json_decode((string) file_get_contents($path), true);
        if (is_array($data) && !empty($data['name'])) {
            $defaults = require __DIR__ . '/content.default.php';
            $data['photos'] = array_merge([
                'avatar' => 'assets/images/avatar.jpg',
                'about' => 'assets/images/about.jpg',
                'peek' => 'assets/images/peek.jpg',
                'portrait' => 'assets/images/portrait.jpg',
                'gallery_1' => 'assets/images/gallery-1.jpg',
                'gallery_2' => 'assets/images/gallery-2.jpg',
            ], is_array($data['photos'] ?? null) ? $data['photos'] : []);
            $data['social'] = array_merge(
                is_array($defaults['social'] ?? null) ? $defaults['social'] : [],
                array_filter(is_array($data['social'] ?? null) ? $data['social'] : [], static fn($value) => $value !== '' && $value !== null)
            );
            return $data;
        }
    }

    return require __DIR__ . '/content.default.php';
}

function content_photo(array $content, string $key): string
{
    return trim((string) ($content['photos'][$key] ?? ''));
}

function save_content(array $content): bool
{
    $dir = dirname(content_path());
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
        return false;
    }

    $json = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }

    return file_put_contents(content_path(), $json . PHP_EOL, LOCK_EX) !== false;
}

function admin_account(): ?array
{
    $path = admin_path();
    if (!is_readable($path)) {
        return null;
    }

    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) && !empty($data['username']) && !empty($data['password']) ? $data : null;
}

function save_admin_account(string $username, string $password): bool
{
    $dir = dirname(admin_path());
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
        return false;
    }

    $payload = json_encode([
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'created_at' => date('c'),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    return $payload !== false && file_put_contents(admin_path(), $payload . PHP_EOL, LOCK_EX) !== false;
}

function digits_only(string $value): string
{
    return preg_replace('/\D+/', '', $value) ?? '';
}

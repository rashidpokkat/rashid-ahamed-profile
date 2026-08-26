<?php

declare(strict_types=1);

function messages_path(): string
{
    return dirname(__DIR__) . '/data/messages.json';
}

function load_messages(): array
{
    $path = messages_path();
    if (!is_readable($path)) {
        return [];
    }

    $data = json_decode((string) file_get_contents($path), true);
    if (!is_array($data)) {
        return [];
    }

    $out = [];
    foreach ($data as $row) {
        if (!is_array($row) || empty($row['id'])) {
            continue;
        }
        $out[] = $row;
    }

    return $out;
}

function save_messages(array $messages): bool
{
    $dir = dirname(messages_path());
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
        return false;
    }

    $messages = array_values(array_slice($messages, 0, 200));
    $json = json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }

    return file_put_contents(messages_path(), $json . PHP_EOL, LOCK_EX) !== false;
}

function add_contact_message(string $name, string $email, string $message): bool
{
    $entry = [
        'id' => bin2hex(random_bytes(8)),
        'name' => $name,
        'email' => $email,
        'message' => $message,
        'created_at' => date('c'),
        'read' => false,
        'ip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
    ];

    $messages = load_messages();
    array_unshift($messages, $entry);
    return save_messages($messages);
}

function unread_message_count(array $messages): int
{
    $count = 0;
    foreach ($messages as $row) {
        if (empty($row['read'])) {
            $count++;
        }
    }
    return $count;
}

function update_message(string $id, callable $mutator): bool
{
    $messages = load_messages();
    $found = false;
    foreach ($messages as $i => $row) {
        if (($row['id'] ?? '') !== $id) {
            continue;
        }
        $messages[$i] = $mutator($row);
        $found = true;
        break;
    }

    return $found && save_messages($messages);
}

function delete_message(string $id): bool
{
    $messages = load_messages();
    $next = array_values(array_filter($messages, static fn ($row) => ($row['id'] ?? '') !== $id));
    if (count($next) === count($messages)) {
        return false;
    }
    return save_messages($next);
}

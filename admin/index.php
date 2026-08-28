<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/init.php';
require_once dirname(__DIR__) . '/includes/content.php';

function lines_of(string $text): array
{
    $parts = preg_split('/\r\n|\r|\n/', $text) ?: [];
    $out = [];
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part !== '') {
            $out[] = $part;
        }
    }
    return $out;
}

function csv_of(string $text): array
{
    $parts = preg_split('/[,;\n]+/', $text) ?: [];
    $out = [];
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part !== '') {
            $out[] = $part;
        }
    }
    return $out;
}

function store_upload(array $file, string $targetRel): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 3500000) {
        return null;
    }

    $info = new finfo(FILEINFO_MIME_TYPE);
    $mime = $info->file($file['tmp_name']);
    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => null,
    };
    if ($ext === null) {
        return null;
    }

    $dir = dirname(__DIR__) . '/assets/images';
    $name = pathinfo($targetRel, PATHINFO_FILENAME) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }

    return 'assets/images/' . $name;
}

$account = admin_account();
$loggedIn = !empty($_SESSION['admin_user']);
$error = '';
$notice = $_SESSION['admin_flash'] ?? '';
unset($_SESSION['admin_flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
        $error = 'Your session expired. Please refresh and try again.';
    } elseif (!$account && ($_POST['action'] ?? '') === 'setup') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['confirm'] ?? '');
        if (strlen($username) < 3 || strlen($username) > 40) {
            $error = 'Username must be 3–40 characters.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (!save_admin_account($username, $password)) {
            $error = 'Could not save the admin account. Check folder permissions on data/.';
        } else {
            $_SESSION['admin_user'] = $username;
            $_SESSION['admin_flash'] = 'Admin account created. You are signed in.';
            header('Location: index.php');
            exit;
        }
    } elseif ($account && !$loggedIn && ($_POST['action'] ?? '') === 'login') {
        $attempts = array_values(array_filter($_SESSION['login_attempts'] ?? [], static fn ($t) => $t > time() - 900));
        if (count($attempts) >= 8) {
            $error = 'Too many attempts. Wait a few minutes and try again.';
        } else {
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $okUser = hash_equals((string) $account['username'], $username);
            $okPass = password_verify($password, (string) $account['password']);
            if ($okUser && $okPass) {
                session_regenerate_id(true);
                $_SESSION['admin_user'] = $username;
                $_SESSION['login_attempts'] = [];
                header('Location: index.php');
                exit;
            }
            $attempts[] = time();
            $_SESSION['login_attempts'] = $attempts;
            $error = 'Username or password is incorrect.';
        }
    } elseif ($loggedIn && in_array((string) ($_POST['action'] ?? ''), ['message_read', 'message_unread', 'message_delete'], true)) {
        require_once dirname(__DIR__) . '/includes/messages.php';
        $messageId = trim((string) ($_POST['message_id'] ?? ''));
        $ok = false;
        if ($messageId !== '') {
            $ok = match ((string) $_POST['action']) {
                'message_read' => update_message($messageId, static function (array $row): array {
                    $row['read'] = true;
                    return $row;
                }),
                'message_unread' => update_message($messageId, static function (array $row): array {
                    $row['read'] = false;
                    return $row;
                }),
                'message_delete' => delete_message($messageId),
                default => false,
            };
        }
        $_SESSION['admin_flash'] = $ok
            ? (((string) $_POST['action'] === 'message_delete') ? 'Message deleted.' : 'Inbox updated.')
            : 'Could not update that message.';
        header('Location: index.php#messages');
        exit;
    } elseif ($loggedIn && ($_POST['action'] ?? '') === 'visitors_clear') {
        require_once dirname(__DIR__) . '/includes/visitors.php';
        $_SESSION['admin_flash'] = clear_visitor_stats()
            ? 'Visitor log cleared.'
            : 'Could not clear the visitor log.';
        header('Location: index.php#visitors');
        exit;
    } elseif ($loggedIn && ($_POST['action'] ?? '') === 'save') {
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $digits = digits_only($phone);
        $content = load_content();

        $content['name'] = trim((string) ($_POST['name'] ?? $content['name']));
        $content['short_name'] = trim((string) ($_POST['short_name'] ?? $content['short_name']));
        $content['initials'] = strtoupper(substr(trim((string) ($_POST['initials'] ?? 'RA')), 0, 3));
        $content['title'] = trim((string) ($_POST['title'] ?? $content['title']));
        $content['status_line'] = trim((string) ($_POST['status_line'] ?? ''));
        $content['tagline'] = trim((string) ($_POST['tagline'] ?? ''));
        $content['location'] = trim((string) ($_POST['location'] ?? ''));
        $content['timezone'] = trim((string) ($_POST['timezone'] ?? 'IST'));
        $content['email'] = trim((string) ($_POST['email'] ?? ''));
        $content['phone'] = $phone;
        $content['phone_raw'] = $digits;
        $content['about'] = trim((string) ($_POST['about'] ?? ''));
        $content['roles'] = lines_of((string) ($_POST['roles'] ?? ''));
        $content['social'] = [
            'linkedin' => trim((string) ($_POST['linkedin'] ?? '')),
            'instagram' => trim((string) ($_POST['instagram'] ?? '')),
            'facebook' => trim((string) ($_POST['facebook'] ?? '')),
            'whatsapp' => trim((string) ($_POST['whatsapp'] ?? ('https://wa.me/' . $digits))),
            'github' => trim((string) ($_POST['github'] ?? '')) ?: 'https://github.com/rashidpokkat',
            'email' => 'mailto:' . trim((string) ($_POST['email'] ?? '')),
        ];

        $stats = [];
        foreach ((array) ($_POST['stats'] ?? []) as $row) {
            $value = trim((string) ($row['value'] ?? ''));
            $label = trim((string) ($row['label'] ?? ''));
            if ($value !== '' || $label !== '') {
                $stats[] = ['value' => $value, 'label' => $label];
            }
        }
        $content['stats'] = $stats;

        $focus = [];
        foreach ((array) ($_POST['focus'] ?? []) as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $icon = (string) ($row['icon'] ?? 'cloud');
            if (!in_array($icon, ['cloud', 'repeat', 'layers', 'terminal'], true)) {
                $icon = 'cloud';
            }
            $focus[] = [
                'kicker' => trim((string) ($row['kicker'] ?? '')),
                'title' => $title,
                'icon' => $icon,
                'text' => trim((string) ($row['text'] ?? '')),
            ];
        }
        $content['focus'] = $focus;

        $jobs = [];
        foreach ((array) ($_POST['jobs'] ?? []) as $row) {
            $role = trim((string) ($row['role'] ?? ''));
            if ($role === '') {
                continue;
            }
            $jobs[] = [
                'role' => $role,
                'company' => trim((string) ($row['company'] ?? '')),
                'logo' => trim((string) ($row['logo'] ?? '')),
                'logo_wide' => !empty($row['logo_wide']),
                'location' => trim((string) ($row['location'] ?? '')),
                'period' => trim((string) ($row['period'] ?? '')),
                'current' => !empty($row['current']),
                'points' => lines_of((string) ($row['points'] ?? '')),
            ];
        }
        $content['experience'] = $jobs;

        $skills = [];
        foreach ((array) ($_POST['skill_groups'] ?? []) as $row) {
            $group = trim((string) ($row['name'] ?? ''));
            if ($group === '') {
                continue;
            }
            $skills[$group] = csv_of((string) ($row['items'] ?? ''));
        }
        $content['skills'] = $skills;

        $logos = [];
        foreach ((array) ($_POST['skill_logos'] ?? []) as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $file = trim((string) ($row['file'] ?? ''));
            if ($name !== '' && $file !== '') {
                $logos[] = ['name' => $name, 'file' => $file];
            }
        }
        $content['skill_logos'] = $logos;

        $content['education'] = [
            'degree' => trim((string) ($_POST['degree'] ?? '')),
            'school' => trim((string) ($_POST['school'] ?? '')),
            'period' => trim((string) ($_POST['edu_period'] ?? '')),
            'logo' => trim((string) ($_POST['edu_logo'] ?? '')),
        ];
        $content['certifications'] = lines_of((string) ($_POST['certifications'] ?? ''));

        $photoMap = [
            'avatar' => 'avatar.jpg',
            'peek' => 'peek.jpg',
            'about' => 'about.jpg',
            'portrait' => 'portrait.jpg',
            'gallery_1' => 'gallery-1.jpg',
        ];
        $removePhotos = (array) ($_POST['remove_photos'] ?? []);
        foreach ($photoMap as $key => $fallbackName) {
            $saved = null;
            if (!empty($_FILES[$key]) && is_array($_FILES[$key])) {
                $saved = store_upload($_FILES[$key], $fallbackName);
            }
            if ($saved) {
                $content['photos'][$key] = $saved;
                continue;
            }
            if (!empty($removePhotos[$key])) {
                $content['photos'][$key] = '';
            }
        }

        if (!save_content($content)) {
            $error = 'Could not save content. Check that the data/ folder is writable.';
        } else {
            $_SESSION['admin_flash'] = 'Saved. The public site now shows your updates.';
            header('Location: index.php');
            exit;
        }
    }
}

$c = load_content();
$skillGroups = [];
foreach (($c['skills'] ?? []) as $group => $items) {
    $skillGroups[] = ['name' => $group, 'items' => is_array($items) ? implode(', ', $items) : ''];
}

function admin_photo(array $content, string $key): string
{
    $rel = content_photo($content, $key);
    if ($rel === '') {
        return '';
    }
    $abs = dirname(__DIR__) . '/' . $rel;
    $ver = is_file($abs) ? (string) filemtime($abs) : (string) time();
    return '../' . $rel . '?v=' . $ver;
}

$jobCount = count($c['experience'] ?? []);
$skillCount = count($c['skills'] ?? []);
$certCount = count($c['certifications'] ?? []);
$adminUser = (string) ($_SESSION['admin_user'] ?? 'admin');
require_once dirname(__DIR__) . '/includes/messages.php';
require_once dirname(__DIR__) . '/includes/visitors.php';
$inbox = $loggedIn ? load_messages() : [];
$unreadCount = unread_message_count($inbox);
$traffic = $loggedIn ? load_visitor_stats() : [
    'views' => 0,
    'unique' => 0,
    'today_views' => 0,
    'today_unique' => 0,
    'week_views' => 0,
    'week_unique' => 0,
    'countries' => [],
    'regions' => [],
    'visits' => [],
];
include __DIR__ . '/view.php';

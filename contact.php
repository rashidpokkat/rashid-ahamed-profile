<?php
require __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/messages.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Please submit the form to send a message.']);
    exit;
}

$csrf = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', (string) $csrf)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Your session expired. Please refresh and try again.']);
    exit;
}

if (!empty($_POST['website'])) {
    echo json_encode(['ok' => true, 'message' => 'Thanks, I will get back to you shortly.']);
    exit;
}

$attempts = array_values(array_filter($_SESSION['contact_attempts'] ?? [], static fn ($t) => $t > time() - 900));
if (count($attempts) >= 8) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'message' => 'Too many messages. Please wait a few minutes and try again.']);
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
$len = static fn (string $value): int => function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);

if ($name === '' || $len($name) > 80) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Please enter your name.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

if ($message === '' || $len($message) < 8 || $len($message) > 2000) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Please write a short message (at least 8 characters).']);
    exit;
}

$attempts[] = time();
$_SESSION['contact_attempts'] = $attempts;

$stored = add_contact_message($name, $email, $message);

$to = (string) ($config['email'] ?? '');
if ($to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL)) {
    $subject = 'Portfolio message from ' . $name;
    $body = "Name: {$name}\nEmail: {$email}\n\n{$message}\n";
    $headers = [
        'From: ' . $to,
        'Reply-To: ' . $email,
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: PHP/' . PHP_VERSION,
    ];
    @mail($to, $subject, $body, implode("\r\n", $headers));
}

if ($stored) {
    echo json_encode(['ok' => true, 'message' => 'Message sent. I will reply as soon as I can.']);
    exit;
}

echo json_encode(['ok' => false, 'message' => 'Could not send. Please use email or WhatsApp.']);

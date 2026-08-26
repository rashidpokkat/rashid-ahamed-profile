<?php
/** @var array $config */
$pageTitle = $pageTitle ?? ($config['name'] . ' — ' . $config['title']);
$pageDescription = $pageDescription ?? $config['tagline'];
$ogImage = $ogImage ?? content_photo($config, 'avatar');
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
    . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="author" content="<?= e($config['name']) ?>">
    <meta name="color-scheme" content="dark light">
    <meta name="theme-color" content="#0d0e12">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= e($config['short_name'] ?? 'Profile') ?>">
    <link rel="manifest" href="manifest.webmanifest">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:type" content="website">
    <?php if ($ogImage !== ''): ?>
    <meta property="og:image" content="<?= e($baseUrl . '/' . $ogImage) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <?php else: ?>
    <meta name="twitter:card" content="summary">
    <?php endif; ?>
    <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        (function () {
            try {
                var saved = localStorage.getItem("theme");
                var theme = saved || (window.matchMedia("(prefers-color-scheme: light)").matches ? "light" : "dark");
                document.documentElement.setAttribute("data-theme", theme);
            } catch (e) {}
        })();
    </script>
    <script type="application/ld+json">
    <?= json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => $config['name'],
        'jobTitle' => $config['title'],
        'email' => $config['email'],
        'telephone' => $config['phone'],
        'address' => [
            '@type' => 'PostalAddress',
            'addressRegion' => 'Kerala',
            'addressCountry' => 'IN',
        ],
        'url' => $baseUrl,
        'sameAs' => array_values(array_filter([
            $config['social']['linkedin'] ?? '',
            $config['social']['github'] ?? '',
            $config['social']['instagram'] ?? '',
            $config['social']['facebook'] ?? '',
        ])),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>

    <header class="site-header" id="site-header">
        <div class="container header-inner">
            <a class="logo" href="#about">
                <span class="logo-mark"><?= e($config['initials']) ?></span>
                <span class="logo-copy">
                    <strong><?= e($config['short_name']) ?></strong>
                    <span class="role-rotate" id="role-rotate"><?= e($config['roles'][0] ?? 'DevOps Engineer') ?></span>
                </span>
            </a>
            <nav class="site-nav" id="site-nav" aria-label="Primary">
                <a href="#about">About</a>
                <a href="#work">Work</a>
                <a href="#skills">Skills</a>
                <a href="#github">GitHub</a>
                <a href="#contact">Contact</a>
            </nav>
            <div class="header-actions">
                <a class="status-chip" href="<?= e($config['social']['email']) ?>">
                    <span class="pulse"></span> available
                </a>
                <button class="theme-toggle" id="theme-toggle" type="button" aria-label="Toggle theme">
                    <span class="icon-sun"><?= icon('sun') ?></span>
                    <span class="icon-moon"><?= icon('moon') ?></span>
                </button>
            </div>
        </div>
    </header>

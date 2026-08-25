<?php
require __DIR__ . '/includes/init.php';
include __DIR__ . '/includes/header.php';
?>
<main id="main">
    <section class="hero" id="about">
        <div class="container hero-grid">
            <div class="avatar-wrap" data-reveal>
                <div class="portrait">
                    <div class="portrait-back" aria-hidden="true"></div>
                    <figure class="portrait-main">
                        <img src="assets/images/avatar.jpg" alt="Portrait of Rashid Ahamed" width="420" height="520" fetchpriority="high">
                        <span class="portrait-brackets" aria-hidden="true"></span>
                        <figcaption class="portrait-plate">
                            <strong><?= e($config['short_name']) ?></strong>
                            <span><?= e($config['location']) ?></span>
                        </figcaption>
                    </figure>
                    <figure class="portrait-peek">
                        <img src="assets/images/about.jpg" alt="" width="220" height="280">
                    </figure>
                    <span class="portrait-chip">operator / 01</span>
                </div>
                <div class="meta-pills">
                    <span><i class="pulse"></i> available</span>
                    <span>tz <?= e($config['timezone']) ?></span>
                    <span>reply &lt;24h</span>
                </div>
            </div>
            <div class="hero-copy">
                <p class="kicker" data-reveal>operator / 01</p>
                <h1 data-reveal>About Me<span class="dot">.</span></h1>
                <p class="status-line" data-reveal>◆ <?= e($config['status_line']) ?></p>
                <p class="about-lead" data-reveal><?= e($config['about']) ?></p>
                <div class="hero-actions" data-reveal>
                    <a class="btn btn-primary" href="#contact">Let’s talk <?= icon('arrow') ?></a>
                    <a class="btn btn-ghost" href="<?= e($config['social']['whatsapp']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('whatsapp') ?> WhatsApp</a>
                </div>
                <div class="socials" data-reveal>
                    <a href="<?= e($config['social']['linkedin']) ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><?= icon('linkedin') ?></a>
                    <a href="<?= e($config['social']['instagram']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><?= icon('instagram') ?></a>
                    <a href="<?= e($config['social']['facebook']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><?= icon('facebook') ?></a>
                    <a href="<?= e($config['social']['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><?= icon('whatsapp') ?></a>
                    <a href="<?= e($config['social']['email']) ?>" aria-label="Email"><?= icon('mail') ?></a>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="stats">
        <div class="container">
            <div class="section-head">
                <p class="kicker" data-reveal>Stats</p>
                <h2 data-reveal>By the Numbers</h2>
            </div>
            <div class="stats-grid">
                <?php foreach ($config['stats'] as $stat): ?>
                    <article class="stat-card" data-reveal>
                        <strong><?= e($stat['value']) ?></strong>
                        <span><?= e($stat['label']) ?></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section" id="doing">
        <div class="container">
            <div class="section-head">
                <p class="kicker" data-reveal>Scope</p>
                <h2 data-reveal>What I’m Doing</h2>
            </div>
            <div class="focus-grid">
                <?php foreach ($config['focus'] as $item): ?>
                    <article class="focus-card" data-reveal>
                        <p class="kicker"><?= e($item['kicker']) ?></p>
                        <div class="focus-icon"><?= icon($item['icon']) ?></div>
                        <h3><?= e($item['title']) ?></h3>
                        <p><?= e($item['text']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section" id="work">
        <div class="container work-layout">
            <div class="section-head">
                <p class="kicker" data-reveal>Career</p>
                <h2 data-reveal>Where I’ve built platforms</h2>
            </div>
            <ol class="timeline">
                <?php foreach ($config['experience'] as $job): ?>
                    <li class="timeline-item" data-reveal>
                        <div class="timeline-dot<?= !empty($job['current']) ? ' is-current' : '' ?>"></div>
                        <article class="card">
                            <div class="job-head">
                                <?php if (!empty($job['logo'])): ?>
                                    <img class="company-logo<?= !empty($job['logo_wide']) ? ' is-wide' : '' ?>" src="assets/logos/<?= e($job['logo']) ?>" alt="<?= e($job['company']) ?> logo" width="48" height="48">
                                <?php endif; ?>
                                <div class="job-copy">
                                    <div class="timeline-top">
                                        <h3><?= e($job['role']) ?></h3>
                                        <span class="period"><?= e($job['period']) ?></span>
                                    </div>
                                    <p class="company"><?= e($job['company']) ?> · <?= e($job['location']) ?></p>
                                </div>
                            </div>
                            <ul>
                                <?php foreach ($job['points'] as $point): ?>
                                    <li><?= e($point) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <section class="section" id="skills">
        <div class="container">
            <div class="section-head">
                <p class="kicker" data-reveal>Stack</p>
                <h2 data-reveal>Tools I work with</h2>
            </div>
            <div class="tech-wall" data-reveal>
                <?php foreach ($config['skill_logos'] as $logo): ?>
                    <article class="tech-tile">
                        <span class="tech-mark">
                            <img src="assets/logos/<?= e($logo['file']) ?>" alt="<?= e($logo['name']) ?> logo" width="36" height="36">
                        </span>
                        <span><?= e($logo['name']) ?></span>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="skills-grid">
                <?php foreach ($config['skills'] as $group => $items): ?>
                    <article class="card skill-card" data-reveal>
                        <h3><?= e($group) ?></h3>
                        <div class="pills">
                            <?php foreach ($items as $item): ?>
                                <span><?= e($item) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="edu-grid">
                <article class="card edu-card" data-reveal>
                    <?php if (!empty($config['education']['logo'])): ?>
                        <img class="company-logo" src="assets/logos/<?= e($config['education']['logo']) ?>" alt="<?= e($config['education']['school']) ?> logo" width="48" height="48">
                    <?php endif; ?>
                    <p class="kicker">Education</p>
                    <h3><?= e($config['education']['degree']) ?></h3>
                    <p><?= e($config['education']['school']) ?></p>
                    <p class="muted"><?= e($config['education']['period']) ?></p>
                </article>
                <article class="card" data-reveal>
                    <p class="kicker">Certifications</p>
                    <ul class="cert-list">
                        <?php foreach ($config['certifications'] as $cert): ?>
                            <li><?= e($cert) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <section class="section" id="photos">
        <div class="container">
            <div class="section-head">
                <p class="kicker" data-reveal>Beyond the terminal</p>
                <h2 data-reveal>A few frames from the hills</h2>
            </div>
            <div class="photo-grid">
                <figure data-reveal>
                    <img src="assets/images/about.jpg" alt="Rashid Ahamed with arms open at a tea plantation" width="800" height="1100" loading="lazy">
                </figure>
                <figure data-reveal>
                    <img src="assets/images/portrait.jpg" alt="Rashid Ahamed against misty tea hills" width="720" height="960" loading="lazy">
                </figure>
                <figure data-reveal>
                    <img src="assets/images/gallery-1.jpg" alt="Rashid Ahamed looking across a foggy plantation" width="720" height="960" loading="lazy">
                </figure>
            </div>
        </div>
    </section>

    <section class="section section-contact" id="contact">
        <div class="container contact-grid">
            <div>
                <p class="kicker" data-reveal>Contact</p>
                <h2 data-reveal>Let’s work together.</h2>
                <p data-reveal>For DevOps, cloud platforms, or a conversation about reliability — reach me on any of these channels.</p>
                <div class="contact-cards">
                    <a class="card contact-card" href="<?= e($config['social']['email']) ?>" data-reveal>
                        <?= icon('mail') ?>
                        <div><strong>Email</strong><span><?= e($config['email']) ?></span></div>
                    </a>
                    <a class="card contact-card" href="<?= e($config['social']['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" data-reveal>
                        <?= icon('whatsapp') ?>
                        <div><strong>WhatsApp</strong><span><?= e($config['phone']) ?></span></div>
                    </a>
                    <a class="card contact-card" href="tel:+<?= e($config['phone_raw']) ?>" data-reveal>
                        <?= icon('phone') ?>
                        <div><strong>Call</strong><span><?= e($config['phone']) ?></span></div>
                    </a>
                    <a class="card contact-card" href="<?= e($config['social']['linkedin']) ?>" target="_blank" rel="noopener noreferrer" data-reveal>
                        <?= icon('linkedin') ?>
                        <div><strong>LinkedIn</strong><span>rashidpokkat</span></div>
                    </a>
                    <a class="card contact-card" href="<?= e($config['social']['instagram']) ?>" target="_blank" rel="noopener noreferrer" data-reveal>
                        <?= icon('instagram') ?>
                        <div><strong>Instagram</strong><span>@rashidahamed_</span></div>
                    </a>
                    <a class="card contact-card" href="<?= e($config['social']['facebook']) ?>" target="_blank" rel="noopener noreferrer" data-reveal>
                        <?= icon('facebook') ?>
                        <div><strong>Facebook</strong><span>rashidpokkat</span></div>
                    </a>
                </div>
            </div>
            <form class="card contact-form" id="contact-form" method="post" action="contact.php" data-reveal>
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <div class="honeypot" aria-hidden="true">
                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <label>
                    Name
                    <input type="text" name="name" maxlength="80" required placeholder="Your name" autocomplete="name">
                </label>
                <label>
                    Email
                    <input type="email" name="email" required placeholder="you@example.com" autocomplete="email">
                </label>
                <label>
                    Message
                    <textarea name="message" rows="5" minlength="8" maxlength="2000" required placeholder="How can I help?"></textarea>
                </label>
                <button class="btn btn-primary" type="submit">Send message <?= icon('arrow') ?></button>
                <p class="form-status" id="form-status" role="status" aria-live="polite"></p>
            </form>
        </div>
    </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>

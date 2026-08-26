    <footer class="site-footer">
        <div class="container footer-inner">
            <p>© <?= date('Y') ?> <?= e($config['name']) ?> · <?= e($config['location']) ?></p>
            <div class="socials">
                <a href="<?= e($config['social']['linkedin']) ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><?= icon('linkedin') ?></a>
                <?php if (!empty($config['social']['github'])): ?>
                    <a href="<?= e($config['social']['github']) ?>" target="_blank" rel="noopener noreferrer" aria-label="GitHub"><?= icon('github') ?></a>
                <?php endif; ?>
                <a href="<?= e($config['social']['instagram']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><?= icon('instagram') ?></a>
                <a href="<?= e($config['social']['facebook']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><?= icon('facebook') ?></a>
                <a href="<?= e($config['social']['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><?= icon('whatsapp') ?></a>
                <a href="<?= e($config['social']['email']) ?>" aria-label="Email"><?= icon('mail') ?></a>
            </div>
        </div>
    </footer>

    <nav class="bottom-nav" aria-label="Mobile">
        <a href="#about" data-nav="about"><?= icon('home') ?><span>Home</span></a>
        <a href="#work" data-nav="work"><?= icon('briefcase') ?><span>Work</span></a>
        <a href="#skills" data-nav="skills"><?= icon('grid') ?><span>Skills</span></a>
        <a href="#github" data-nav="github"><?= icon('github') ?><span>GitHub</span></a>
        <a href="#contact" data-nav="contact"><?= icon('chat') ?><span>Contact</span></a>
    </nav>

    <script>window.__ROLES__ = <?= json_encode($config['roles'], JSON_UNESCAPED_UNICODE) ?>;</script>
    <script src="assets/js/main.js"></script>
</body>
</html>

<?php
/** @var array $c */
/** @var bool $account */
/** @var bool $loggedIn */
/** @var string $error */
/** @var string $notice */
/** @var array $skillGroups */
/** @var int $jobCount */
/** @var int $skillCount */
/** @var int $certCount */
/** @var string $adminUser */

$focusIcons = [
    'cloud' => 'Cloud',
    'repeat' => 'Delivery',
    'layers' => 'Platform',
    'terminal' => 'Automation',
];
$photoGroups = [
    'Hero' => [
        'avatar' => ['Profile photo', 'Main portrait on About Me'],
        'peek' => ['Peek portrait', 'Small overlapping photo beside the profile'],
    ],
    'Gallery' => [
        'about' => ['Gallery photo 1', 'First frame in the photo strip'],
        'portrait' => ['Gallery photo 2', 'Second frame in the photo strip'],
        'gallery_1' => ['Gallery photo 3', 'Third frame in the photo strip'],
    ],
];
$socialFields = [
    'linkedin' => ['LinkedIn', 'linkedin', 'https://www.linkedin.com/in/…'],
    'github' => ['GitHub', 'github', 'https://github.com/username'],
    'instagram' => ['Instagram', 'instagram', 'https://www.instagram.com/…'],
    'facebook' => ['Facebook', 'facebook', 'https://www.facebook.com/…'],
    'whatsapp' => ['WhatsApp', 'whatsapp', 'https://wa.me/91…'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Studio — <?= e($c['name'] ?? 'Profile') ?></title>
    <meta name="color-scheme" content="dark">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<?php if (!$account): ?>
    <main class="auth-screen">
        <form class="auth-card" method="post">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" value="setup">
            <div class="brand-row">
                <span class="logo-mark"><?= e($c['initials'] ?? 'RA') ?></span>
                <div>
                    <p class="kicker">First run</p>
                    <strong>Profile studio</strong>
                </div>
            </div>
            <h1>Create your admin account</h1>
            <p class="lead">Choose a username and a strong password. You will use these to edit the public site.</p>
            <?php if ($error): ?><p class="flash err"><?= icon('chat') ?><span><?= e($error) ?></span></p><?php endif; ?>
            <label class="field"><span>Username</span><input name="username" required minlength="3" maxlength="40" autocomplete="username"></label>
            <label class="field"><span>Password</span>
                <div class="password-wrap">
                    <input id="setup-password" type="password" name="password" required minlength="8" autocomplete="new-password">
                    <button class="password-toggle" type="button" data-password="setup-password" aria-label="Show password"><?= icon('eye') ?></button>
                </div>
            </label>
            <label class="field"><span>Confirm password</span><input type="password" name="confirm" required minlength="8" autocomplete="new-password"></label>
            <button class="btn" type="submit"><?= icon('check') ?> Create account</button>
        </form>
    </main>
    <script src="admin.js"></script>
<?php elseif (!$loggedIn): ?>
    <main class="auth-screen">
        <form class="auth-card" method="post">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" value="login">
            <div class="brand-row">
                <span class="logo-mark"><?= e($c['initials'] ?? 'RA') ?></span>
                <div>
                    <p class="kicker">Studio</p>
                    <strong><?= e($c['short_name'] ?? 'Profile') ?></strong>
                </div>
            </div>
            <h1>Welcome back</h1>
            <p class="lead">Sign in to update copy, photos, and experience on the public site.</p>
            <?php if ($error): ?><p class="flash err"><?= icon('chat') ?><span><?= e($error) ?></span></p><?php endif; ?>
            <label class="field"><span>Username</span><input name="username" required autocomplete="username" autofocus></label>
            <label class="field"><span>Password</span>
                <div class="password-wrap">
                    <input id="login-password" type="password" name="password" required autocomplete="current-password">
                    <button class="password-toggle" type="button" data-password="login-password" aria-label="Show password"><?= icon('eye') ?></button>
                </div>
            </label>
            <button class="btn" type="submit">Sign in <?= icon('arrow') ?></button>
        </form>
    </main>
    <script src="admin.js"></script>
<?php else: ?>
    <div class="studio">
        <aside class="studio-nav">
            <a class="studio-brand" href="#overview">
                <span class="logo-mark"><?= e($c['initials'] ?? 'RA') ?></span>
                <span>
                    <strong>Profile studio</strong>
                    <span class="brand-copy">Content editor</span>
                </span>
            </a>
            <nav aria-label="Sections">
                <a href="#overview" class="is-active"><?= icon('home') ?> Overview</a>
                <a href="#profile"><?= icon('user') ?> Profile</a>
                <a href="#social"><?= icon('link') ?> Social</a>
                <a href="#photos"><?= icon('image') ?> Photos</a>
                <a href="#stats"><?= icon('chart') ?> Stats</a>
                <a href="#focus"><?= icon('layers') ?> Focus</a>
                <a href="#experience"><?= icon('briefcase') ?> Experience</a>
                <a href="#skills"><?= icon('grid') ?> Skills</a>
                <a href="#education"><?= icon('check') ?> Education</a>
            </nav>
            <div class="nav-user">
                <p>Signed in as <strong><?= e($adminUser) ?></strong></p>
                <a href="logout.php"><?= icon('logout') ?> Log out</a>
            </div>
        </aside>

        <div class="studio-main">
            <header class="studio-top">
                <div>
                    <h1>Edit site content</h1>
                    <p>Changes go live on the public page as soon as you save.</p>
                </div>
                <div class="studio-actions">
                    <a class="btn btn-ghost btn-sm" href="../" target="_blank" rel="noopener noreferrer"><?= icon('external') ?> View site</a>
                    <button class="btn btn-sm save-btn" type="submit" form="content-form"><?= icon('save') ?> Save</button>
                </div>
            </header>

            <form class="wrap" id="content-form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="save">
                <?php if ($notice): ?><p class="flash ok"><?= icon('check') ?><span><?= e($notice) ?></span></p><?php endif; ?>
                <?php if ($error): ?><p class="flash err"><?= icon('chat') ?><span><?= e($error) ?></span></p><?php endif; ?>

                <section class="overview" id="overview">
                    <article class="overview-hero">
                        <p class="kicker">Now live</p>
                        <h2><?= e($c['name'] ?? '') ?></h2>
                        <p><?= e($c['title'] ?? '') ?> · <?= e($c['location'] ?? '') ?></p>
                    </article>
                    <div class="overview-chips">
                        <article class="stat-chip"><strong><?= $jobCount ?></strong><span>Experience roles</span></article>
                        <article class="stat-chip"><strong><?= $skillCount ?></strong><span>Skill groups</span></article>
                        <article class="stat-chip"><strong><?= $certCount ?></strong><span>Certifications</span></article>
                    </div>
                </section>

                <section class="section" id="profile">
                    <div class="section-head">
                        <div class="section-copy">
                            <p class="kicker">Identity</p>
                            <h2>Profile</h2>
                            <p>Name, contact, and the About copy visitors see first.</p>
                        </div>
                    </div>
                    <div class="grid-2">
                        <label class="field"><span>Full name</span><input name="name" value="<?= e($c['name'] ?? '') ?>" required></label>
                        <label class="field"><span>Short name</span><input name="short_name" value="<?= e($c['short_name'] ?? '') ?>"></label>
                        <label class="field"><span>Initials</span><input name="initials" value="<?= e($c['initials'] ?? '') ?>" maxlength="3"></label>
                        <label class="field"><span>Title</span><input name="title" value="<?= e($c['title'] ?? '') ?>"></label>
                        <label class="field"><span>Location</span><input name="location" value="<?= e($c['location'] ?? '') ?>"></label>
                        <label class="field"><span>Timezone</span><input name="timezone" value="<?= e($c['timezone'] ?? '') ?>"></label>
                        <label class="field"><span>Email</span><input type="email" name="email" value="<?= e($c['email'] ?? '') ?>"></label>
                        <label class="field"><span>Phone</span><input name="phone" value="<?= e($c['phone'] ?? '') ?>"></label>
                    </div>
                    <label class="field"><span>Status line</span><input name="status_line" value="<?= e($c['status_line'] ?? '') ?>"><span class="help">Shown under the About heading, with a diamond prefix.</span></label>
                    <label class="field"><span>Tagline</span><input name="tagline" value="<?= e($c['tagline'] ?? '') ?>"><span class="help">Used for search and social previews.</span></label>
                    <label class="field"><span>Rotating roles</span><textarea name="roles" rows="4"><?= e(implode("\n", $c['roles'] ?? [])) ?></textarea><span class="help">One per line. These cycle in the header next to your name.</span></label>
                    <label class="field"><span>About</span><textarea name="about" rows="8" data-count="about-count"><?= e($c['about'] ?? '') ?></textarea><span class="counter" id="about-count"></span></label>
                </section>

                <section class="section" id="social">
                    <div class="section-head">
                        <div class="section-copy">
                            <p class="kicker">Channels</p>
                            <h2>Social links</h2>
                            <p>These appear in the hero, footer, and contact cards.</p>
                        </div>
                    </div>
                    <?php foreach ($socialFields as $key => [$label, $iconName, $placeholder]): ?>
                        <div class="social-field">
                            <span class="icon"><?= icon($iconName) ?></span>
                            <label class="field"><span><?= e($label) ?></span><input name="<?= e($key) ?>" value="<?= e($c['social'][$key] ?? '') ?>" placeholder="<?= e($placeholder) ?>"></label>
                        </div>
                    <?php endforeach; ?>
                </section>

                <section class="section" id="photos">
                    <div class="section-head">
                        <div class="section-copy">
                            <p class="kicker">Media</p>
                            <h2>Photos</h2>
                            <p>Upload profile and portrait separately. JPEG, PNG, or WebP. Max 3.5 MB each. Leave a field empty to keep the current image.</p>
                        </div>
                    </div>
                    <?php foreach ($photoGroups as $groupTitle => $fields): ?>
                        <div class="photo-group">
                            <p class="kicker"><?= e($groupTitle) ?></p>
                            <div class="photo-grid<?= count($fields) > 2 ? ' cols-3' : '' ?>">
                                <?php foreach ($fields as $key => [$title, $help]): ?>
                                    <?php $src = admin_photo($c, $key); ?>
                                    <label class="photo-tile">
                                        <div class="photo-frame">
                                            <?php if ($src): ?>
                                                <img src="<?= e($src) ?>" alt="<?= e($title) ?>">
                                            <?php else: ?>
                                                <?= icon('image') ?>
                                            <?php endif; ?>
                                        </div>
                                        <strong><?= e($title) ?></strong>
                                        <span><?= e($help) ?></span>
                                        <input type="file" name="<?= e($key) ?>" accept="image/jpeg,image/png,image/webp">
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </section>

                <section class="section" id="stats">
                    <div class="section-head">
                        <div class="section-copy">
                            <p class="kicker">Numbers</p>
                            <h2>Stats</h2>
                            <p>The four highlight figures under About.</p>
                        </div>
                        <button class="btn btn-ghost btn-sm" type="button" data-add="stats"><?= icon('plus') ?> Add stat</button>
                    </div>
                    <div id="stats-list">
                        <?php foreach ($c['stats'] ?? [['value' => '', 'label' => '']] as $i => $stat): ?>
                            <article class="repeat-card">
                                <div class="repeat-head">
                                    <strong>Stat <?= $i + 1 ?></strong>
                                    <button class="btn btn-danger" type="button" data-remove>Remove</button>
                                </div>
                                <div class="grid-2">
                                    <label class="field"><span>Value</span><input name="stats[<?= $i ?>][value]" value="<?= e($stat['value'] ?? '') ?>"></label>
                                    <label class="field"><span>Label</span><input name="stats[<?= $i ?>][label]" value="<?= e($stat['label'] ?? '') ?>"></label>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="section" id="focus">
                    <div class="section-head">
                        <div class="section-copy">
                            <p class="kicker">Scope</p>
                            <h2>What I’m doing</h2>
                            <p>The four focus cards on the homepage.</p>
                        </div>
                        <button class="btn btn-ghost btn-sm" type="button" data-add="focus"><?= icon('plus') ?> Add card</button>
                    </div>
                    <div id="focus-list">
                        <?php foreach ($c['focus'] ?? [] as $i => $item): ?>
                            <article class="repeat-card">
                                <div class="repeat-head">
                                    <strong><?= e($item['title'] ?? 'Focus card') ?></strong>
                                    <button class="btn btn-danger" type="button" data-remove>Remove</button>
                                </div>
                                <div class="grid-2">
                                    <label class="field"><span>Kicker</span><input name="focus[<?= $i ?>][kicker]" value="<?= e($item['kicker'] ?? '') ?>"></label>
                                    <label class="field"><span>Title</span><input name="focus[<?= $i ?>][title]" value="<?= e($item['title'] ?? '') ?>"></label>
                                </div>
                                <p class="kicker">Icon</p>
                                <div class="icon-pick">
                                    <?php foreach ($focusIcons as $icon => $iconLabel): ?>
                                        <label>
                                            <input type="radio" name="focus[<?= $i ?>][icon]" value="<?= $icon ?>"<?= ($item['icon'] ?? '') === $icon ? ' checked' : '' ?>>
                                            <?= icon($icon) ?> <?= e($iconLabel) ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <label class="field"><span>Text</span><textarea name="focus[<?= $i ?>][text]" rows="3"><?= e($item['text'] ?? '') ?></textarea></label>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="section" id="experience">
                    <div class="section-head">
                        <div class="section-copy">
                            <p class="kicker">Career</p>
                            <h2>Experience</h2>
                            <p>Timeline roles, newest first. Logo path is relative to assets/logos/.</p>
                        </div>
                        <button class="btn btn-ghost btn-sm" type="button" data-add="jobs"><?= icon('plus') ?> Add role</button>
                    </div>
                    <div id="jobs-list">
                        <?php foreach ($c['experience'] ?? [] as $i => $job): ?>
                            <article class="repeat-card">
                                <div class="repeat-head">
                                    <strong><?= e($job['role'] ?? 'Role') ?><?php if (!empty($job['company'])): ?> · <?= e($job['company']) ?><?php endif; ?></strong>
                                    <button class="btn btn-danger" type="button" data-remove>Remove</button>
                                </div>
                                <div class="grid-2">
                                    <label class="field"><span>Role</span><input name="jobs[<?= $i ?>][role]" value="<?= e($job['role'] ?? '') ?>"></label>
                                    <label class="field"><span>Company</span><input name="jobs[<?= $i ?>][company]" value="<?= e($job['company'] ?? '') ?>"></label>
                                    <label class="field"><span>Location</span><input name="jobs[<?= $i ?>][location]" value="<?= e($job['location'] ?? '') ?>"></label>
                                    <label class="field"><span>Period</span><input name="jobs[<?= $i ?>][period]" value="<?= e($job['period'] ?? '') ?>"></label>
                                </div>
                                <label class="field"><span>Logo path</span><input name="jobs[<?= $i ?>][logo]" value="<?= e($job['logo'] ?? '') ?>"></label>
                                <div class="toggle-row">
                                    <label class="toggle">
                                        <input type="checkbox" name="jobs[<?= $i ?>][current]" value="1"<?= !empty($job['current']) ? ' checked' : '' ?>>
                                        <span class="toggle-ui"></span>
                                        <span>Current role</span>
                                    </label>
                                    <label class="toggle">
                                        <input type="checkbox" name="jobs[<?= $i ?>][logo_wide]" value="1"<?= !empty($job['logo_wide']) ? ' checked' : '' ?>>
                                        <span class="toggle-ui"></span>
                                        <span>Wide logo</span>
                                    </label>
                                </div>
                                <label class="field"><span>Highlights</span><textarea name="jobs[<?= $i ?>][points]" rows="4"><?= e(implode("\n", $job['points'] ?? [])) ?></textarea><span class="help">One achievement per line.</span></label>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="section" id="skills">
                    <div class="section-head">
                        <div class="section-copy">
                            <p class="kicker">Stack</p>
                            <h2>Skills</h2>
                            <p>Groups become chips on the site. Logo files live under assets/logos/.</p>
                        </div>
                        <button class="btn btn-ghost btn-sm" type="button" data-add="groups"><?= icon('plus') ?> Add group</button>
                    </div>
                    <div id="groups-list">
                        <?php foreach ($skillGroups as $i => $group): ?>
                            <article class="repeat-card">
                                <div class="repeat-head">
                                    <strong><?= e($group['name'] ?: 'Skill group') ?></strong>
                                    <button class="btn btn-danger" type="button" data-remove>Remove</button>
                                </div>
                                <div class="grid-2">
                                    <label class="field"><span>Group</span><input name="skill_groups[<?= $i ?>][name]" value="<?= e($group['name']) ?>"></label>
                                    <label class="field"><span>Items</span><input name="skill_groups[<?= $i ?>][items]" value="<?= e($group['items']) ?>"><span class="help">Comma-separated.</span></label>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="section-head" style="margin-top: 22px;">
                        <div class="section-copy">
                            <h2>Skill logos</h2>
                            <p>Shown in the tools wall. Example file: skills/docker.svg</p>
                        </div>
                        <button class="btn btn-ghost btn-sm" type="button" data-add="logos"><?= icon('plus') ?> Add logo</button>
                    </div>
                    <div id="logos-list">
                        <?php foreach ($c['skill_logos'] ?? [] as $i => $logo): ?>
                            <article class="repeat-card">
                                <div class="repeat-head">
                                    <strong><?= e($logo['name'] ?? 'Logo') ?></strong>
                                    <button class="btn btn-danger" type="button" data-remove>Remove</button>
                                </div>
                                <div class="grid-2">
                                    <label class="field"><span>Name</span><input name="skill_logos[<?= $i ?>][name]" value="<?= e($logo['name'] ?? '') ?>"></label>
                                    <label class="field"><span>File</span><input name="skill_logos[<?= $i ?>][file]" value="<?= e($logo['file'] ?? '') ?>"></label>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="section" id="education">
                    <div class="section-head">
                        <div class="section-copy">
                            <p class="kicker">Background</p>
                            <h2>Education &amp; certifications</h2>
                        </div>
                    </div>
                    <div class="grid-2">
                        <label class="field"><span>Degree</span><input name="degree" value="<?= e($c['education']['degree'] ?? '') ?>"></label>
                        <label class="field"><span>School</span><input name="school" value="<?= e($c['education']['school'] ?? '') ?>"></label>
                        <label class="field"><span>Period</span><input name="edu_period" value="<?= e($c['education']['period'] ?? '') ?>"></label>
                        <label class="field"><span>Logo path</span><input name="edu_logo" value="<?= e($c['education']['logo'] ?? '') ?>"></label>
                    </div>
                    <label class="field"><span>Certifications</span><textarea name="certifications" rows="4"><?= e(implode("\n", $c['certifications'] ?? [])) ?></textarea><span class="help">One per line.</span></label>
                </section>

                <div class="sticky-save">
                    <p id="dirty-note">All changes saved on the last submit.</p>
                    <button class="btn save-btn" type="submit"><?= icon('save') ?> Save changes</button>
                </div>
            </form>
        </div>
    </div>
    <script src="admin.js"></script>
<?php endif; ?>
</body>
</html>

<?php
// Bu dosya: Ziyaretçi istatistiklerini güncelleyen ve portfolyo ana sayfasını oluşturan giriş dosyası.
// Ortak veritabanı bağlantısı ana sayfanın dinamik bölümleri için yüklenir.
require_once __DIR__ . '/config/database.php';
// Ziyaret sayaçları tablo yoksa otomatik oluşturulur ve her sayfa açılışında güncellenir.
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS visitor_stats (
        id INT PRIMARY KEY DEFAULT 1,
        total_views INT NOT NULL DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("INSERT INTO visitor_stats (id, total_views) VALUES (1, 1)
        ON DUPLICATE KEY UPDATE total_views = total_views + 1");
    $pdo->exec("CREATE TABLE IF NOT EXISTS visitor_daily_stats (
        visit_date DATE PRIMARY KEY,
        views INT NOT NULL DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("INSERT INTO visitor_daily_stats (visit_date, views) VALUES (CURDATE(), 1)
        ON DUPLICATE KEY UPDATE views = views + 1");
} catch (Throwable $e) {
     
}
// Portfolyoda gösterilecek öne çıkan projeler veritabanından alınır.
try {
    $projects = $pdo->query("SELECT * FROM projects WHERE featured = 1 ORDER BY sort_order ASC, created_at DESC")->fetchAll();
} catch (Throwable $e) {
    $projects = [];
}

// Yetenekler kategori gruplaması için veritabanından okunur.
try {
    $skills = $pdo->query("SELECT * FROM skills ORDER BY sort_order ASC")->fetchAll();
} catch (Throwable $e) {
    $skills = [];
}

// Yetenekler arayüzde sekmeli/kategorili gösterilebilmesi için kategoriye göre gruplanır.
$skillsByCategory = [];
foreach ($skills as $skill) {
    $skillsByCategory[$skill['category']][] = $skill;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İrem Öztürk | Full Stack Developer</title>
    <meta name="description" content="Creative full-stack portfolio of İrem Öztürk, focused on backend systems, AI and web applications.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon-32.png">
    <link rel="icon" type="image/png" sizes="64x64" href="assets/images/favicon-64.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/character-guide.css">
</head>
<body class="is-loading">
    <div class="curtain-top" aria-hidden="true"></div>
    <div class="curtain-bottom" aria-hidden="true"></div>

    <div class="brand-wrap intro-brand" aria-hidden="true">
        <div class="brand-name">
            <span class="typed-name"></span><span class="brand-v">.</span><span class="cursor"></span>
        </div>
        <div class="brand-sub" data-i18n="heroRole">Computer Engineer</div>
    </div>

    <div class="cursor-dot"></div>
    <div class="noise"></div>

    <div class="scroll-orbit" aria-hidden="true">
        <span class="scroll-orbit-track">
            <span class="scroll-orbit-fill"></span>
            <span class="scroll-orbit-thumb"></span>
        </span>
    </div>

    <!-- Ana navigasyon: logo, menü bağlantıları, dil ve tema kontrolleri burada yer alır. -->
    <header class="site-header aus-nav" data-nav-state="top">
        <a class="nav-brand wave-brand" href="#hero" aria-label="Go to hero section">
            <img class="nav-logo" src="assets/images/brand-logo.png" alt="" aria-hidden="true">
            <span class="wave-word wave-word-primary" aria-hidden="true">
                <span>İ</span><span>r</span><span>e</span><span>m</span>
            </span>
            <span class="wave-separator" aria-hidden="true">•</span>
            <span class="wave-word wave-word-secondary" aria-hidden="true">
                <span>Ö</span><span>z</span><span>t</span><span>ü</span><span>r</span><span>k</span>
            </span>
            <span class="sr-only">İrem Öztürk</span>
        </a>

        <nav class="main-nav aus-menu" aria-label="Main navigation">
            <a href="#about" data-i18n="navAbout">About</a>
            <a href="#skills" data-i18n="navSkills">Skills</a>
            <a href="#experience" data-i18n="navExperience">Experience</a>
            <a href="#projects" data-i18n="navProjects">Projects</a>
            <a href="#contact" data-i18n="navContact">Contact</a>
        </nav>

        <div class="nav-actions">
            <button class="theme-toggle square-control" type="button" aria-label="Toggle dark and light mode" data-theme-toggle>
                <span class="theme-glyph theme-moon" aria-hidden="true">☾</span>
                <span class="theme-glyph theme-sun" aria-hidden="true">☀</span>
            </button>

            <span class="nav-actions-divider" aria-hidden="true"></span>

            <div class="language-control" data-lang-dropdown>
                <button class="lang-toggle square-control" type="button" aria-label="Change language" aria-expanded="false" data-lang-toggle>
                    <span class="lang-current">EN</span>
                </button>
                <div class="lang-menu" role="menu" aria-label="Language options">
                    <button type="button" class="lang-option" role="menuitem" data-lang-option="en">
                        <span class="lang-code">US</span>
                        <span class="lang-name" data-i18n="langEnglish">English</span>
                        <span class="lang-dot" aria-hidden="true"></span>
                    </button>
                    <button type="button" class="lang-option" role="menuitem" data-lang-option="tr">
                        <span class="lang-code">TR</span>
                        <span class="lang-name" data-i18n="langTurkish">Türkçe</span>
                        <span class="lang-dot" aria-hidden="true"></span>
                    </button>
                </div>
            </div>

            <span class="nav-actions-divider" aria-hidden="true"></span>

            <a class="nav-cv-link cv-control" href="assets/cv/irem-ozturk-cv-en.pdf" target="_blank" rel="noopener" data-cv-link data-i18n="navCv" aria-label="View English CV">View CV</a>
        </div>

        <button class="menu-button" type="button" aria-label="Open menu" data-i18n="menuButton" data-i18n-aria-label="menuButtonAria">Menu</button>
    </header>

    <!-- Ana içerik: portfolyonun ziyaretçiye gösterilen tüm bölümleri bu kapsayıcıdadır. -->
    <main>
        <!-- Hero bölümü: ilk izlenim, tanıtım metni, video ve hızlı aksiyonlar. -->
        <section class="hero panel" id="hero">

            <div class="hero-live-visual" aria-hidden="true">
                <video
                    class="hero-live-video hero-live-video-fill"
                    data-theme-video
                    data-dark-src="assets/videos/hero-showreel.mp4"
                    data-light-src="assets/videos/light-hero-video.mp4"
                    autoplay
                    muted
                    playsinline
                    preload="auto"
                    loop
                >
                    <source src="assets/videos/hero-showreel.mp4" type="video/mp4">
                </video>
                <video
                    class="hero-live-video hero-live-video-main"
                    id="hero-live-video"
                    data-theme-video
                    data-dark-src="assets/videos/hero-showreel.mp4"
                    data-light-src="assets/videos/light-hero-video.mp4"
                    poster="assets/images/irem-inaction-poster.png"
                    autoplay
                    muted
                    playsinline
                    preload="auto"
                    loop
                >
                    <source src="assets/videos/hero-showreel.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
            <div class="hero-bg hero-bg-one"></div>
            <div class="hero-bg hero-bg-two"></div>
            <div class="hero-content">
                <p class="eyebrow" data-i18n="heroEyebrow">Computer Engineering × Software Engineering</p>
                <h1 class="hero-title" aria-label="İrem Öztürk">
                    <span class="name-line name-left js-split-name" data-speed="0.7" data-name="İrem">İrem</span>
                    <span class="hero-reel" aria-label="Animated technology showcase">
                        <span class="reel-video" aria-hidden="true">
                            <span class="reel-strip reel-strip-one">
                                <span class="reel-card"><strong>BACK</strong><em>PHP</em></span>
                                <span class="reel-card"><strong>DATA</strong><em>MySQL</em></span>
                                <span class="reel-card"><strong>AI</strong><em>ML</em></span>
                                <span class="reel-card"><strong>UI</strong><em>JS</em></span>
                            </span>
                            <span class="reel-strip reel-strip-two">
                                <span class="reel-card"><strong>API</strong><em>REST</em></span>
                                <span class="reel-card"><strong>WEB</strong><em>CSS</em></span>
                                <span class="reel-card"><strong>DB</strong><em>SQL</em></span>
                                <span class="reel-card"><strong>FULL</strong><em>STACK</em></span>
                            </span>
                            <span class="reel-grid"></span>
                            <span class="binary-rain" aria-hidden="true">
                                <span class="binary-column">0101010101010101010101010101010101010101010101010101010101010101</span>
                                <span class="binary-column">1010011010011010011010011010011010011010011010011010011010011010</span>
                                <span class="binary-column">0110100101101001011010010110100101101001011010010110100101101001</span>
                                <span class="binary-column">1100101111001011110010111100101111001011110010111100101111001011</span>
                                <span class="binary-column">0011010100110101001101010011010100110101001101010011010100110101</span>
                                <span class="binary-column">1001011010010110100101101001011010010110100101101001011010010110</span>
                                <span class="binary-column">0101101001011010010110100101101001011010010110100101101001011010</span>
                                <span class="binary-column">1110001110001110001110001110001110001110001110001110001110001110</span>
                            </span>
                            <span class="reel-orb reel-orb-one"></span>
                            <span class="reel-orb reel-orb-two"></span>
                            <span class="reel-scan"></span>
                            <span class="reel-corner reel-corner-tl" aria-hidden="true"></span>
                            <span class="reel-corner reel-corner-tr" aria-hidden="true"></span>
                            <span class="reel-corner reel-corner-bl" aria-hidden="true"></span>
                            <span class="reel-corner reel-corner-br" aria-hidden="true"></span>
                        </span>
                        <span class="reel-content">
                            <span class="tech-stage">
                                <span class="tech-word" data-tech-word data-i18n="heroTechWord">Backend</span>
                                <span class="tech-cursor" aria-hidden="true"></span>
                            </span>
                        </span>
                    </span>
                    <span class="name-line name-right js-split-name" data-speed="1.1" data-name="Öztürk">Öztürk</span>
                </h1>
                <p class="hero-role-final" data-i18n="heroFinalRole">Full Stack Developer</p>
            </div>
            <div class="scroll-hint" data-i18n="scroll">Scroll</div>
        </section>

        <!-- Hakkımda bölümü: profil anlatımı ve kişisel öne çıkan bilgiler. -->
        <section class="about panel section-padding" id="about">
            <div class="section-label" data-i18n="aboutLabel">01 / About</div>
            <div class="split-grid about-grid">
                <div class="about-copy-shell">
                    <h2 class="section-title" data-i18n="aboutTitle">I design systems that feel alive.</h2>
                    <div class="large-copy">
                        <p data-i18n="aboutP1">I am a double major student in Computer Engineering and Software Engineering at Haliç University. I focus on full-stack development, AI-powered applications and backend systems that connect data, APIs and interfaces into one smooth experience.</p>
                        <p data-i18n="aboutP2">My work combines Python, Go, Vue.js, MySQL, Docker and RESTful APIs. I enjoy turning complex technical ideas into products people can understand and use.</p>
                    </div>
                </div>
                <div class="about-visual reveal-card" aria-label="Scanned portrait of İrem Öztürk">
                    <div class="about-visual-shell">
                        <div class="scan-card-head">
                            <span data-i18n="aboutScanProfile">PROFILE / ABOUT</span>
                            <span data-i18n="aboutScanLive">LIVE SCAN</span>
                        </div>
                        <div class="scan-portrait-frame">
                            <img src="assets/images/about-irem.png" alt="Portrait of İrem Öztürk">
                            <span class="scan-grid-overlay" aria-hidden="true"></span>
                            <span class="scan-noise-overlay" aria-hidden="true"></span>
                            <span class="scan-tint-overlay" aria-hidden="true"></span>
                            <span class="scan-sweep" aria-hidden="true"></span>
                            <span class="scan-corner scan-corner-tl" aria-hidden="true"></span>
                            <span class="scan-corner scan-corner-tr" aria-hidden="true"></span>
                            <span class="scan-corner scan-corner-bl" aria-hidden="true"></span>
                            <span class="scan-corner scan-corner-br" aria-hidden="true"></span>
                            <span class="scan-status scan-status-top" data-i18n="scanStatusTop">AI / ML ACTIVE</span>
                            <span class="scan-status scan-status-bottom" data-i18n="scanStatusBottom">FULL STACK // READY</span>
                        </div>
                    </div>

                    <div class="about-intel-panel" aria-label="Profile details">
                        <div class="about-intel-grid">
                            <article class="intel-item">
                                <span class="intel-label" data-i18n="intelTrackLabel">TRACK:</span>
                                <strong class="intel-value" data-i18n="intelTrackValue">DOUBLE_MAJOR</strong>
                            </article>
                            <article class="intel-item">
                                <span class="intel-label" data-i18n="intelFocusLabel">FOCUS:</span>
                                <strong class="intel-value" data-i18n="intelFocusValue">FULLSTACK_DEV</strong>
                            </article>
                            <article class="intel-item">
                                <span class="intel-label" data-i18n="intelLang1Label">LANG_1:</span>
                                <strong class="intel-value" data-i18n="intelLang1Value">TR (Native)</strong>
                            </article>
                            <article class="intel-item">
                                <span class="intel-label" data-i18n="intelLang2Label">LANG_2:</span>
                                <strong class="intel-value" data-i18n="intelLang2Value">EN (Fluent)</strong>
                            </article>
                        </div>

                        <div class="system-alert-card">
                            <div class="system-alert-label"><span class="alert-dot" aria-hidden="true"></span> <span data-i18n="systemStatusLabel">SYSTEM_STATUS</span></div>
                            <div class="system-alert-title" data-i18n="systemStatusTitle">OPEN TO OPPORTUNITIES</div>
                            <div class="system-alert-meta">
                                <span data-i18n="systemCollabs">// COLLABS: ENABLED</span>
                                <span data-i18n="systemRemote">[REMOTE_READY]</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Yetenekler bölümü: teknik beceriler kategorilere ayrılarak gösterilir. -->
        <section class="skills panel section-padding" id="skills">
            <div class="section-label" data-i18n="skillsLabel">02 / Skills</div>
            <h2 class="section-title centered" data-i18n="skillsTitle">A flexible engineering toolkit.</h2>

            <div class="skills-grid-board">
                <?php $skillThemes = ['lime', 'orange', 'blue', 'teal', 'lime']; $themeIndex = 0; ?>
                <?php foreach ($skillsByCategory as $category => $items): ?>
                    <?php $theme = $skillThemes[$themeIndex % count($skillThemes)]; ?>
                    <article class="skills-card skills-card-<?= $theme ?> reveal-card">
                        <div class="skills-card-head">
                            <div class="skills-card-title-wrap">
                                <h3 data-skill-category="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></h3>
                                <span class="skills-card-accent"></span>
                            </div>
                            <button class="skills-reset" type="button" data-skills-reset>
                                <span aria-hidden="true">↺</span>
                                <span data-i18n="skillsReset">Reset</span>
                            </button>
                        </div>

                        <div class="skills-physics-scene is-card-scene" data-skills-scene data-skill-area="<?= htmlspecialchars($category) ?>" aria-label="<?= htmlspecialchars($category) ?> interactive skills area">
                            <?php foreach ($items as $skill): ?>
                                <?php $nameLength = function_exists('mb_strlen') ? mb_strlen($skill['name']) : strlen($skill['name']); ?>
                                <?php $pillSize = $nameLength <= 3 ? 'circle' : ($nameLength <= 10 ? 'small' : 'medium'); ?>
                                <button class="physics-pill physics-pill-skill theme-<?= $theme ?> size-<?= $pillSize ?>" type="button" data-pill-size="<?= $pillSize ?>" title="<?= htmlspecialchars($skill['description']) ?>">
                                    <?= htmlspecialchars($skill['name']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php $themeIndex++; endforeach; ?>
            </div>
        </section>

        <!-- Deneyim bölümü: zaman çizelgesi ve kariyer/öğrenim adımları. -->
        <section class="experience panel" id="experience">

             
            <div class="exp-orb exp-orb-a" aria-hidden="true"></div>
            <div class="exp-orb exp-orb-b" aria-hidden="true"></div>
            <div class="exp-orb exp-orb-c" aria-hidden="true"></div>

             
            <div class="exp-wave-top" aria-hidden="true">
                <svg viewBox="0 0 1440 90" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0,0 C280,72 580,8 840,52 C1060,88 1260,18 1440,54 L1440,0 Z" fill="#11111a"/>
                    <path d="M0,0 C320,44 640,4 960,38 C1160,62 1340,22 1440,36 L1440,0 Z" fill="#0b0b0f" opacity="0.9"/>
                    <path d="M0,46 C240,12 520,68 800,32 C1040,4 1260,56 1440,28" fill="none" stroke="rgba(199,255,46,0.13)" stroke-width="1.5"/>
                </svg>
            </div>

             
            <div class="exp-inner" id="expInner">

                 
                <div class="exp-header" id="expHeader">
                    <div class="section-label" data-i18n="experienceLabel">03 / Experience</div>
                    <h2 class="exp-main-title" data-i18n-html="experienceTitle">From academic<br>foundation to<br><em>production thinking.</em></h2>
                    <div class="exp-progress-bar" aria-hidden="true">
                        <div class="exp-progress-fill" id="expProgressFill"></div>
                    </div>
                </div>

                 
                <div class="exp-timeline" id="expTimeline">

                    <div class="exp-tl-line" aria-hidden="true">
                        <div class="exp-tl-line-fill" id="expTlLineFill"></div>
                    </div>

                     
                    <div class="exp-tl-item exp-tl-edu" data-tl-item="0">
                        <div class="exp-tl-dot" aria-hidden="true"></div>
                        <div class="exp-tl-meta">
                            <span class="exp-tl-kind" data-i18n="expKindEducation">Education</span>
                            <span class="exp-tl-period">2016 — 2020</span>
                        </div>
                        <article class="exp-tl-card">
                            <div class="exp-tl-card-shine" aria-hidden="true"></div>
                            <h3 data-i18n-html="exp1Title">Rotary 100. Yıl<br>Anatolian High School</h3>
                            <p class="exp-tl-place" data-i18n="exp1Place">High School Foundation</p>
                            <p class="exp-tl-copy" data-i18n="exp1Copy">Built the discipline, curiosity and academic base that led me into engineering and software development.</p>
                            <div class="exp-tl-tags">
                                <span data-i18n="exp1Tag2">Problem Solving</span>
                                <span data-i18n="exp1Tag3">Foundation</span>
                            </div>
                        </article>
                    </div>

                     
                    <div class="exp-tl-item exp-tl-edu" data-tl-item="1">
                        <div class="exp-tl-dot" aria-hidden="true"></div>
                        <div class="exp-tl-meta">
                            <span class="exp-tl-kind" data-i18n="expKindEducation">Education</span>
                            <span class="exp-tl-period">2022 — 2027</span>
                        </div>
                        <article class="exp-tl-card">
                            <div class="exp-tl-card-shine" aria-hidden="true"></div>
                            <h3 data-i18n="exp2Title">Computer Engineering</h3>
                            <p class="exp-tl-place" data-i18n="halicUniversity">Haliç University</p>
                            <p class="exp-tl-copy" data-i18n="exp2Copy">Focused on software fundamentals, algorithms, systems, databases and engineering thinking.</p>
                            <div class="exp-tl-tags">
                                <span data-i18n="exp2Tag1">Algorithms</span>
                                <span data-i18n="exp2Tag2">Systems</span>
                                <span data-i18n="exp2Tag3">Engineering</span>
                            </div>
                        </article>
                    </div>

                     
                    <div class="exp-tl-item exp-tl-edu" data-tl-item="2">
                        <div class="exp-tl-dot" aria-hidden="true"></div>
                        <div class="exp-tl-meta">
                            <span class="exp-tl-kind" data-i18n="expKindEducation">Education</span>
                            <span class="exp-tl-period">2024 — 2028</span>
                        </div>
                        <article class="exp-tl-card">
                            <div class="exp-tl-card-shine" aria-hidden="true"></div>
                            <h3 data-i18n-html="exp3Title">Software Engineering<small>Double Major</small></h3>
                            <p class="exp-tl-place" data-i18n="halicUniversity">Haliç University</p>
                            <p class="exp-tl-copy" data-i18n="exp3Copy">Expanded my focus into software architecture, product thinking and full-stack application design.</p>
                            <div class="exp-tl-tags">
                                <span data-i18n="exp3Tag1">Full-Stack</span>
                                <span data-i18n="exp3Tag2">Architecture</span>
                                <span data-i18n="exp3Tag3">Product Thinking</span>
                            </div>
                        </article>
                    </div>

                     
                    <div class="exp-tl-item exp-tl-work" data-tl-item="3">
                        <div class="exp-tl-dot" aria-hidden="true"></div>
                        <div class="exp-tl-meta">
                            <span class="exp-tl-kind" data-i18n="expKindExperience">Experience</span>
                            <span class="exp-tl-period" data-i18n="exp4Period">Jun — Aug 2025</span>
                        </div>
                        <article class="exp-tl-card">
                            <div class="exp-tl-card-shine" aria-hidden="true"></div>
                            <h3 data-i18n="exp4Title">IT Department Intern</h3>
                            <p class="exp-tl-place" data-i18n="exp4Place">Ensmart Technology · Istanbul</p>
                            <ul class="exp-tl-bullets">
                                <li data-i18n="exp4Bullet1">Maintained internal software systems and developed new features with a full-stack approach.</li>
                                <li data-i18n="exp4Bullet2">Worked on RESTful APIs and backend services with Python and Go.</li>
                                <li data-i18n="exp4Bullet3">Used Docker, MySQL and Postman in day-to-day development workflows.</li>
                            </ul>
                            <div class="exp-tl-tags">
                                <span>Python</span>
                                <span>Go</span>
                                <span>Docker</span>
                                <span>MySQL</span>
                            </div>
                        </article>
                    </div>

                </div> 
            </div> 

             
            <div class="exp-wave-bot" aria-hidden="true">
                <svg viewBox="0 0 1440 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                     
                    <path d="M0,120 C300,55 600,100 900,62 C1120,32 1300,80 1440,55 L1440,120 Z" fill="#0e0b14"/>
                    <path d="M0,120 C260,70 540,110 820,72 C1060,42 1280,88 1440,66 L1440,120 Z" fill="#0e0b14" opacity="0.75"/>
                     
                    <path d="M0,88 C220,48 480,96 740,58 C980,24 1220,74 1440,46" fill="none" stroke="rgba(139,100,255,0.18)" stroke-width="1.8"/>
                     
                    <path d="M0,106 C320,72 620,108 920,78 C1140,56 1320,90 1440,72" fill="none" stroke="rgba(199,255,46,0.09)" stroke-width="1"/>
                </svg>
            </div>

        </section>


        <!-- Projeler bölümü: veritabanından gelen öne çıkan işler kartlara dönüştürülür. -->
        <section class="projects panel" id="projects">
             
            <div class="projects-top-fade" aria-hidden="true"></div>
            <div class="projects-header">
                <div class="section-label" data-i18n="projectsLabel">04 — Projects</div>
                <div class="projects-copy" aria-label="Projects section tagline">
                    <h2 class="projects-tagline" data-i18n-html="projectsTagline"><span class="tagline-main">Projects that turn ideas into</span><span class="tagline-accent">real, usable experiences.</span></h2>
                </div>
            </div>

            <div class="project-carousel" aria-label="Featured projects carousel" data-i18n-aria-label="projectsCarouselAria">
                <div class="project-stage">
                    <?php foreach ($projects as $index => $project): ?>
                        <article class="project-card <?= $index === 0 ? 'is-active' : '' ?>" data-project-card data-index="<?= $index ?>">
                            <img class="project-card-image" src="<?= htmlspecialchars($project['image']) ?>" alt="<?= htmlspecialchars($project['title']) ?> preview" loading="lazy">
                            <div class="project-card-overlay" aria-hidden="true"></div>
                            <div class="project-deco" aria-hidden="true"></div>
                            <div class="project-actions">
                                <a class="project-github-btn" href="<?= htmlspecialchars($project['github_url'] ?: '#') ?>" target="_blank" rel="noopener" aria-label="Open <?= htmlspecialchars($project['title']) ?> on GitHub">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" focusable="false">
                                        <path fill="currentColor" d="M12 .5a12 12 0 0 0-3.79 23.39c.6.11.82-.26.82-.58v-2.05c-3.34.73-4.04-1.42-4.04-1.42-.55-1.39-1.34-1.76-1.34-1.76-1.09-.75.08-.74.08-.74 1.21.09 1.85 1.24 1.85 1.24 1.07 1.84 2.82 1.31 3.5 1 .11-.78.42-1.31.76-1.61-2.67-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.12-.3-.54-1.52.12-3.18 0 0 1.01-.32 3.3 1.23a11.5 11.5 0 0 1 6.01 0c2.29-1.55 3.3-1.23 3.3-1.23.66 1.66.24 2.88.12 3.18.77.84 1.24 1.91 1.24 3.22 0 4.61-2.81 5.63-5.49 5.93.43.37.81 1.1.81 2.22v3.29c0 .32.22.7.83.58A12 12 0 0 0 12 .5Z"/>
                                    </svg>
                                </a>
                            </div>
                            <div class="project-card-content">
                                <span class="project-code" data-project-code><?= htmlspecialchars($project['code_name']) ?></span>
                                <div class="project-title-clip"><h3 data-project-title><?= htmlspecialchars($project['title']) ?></h3></div>
                                <p data-project-description><?= htmlspecialchars($project['short_description'] ?: $project['description']) ?></p>
                                <div class="project-tech">
                                    <?php foreach (array_filter(array_map('trim', explode(',', $project['tech_stack']))) as $tech): ?>
                                        <span><?= htmlspecialchars($tech) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="project-number" aria-hidden="true"><?= str_pad((string)($index+1), 2, '0', STR_PAD_LEFT) ?></div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="project-controls" aria-label="Project carousel controls" data-i18n-aria-label="projectControlsAria">
                    <button class="project-control" type="button" data-project-prev aria-label="Previous project" data-i18n-aria-label="prevProjectAria">←</button>
                    <button class="project-control" type="button" data-project-next aria-label="Next project" data-i18n-aria-label="nextProjectAria">→</button>
                </div>
            </div>
             
            <div class="projects-contact-wave" aria-hidden="true">
                <svg viewBox="0 0 1440 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path class="pcw-fill-main" d="M0,120 C300,55 600,100 900,62 C1120,32 1300,80 1440,55 L1440,120 Z" />
                    <path class="pcw-fill-soft" d="M0,120 C260,70 540,110 820,72 C1060,42 1280,88 1440,66 L1440,120 Z" />
                    <path class="pcw-line-glow" d="M0,88 C220,48 480,96 740,58 C980,24 1220,74 1440,46" />
                    <path class="pcw-line-soft" d="M0,106 C320,72 620,108 920,78 C1140,56 1320,90 1440,72" />
                </svg>
            </div>
        </section>

        <!-- İletişim bölümü: ziyaretçi mesajları AJAX ile API uç noktasına gönderilir. -->
        <section class="contact panel section-padding" id="contact">
            <span class="contact-orb contact-orb-one" aria-hidden="true"></span>
            <span class="contact-orb contact-orb-two" aria-hidden="true"></span>

            <div class="contact-head">
                <p class="section-label" data-i18n="contactLabel">05 / Contact</p>
                <h2 class="section-title contact-title">
                    <span data-i18n="contactTitleA">Let's</span>
                    <span class="lime-word" data-i18n="contactTitleBuild">Build</span><br>
                    <span data-i18n="contactTitleB">Something.</span>
                </h2>
            </div>

            <div class="contact-shell">
                <div class="contact-copy">
                    <p data-i18n="contactCopy">Have a project idea, collaboration request, or just want to say hello? Leave a signal. I will answer within 24 hours.</p>

                    <aside class="contact-info-card" aria-label="Contact information">
                        <span class="cic-accent cic-accent-tl" aria-hidden="true"></span>
                        <span class="cic-accent cic-accent-tr" aria-hidden="true"></span>
                        <span class="cic-accent cic-accent-bl" aria-hidden="true"></span>
                        <span class="cic-accent cic-accent-br" aria-hidden="true"></span>

                        <div class="cic-row">
                            <span class="cic-key" data-i18n="contactStatus">Status</span>
                            <span class="cic-online"><span class="cic-dot"></span><span data-i18n="contactAvailable">Available</span></span>
                        </div>

                        <div class="cic-divider"></div>

                        <a class="cic-item" href="mailto:iremozturk36@gmail.com">
                            <span class="cic-icon">✉</span>
                            <span>
                                <span class="cic-sublabel" data-i18n="contactEmailLabel">Email</span>
                                <span class="cic-val">iremozturk36@gmail.com</span>
                            </span>
                        </a>

                        <div class="cic-item">
                            <span class="cic-icon">⌖</span>
                            <span>
                                <span class="cic-sublabel" data-i18n="contactLocationLabel">Location</span>
                                <span class="cic-val">Bayrampaşa , İstanbul</span>
                            </span>
                        </div>

                        <a class="cic-item" href="tel:+905452993234">
                            <span class="cic-icon">☎</span>
                            <span>
                                <span class="cic-sublabel" data-i18n="contactPhoneLabel">Phone</span>
                                <span class="cic-val">0545 299 32 34</span>
                            </span>
                        </a>

                        <div class="cic-item">
                            <span class="cic-icon">↯</span>
                            <span>
                                <span class="cic-sublabel" data-i18n="contactResponseLabel">Response time</span>
                                <span class="cic-val">&lt; 24h</span>
                            </span>
                        </div>

                        <div class="cic-divider"></div>

                        <div class="cic-row cic-terminal-line">
                            <span class="cic-key" data-i18n="contactFreelance">Freelance</span>
                            <span class="cic-work" data-i18n="contactOpenWork">OPEN TO WORK</span>
                        </div>
                    </aside>
</div>

                <div class="contact-form-shell">
                    <div class="cf-mail-animation" aria-hidden="true">
                        <div class="mail-envelope">
                            <span class="mail-letter"></span>
                            <div class="mail-flap"><div class="mail-flap-top"></div></div>
                            <span class="seal-splat"></span>
                            <span class="seal-rings"><span class="seal-ring"></span></span>
                            <span class="seal-sparks"></span>
                            <span class="mail-seal"></span>
                        </div>
                        <p class="mail-caption" id="mail-caption-text" data-i18n="mailCaption">Your message is ready to fly ✦</p>
                    </div>

                    <span class="cf-corner cf-tl"></span>
                    <span class="cf-corner cf-tr"></span>
                    <span class="cf-corner cf-bl"></span>
                    <span class="cf-corner cf-br"></span>

                    <form class="contact-form" id="contactForm" novalidate>
                        <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="contact-honeypot">
                        <div class="cf-row">
                            <div class="cf-field">
                                <label class="cf-label" for="cf-name"><span data-i18n="formName">Name</span> <span class="cf-req">*</span></label>
                                <input class="cf-input" id="cf-name" name="name" type="text" placeholder="Your name" data-i18n-placeholder="formNamePlaceholder" autocomplete="name" required minlength="2">
                                <span class="cf-err" id="err-name" data-i18n="errName">Name must be at least 2 characters.</span>
                            </div>

                            <div class="cf-field">
                                <label class="cf-label" for="cf-email"><span data-i18n="formEmail">Email</span> <span class="cf-req">*</span></label>
                                <input class="cf-input" id="cf-email" name="email" type="email" placeholder="email@example.com" data-i18n-placeholder="formEmailPlaceholder" autocomplete="email" required>
                                <span class="cf-err" id="err-email" data-i18n="errEmail">Please enter a valid email.</span>
                            </div>
                        </div>

                        <div class="cf-field">
                            <label class="cf-label" for="cf-subject"><span data-i18n="formSubject">Subject</span> <span class="cf-req">*</span></label>
                            <input class="cf-input" id="cf-subject" name="subject" type="text" placeholder="Project / Collaboration / Hello" data-i18n-placeholder="formSubjectPlaceholder" required minlength="3">
                            <span class="cf-err" id="err-subject" data-i18n="errSubject">Subject must be at least 3 characters.</span>
                        </div>

                        <div class="cf-field">
                            <label class="cf-label" for="cf-msg"><span data-i18n="formMessage">Message</span> <span class="cf-req">*</span></label>
                            <textarea class="cf-input" id="cf-msg" name="message" placeholder="Tell me about your idea..." data-i18n-placeholder="formMessagePlaceholder" maxlength="1000" required minlength="10"></textarea>
                            <div class="cf-char-wrap">
                                <span class="cf-err" id="err-msg" data-i18n="errMessage">Message must be at least 10 characters.</span>
                                <span class="cf-char-count"><span id="cf-count">0</span> / 1000</span>
                            </div>
                        </div>

                        <button type="submit" class="magnetic-button cf-submit" id="cf-btn">
                            <span id="cf-btn-text" data-i18n="sendMessage">Send Message</span>
                            <span class="cf-arrow" id="cf-arrow">→</span>
                        </button>

                        <div class="form-status cf-status-success" id="cf-success" role="status"></div>
                        <div class="form-status cf-status-error" id="cf-server-err" role="alert"></div>
                    </form>
                </div>
            </div>
        </section>


    </main>

    <!-- Footer: marka kapanışı, sosyal bağlantılar ve hızlı iletişim çağrısı. -->
    <footer class="site-footer" aria-labelledby="footer-name">
        <div class="footer-orb footer-orb-left" aria-hidden="true"></div>
        <div class="footer-orb footer-orb-right" aria-hidden="true"></div>

        <div class="footer-top">
            <div class="footer-brand-block">
                <a class="footer-logo-link" href="#hero" aria-label="Go to hero section" data-i18n-aria-label="goHeroAria">
                    <img class="footer-logo-image" src="assets/images/brand-logo.png" alt="İrem Öztürk logo" data-i18n-alt="footerLogoAlt">
                </a>

                <div class="footer-brand-copy">
                    <h2 id="footer-name">İrem Öztürk</h2>
                    <p class="footer-role-static" data-i18n="footerRole">Full Stack Developer</p>
                    <p class="footer-role-dynamic" aria-live="polite">
                        <span class="footer-role-typed" data-footer-typed></span><span class="footer-role-caret" aria-hidden="true"></span>
                    </p>
                </div>
            </div>

            <div class="footer-actions-row">
                <div class="footer-links-cluster" aria-label="Social and contact links">
                    <a class="footer-social-link social-github" href="https://github.com/iiremozturkk" target="_blank" rel="noopener" aria-label="GitHub profile">
                        <span class="footer-social-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 .5C5.65.5.5 5.66.5 12.02c0 5.09 3.29 9.4 7.86 10.93.58.11.79-.25.79-.56 0-.28-.01-1.2-.02-2.18-3.2.69-3.87-1.36-3.87-1.36-.52-1.33-1.27-1.68-1.27-1.68-1.04-.71.08-.7.08-.7 1.15.08 1.76 1.18 1.76 1.18 1.02 1.76 2.68 1.25 3.34.96.1-.74.4-1.25.73-1.54-2.55-.29-5.23-1.28-5.23-5.68 0-1.25.45-2.28 1.18-3.08-.12-.29-.51-1.46.11-3.05 0 0 .97-.31 3.16 1.18a10.9 10.9 0 0 1 5.76 0c2.19-1.49 3.16-1.18 3.16-1.18.62 1.59.23 2.76.11 3.05.74.8 1.18 1.83 1.18 3.08 0 4.41-2.69 5.39-5.26 5.67.41.36.78 1.06.78 2.15 0 1.55-.01 2.8-.01 3.18 0 .31.21.67.8.56A11.54 11.54 0 0 0 23.5 12C23.5 5.66 18.35.5 12 .5Z"/></svg>
                        </span>
                        <span class="footer-social-label">GitHub</span>
                    </a>

                    <a class="footer-social-link social-linkedin" href="https://linkedin.com/in/irem-öztürk-7a48b62a1" target="_blank" rel="noopener" aria-label="LinkedIn profile">
                        <span class="footer-social-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M4.98 3.5A2.49 2.49 0 1 0 5 8.48 2.49 2.49 0 0 0 4.98 3.5ZM3 9h4v12H3Zm7 0h3.83v1.64h.05c.53-1.01 1.84-2.08 3.79-2.08 4.05 0 4.8 2.66 4.8 6.12V21h-4v-5.58c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.13 1.45-2.13 2.95V21h-4Z"/></svg>
                        </span>
                        <span class="footer-social-label">LinkedIn</span>
                    </a>

                    <a class="footer-social-link social-instagram" href="#contact" aria-label="Instagram contact shortcut">
                        <span class="footer-social-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="5" ry="5"/><path d="M16.5 7.5h.01"/><circle cx="12" cy="12" r="4"/></svg>
                        </span>
                        <span class="footer-social-label" data-i18n="footerInstagram">Instagram</span>
                    </a>

                    <a class="footer-social-link social-email" href="mailto:iremozturk36@gmail.com" aria-label="Send an email">
                        <span class="footer-social-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2" ry="2"/><path d="m3 7 9 6 9-6"/></svg>
                        </span>
                        <span class="footer-social-label" data-i18n="footerEmail">Email</span>
                    </a>

                    <a class="footer-social-link social-phone" href="#contact" aria-label="Open contact section for phone details">
                        <span class="footer-social-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.35 1.77.68 2.6a2 2 0 0 1-.45 2.11L8.08 9.91a16 16 0 0 0 6 6l1.48-1.26a2 2 0 0 1 2.11-.45c.83.33 1.7.56 2.6.68A2 2 0 0 1 22 16.92z"/></svg>
                        </span>
                        <span class="footer-social-label" data-i18n="footerPhone">Phone</span>
                    </a>
                </div>

            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-meta-item footer-meta-copyright">
                <span class="footer-meta-dot" aria-hidden="true"></span>
                <span>© 2026 İrem Öztürk</span>
            </div>

            <div class="footer-meta-item footer-meta-built">
                <span class="footer-meta-icon" aria-hidden="true">&lt;/&gt;</span>
                <span data-i18n="footerBuiltWith">Built with HTML, CSS, JavaScript, PHP &amp; MySQL.</span>
            </div>

            <div class="footer-meta-item footer-meta-tagline">
                <span class="footer-meta-spark" aria-hidden="true">✦</span>
                <span data-i18n="footerTagline">Designed to leave a mark.</span>
            </div>
        </div>

         
        <a href="admin/login.php" class="secret-door" tabindex="-1" aria-hidden="true">
            <div class="secret-door-frame">
                <div class="secret-door-panel">
                    <div class="secret-door-light"></div>
                    <div class="secret-door-knob"></div>
                </div>
                <div class="secret-door-glow"></div>
                <span class="secret-door-label" data-i18n="adminLabel">ADMIN</span>
            </div>
        </a>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.42/dist/lenis.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/matter-js@0.20.0/build/matter.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/ajax.js"></script>
    <script src="assets/js/character-guide.js"></script>
</body>
</html>

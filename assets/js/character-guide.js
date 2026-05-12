// Bu dosya: Sayfa üzerinde dolaşan karakter rehberinin hareket, konuşma ve etkileşim davranışlarını yönetir.









// Karakter rehberi değişkenlerini global alanı kirletmeden izole bir kapsamda çalıştırır.
(function () {
    'use strict';

    // Karakterin hız, mesafe ve zamanlama ayarları tek konfigürasyonda tutulur.
    const CFG = {
        charW: 76,
        charH: 98,
        floor: 18,
        margin: 14,
        walkSpeed: 2.2,
        interactSpeed: 2.55,
        accel: 0.11,
        friction: 0.9,
        dragFriction: 0.78,
        cursorRadius: 160,
        engageRadius: 120,
        clickBubbleDelay: 350,
        bubbleDur: 2800,
        bubbleWait: 5200,
        roamMin: 2200,
        roamMax: 4600,
        portalDelay: 320,
        birthWalk: 2600,
        obstaclePad: 12,
        obstacleTextPad: 18,
        targetSnap: 18
    };

    // Karakter baloncuklarında kullanılan Türkçe/İngilizce metin sözlüğü.
    const GUIDE_I18N = {
        tr: {
            messages: {
                hero: ['Merhaba!', 'İstersen bana tıkla, oraya yürüyeyim.'],
                about: ['Burası hikâyem.', 'Kendimi burada anlatıyorum.', 'Kenardan dolaşıyorum, yazılara basmıyorum.'],
                skills: ['Yetenek alanım burası.', 'Teknolojilerin etrafında geziyorum.', 'Bir kart seç, ben de eşlik edeyim.'],
                experience: ['Deneyim yolculuğu burada.', 'Zaman çizgisini seviyorum.', 'Adım adım ilerleyelim.'],
                projects: ['Projelerim burada.', 'Kartların yanında durmak daha güzel.', 'İstersen beni sürükleyebilirsin.'],
                contact: ['Bana mesaj bırakabilirsin.', 'Formu göndermeden önce kontrol et.', 'Burada seni bekliyorum.']
            },
            sectionLines: {
                hero: 'Hero bölümünde seni karşılıyorum',
                about: 'Hikâyeyi birlikte okuyalım',
                skills: 'Teknolojilere yakından bakıyorum',
                experience: 'Deneyim yolunda adım adım ilerliyorum',
                projects: 'Projeleri inceliyorum',
                contact: 'Mesaj kutusunun yanında bekliyorum'
            },
            drag: ['Tamam, beni taşıyabilirsin', 'Nereye gidiyoruz?', 'Buraya kadar geldik!'],
            hover: ['Ben buradayım', 'İstersen beni tutup taşı.', 'Bir yere tıkla, oraya yürüyeyim.'],
            click: ['Geliyorum', 'Hemen oraya geçiyorum.', 'Orası güzel bir yer.'],
            near: ['İstersen birlikte gezelim.', 'Seni takip ediyorum.'],
            ui: {
                firstHello: 'Merhaba, ben geldim!',
                returnHello: 'Geri döndüm!',
                dropDone: 'Tamam, buradan devam ediyorum',
                contactHover: 'Mesaj göndermeden önce alanları kontrol et',
                showButton: 'Karakteri göster',
                hideButton: 'Karakteri gizle',
                toggleButton: 'Karakteri gizle veya göster',
                hitTitle: "İrem'i tutup sürükleyebilirsin"
            }
        },
        en: {
            messages: {
                hero: ['Hi!', 'Click anywhere and I will walk there.'],
                about: ['This is my story.', 'I introduce myself here.', 'I walk around the edges without stepping on the text.'],
                skills: ['This is my skills area.', 'I am walking around the technologies.', 'Choose a card and I will join you.'],
                experience: ['The experience journey is here.', 'I like the timeline.', "Let\'s move forward step by step."],
                projects: ['My projects are here.', 'It is nicer to stand beside the cards.', 'You can drag me if you want.'],
                contact: ['You can leave me a message.', 'Check the form before sending it.', 'I am waiting for you here.']
            },
            sectionLines: {
                hero: 'I am welcoming you in the Hero section',
                about: "Let\'s read the story together",
                skills: 'I am looking closely at the technologies',
                experience: 'I am moving step by step through the experience path',
                projects: 'I am reviewing the projects',
                contact: 'I am waiting beside the message box'
            },
            drag: ['Okay, you can carry me', 'Where are we going?', 'We made it this far!'],
            hover: ['I am here', 'You can hold and move me.', 'Click somewhere and I will walk there.'],
            click: ['I am coming', 'I am heading there now.', 'That is a nice spot.'],
            near: ['We can explore together.', 'I am following you.'],
            ui: {
                firstHello: 'Hi, I am here!',
                returnHello: 'I am back!',
                dropDone: 'Okay, I will continue from here',
                contactHover: 'Check the fields before sending your message',
                showButton: 'Show character',
                hideButton: 'Hide character',
                toggleButton: 'Hide or show character',
                hitTitle: 'You can hold and drag İrem'
            }
        }
    };

    const SECTION_REACTIONS = {
        hero: { cls: 'ig-react-wave' },
        about: { cls: 'ig-react-read' },
        skills: { cls: 'ig-react-curious' },
        experience: { cls: 'ig-react-step' },
        projects: { cls: 'ig-react-inspect' },
        contact: { cls: 'ig-react-mail' }
    };

    const SECTION_IDS = ['hero', 'about', 'skills', 'experience', 'projects', 'contact'];
    const LAYOUT_SELECTORS = [
        '.hero-title .name-line', '.hero-role-final', '.section-title', '.large-copy p',
        '.skill-category', '.project-card', '.experience-item', '.timeline-item',
        '.about-visual', '.about-copy-shell', '.contact-card', '.contact-form-shell',
        '.contact-form', '.metric', '.detail-card', '.info-card', '.project-copy',
        '.project-meta', '.project-visual', '.large-copy', '.hero-reel'
    ];

    const EDGE_TARGET_SELECTORS = [
        '.skill-category', '.project-card', '.experience-item', '.timeline-item',
        '.about-visual', '.about-copy-shell', '.contact-card', '.contact-form-shell',
        '.metric', '.detail-card', '.info-card', '.project-visual', '.project-copy'
    ];

    let x = window.innerWidth * 0.72;
    let y = groundY();
    let vx = 0;
    let vy = 0;
    let targetX = x;
    let targetY = y;
    let cursorX = -9999;
    let cursorY = -9999;
    let pointerSpeed = 0;
    let lastPointer = { x: -9999, y: -9999, t: performance.now() };
    let facingRight = true;
    let born = false;
    let sleeping = false;
    let dragging = false;
    let dragOffsetX = 0;
    let dragOffsetY = 0;
    let dragLast = { x: 0, y: 0, t: 0 };
    let currentSection = 'hero';
    let bubbleTimer = null;
    let bubbleCooldown = false;
    let roamTimer = null;
    let idleTimer = null;
    let peekTimer = null;
    let interactUntil = 0;
    let walkPhase = 0;
    let bobAmp = 0;
    let leanAmp = 0;
    let squashAmp = 0;
    let lastNearBubble = 0;
    let hiddenByUser = false;
    let themeObserver = null;
    const MOBILE_QUERY = window.matchMedia ? window.matchMedia('(max-width: 760px)') : null;

    const $ = (id) => document.getElementById(id);
    const clamp = (v, min, max) => Math.max(min, Math.min(max, v));
    const lerp = (a, b, t) => a + (b - a) * t;
    const rnd = (min, max) => min + Math.random() * (max - min);
    const pick = (arr) => arr[Math.floor(Math.random() * arr.length)];

    // Karakterin konuşma dilini sayfanın aktif dil tercihinden belirler.
    function guideLang() {
        let raw = 'en';
        try {
            raw = document.documentElement.lang
                || window.localStorage?.getItem('portfolioLang')
                || document.cookie.split('; ').find((row) => row.startsWith('portfolio_lang='))?.split('=')[1]
                || 'en';
        } catch (_) {
            raw = document.documentElement.lang || 'en';
        }
        return String(raw).toLowerCase().startsWith('tr') ? 'tr' : 'en';
    }

    function guideDict() {
        return GUIDE_I18N[guideLang()] || GUIDE_I18N.en;
    }

    function guideText(key) {
        return guideDict().ui[key] || GUIDE_I18N.en.ui[key] || '';
    }

    function guideList(key) {
        return guideDict()[key] || GUIDE_I18N.en[key] || [];
    }

    function guideSectionMessages(section) {
        const dict = guideDict();
        return dict.messages[section] || dict.messages.hero;
    }

    function guideSectionLine(section) {
        const dict = guideDict();
        return dict.sectionLines[section] || dict.sectionLines.hero;
    }

    function guideLine(key) {
        return pick(guideList(key));
    }

    function refreshGuideUiText() {
        const btn = $('ig-btn');
        const hit = $('ig-hit');
        if (hit) hit.title = guideText('hitTitle');
        if (btn) {
            btn.title = hiddenByUser ? guideText('showButton') : guideText('hideButton');
            btn.setAttribute('aria-label', hiddenByUser ? guideText('showButton') : guideText('hideButton'));
        }
    }

    function groundY() {
        return Math.max(130, window.innerHeight - CFG.floor - CFG.charH);
    }

    function bounds() {
        return {
            minX: CFG.margin,
            maxX: Math.max(CFG.margin, window.innerWidth - CFG.charW - CFG.margin),
            minY: 74,
            maxY: groundY()
        };
    }

    function isMobileCalm() {
        return Boolean(MOBILE_QUERY && MOBILE_QUERY.matches);
    }

    function syncGuideContext() {
        const root = $('ig-root');
        if (!root) return;
        const isLight = document.body.classList.contains('light-mode');
        root.classList.toggle('ig-light', isLight);
        root.classList.toggle('ig-dark', !isLight);
        root.classList.toggle('ig-mobile-calm', isMobileCalm());
        root.dataset.section = currentSection;
        SECTION_IDS.forEach((id) => root.classList.toggle(`ig-section-${id}`, id === currentSection));
        refreshGuideUiText();
    }

    function saveHiddenPreference() {
        try {
            window.localStorage?.setItem('irem-character-hidden', hiddenByUser ? '1' : '0');
        } catch (_) {}
    }

    // Kullanıcı karakteri gizlediğinde durum hem arayüze hem localStorage’a yansıtılır.
    function setHiddenState(hidden, persist = true) {
        const root = $('ig-root');
        const btn = $('ig-btn');
        hiddenByUser = Boolean(hidden);
        sleeping = hiddenByUser;
        root?.classList.toggle('ig-hidden', hiddenByUser);
        root?.classList.toggle('ig-sleeping', hiddenByUser);
        if (btn) {
            btn.textContent = hiddenByUser ? '🪄' : '✕';
            btn.title = hiddenByUser ? guideText('showButton') : guideText('hideButton');
            btn.setAttribute('aria-label', hiddenByUser ? guideText('showButton') : guideText('hideButton'));
        }
        if (persist) saveHiddenPreference();
        if (hiddenByUser) {
            window.clearTimeout(roamTimer);
            window.clearTimeout(bubbleTimer);
            $('ig-bubble')?.classList.remove('ig-bv');
        }
    }

    function restoreHiddenPreference() {
        try {
            setHiddenState(window.localStorage?.getItem('irem-character-hidden') === '1', false);
        } catch (_) {
            setHiddenState(false, false);
        }
    }

    function runSectionReaction(section) {
        const root = $('ig-root');
        if (!root || hiddenByUser || sleeping || !born) return;
        const reaction = SECTION_REACTIONS[section];
        if (!reaction) return;
        Object.values(SECTION_REACTIONS).forEach((entry) => root.classList.remove(entry.cls));
        root.classList.add(reaction.cls);
        window.setTimeout(() => root.classList.remove(reaction.cls), 1450);
        if (!bubbleCooldown) bubble(guideSectionLine(section), true);
    }

    function setupThemeAndViewportWatchers() {
        syncGuideContext();
        if (themeObserver) themeObserver.disconnect();
        themeObserver = new MutationObserver(syncGuideContext);
        themeObserver.observe(document.body, { attributes: true, attributeFilter: ['class'] });
        window.addEventListener('portfolio:language-applied', () => {
            syncGuideContext();
            refreshGuideUiText();
        });
        MOBILE_QUERY?.addEventListener?.('change', () => {
            syncGuideContext();
            scheduleRoam(isMobileCalm() ? 5600 : 1200);
        });
    }

    // Karakterin DOM elemanları ve kontrol düğmesi sayfaya eklenir.
    function buildCharacter() {
        if ($('ig-root')) return;

        const root = document.createElement('div');
        root.id = 'ig-root';
        root.setAttribute('aria-hidden', 'true');
        root.innerHTML = `
            <div id="ig-bubble"></div>
            <div id="ig-swrap">
                <div id="ig-hit" title="">
                    <span class="ig-hand ig-hand-left"></span>
                    <img id="ig-spr" src="assets/images/irem-character.png" draggable="false" alt="">
                    <span class="ig-hand ig-hand-right"></span>
                </div>
                <div id="ig-shad"></div>
            </div>
            <button id="ig-btn" type="button" title="" aria-label="">✕</button>
            <span id="ig-spark" aria-hidden="true"></span>
        `;
        document.body.appendChild(root);
    }

    function buildPortal() {
        const reel = document.querySelector('.hero-reel');
        if (!reel || $('ig-portal')) return;

        const portal = document.createElement('span');
        portal.id = 'ig-portal';
        portal.setAttribute('aria-hidden', 'true');
        portal.innerHTML = `
            <span class="ig-portal-aura"></span>
            <span class="ig-portal-stage">
                <span class="ig-door-frame"></span>
                <span class="ig-door-panel ig-door-left"></span>
                <span class="ig-door-panel ig-door-right"></span>
                <span class="ig-door-floor"></span>
            </span>
        `;
        reel.appendChild(portal);
    }

    function setRootTransform() {
        const root = $('ig-root');
        if (!root) return;
        root.style.transform = `translate3d(${x.toFixed(1)}px, ${y.toFixed(1)}px, 0)`;
    }

    function placeAtPortal() {
        const reel = document.querySelector('.hero-reel');
        const b = bounds();
        if (!reel) {
            x = clamp(window.innerWidth * 0.72, b.minX, b.maxX);
            y = b.maxY;
            return;
        }

        const rect = reel.getBoundingClientRect();
        x = clamp(rect.left + rect.width * 0.5 - CFG.charW * 0.5, b.minX, b.maxX);
        y = clamp(rect.top + rect.height * 0.55 - CFG.charH * 0.72, b.minY, b.maxY);
        targetX = x;
        targetY = y;
        vx = 0;
        vy = 0;
    }

    // Karakter konuşma balonunu kontrollü süreyle gösterir.
    function bubble(text, force = false) {
        if (sleeping || hiddenByUser || !born) return;
        if (bubbleCooldown && !force) return;

        const b = $('ig-bubble');
        if (!b) return;

        window.clearTimeout(bubbleTimer);
        b.textContent = text;
        b.classList.add('ig-bv');
        bubbleCooldown = true;

        bubbleTimer = window.setTimeout(() => {
            b.classList.remove('ig-bv');
            window.setTimeout(() => {
                bubbleCooldown = false;
            }, Math.max(0, CFG.bubbleWait - CFG.bubbleDur));
        }, CFG.bubbleDur);
    }

    // Sayfanın hangi bölümünde bulunulduğunu görünürlük oranına göre hesaplar.
    function detectSection() {
        let best = 'hero';
        let bestVisible = 0;

        SECTION_IDS.forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;
            const r = el.getBoundingClientRect();
            const visible = Math.min(r.bottom, window.innerHeight) - Math.max(r.top, 0);
            if (visible > bestVisible) {
                bestVisible = visible;
                best = id;
            }
        });

        return best;
    }

    function isVisibleRect(rect) {
        return rect.width > 34 && rect.height > 20 && rect.bottom > 0 && rect.right > 0 && rect.top < window.innerHeight && rect.left < window.innerWidth;
    }

    function getPaddingForElement(el) {
        return el.matches('.hero-title .name-line, .hero-role-final, .section-title, .large-copy p, .large-copy, .project-copy, .project-meta')
            ? CFG.obstacleTextPad
            : CFG.obstaclePad;
    }

    function layoutElements(selectors) {
        return selectors
            .flatMap((selector) => Array.from(document.querySelectorAll(selector)))
            .filter((el) => {
                const style = window.getComputedStyle(el);
                if (style.display === 'none' || style.visibility === 'hidden' || Number(style.opacity) === 0) return false;
                const rect = el.getBoundingClientRect();
                return isVisibleRect(rect);
            })
            .map((el) => ({ el, rect: el.getBoundingClientRect(), pad: getPaddingForElement(el) }));
    }

    function obstacleRects() {
        return layoutElements(LAYOUT_SELECTORS).map(({ rect, pad }) => ({
            left: rect.left - pad,
            right: rect.right + pad,
            top: rect.top - pad,
            bottom: rect.bottom + pad,
            width: rect.width + pad * 2,
            height: rect.height + pad * 2
        }));
    }

    function overlaps(nx, ny, rect) {
        return !(
            nx + CFG.charW <= rect.left ||
            nx >= rect.right ||
            ny + CFG.charH <= rect.top ||
            ny >= rect.bottom
        );
    }

    // Karakterin hedef konumu ekran sınırları ve engellerle çakışmayacak şekilde düzeltilir.
    function resolvePosition(nx, ny) {
        const b = bounds();
        let px = clamp(nx, b.minX, b.maxX);
        let py = clamp(ny, b.minY, b.maxY);
        const rects = obstacleRects();

        for (let i = 0; i < 8; i += 1) {
            let changed = false;

            for (const rect of rects) {
                if (!overlaps(px, py, rect)) continue;

                const candidates = [
                    { x: rect.left - CFG.charW - 10, y: py },
                    { x: rect.right + 10, y: py },
                    { x: px, y: rect.top - CFG.charH - 10 },
                    { x: px, y: rect.bottom + 10 }
                ].map((candidate) => ({
                    x: clamp(candidate.x, b.minX, b.maxX),
                    y: clamp(candidate.y, b.minY, b.maxY)
                }));

                let best = null;
                for (const candidate of candidates) {
                    if (overlaps(candidate.x, candidate.y, rect)) continue;
                    const cost = Math.hypot(candidate.x - px, candidate.y - py);
                    if (!best || cost < best.cost) {
                        best = { ...candidate, cost };
                    }
                }

                if (best) {
                    px = best.x;
                    py = best.y;
                    changed = true;
                }
            }

            if (!changed) break;
        }

        return { x: clamp(px, b.minX, b.maxX), y: clamp(py, b.minY, b.maxY) };
    }

    function projectTarget(nx, ny) {
        return resolvePosition(nx, ny);
    }

    function edgeTargetsFromRect(rect) {
        const b = bounds();
        const sideGap = 16;
        const verticalGap = 14;
        return [
            { x: rect.left - CFG.charW - sideGap, y: clamp(rect.top + rect.height * 0.5 - CFG.charH * 0.7, b.minY, b.maxY), weight: 1 },
            { x: rect.right + sideGap, y: clamp(rect.top + rect.height * 0.5 - CFG.charH * 0.7, b.minY, b.maxY), weight: 1 },
            { x: clamp(rect.left + rect.width * 0.25, b.minX, b.maxX), y: rect.bottom + verticalGap, weight: 1.15 },
            { x: clamp(rect.right - rect.width * 0.25 - CFG.charW, b.minX, b.maxX), y: rect.bottom + verticalGap, weight: 1.15 },
            { x: clamp(rect.left + rect.width * 0.25, b.minX, b.maxX), y: rect.top - CFG.charH - verticalGap, weight: 1.35 },
            { x: clamp(rect.right - rect.width * 0.25 - CFG.charW, b.minX, b.maxX), y: rect.top - CFG.charH - verticalGap, weight: 1.35 }
        ].map((candidate) => {
            const projected = projectTarget(candidate.x, candidate.y);
            const dist = Math.hypot(projected.x - x, projected.y - y) * candidate.weight;
            return { ...projected, dist };
        });
    }

    function pickTargetFromLayout() {
        const elements = layoutElements(EDGE_TARGET_SELECTORS);
        const b = bounds();

        if (!elements.length) {
            const fallback = projectTarget(rnd(b.minX + 24, b.maxX - 24), rnd(Math.max(b.minY, b.maxY - 180), b.maxY));
            targetX = fallback.x;
            targetY = fallback.y;
            return;
        }

        const chosen = pick(elements).rect;
        const candidates = edgeTargetsFromRect(chosen).sort((a, b2) => a.dist - b2.dist);
        const best = candidates[0] || projectTarget(b.maxX - 40, b.maxY - 10);
        targetX = best.x;
        targetY = best.y;
    }

    function scheduleRoam(delay = rnd(CFG.roamMin, CFG.roamMax)) {
        window.clearTimeout(roamTimer);
        const calmDelay = isMobileCalm() ? Math.max(delay * 1.9, 5200) : delay;
        roamTimer = window.setTimeout(() => {
            if (!dragging && !sleeping && !hiddenByUser && born && performance.now() > interactUntil) {
                pickTargetFromLayout();
            }
            scheduleRoam();
        }, delay);
    }

    function pointerState(now) {
        const cx = x + CFG.charW * 0.5;
        const cy = y + CFG.charH * 0.56;
        const dx = cursorX - cx;
        const dy = cursorY - cy;
        const d = Math.hypot(dx, dy);
        if (hiddenByUser || isMobileCalm()) {
            return { dx, dy, d, near: false, close: false, engaged: false, fx: 0, fy: 0, boost: 1 };
        }
        const near = d < CFG.cursorRadius;
        const close = d < CFG.engageRadius;
        const engaged = near && !dragging && !sleeping && born;
        const slowPointer = pointerSpeed < 0.42;
        let fx = 0;
        let fy = 0;
        let boost = 1;

        if (engaged && d > 40 && slowPointer) {
            const follow = clamp((CFG.cursorRadius - d) / CFG.cursorRadius, 0, 1) * 0.055;
            fx = dx * follow;
            fy = dy * follow * 0.35;
            boost = 1.16;
        }

        if (close && now - lastNearBubble > 5200 && !bubbleCooldown && Math.random() < 0.018) {
            lastNearBubble = now;
            bubble(guideLine('near'), true);
        }

        return { dx, dy, d, near, close, engaged, fx, fy, boost };
    }

    // Her animasyon karesinde karakterin hızı, yönü ve görsel pozu güncellenir.
    function updateMovement(now) {
        const root = $('ig-root');
        const spr = $('ig-spr');
        const swrap = $('ig-swrap');
        const shad = $('ig-shad');
        if (!root || !spr || !swrap) return;
        syncGuideContext();
        if (!born) return;
        if (hiddenByUser) {
            root.classList.add('ig-hidden');
            return;
        }

        const b = bounds();
        const pointer = pointerState(now);

        if (dragging) {
            bobAmp = lerp(bobAmp, 0.28, 0.18);
            leanAmp = lerp(leanAmp, 0.24, 0.16);
            squashAmp = lerp(squashAmp, 0.16, 0.14);
        } else {
            const dx = targetX - x;
            const dy = targetY - y;
            const dist = Math.hypot(dx, dy);

            if (dist > 1) {
                const ease = clamp(dist / 220, 0.18, 1);
                vx += (dx / dist) * CFG.accel * ease;
                vy += (dy / dist) * CFG.accel * 0.72 * ease;
            }

            vx += pointer.fx;
            vy += pointer.fy;
            vx *= CFG.friction;
            vy *= CFG.friction;

            const speedLimit = (pointer.engaged ? CFG.interactSpeed : CFG.walkSpeed) * pointer.boost;
            const speed = Math.hypot(vx, vy);
            if (speed > speedLimit) {
                const scale = speedLimit / speed;
                vx *= scale;
                vy *= scale;
            }

            const next = resolvePosition(x + vx, y + vy);
            vx = next.x - x;
            vy = next.y - y;
            x = next.x;
            y = next.y;

            if (x < b.minX) { x = b.minX; vx = 0; }
            if (x > b.maxX) { x = b.maxX; vx = 0; }
            if (y < b.minY) { y = b.minY; vy = 0; }
            if (y > b.maxY) { y = b.maxY; vy = 0; }

            if (Math.hypot(targetX - x, targetY - y) < CFG.targetSnap && Math.hypot(vx, vy) < 0.42) {
                vx *= 0.42;
                vy *= 0.42;
            }
        }

        const speed = Math.hypot(vx, vy);
        const moving = dragging || speed > 0.18;

        if (dragging) {
            if (Math.abs(vx) > 0.12) facingRight = vx > 0;
        } else if (pointer.close) {
            facingRight = pointer.dx > 0;
        } else if (Math.abs(vx) > 0.1) {
            facingRight = vx > 0;
        }

        bobAmp = lerp(bobAmp, moving ? 1 : 0.14, 0.095);
        leanAmp = lerp(leanAmp, moving ? 1 : 0.16, 0.08);
        squashAmp = lerp(squashAmp, moving ? 1 : 0.12, 0.08);
        walkPhase += Math.max(0.02, speed * 0.12);

        const stride = Math.sin(walkPhase);
        const strideAbs = Math.abs(stride);
        const secondary = Math.sin(walkPhase * 2);
        const bobY = strideAbs * 5.2 * bobAmp;
        const swayX = Math.sin(walkPhase + 0.8) * 1.45 * bobAmp;
        const hipTilt = secondary * 0.9 * bobAmp;
        const pointerLean = pointer.close ? clamp(pointer.dx * 0.02, -4, 4) : 0;
        const moveLean = clamp(vx * 5.2, -7.5, 7.5);
        const lean = (moveLean + pointerLean) * leanAmp;
        const squashX = 1 + secondary * 0.016 * squashAmp;
        const squashY = 1 - strideAbs * 0.024 * squashAmp;
        const flip = facingRight ? -1 : 1;

        root.classList.toggle('ig-dragging', dragging);
        root.classList.toggle('ig-engaged', pointer.engaged && !dragging);
        root.classList.toggle('ig-cursor-near', pointer.near && !dragging);
        root.classList.toggle('ig-cursor-close', pointer.close && !dragging);
        root.classList.toggle('ig-walking', moving && !dragging);
        setRootTransform();

        swrap.style.transform = `translate3d(${swayX.toFixed(2)}px, ${(-bobY).toFixed(2)}px, 0) rotate(${hipTilt.toFixed(2)}deg)`;
        spr.style.transform = `scaleX(${(flip * squashX).toFixed(4)}) scaleY(${squashY.toFixed(4)}) rotate(${lean.toFixed(2)}deg)`;

        if (shad) {
            const shadowScale = clamp(0.82 + speed * 0.08 - strideAbs * 0.14, 0.64, 1.22);
            const shadowOpacity = clamp(0.18 + bobAmp * 0.11 - strideAbs * 0.045, 0.12, 0.32);
            shad.style.transform = `scaleX(${shadowScale.toFixed(3)})`;
            shad.style.opacity = shadowOpacity.toFixed(3);
        }

        const bub = $('ig-bubble');
        if (bub) {
            const nearRight = window.innerWidth - x < 220;
            const nearTop = y < 136;
            bub.classList.toggle('ig-left-bubble', nearRight);
            bub.classList.toggle('ig-low-bubble', nearTop);
        }
    }

    function frame(now) {
        requestAnimationFrame(frame);
        updateMovement(now);

        const sec = detectSection();
        if (sec !== currentSection) {
            currentSection = sec;
            syncGuideContext();
            if (born && !hiddenByUser) {
                window.setTimeout(() => runSectionReaction(sec), 260);
                if (!dragging && !isMobileCalm()) pickTargetFromLayout();
            }
        }
    }

    function handlePageClick(event) {
        if (!born || dragging || sleeping || hiddenByUser || isMobileCalm()) return;
        if (event.button !== 0) return;
        const target = event.target;
        if (target && target.closest('#ig-root, a, button, input, textarea, select, label, .lang-menu, .menu-button, .theme-toggle')) return;

        const projected = projectTarget(event.clientX - CFG.charW * 0.5, event.clientY - CFG.charH * 0.82);
        targetX = projected.x;
        targetY = projected.y;
        interactUntil = performance.now() + 4200;
        window.clearTimeout(roamTimer);
        window.setTimeout(() => bubble(guideLine('click'), true), CFG.clickBubbleDelay);
        scheduleRoam(3400);
    }

    // Fare ve dokunma olayları karakter sürükleme davranışına bağlanır.
    function setupPointer() {
        window.addEventListener('pointermove', (e) => {
            const now = performance.now();
            const dt = Math.max(16, now - lastPointer.t);
            pointerSpeed = Math.hypot(e.clientX - lastPointer.x, e.clientY - lastPointer.y) / dt;
            cursorX = e.clientX;
            cursorY = e.clientY;
            lastPointer = { x: e.clientX, y: e.clientY, t: now };
        }, { passive: true });

        window.addEventListener('click', handlePageClick, true);

        const hit = $('ig-hit');
        if (!hit) return;

        hit.addEventListener('pointerenter', () => {
            if (!dragging && born) bubble(guideLine('hover'), true);
        });

        hit.addEventListener('pointerdown', (e) => {
            if (!born || sleeping) return;
            e.preventDefault();
            dragging = true;
            hit.setPointerCapture?.(e.pointerId);
            dragOffsetX = e.clientX - x;
            dragOffsetY = e.clientY - y;
            dragLast = { x: e.clientX, y: e.clientY, t: performance.now() };
            interactUntil = performance.now() + 3000;
            window.clearTimeout(roamTimer);
            bubble(guideLine('drag'), true);
        });

        hit.addEventListener('pointermove', (e) => {
            if (!dragging) return;
            const now = performance.now();
            const dt = Math.max(16, now - dragLast.t);
            const b = bounds();
            const nx = clamp(e.clientX - dragOffsetX, b.minX, b.maxX);
            const ny = clamp(e.clientY - dragOffsetY, b.minY, b.maxY);
            const resolved = resolvePosition(nx, ny);
            vx = (resolved.x - x) / (dt / 16.67);
            vy = (resolved.y - y) / (dt / 16.67);
            x = resolved.x;
            y = resolved.y;
            targetX = x;
            targetY = y;
            dragLast = { x: e.clientX, y: e.clientY, t: now };
            vx *= CFG.dragFriction;
            vy *= CFG.dragFriction;
        });

        const stopDrag = (e) => {
            if (!dragging) return;
            dragging = false;
            try { hit.releasePointerCapture?.(e.pointerId); } catch (_) {}
            const projected = projectTarget(x + vx * 10, y + vy * 6);
            targetX = projected.x;
            targetY = projected.y;
            interactUntil = performance.now() + 2600;
            try {
                window.localStorage?.setItem('irem-character-last-drop', JSON.stringify({ x, y }));
            } catch (_) {}
            bubble(guideText('dropDone'), true);
            scheduleRoam(2200);
        };

        hit.addEventListener('pointerup', stopDrag);
        hit.addEventListener('pointercancel', stopDrag);
    }

    function setupToggle() {
        const btn = $('ig-btn');
        if (!btn) return;

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const nextHidden = !hiddenByUser;
            setHiddenState(nextHidden, true);
            if (!nextHidden) {
                if (!born) {
                    openPortal();
                } else {
                    bubble(guideText('returnHello'), true);
                    scheduleRoam(isMobileCalm() ? 5200 : 1000);
                }
            }
        });
    }

    function setupContactWatch() {
        const btn = document.getElementById('cf-btn') || document.querySelector('#contact button, #contact .btn');
        if (!btn) return;
        btn.addEventListener('mouseenter', () => {
            if (!hiddenByUser && currentSection === 'contact') bubble(guideText('contactHover'), true);
        });
    }

    function idleBubbles() {
        const next = () => {
            window.clearTimeout(idleTimer);
            idleTimer = window.setTimeout(() => {
                if (born && !dragging && !sleeping && !hiddenByUser && performance.now() > interactUntil) {
                    bubble(pick(guideSectionMessages(currentSection)));
                }
                next();
            }, isMobileCalm() ? rnd(15000, 24000) : rnd(7600, 14500));
        };
        next();
    }

    function openPortal() {
        if (hiddenByUser) return;
        if (born) return;
        born = true;
        const root = $('ig-root');
        const portal = $('ig-portal');
        if (!root) return;

        placeAtPortal();
        setRootTransform();
        root.classList.add('ig-born', 'ig-emerging');
        portal?.classList.add('is-open');

        window.setTimeout(() => bubble(guideText('firstHello'), true), 520);
        window.setTimeout(() => {
            const firstWalk = projectTarget(x + Math.min(220, window.innerWidth * 0.18), y + 90);
            targetX = firstWalk.x;
            targetY = firstWalk.y;
            vx = 0.95;
            vy = 0.4;
        }, CFG.portalDelay);

        window.setTimeout(() => {
            root.classList.remove('ig-emerging');
            const nextSpot = projectTarget(x + Math.min(300, window.innerWidth * 0.22), groundY() - 16);
            targetX = nextSpot.x;
            targetY = nextSpot.y;
        }, CFG.birthWalk);

        window.setTimeout(() => {
            portal?.classList.add('is-faded');
            pickTargetFromLayout();
            scheduleRoam(1100);
        }, CFG.birthWalk + 550);
    }

    function startPeekFallback() {
        window.clearTimeout(peekTimer);
        peekTimer = window.setTimeout(() => {
            if (!born) openPortal();
        }, 11000);
    }

    function restoreLastDropAfterBirth() {
        try {
            const saved = JSON.parse(window.localStorage?.getItem('irem-character-last-drop') || 'null');
            if (!saved || typeof saved.x !== 'number' || typeof saved.y !== 'number') return;
            window.setTimeout(() => {
                if (!born || dragging) return;
                const projected = projectTarget(saved.x, saved.y);
                targetX = projected.x;
                targetY = projected.y;
            }, 5200);
        } catch (_) {}
    }

    function onResize() {
        const b = bounds();
        x = clamp(x, b.minX, b.maxX);
        y = clamp(y, b.minY, b.maxY);
        targetX = clamp(targetX, b.minX, b.maxX);
        targetY = clamp(targetY, b.minY, b.maxY);
        if (!born) placeAtPortal();
        syncGuideContext();
    }

    // Karakter rehberinin tüm parçalarını oluşturup animasyon döngüsünü başlatır.
    function init() {
        buildPortal();
        buildCharacter();
        restoreHiddenPreference();
        setupThemeAndViewportWatchers();
        placeAtPortal();
        setRootTransform();
        setupPointer();
        setupToggle();
        setupContactWatch();
        idleBubbles();
        restoreLastDropAfterBirth();
        window.addEventListener('resize', onResize, { passive: true });
        window.addEventListener('irem:portal-open', openPortal, { once: true });
        if (!hiddenByUser) startPeekFallback();
        requestAnimationFrame(frame);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

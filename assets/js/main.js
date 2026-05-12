// Bu dosya: Ana portfolyo sayfasındaki menü, tema/dil, animasyon, video ve etkileşim akışlarını yönetir.
// Sayfa DOM yapısı hazır olduğunda ana portfolyo etkileşimleri başlatılır.
document.addEventListener('DOMContentLoaded', async () => {
    // Mobil menü butonu body üzerindeki sınıfı değiştirerek menüyü açıp kapatır.
    const menuButton = document.querySelector('.menu-button');
    menuButton?.addEventListener('click', () => document.body.classList.toggle('menu-open'));

    // Masaüstü cihazlarda özel yeşil imleç efektini etkinleştirir.
    const initLimeCursor = () => {
        const cursorLayer = document.querySelector('.cursor-dot');
        const html = document.documentElement;
        const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        cursorLayer?.setAttribute('aria-hidden', 'true');

        if (finePointer && !reduceMotion) {
            html.classList.add('lime-cursor');
        }
    };

    initLimeCursor();

    // Lenis yüklüyse sayfa kaydırma hareketi daha yumuşak hâle getirilir.
    if (window.Lenis) {
        const lenis = new Lenis({ lerp: 0.08, smoothWheel: true });
        function raf(time) {
            lenis.raf(time);
            requestAnimationFrame(raf);
        }
        requestAnimationFrame(raf);
    }

    // GSAP mevcutsa giriş ve bölüm animasyonları devreye alınır.
    if (window.gsap) {
        gsap.registerPlugin(ScrollTrigger);

        const typedName = document.querySelector('.typed-name');
        const introBrand = document.querySelector('.intro-brand');
        const introDot = document.querySelector('.brand-v');
        const introSub = document.querySelector('.brand-sub');
        const introCursor = document.querySelector('.cursor');
        const fullName = 'İrem Öztürk';

        // Hero başlığındaki harfler tek tek animasyonlanabilmesi için span elemanlarına ayrılır.
        const splitHeroName = () => {
            document.querySelectorAll('.js-split-name').forEach((line) => {
                const text = line.dataset.name || line.textContent.trim();
                line.innerHTML = Array.from(text).map((char) => {
                    const safeChar = char === ' ' ? '&nbsp;' : char;
                    return `<span class="char-wrap"><span class="char">${safeChar}</span></span>`;
                }).join('');
            });
        };

        splitHeroName();

        const getSavedLanguage = () => localStorage.getItem('portfolioLang') || document.cookie.split('; ').find((row) => row.startsWith('portfolio_lang='))?.split('=')[1] || document.documentElement.lang || 'en';
        const getTechWords = () => getSavedLanguage() === 'tr'
            ? ['Backend', 'Frontend', 'Veritabanı', 'Yapay Zekâ / ML']
            : ['Backend', 'Frontend', 'Database', 'AI/ML Technologies'];
        const techWord = document.querySelector('[data-tech-word]');
        const roleText = document.querySelector('.hero-role-final');
        const heroPanelLoop = document.querySelector('.hero.panel#hero');


        // Hero görselindeki ikili kod yağmuru rastgele sütunlarla oluşturulur.
        const initBinaryRain = () => {
            const binaryRain = document.querySelector('.hero-reel .binary-rain');
            if (!binaryRain) return;

            const createBinaryString = (length = 72) => Array.from({ length }, () => (Math.random() > 0.5 ? '1' : '0')).join('');

            const columnCount = window.innerWidth <= 520 ? 8 : window.innerWidth <= 900 ? 10 : 14;
            binaryRain.innerHTML = '';

            for (let i = 0; i < columnCount; i += 1) {
                const col = document.createElement('span');
                col.className = 'binary-column';
                col.textContent = createBinaryString(78 + Math.floor(Math.random() * 34));
                col.style.setProperty('--binary-duration', `${(3.2 + Math.random() * 3.8).toFixed(2)}s`);
                col.style.setProperty('--binary-delay', `-${(Math.random() * 5.5).toFixed(2)}s`);
                col.style.setProperty('--binary-start', `${(-120 - Math.random() * 65).toFixed(2)}%`);
                col.style.setProperty('--binary-end', `${(65 + Math.random() * 80).toFixed(2)}%`);
                col.style.setProperty('--binary-flicker', `${(1.1 + Math.random() * 1.9).toFixed(2)}s`);
                col.style.setProperty('--binary-opacity', `${(0.35 + Math.random() * 0.4).toFixed(2)}`);
                binaryRain.appendChild(col);
            }

            const refreshColumn = (col) => {
                if (!col) return;
                col.textContent = createBinaryString(78 + Math.floor(Math.random() * 34));
                col.style.setProperty('--binary-duration', `${(3.0 + Math.random() * 3.6).toFixed(2)}s`);
                col.style.setProperty('--binary-delay', `-${(Math.random() * 5).toFixed(2)}s`);
                col.style.setProperty('--binary-start', `${(-120 - Math.random() * 65).toFixed(2)}%`);
                col.style.setProperty('--binary-end', `${(65 + Math.random() * 80).toFixed(2)}%`);
                col.style.setProperty('--binary-flicker', `${(1.0 + Math.random() * 2.2).toFixed(2)}s`);
                col.style.setProperty('--binary-opacity', `${(0.35 + Math.random() * 0.4).toFixed(2)}`);
            };

            binaryRain.querySelectorAll('.binary-column').forEach((col, index) => {
                window.setInterval(() => refreshColumn(col), 1300 + index * 210);
            });
        };

        const wait = (seconds) => new Promise((resolve) => gsap.delayedCall(seconds, resolve));

        // Daktilo efektiyle metni karakter karakter yazar.
        const typeText = (element, text, speed = 0.055) => {
            if (!element) return Promise.resolve();

            return new Promise((resolve) => {
                let index = 0;
                element.textContent = '';
                element.classList.add('is-typing');
                gsap.set(element, {
                    opacity: 1,
                    y: 0,
                    yPercent: 0,
                    filter: 'blur(0px)',
                    clipPath: 'inset(0 0% 0 0)'
                });

                const tick = () => {
                    element.textContent = text.slice(0, index);
                    index += 1;

                    if (index <= text.length) {
                        window.setTimeout(tick, speed * 1000);
                    } else {
                        resolve();
                    }
                };

                tick();
            });
        };

        // Daktilo efektindeki metni sondan başa doğru siler.
        const deleteText = (element, speed = 0.035) => {
            if (!element) return Promise.resolve();

            return new Promise((resolve) => {
                let index = element.textContent.length;
                element.classList.add('is-typing');

                const tick = () => {
                    element.textContent = element.textContent.slice(0, index);
                    index -= 1;

                    if (index >= 0) {
                        window.setTimeout(tick, speed * 1000);
                    } else {
                        element.classList.remove('is-typing');
                        resolve();
                    }
                };

                tick();
            });
        };

        const playTypewriterWord = async (element, text, hold = 0.55) => {
            if (element) {
                element.classList.toggle('tech-word-long', text === 'AI/ML Technologies');
            }
            await typeText(element, text);
            await wait(hold);
            await deleteText(element);
            if (element) {
                element.classList.remove('tech-word-long');
            }
            await wait(0.16);
        };

        // Hero alanındaki teknoloji kelimeleri ve rol metni sürekli dönen bir animasyonla gösterilir.
        const startHeroLoop = () => {
            if (!techWord) return;

            let portalHasOpened = false;

            const openCharacterPortal = async () => {
                if (portalHasOpened) return;
                portalHasOpened = true;
                techWord.textContent = '';
                techWord.classList.remove('is-typing', 'tech-word-long');
                document.querySelector('.hero-reel')?.classList.add('has-irem-door');
                window.dispatchEvent(new CustomEvent('irem:portal-open'));
                await wait(3.35);
                document.querySelector('.hero-reel')?.classList.remove('has-irem-door');
            };

            const runCycle = async () => {
                gsap.killTweensOf([techWord, roleText]);
                techWord.textContent = '';
                techWord.classList.remove('is-typing');

                if (roleText) {
                    roleText.textContent = '';
                    roleText.classList.remove('is-typing');
                    gsap.set(roleText, {
                        opacity: 0,
                        y: 0,
                        filter: 'blur(0px)',
                        clipPath: 'inset(0 0% 0 0)'
                    });
                }

                for (const word of getTechWords()) {
                    await playTypewriterWord(techWord, word, 0.62);
                }

                if (roleText) {
                    gsap.set(roleText, { opacity: 1, y: 0, filter: 'blur(0px)', clipPath: 'inset(0 0% 0 0)' });
                    const typedRole = window.portfolioI18n?.heroTypedRole || (getSavedLanguage() === 'tr' ? 'Full Stack Geliştirici' : 'Full Stack Developer');
                    await playTypewriterWord(roleText, typedRole, 0.95);
                    gsap.set(roleText, { opacity: 0 });
                }

                runCycle();
            };

            runCycle();
        };

        initBinaryRain();

        let binaryRainResizeTimer;
        window.addEventListener('resize', () => {
            window.clearTimeout(binaryRainResizeTimer);
            binaryRainResizeTimer = window.setTimeout(initBinaryRain, 180);
        });

        const typeIntroName = () => {
            if (!typedName) return Promise.resolve();

            return new Promise((resolve) => {
                let index = 0;
                typedName.textContent = '';

                const typeNext = () => {
                    typedName.textContent = fullName.slice(0, index);
                    index += 1;

                    if (index <= fullName.length) {
                        window.setTimeout(typeNext, 70);
                    } else {
                        window.setTimeout(resolve, 170);
                    }
                };

                typeNext();
            });
        };

        typeIntroName().then(() => {
            introDot?.classList.add('visible');
            introSub?.classList.add('visible');

            window.setTimeout(() => {
                introCursor?.classList.add('hidden');
                document.querySelector('.curtain-top')?.classList.add('open');
                document.querySelector('.curtain-bottom')?.classList.add('open');
                introBrand?.classList.add('hide');

                window.setTimeout(() => {
                    document.body.classList.remove('is-loading');
                    window.setTimeout(() => {
                        document.querySelector('.curtain-top')?.remove();
                        document.querySelector('.curtain-bottom')?.remove();
                        introBrand?.remove();
                    }, 1050);
                    ScrollTrigger.refresh();

                    gsap.from('.brand, .main-nav, .nav-actions, .menu-button', {
                        y: -30,
                        opacity: 0,
                        duration: 0.8,
                        stagger: 0.08,
                        ease: 'power3.out'
                    });

                    gsap.from('.eyebrow', {
                        y: 50,
                        opacity: 0,
                        duration: .75,
                        ease: 'power3.out'
                    });

                    const heroTitle = document.querySelector('.hero-title');
                    const leftName = document.querySelector('.name-left');
                    const rightName = document.querySelector('.name-right');
                    const reel = document.querySelector('.hero-reel');
                    const chars = gsap.utils.toArray('.hero-title .char');
                    const reelCards = gsap.utils.toArray('.hero-reel .reel-card');
                    let squeezeTimeline;

                    const lockHeroFinalState = () => {
                        gsap.set(heroTitle, { x: 0, y: 0, xPercent: 0, yPercent: 0, scale: 1, rotate: 0, transformOrigin: '50% 100%' });
                        gsap.set(leftName, { x: 0, xPercent: 0, scaleX: 1, opacity: 1, filter: 'blur(0px)', transformOrigin: '0% 50%' });
                        gsap.set(rightName, { x: 0, xPercent: 0, scaleX: 1, opacity: 1, filter: 'blur(0px)', transformOrigin: '100% 50%' });
                        gsap.set(reel, {
                            x: 0,
                            xPercent: 0,
                            scaleX: 1,
                            scaleY: 1,
                            opacity: 1,
                            clipPath: 'inset(0 0% 0 0% round 14px)',
                            filter: 'brightness(1) saturate(1)',
                            transformOrigin: '50% 50%'
                        });
                        gsap.set(chars, { scaleX: 1, scaleY: 1, opacity: 1, filter: 'blur(0px)', yPercent: 0, transformOrigin: '50% 55%' });
                    };

                    const playSqueezeHero = () => {
                        if (!heroTitle || !leftName || !rightName || !reel) return;
                        if (squeezeTimeline?.isActive()) return;
                        gsap.killTweensOf([leftName, rightName, reel, chars, reelCards]);

                        squeezeTimeline = gsap.timeline({ defaults: { ease: 'power4.inOut' }, onComplete: lockHeroFinalState });

                        squeezeTimeline
                            .to(leftName, { scaleX: 0.64, x: 18, filter: 'blur(2.4px)', duration: 0.36 }, 0)
                            .to(rightName, { scaleX: 0.64, x: -18, filter: 'blur(2.4px)', duration: 0.36 }, 0)
                            .to(reel, {
                                scaleX: 0.16,
                                scaleY: 1.08,
                                clipPath: 'inset(0 43% 0 43% round 10px)',
                                filter: 'brightness(1.5) saturate(1.35)',
                                duration: 0.36
                            }, 0)
                            .to(chars, { scaleX: 0.84, yPercent: -2, duration: 0.36, stagger: { each: 0.006, from: 'center' } }, 0)
                            .to(reelCards, { yPercent: -28, opacity: .55, duration: 0.24, stagger: { each: .015, from: 'random' } }, 0.04)
                            .to(leftName, { scaleX: 1, x: 0, filter: 'blur(0px)', ease: 'expo.out', duration: 0.95 }, 0.36)
                            .to(rightName, { scaleX: 1, x: 0, filter: 'blur(0px)', ease: 'expo.out', duration: 0.95 }, 0.36)
                            .to(reel, { scaleX: 1, scaleY: 1, clipPath: 'inset(0 0% 0 0% round 14px)', filter: 'brightness(1) saturate(1)', ease: 'expo.out', duration: 0.95 }, 0.36)
                            .to(chars, { scaleX: 1, yPercent: 0, ease: 'expo.out', duration: 0.95, stagger: { each: 0.006, from: 'center' } }, 0.36)
                            .to(reelCards, { yPercent: 0, opacity: 1, ease: 'expo.out', duration: 0.75, stagger: { each: .015, from: 'random' } }, 0.42);
                    };

                    const playHeroIntro = () => {
                        if (!heroTitle || !leftName || !rightName || !reel) return;
                        gsap.killTweensOf([heroTitle, leftName, rightName, reel, chars, reelCards, '.hero-role-final']);

                        gsap.set(heroTitle, { x: 0, y: 0, xPercent: 0, yPercent: 0, scale: 1 });
                        gsap.set(leftName, { scaleX: 0.08, x: 90, opacity: 0, filter: 'blur(22px)', transformOrigin: '0% 50%' });
                        gsap.set(rightName, { scaleX: 0.08, x: -90, opacity: 0, filter: 'blur(22px)', transformOrigin: '100% 50%' });
                        gsap.set(reel, { scaleX: 0.02, scaleY: .9, opacity: 0, clipPath: 'inset(0 50% 0 50% round 20px)', filter: 'brightness(2.1) saturate(1.55)', transformOrigin: '50% 50%' });
                        gsap.set(chars, { yPercent: 105, opacity: 0, filter: 'blur(12px)', transformOrigin: '50% 55%' });
                        gsap.set(reelCards, { yPercent: 24, opacity: 0 });

                        gsap.timeline({ defaults: { ease: 'expo.out' } })
                            .to([leftName, rightName], { scaleX: 1, x: 0, opacity: 1, filter: 'blur(0px)', duration: 1.08 }, 0)
                            .to(chars, { yPercent: 0, opacity: 1, filter: 'blur(0px)', duration: .86, stagger: { each: .026, from: 'edges' } }, .05)
                            .to(reel, { scaleX: 1, scaleY: 1, opacity: 1, clipPath: 'inset(0 0% 0 0% round 14px)', filter: 'brightness(1) saturate(1)', duration: 1.0 }, .12)
                            .to(reelCards, { yPercent: 0, opacity: 1, duration: .74, stagger: { each: .035, from: 'random' } }, .28)
                            .call(() => {
                                lockHeroFinalState();
                            });
                    };

                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        lockHeroFinalState();
                        gsap.set('.hero-role-final', { opacity: 0, y: 16, filter: 'blur(10px)', clipPath: 'inset(0 100% 0 0)' });
                    } else {
                        playHeroIntro();
                    }

                    const heroPanel = document.querySelector('#hero');
                    const fitHeroTitleToScreen = () => {
                        if (!heroPanel || !heroTitle) return;
                        heroTitle.style.setProperty('--hero-fit-scale', '1');
                        const safeWidth = Math.max(320, heroPanel.clientWidth - 22);
                        const titleWidth = heroTitle.scrollWidth || heroTitle.getBoundingClientRect().width;
                        const scale = gsap.utils.clamp(0.54, 1.0, safeWidth / Math.max(1, titleWidth));
                        heroTitle.style.setProperty('--hero-fit-scale', scale.toFixed(4));
                    };
                    fitHeroTitleToScreen();
                    window.addEventListener('resize', () => {
                        gsap.delayedCall(0.05, fitHeroTitleToScreen);
                    });

                    const leftChars = gsap.utils.toArray('.name-left .char');
                    const rightChars = gsap.utils.toArray('.name-right .char');

                    const clamp = gsap.utils.clamp;
                    const prefersReducedNameMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    const canUseNamePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches && !prefersReducedNameMotion;
                    let namePointerTicking = false;
                    let latestNamePointer = null;

                    const setNamePointerVars = (event) => {
                        if (!heroTitle) return;
                        const titleRect = heroTitle.getBoundingClientRect();
                        const pointerX = clamp(0, 100, ((event.clientX - titleRect.left) / Math.max(1, titleRect.width)) * 100);
                        const pointerY = clamp(0, 100, ((event.clientY - titleRect.top) / Math.max(1, titleRect.height)) * 100);

                        heroTitle.style.setProperty('--name-pointer-x', `${pointerX.toFixed(2)}%`);
                        heroTitle.style.setProperty('--name-pointer-y', `${pointerY.toFixed(2)}%`);
                    };

                    const animateNameCharacters = () => {
                        namePointerTicking = false;
                        if (!latestNamePointer || !heroTitle) return;

                        const event = latestNamePointer;
                        const allChars = [...leftChars, ...rightChars];
                        const radius = window.innerWidth <= 720 ? 112 : 215;

                        heroTitle.classList.add('is-pointer-active');
                        setNamePointerVars(event);

                        allChars.forEach((letter, index) => {
                            const bounds = letter.getBoundingClientRect();
                            const centerX = bounds.left + bounds.width / 2;
                            const centerY = bounds.top + bounds.height / 2;
                            const dx = centerX - event.clientX;
                            const dy = centerY - event.clientY;
                            const distance = Math.hypot(dx, dy);
                            const force = clamp(0, 1, 1 - distance / radius);
                            const safeDistance = Math.max(distance, 1);
                            const wave = Math.sin(performance.now() / 180 + index * 0.72) * force;
                            const polarity = letter.closest('.name-left') ? -1 : 1;

                            gsap.to(letter, {
                                x: (dx / safeDistance) * force * 30 + wave * 3,
                                y: (dy / safeDistance) * force * 18 - force * 12,
                                rotateZ: clamp(-10, 10, (dx / radius) * 18 * force + wave * 2.2),
                                skewX: clamp(-8, 8, polarity * force * 4 + wave * 1.8),
                                scaleX: 1 + force * 0.13,
                                scaleY: 1 + force * 0.2,
                                filter: `drop-shadow(0 0 ${Math.round(8 + force * 30)}px rgba(255, 122, 31, ${0.1 + force * 0.58}))`,
                                color: force > 0.08 ? '#ff9f3f' : '',
                                duration: 0.3,
                                ease: 'power3.out',
                                overwrite: 'auto'
                            });
                        });
                    };

                    const queueNamePointer = (event) => {
                        if (!canUseNamePointer) return;
                        latestNamePointer = event;
                        if (!namePointerTicking) {
                            namePointerTicking = true;
                            requestAnimationFrame(animateNameCharacters);
                        }
                    };

                    const resetNamePointer = () => {
                        latestNamePointer = null;
                        heroTitle.classList.remove('is-pointer-active');
                        heroTitle.style.removeProperty('--name-pointer-x');
                        heroTitle.style.removeProperty('--name-pointer-y');

                        gsap.to([...leftChars, ...rightChars], {
                            x: 0,
                            y: 0,
                            rotateZ: 0,
                            skewX: 0,
                            scaleX: 1,
                            scaleY: 1,
                            filter: 'drop-shadow(0 0 0 rgba(255, 122, 31, 0))',
                            color: '',
                            clearProps: 'color',
                            duration: 0.72,
                            ease: 'elastic.out(1, 0.54)',
                            overwrite: 'auto'
                        });
                    };

                    if (canUseNamePointer) {
                        [leftName, rightName].filter(Boolean).forEach((nameLine) => {
                            nameLine.addEventListener('pointermove', queueNamePointer);
                            nameLine.addEventListener('pointerleave', resetNamePointer);
                        });
                    }


                    startHeroLoop();
                }, 90);
            }, 650);
        });

        // Hero title stays fixed in its final full-width position; no shrinking or side drift on scroll.
        gsap.set('.hero-title', { x: 0, y: 0, xPercent: 0, yPercent: 0, scale: 1 });

        gsap.to('.float-card', {
            y: -60,
            rotate: 4,
            scrollTrigger: {
                trigger: '.hero',
                start: 'top top',
                end: 'bottom top',
                scrub: true
            }
        });

        document.querySelectorAll('.section-title, .large-copy, .about-metrics article, .reveal-card, .timeline-item, .contact-shell').forEach((element) => {
            gsap.from(element, {
                y: 70,
                opacity: 0,
                duration: 0.9,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: element,
                    start: 'top 86%'
                }
            });
        });

        const loadProjectsFromDatabase = async () => {
            const projectStage = document.querySelector('.project-stage');
            if (!projectStage || !window.fetch) return;

            const escapeHtml = (value = '') => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const renderProjectCard = (project, index) => {
                const translatedProject = window.portfolioI18n ? resolveProjectTranslation(window.portfolioI18n, index, [project.title, project.code_name, project.github_url]) : null;
                const currentLang = localStorage.getItem('portfolioLang') || getCookie('portfolio_lang') || document.documentElement.lang || 'en';
                const title = translatedProject?.title || project.title || 'Untitled Project';
                const codeName = translatedProject?.code || project.code_name || title;
                const description = translatedProject?.description || project.short_description || project.description || '';
                const image = project.image || 'assets/images/project-portfolio.svg';
                const githubUrl = project.github_url || '#';
                const techStack = String(project.tech_stack || '')
                    .split(',')
                    .map((tech) => tech.trim())
                    .filter(Boolean)
                    .map((tech) => `<span>${escapeHtml(tech)}</span>`)
                    .join('');

                return `
                    <article class="project-card ${index === 0 ? 'is-active' : ''}" data-project-card data-index="${index}" data-project-source-title="${escapeHtml(project.title || '')}" data-project-source-code="${escapeHtml(project.code_name || '')}" data-project-github="${escapeHtml(project.github_url || '')}">
                        <img class="project-card-image" src="${escapeHtml(image)}" alt="${escapeHtml(title)} preview" loading="lazy">
                        <div class="project-card-overlay" aria-hidden="true"></div>
                        <div class="project-deco" aria-hidden="true"></div>
                        <div class="project-actions">
                            <a class="project-github-btn" href="${escapeHtml(githubUrl)}" target="_blank" rel="noopener" aria-label="${currentLang === 'tr' ? `${escapeHtml(title)} projesini GitHub'da aç` : `Open ${escapeHtml(title)} on GitHub`}">
                                <svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" focusable="false">
                                    <path fill="currentColor" d="M12 .5a12 12 0 0 0-3.79 23.39c.6.11.82-.26.82-.58v-2.05c-3.34.73-4.04-1.42-4.04-1.42-.55-1.39-1.34-1.76-1.34-1.76-1.09-.75.08-.74.08-.74 1.21.09 1.85 1.24 1.85 1.24 1.07 1.84 2.82 1.31 3.5 1 .11-.78.42-1.31.76-1.61-2.67-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.12-.3-.54-1.52.12-3.18 0 0 1.01-.32 3.3 1.23a11.5 11.5 0 0 1 6.01 0c2.29-1.55 3.3-1.23 3.3-1.23.66 1.66.24 2.88.12 3.18.77.84 1.24 1.91 1.24 3.22 0 4.61-2.81 5.63-5.49 5.93.43.37.81 1.1.81 2.22v3.29c0 .32.22.7.83.58A12 12 0 0 0 12 .5Z"/>
                                </svg>
                            </a>
                        </div>
                        <div class="project-card-content">
                            <span class="project-code" data-project-code>${escapeHtml(codeName)}</span>
                            <div class="project-title-clip"><h3 data-project-title>${escapeHtml(title)}</h3></div>
                            <p data-project-description>${escapeHtml(description)}</p>
                            <div class="project-tech">${techStack}</div>
                        </div>
                        <div class="project-number" aria-hidden="true">${String(index + 1).padStart(2, '0')}</div>
                    </article>`;
            };

            try {
                const response = await fetch('api/get-projects.php', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store'
                });
                const data = await response.json();
                if (!response.ok || !data.success || !Array.isArray(data.projects) || !data.projects.length) return;
                projectStage.innerHTML = data.projects.map(renderProjectCard).join('');
                projectStage.dataset.loadedViaAjax = 'true';
            } catch (error) {
                // The PHP-rendered cards stay visible if AJAX loading is unavailable.
            }
        };

        await loadProjectsFromDatabase();

        const projectCards = Array.from(document.querySelectorAll('[data-project-card]'));
        const projectPrev = document.querySelector('[data-project-prev]');
        const projectNext = document.querySelector('[data-project-next]');
        const projectSection = document.querySelector('.projects');
        const projectCount = document.querySelector('[data-project-count]');
        const projectStage = document.querySelector('.project-stage');
        let projectFlyingTitle = null;
        let projectTitleTick = 0;

        if (projectCards.length) {
            let activeProject = 0;
            if (projectStage) {
                projectFlyingTitle = document.createElement('div');
                projectFlyingTitle.className = 'project-flying-title';
                projectFlyingTitle.setAttribute('aria-hidden', 'true');
                projectStage.appendChild(projectFlyingTitle);
            }
            const wrapIndex = (index) => (index + projectCards.length) % projectCards.length;

            const paintProjects = () => {
                if (projectCount) {
                    projectCount.textContent = `${String(activeProject + 1).padStart(2, '0')} / ${String(projectCards.length).padStart(2, '0')}`;
                }

                const activeCard = projectCards[activeProject];
                const activeTitle = activeCard?.querySelector('[data-project-code]')?.textContent?.trim() || activeCard?.querySelector('[data-project-title]')?.textContent?.trim() || '';

                projectCards.forEach((card, index) => {
                    const diff = (index - activeProject + projectCards.length) % projectCards.length;
                    card.classList.remove('is-active', 'is-prev', 'is-next', 'is-far-prev', 'is-far-next');

                    if (diff === 0) card.classList.add('is-active');
                    else if (diff === 1) card.classList.add('is-next');
                    else if (diff === projectCards.length - 1) card.classList.add('is-prev');
                    else if (diff === 2) card.classList.add('is-far-next');
                    else if (diff === projectCards.length - 2) card.classList.add('is-far-prev');
                });

                if (projectFlyingTitle && activeTitle) {
                    const titleLines = {
                        'Industrial Attack Detection System': ['Industrial Attack', 'Detection System'],
                        'Endüstriyel Saldırı Tespit Sistemi': ['Endüstriyel Saldırı', 'Tespit Sistemi'],
                        'Go Product Management': ['Go Product', 'Management'],
                        'Smart Transportation Database': ['Smart Transportation', 'Database'],
                        'Go Ürün Yönetimi': ['Go Ürün', 'Yönetimi'],
                        'Akıllı Ulaşım Veritabanı': ['Akıllı Ulaşım', 'Veritabanı']
                    };
                    const lines = titleLines[activeTitle] || [activeTitle];

                    projectTitleTick += 1;
                    projectFlyingTitle.replaceChildren(...lines.map((line) => {
                        const span = document.createElement('span');
                        span.textContent = line;
                        return span;
                    }));
                    projectFlyingTitle.classList.toggle('has-two-lines', lines.length > 1);
                    projectFlyingTitle.classList.remove('is-flying');
                    projectFlyingTitle.style.animation = 'none';
                    void projectFlyingTitle.offsetWidth;
                    projectFlyingTitle.style.animation = '';
                    projectFlyingTitle.style.setProperty('--title-tilt', projectTitleTick % 2 ? '-2.2deg' : '2.2deg');
                    projectFlyingTitle.style.setProperty('--title-drift', projectTitleTick % 2 ? '28px' : '-28px');
                    projectFlyingTitle.classList.add('is-flying');
                }
            };

            const goProject = (direction) => {
                activeProject = wrapIndex(activeProject + direction);
                paintProjects();
            };

            projectPrev?.addEventListener('click', () => goProject(-1));
            projectNext?.addEventListener('click', () => goProject(1));
            projectCards.forEach((card, index) => {
                card.addEventListener('click', () => {
                    if (index !== activeProject) {
                        activeProject = index;
                        paintProjects();
                    }
                });
            });

            let dragStartX = 0;
            let dragCurrentX = 0;
            let isProjectDragging = false;
            const dragThreshold = 55;
            const getClientX = (event) => event.touches ? event.touches[0].clientX : event.clientX;
            const startProjectDrag = (event) => { isProjectDragging = true; dragStartX = getClientX(event); dragCurrentX = dragStartX; };
            const moveProjectDrag = (event) => { if (!isProjectDragging) return; dragCurrentX = getClientX(event); };
            const endProjectDrag = () => { if (!isProjectDragging) return; const diff = dragCurrentX - dragStartX; isProjectDragging = false; if (Math.abs(diff) < dragThreshold) return; goProject(diff < 0 ? 1 : -1); };
            projectSection?.addEventListener('mousedown', startProjectDrag);
            projectSection?.addEventListener('mousemove', moveProjectDrag);
            window.addEventListener('mouseup', endProjectDrag);
            projectSection?.addEventListener('touchstart', startProjectDrag, { passive: true });
            projectSection?.addEventListener('touchmove', moveProjectDrag, { passive: true });
            projectSection?.addEventListener('touchend', endProjectDrag);

            window.addEventListener('keydown', (event) => {
                if (!projectSection) return;
                const rect = projectSection.getBoundingClientRect();
                const projectsInView = rect.top < window.innerHeight * 0.55 && rect.bottom > window.innerHeight * 0.45;
                if (!projectsInView) return;
                if (event.key === 'ArrowLeft') goProject(-1);
                if (event.key === 'ArrowRight') goProject(1);
            });

            paintProjects();
            window.addEventListener('portfolio:language-applied', paintProjects);
        }
    }
});

// Premium navbar interactions: active section, language and dark/light persistence
(() => {
    // Ana sayfadaki Türkçe/İngilizce metinler merkezi çeviri sözlüğünde tutulur.
    const translations = {
        "en": {
                "navAbout": "About",
                "navSkills": "Skills",
                "navExperience": "Experience",
                "navProjects": "Projects",
                "navContact": "Contact",
                "navCv": "View CV",
                "language": "Language",
                "menuButton": "Menu",
                "menuButtonAria": "Open menu",
                "langEnglish": "English",
                "langTurkish": "Turkish",
                "heroEyebrow": "Computer Engineering × Software Engineering",
                "heroRole": "Computer Engineer",
                "heroFinalRole": "Full Stack Developer",
                "heroTypedRole": "Full Stack Developer",
                "heroCopy": "Full Stack Developer building intelligent, scalable and human-centered web systems.",
                "heroCta": "Explore Work",
                "focusLabel": "Currently focused on",
                "focusText": "AI-backed dashboards, backend services and clean product experiences.",
                "scroll": "Scroll",
                "aboutLabel": "01 / About",
                "aboutTitle": "I design systems that feel alive.",
                "aboutP1": "I am a double major student in Computer Engineering and Software Engineering at Haliç University. I focus on full-stack development, AI-powered applications and backend systems that connect data, APIs and interfaces into one smooth experience.",
                "aboutP2": "My work combines Python, Go, Vue.js, MySQL, Docker and RESTful APIs. I enjoy turning complex technical ideas into products people can understand and use.",
                "aboutScanProfile": "PROFILE / ABOUT",
                "aboutScanLive": "LIVE SCAN",
                "scanStatusTop": "AI / ML ACTIVE",
                "scanStatusBottom": "FULL STACK // READY",
                "intelTrackLabel": "TRACK:",
                "intelTrackValue": "DOUBLE_MAJOR",
                "intelFocusLabel": "FOCUS:",
                "intelFocusValue": "FULLSTACK_DEV",
                "intelLang1Label": "LANG_1:",
                "intelLang1Value": "TR (Native)",
                "intelLang2Label": "LANG_2:",
                "intelLang2Value": "EN (Fluent)",
                "systemStatusLabel": "SYSTEM_STATUS",
                "systemStatusTitle": "OPEN TO OPPORTUNITIES",
                "systemCollabs": "// COLLABS: ENABLED",
                "systemRemote": "[REMOTE_READY]",
                "skillsLabel": "02 / Skills",
                "skillsTitle": "A flexible engineering toolkit.",
                "skillsReset": "Reset",
                "projectsLabel": "04 — Projects",
                "projectsTitle": "Projects as cinematic scenes.",
                "projectsIntro": "Inspired by editorial motion layouts: every project has its own atmosphere, code name and technical story.",
                "projectsTagline": "<span class=\"tagline-main\">Projects that turn ideas into</span><span class=\"tagline-accent\">real, usable experiences.</span>",
                "projectSource": "⌁ source",
                "projectDemo": "demo →",
                "experienceLabel": "03 / Experience",
                "experienceTitle": "From academic<br>foundation to<br><em>production thinking.</em>",
                "expKindEducation": "Education",
                "expKindExperience": "Experience",
                "exp1Title": "Rotary 100. Yıl<br>Anatolian High School",
                "exp1Place": "High School Foundation",
                "exp1Copy": "Built the discipline, curiosity and academic base that led me into engineering and software development.",
                "exp1Tag1": "STEM",
                "exp1Tag2": "Problem Solving",
                "exp1Tag3": "Foundation",
                "exp2Title": "Computer Engineering",
                "halicUniversity": "Haliç University",
                "exp2Copy": "Focused on software fundamentals, algorithms, systems, databases and engineering thinking.",
                "exp2Tag1": "Algorithms",
                "exp2Tag2": "Systems",
                "exp2Tag3": "Engineering",
                "exp3Title": "Software Engineering<small>Double Major</small>",
                "exp3Copy": "Expanded my focus into software architecture, product thinking and full-stack application design.",
                "exp3Tag1": "Full-Stack",
                "exp3Tag2": "Architecture",
                "exp3Tag3": "Product Thinking",
                "exp4Period": "Jun — Aug 2025",
                "exp4Title": "IT Department Intern",
                "exp4Place": "Ensmart Technology · Istanbul",
                "exp4Bullet1": "Maintained internal software systems and developed new features with a full-stack approach.",
                "exp4Bullet2": "Worked on RESTful APIs and backend services with Python and Go.",
                "exp4Bullet3": "Used Docker, MySQL and Postman in day-to-day development workflows.",
                "contactLabel": "05 / Contact",
                "contactTitleA": "Let's",
                "contactTitleBuild": "Build",
                "contactTitleB": "Something.",
                "contactCopy": "Have a project idea, collaboration request, or just want to say hello? Leave a signal. I will answer within 24 hours.",
                "contactStatus": "Status",
                "contactAvailable": "Available",
                "contactEmailLabel": "Email",
                "contactLocationLabel": "Location",
                "contactPhoneLabel": "Phone",
                "contactResponseLabel": "Response time",
                "contactFreelance": "Freelance",
                "contactOpenWork": "OPEN TO WORK",
                "mailCaption": "Your message is ready to fly ✦",
                "mailSealed": "Sealed and sent ✦",
                "formName": "Name",
                "formEmail": "Email",
                "formSubject": "Subject",
                "formMessage": "Message",
                "sendMessage": "Send Message",
                "sending": "Sending...",
                "successMessage": "✓ Your message was sealed and sent. I will reply soon.",
                "errorMessage": "Something went wrong. Please try again.",
                "formNamePlaceholder": "Your name",
                "formEmailPlaceholder": "email@example.com",
                "formSubjectPlaceholder": "Project / Collaboration / Hello",
                "formMessagePlaceholder": "Tell me about your idea...",
                "errName": "Name must be at least 2 characters.",
                "errEmail": "Please enter a valid email.",
                "errSubject": "Subject must be at least 3 characters.",
                "errMessage": "Message must be at least 10 characters.",
                "footerRole": "Full Stack Developer",
                "footerInstagram": "Instagram",
                "footerEmail": "Email",
                "footerPhone": "Phone",
                "footerLetsTalk": "Let’s Talk",
                "footerBuiltWith": "Built with HTML, CSS, JavaScript, PHP & MySQL.",
                "footerTagline": "Designed to leave a mark.",
                "adminLabel": "ADMIN",
                "goHeroAria": "Go to hero section",
                "footerLogoAlt": "İrem Öztürk logo",
                "contactInfoAria": "Contact information",
                "projectsCarouselAria": "Featured projects carousel",
                "projectControlsAria": "Project carousel controls",
                "prevProjectAria": "Previous project",
                "nextProjectAria": "Next project",
                "skillAreaSuffix": "interactive skills area",
                "skillCategories": {
                        "Core Languages": "Core Languages",
                        "Backend Systems": "Backend Systems",
                        "Frontend Interface": "Frontend Interface",
                        "Database & Tools": "Database & Tools",
                        "AI / ML": "AI / ML"
                },
                "projectCards": [
                        {
                                "match": ["Industrial Attack Detection System", "Endüstriyel Saldırı Tespit Sistemi", "Industrial-Attack-Detection-System"],
                                "title": "Industrial Attack Detection System",
                                "code": "Industrial Attack Detection System",
                                "description": "A machine-learning based security system for detecting attacks in Industrial IoT environments. It uses four AI models to identify anomalies and possible cyber threats."
                        },
                        {
                                "match": ["Go Product Management", "Go Ürün Yönetimi", "GoProductManagement"],
                                "title": "Go Product Management",
                                "code": "Go Product Management",
                                "description": "A full-stack product management system built with a Go backend and Vue frontend, supporting product creation, listing and administration workflows."
                        },
                        {
                                "match": ["Smart Transportation Database", "Akıllı Ulaşım Veritabanı", "Smart_Transportation_Database"],
                                "title": "Smart Transportation Database",
                                "code": "Smart Transportation Database",
                                "description": "A database project designed for smart transportation systems, focused on managing vehicles, routes, stops, passengers and mobility data."
                        },
                        {
                                "match": ["Ilanpazar", "İlanpazar", "ILANPAZAR", "ilanpazar"],
                                "title": "Ilanpazar",
                                "code": "ILANPAZAR",
                                "description": "A Next.js marketplace web application where users can create, browse and manage listings through a modern interface."
                        },
                        {
                                "match": ["Shopora", "SHOPORA"],
                                "title": "Shopora",
                                "code": "SHOPORA",
                                "description": "A modern e-commerce style web application focused on product interfaces, user flows and a smooth shopping experience."
                        }
                ],
                "heroTechWord": "Backend"
        },
        "tr": {
                "navAbout": "Hakkımda",
                "navSkills": "Yetenekler",
                "navExperience": "Deneyim",
                "navProjects": "Projeler",
                "navContact": "İletişim",
                "navCv": "CV Görüntüle",
                "language": "Dil",
                "menuButton": "Menü",
                "menuButtonAria": "Menüyü aç",
                "langEnglish": "İngilizce",
                "langTurkish": "Türkçe",
                "heroEyebrow": "Bilgisayar Mühendisliği × Yazılım Mühendisliği",
                "heroRole": "Bilgisayar Mühendisi",
                "heroFinalRole": "Full Stack Geliştirici",
                "heroTypedRole": "Full Stack Geliştirici",
                "heroCopy": "Akıllı, ölçeklenebilir ve kullanıcı odaklı web sistemleri geliştiren full-stack geliştirici.",
                "heroCta": "Projeleri Gör",
                "focusLabel": "Şu anda odaklandığım alan",
                "focusText": "Yapay zekâ destekli paneller, backend servisleri ve temiz ürün deneyimleri.",
                "scroll": "Kaydır",
                "aboutLabel": "01 / Hakkımda",
                "aboutTitle": "Canlı hissettiren sistemler tasarlıyorum.",
                "aboutP1": "Haliç Üniversitesi’nde Bilgisayar Mühendisliği ve Yazılım Mühendisliği çift anadal öğrencisiyim. Full-stack geliştirme, yapay zekâ destekli uygulamalar ve veriyi, API’leri ve arayüzleri tek bir akıcı deneyime bağlayan backend sistemlerine odaklanıyorum.",
                "aboutP2": "Python, Go, Vue.js, MySQL, Docker ve RESTful API’lerle çalışıyorum. Karmaşık teknik fikirleri insanların anlayıp kullanabileceği ürünlere dönüştürmeyi seviyorum.",
                "aboutScanProfile": "PROFİL / HAKKIMDA",
                "aboutScanLive": "CANLI TARAMA",
                "scanStatusTop": "AI / ML AKTİF",
                "scanStatusBottom": "FULL STACK // HAZIR",
                "intelTrackLabel": "YOL:",
                "intelTrackValue": "ÇİFT_ANADAL",
                "intelFocusLabel": "ODAK:",
                "intelFocusValue": "FULLSTACK_DEV",
                "intelLang1Label": "DİL_1:",
                "intelLang1Value": "TR (Ana Dil)",
                "intelLang2Label": "DİL_2:",
                "intelLang2Value": "EN (Akıcı)",
                "systemStatusLabel": "SİSTEM_DURUMU",
                "systemStatusTitle": "FIRSATLARA AÇIK",
                "systemCollabs": "// İŞ BİRLİĞİ: AÇIK",
                "systemRemote": "[UZAKTAN_HAZIR]",
                "skillsLabel": "02 / Yetenekler",
                "skillsTitle": "Esnek bir mühendislik araç seti.",
                "skillsReset": "Sıfırla",
                "projectsLabel": "04 — Projeler",
                "projectsTitle": "Sinematik sahneler gibi projeler.",
                "projectsIntro": "Her projenin kendi atmosferi, kod adı ve teknik hikâyesi olan editoryal hareketli bir yapı.",
                "projectsTagline": "<span class=\"tagline-main\">Fikirleri gerçek, kullanılabilir</span><span class=\"tagline-accent\">deneyimlere dönüştüren projeler.</span>",
                "projectSource": "⌁ kaynak",
                "projectDemo": "demo →",
                "experienceLabel": "03 / Deneyim",
                "experienceTitle": "Akademik<br>temelden<br><em>üretim odaklı düşünmeye.</em>",
                "expKindEducation": "Eğitim",
                "expKindExperience": "Deneyim",
                "exp1Title": "Rotary 100. Yıl<br>Anadolu Lisesi",
                "exp1Place": "Lise Temeli",
                "exp1Copy": "Mühendislik ve yazılım geliştirmeye uzanan disiplin, merak ve akademik temeli burada oluşturdum.",
                "exp1Tag1": "STEM",
                "exp1Tag2": "Problem Çözme",
                "exp1Tag3": "Temel",
                "exp2Title": "Bilgisayar Mühendisliği",
                "halicUniversity": "Haliç Üniversitesi",
                "exp2Copy": "Yazılım temelleri, algoritmalar, sistemler, veritabanları ve mühendislik düşüncesi üzerine yoğunlaştım.",
                "exp2Tag1": "Algoritmalar",
                "exp2Tag2": "Sistemler",
                "exp2Tag3": "Mühendislik",
                "exp3Title": "Yazılım Mühendisliği<small>Çift Anadal</small>",
                "exp3Copy": "Yazılım mimarisi, ürün düşüncesi ve full-stack uygulama tasarımı alanlarında odağımı genişlettim.",
                "exp3Tag1": "Full-Stack",
                "exp3Tag2": "Mimari",
                "exp3Tag3": "Ürün Düşüncesi",
                "exp4Period": "Haz — Ağu 2025",
                "exp4Title": "BT Departmanı Stajyeri",
                "exp4Place": "Ensmart Teknoloji · İstanbul",
                "exp4Bullet1": "Full-stack yaklaşımla iç yazılım sistemlerinin bakımını yaptım ve yeni özellikler geliştirdim.",
                "exp4Bullet2": "Python ve Go ile RESTful API’ler ve backend servisleri üzerinde çalıştım.",
                "exp4Bullet3": "Günlük geliştirme süreçlerinde Docker, MySQL ve Postman kullandım.",
                "contactLabel": "05 / İletişim",
                "contactTitleA": "Birlikte",
                "contactTitleBuild": "İnşa Edelim",
                "contactTitleB": "Bir Şey.",
                "contactCopy": "Bir proje fikrin, iş birliği teklifin ya da sadece merhaba demek istediğin bir konu mu var? Sinyali bırak; 24 saat içinde döneceğim.",
                "contactStatus": "Durum",
                "contactAvailable": "Müsait",
                "contactEmailLabel": "E-posta",
                "contactLocationLabel": "Konum",
                "contactPhoneLabel": "Telefon",
                "contactResponseLabel": "Yanıt süresi",
                "contactFreelance": "Freelance",
                "contactOpenWork": "İŞE AÇIK",
                "mailCaption": "Mesajın uçuşa hazır ✦",
                "mailSealed": "Mühürlendi ve gönderildi ✦",
                "formName": "İsim",
                "formEmail": "E-posta",
                "formSubject": "Konu",
                "formMessage": "Mesaj",
                "sendMessage": "Mesaj Gönder",
                "sending": "Gönderiliyor...",
                "successMessage": "✓ Mesajın mühürlenip gönderildi. En kısa sürede dönüş yapacağım.",
                "errorMessage": "Bir hata oluştu. Lütfen tekrar dene.",
                "formNamePlaceholder": "Adın",
                "formEmailPlaceholder": "email@ornek.com",
                "formSubjectPlaceholder": "Proje / İş birliği / Merhaba",
                "formMessagePlaceholder": "Fikrinden biraz bahset...",
                "errName": "İsim en az 2 karakter olmalı.",
                "errEmail": "Geçerli bir e-posta gir.",
                "errSubject": "Konu en az 3 karakter olmalı.",
                "errMessage": "Mesaj en az 10 karakter olmalı.",
                "footerRole": "Full Stack Geliştirici",
                "footerInstagram": "Instagram",
                "footerEmail": "E-posta",
                "footerPhone": "Telefon",
                "footerLetsTalk": "Hadi Konuşalım",
                "footerBuiltWith": "HTML, CSS, JavaScript, PHP ve MySQL ile geliştirildi.",
                "footerTagline": "İz bırakmak için tasarlandı.",
                "adminLabel": "YÖNETİM",
                "goHeroAria": "Hero bölümüne git",
                "footerLogoAlt": "İrem Öztürk logosu",
                "contactInfoAria": "İletişim bilgileri",
                "projectsCarouselAria": "Öne çıkan projeler karuseli",
                "projectControlsAria": "Proje karuseli kontrolleri",
                "prevProjectAria": "Önceki proje",
                "nextProjectAria": "Sonraki proje",
                "skillAreaSuffix": "etkileşimli yetenek alanı",
                "skillCategories": {
                        "Core Languages": "Temel Diller",
                        "Backend Systems": "Backend Sistemleri",
                        "Frontend Interface": "Frontend Arayüz",
                        "Database & Tools": "Veritabanı ve Araçlar",
                        "AI / ML": "Yapay Zekâ / ML"
                },
                "projectCards": [
                        {
                                "match": ["Industrial Attack Detection System", "Endüstriyel Saldırı Tespit Sistemi", "Industrial-Attack-Detection-System"],
                                "title": "Endüstriyel Saldırı Tespit Sistemi",
                                "code": "Endüstriyel Saldırı Tespit Sistemi",
                                "description": "Industrial IoT ortamlarında saldırı tespiti yapan makine öğrenmesi tabanlı güvenlik sistemi. 4 farklı AI modeli ile anomali/saldırı tespiti amaçlanır."
                        },
                        {
                                "match": ["Go Product Management", "Go Ürün Yönetimi", "GoProductManagement"],
                                "title": "Go Ürün Yönetimi",
                                "code": "Go Ürün Yönetimi",
                                "description": "Go backend ve Vue frontend kullanılan ürün yönetim sistemi. Ürün ekleme, listeleme ve yönetme işlemleri için full-stack yapıdadır."
                        },
                        {
                                "match": ["Smart Transportation Database", "Akıllı Ulaşım Veritabanı", "Smart_Transportation_Database"],
                                "title": "Akıllı Ulaşım Veritabanı",
                                "code": "Akıllı Ulaşım Veritabanı",
                                "description": "Akıllı ulaşım sistemleri için tasarlanmış veritabanı projesi. Araç, rota, durak, yolcu ve ulaşım verilerini yönetmeye odaklanır."
                        },
                        {
                                "match": ["Ilanpazar", "İlanpazar", "ILANPAZAR", "ilanpazar"],
                                "title": "İlanpazar",
                                "code": "ILANPAZAR",
                                "description": "Kullanıcıların ilan oluşturup görüntüleyebildiği Next.js tabanlı ilan/pazar yeri web uygulaması."
                        },
                        {
                                "match": ["Shopora", "SHOPORA"],
                                "title": "Shopora",
                                "code": "SHOPORA",
                                "description": "Modern e-ticaret mantığında geliştirilmiş web uygulaması. Ürün arayüzü, kullanıcı işlemleri ve alışveriş deneyimi üzerine kuruludur."
                        }
                ],
                "heroTechWord": "Backend"
        }
};

    function normalizeProjectIdentity(value = '') {
        return String(value ?? '')
            .toLocaleLowerCase('tr-TR')
            .normalize('NFKD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/ı/g, 'i')
            .replace(/[^a-z0-9]+/g, '');
    }

    function resolveProjectTranslation(dictionary, projectIndex, candidates = []) {
        const projectCards = dictionary?.projectCards || [];
        const normalizedCandidates = candidates
            .filter(Boolean)
            .map(normalizeProjectIdentity)
            .filter(Boolean);

        const exactMatch = projectCards.find((projectCard) => {
            const aliases = [projectCard.title, projectCard.code, ...(projectCard.match || [])]
                .filter(Boolean)
                .map(normalizeProjectIdentity);
            return aliases.some((alias) => normalizedCandidates.includes(alias));
        });

        return exactMatch || projectCards?.[projectIndex] || null;
    }

    function getCookie(name) {
        return document.cookie.split('; ').find((row) => row.startsWith(`${name}=`))?.split('=')[1];
    }

    function setCookie(name, value) {
        document.cookie = `${name}=${value}; path=/; max-age=31536000`;
    }

    // Seçilen dile göre metinler, belge dili ve kayıtlı kullanıcı tercihi güncellenir.
    const applyLanguage = (lang) => {
        const dictionary = translations[lang] || translations.en;
        const hasKey = (key) => Object.prototype.hasOwnProperty.call(dictionary, key);

        document.documentElement.lang = lang === 'tr' ? 'tr' : 'en';
        document.title = lang === 'tr' ? 'İrem Öztürk | Full Stack Geliştirici' : 'İrem Öztürk | Full Stack Developer';

        document.querySelectorAll('[data-i18n]').forEach((element) => {
            const key = element.getAttribute('data-i18n');
            if (hasKey(key)) element.textContent = dictionary[key];
        });

        document.querySelectorAll('[data-i18n-html]').forEach((element) => {
            const key = element.getAttribute('data-i18n-html');
            if (hasKey(key)) element.innerHTML = dictionary[key];
        });

        document.querySelectorAll('[data-i18n-placeholder]').forEach((element) => {
            const key = element.getAttribute('data-i18n-placeholder');
            if (hasKey(key)) element.setAttribute('placeholder', dictionary[key]);
        });

        document.querySelectorAll('[data-i18n-aria-label]').forEach((element) => {
            const key = element.getAttribute('data-i18n-aria-label');
            if (hasKey(key)) element.setAttribute('aria-label', dictionary[key]);
        });

        document.querySelectorAll('[data-i18n-title]').forEach((element) => {
            const key = element.getAttribute('data-i18n-title');
            if (hasKey(key)) element.setAttribute('title', dictionary[key]);
        });

        document.querySelectorAll('[data-i18n-alt]').forEach((element) => {
            const key = element.getAttribute('data-i18n-alt');
            if (hasKey(key)) element.setAttribute('alt', dictionary[key]);
        });

        document.querySelectorAll('[data-cv-link]').forEach((link) => {
            const isTurkish = lang === 'tr';
            link.href = isTurkish ? 'assets/cv/irem-ozturk-cv-tr.pdf' : 'assets/cv/irem-ozturk-cv-en.pdf';
            link.setAttribute('aria-label', isTurkish ? 'Türkçe CV görüntüle' : 'View English CV');
            link.setAttribute('title', dictionary.navCv);
        });

        document.querySelectorAll('[data-skill-category]').forEach((heading) => {
            const originalCategory = heading.getAttribute('data-skill-category');
            heading.textContent = dictionary.skillCategories?.[originalCategory] || originalCategory;
        });

        document.querySelectorAll('[data-skill-area]').forEach((area) => {
            const originalCategory = area.getAttribute('data-skill-area');
            const categoryName = dictionary.skillCategories?.[originalCategory] || originalCategory;
            area.setAttribute('aria-label', `${categoryName} ${dictionary.skillAreaSuffix}`);
        });

        document.querySelectorAll('[data-project-card]').forEach((card, index) => {
            const projectIndex = Number.isFinite(Number(card.dataset.index)) ? Number(card.dataset.index) : index;
            const titleElement = card.querySelector('[data-project-title]');
            const codeElement = card.querySelector('[data-project-code]');
            const descriptionElement = card.querySelector('[data-project-description]');
            const translatedProject = resolveProjectTranslation(dictionary, projectIndex, [
                card.dataset.projectSourceTitle,
                card.dataset.projectSourceCode,
                card.dataset.projectGithub,
                titleElement?.textContent,
                codeElement?.textContent
            ]);

            if (translatedProject) {
                if (titleElement && translatedProject.title) titleElement.textContent = translatedProject.title;
                if (codeElement && translatedProject.code) codeElement.textContent = translatedProject.code;
                if (descriptionElement && translatedProject.description) descriptionElement.textContent = translatedProject.description;
            }

            const currentTitle = titleElement?.textContent?.trim() || (lang === 'tr' ? 'Proje' : 'Project');
            const image = card.querySelector('.project-card-image');
            const github = card.querySelector('.project-github-btn');

            if (image) {
                image.alt = lang === 'tr' ? `${currentTitle} önizlemesi` : `${currentTitle} preview`;
            }
            if (github) {
                github.setAttribute('aria-label', lang === 'tr'
                    ? `${currentTitle} projesini GitHub'da aç`
                    : `Open ${currentTitle} on GitHub`);
            }
        });

        document.querySelector('.lang-current')?.replaceChildren(document.createTextNode(lang.toUpperCase()));
        document.querySelectorAll('[data-lang-option]').forEach((option) => {
            option.classList.toggle('is-active', option.dataset.langOption === lang);
        });
        localStorage.setItem('portfolioLang', lang);
        setCookie('portfolio_lang', lang);
        window.portfolioI18n = dictionary;
        window.dispatchEvent(new CustomEvent('portfolio:language-applied', { detail: { lang } }));
    };

        const applyTheme = (theme) => {
        document.body.classList.toggle('light-mode', theme === 'light');
        localStorage.setItem('portfolioTheme', theme);
        setCookie('portfolio_theme', theme);
        // Swap hero video and about portrait for light / dark mode
        document.querySelectorAll('[data-theme-video]').forEach((themeVideo) => {
            const nextSrc = theme === 'light' ? themeVideo.dataset.lightSrc : themeVideo.dataset.darkSrc;
            const source = themeVideo.querySelector('source');
            if (!nextSrc || !source || source.getAttribute('src') === nextSrc) return;
            const wasPaused = themeVideo.paused;
            source.setAttribute('src', nextSrc);
            themeVideo.load();
            if (!wasPaused) {
                const playPromise = themeVideo.play();
                if (playPromise && typeof playPromise.catch === 'function') {
                    playPromise.catch(() => {});
                }
            }
        });

        const aboutImg = document.querySelector('.scan-portrait-frame img');
        if (aboutImg) {
            if (theme === 'light') {
                if (!aboutImg.dataset.darkSrc) aboutImg.dataset.darkSrc = aboutImg.src;
                aboutImg.src = aboutImg.src.replace(/about-irem(\.png)?$/, 'about-irem-light.png');
            } else {
                if (aboutImg.dataset.darkSrc) aboutImg.src = aboutImg.dataset.darkSrc;
            }
        }
    };

    const savedLang = localStorage.getItem('portfolioLang') || getCookie('portfolio_lang') || 'en';
    const savedTheme = localStorage.getItem('portfolioTheme') || getCookie('portfolio_theme') || 'dark';
    applyLanguage(savedLang);
    applyTheme(savedTheme);

    const languageControl = document.querySelector('[data-lang-dropdown]');
    const languageToggle = document.querySelector('[data-lang-toggle]');

    const langMenu = languageControl?.querySelector('.lang-menu');

    const closeLanguageMenu = () => {
        languageControl?.classList.remove('is-open');
        languageToggle?.setAttribute('aria-expanded', 'false');
        if (langMenu) {
            langMenu.style.opacity = '';
            langMenu.style.transform = '';
            langMenu.style.pointerEvents = '';
        }
    };

    languageToggle?.addEventListener('click', (event) => {
        event.stopPropagation();
        const willOpen = !languageControl?.classList.contains('is-open');

        if (!willOpen) {
            closeLanguageMenu();
            return;
        }

        languageControl?.classList.add('is-open');
        languageToggle.setAttribute('aria-expanded', 'true');
        if (willOpen && window.gsap && langMenu) {
            gsap.fromTo(langMenu, { y: -6, opacity: 0, scale: .98 }, { y: 0, opacity: 1, scale: 1, duration: .22, ease: 'power2.out' });
        }
    });

    document.querySelectorAll('[data-lang-option]').forEach((option) => {
        option.addEventListener('click', (event) => {
            event.stopPropagation();
            const next = option.dataset.langOption || 'en';
            applyLanguage(next);
            closeLanguageMenu();
            if (window.gsap) gsap.fromTo('.lang-current', { y: -12, opacity: 0 }, { y: 0, opacity: 1, duration: .35, ease: 'power3.out' });
        });
    });

    document.addEventListener('click', closeLanguageMenu);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeLanguageMenu();
    });

    let isThemeRevealRunning = false;

    // Tema butonu koyu/açık görünüm arasında geçiş yapar ve seçimi saklar.
    document.querySelector('[data-theme-toggle]')?.addEventListener('click', (event) => {
        if (isThemeRevealRunning) return;
        isThemeRevealRunning = true;

        const next = document.body.classList.contains('light-mode') ? 'dark' : 'light';
        const rect = event.currentTarget.getBoundingClientRect();
        const ox = `${(rect.left + rect.width / 2).toFixed(0)}px`;
        const oy = `${(rect.top + rect.height / 2).toFixed(0)}px`;

        const overlay = document.createElement('div');
        overlay.className = 'theme-reveal-overlay';
        overlay.style.background = next === 'light' ? '#f4efe8' : '#0b0b0f';
        overlay.style.clipPath = `circle(0% at ${ox} ${oy})`;
        document.body.appendChild(overlay);

        requestAnimationFrame(() => {
            overlay.style.clipPath = `circle(150% at ${ox} ${oy})`;
        });

        overlay.addEventListener('transitionend', () => {
            applyTheme(next);
            overlay.remove();
            isThemeRevealRunning = false;
        }, { once: true });
    });

    const skillsStage = document.querySelector('.skills-stage');
    const skillFloatItems = [...document.querySelectorAll('.skill-float-item')];

    if (skillsStage && skillFloatItems.length) {
        const resetSkillFloat = () => {
            skillFloatItems.forEach((item) => {
                item.style.setProperty('--mx', '0px');
                item.style.setProperty('--my', '0px');
            });
        };

        const updateSkillFloat = (event) => {
            if (window.innerWidth <= 980) {
                resetSkillFloat();
                return;
            }

            const rect = skillsStage.getBoundingClientRect();
            const px = ((event.clientX - rect.left) / rect.width - 0.5) * 2;
            const py = ((event.clientY - rect.top) / rect.height - 0.5) * 2;

            skillFloatItems.forEach((item, index) => {
                const depth = Number(item.dataset.depth || (10 + index * 2));
                item.style.setProperty('--mx', `${(-px * depth).toFixed(2)}px`);
                item.style.setProperty('--my', `${(-py * depth * 0.75).toFixed(2)}px`);
            });
        };

        skillsStage.addEventListener('pointermove', updateSkillFloat);
        skillsStage.addEventListener('pointerleave', resetSkillFloat);
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 980) resetSkillFloat();
        });
    }

    // Yetenek rozetleri için fizik hissi veren sürüklenebilir alanı hazırlar.
    const initSkillsPhysics = () => {
        const stage = document.querySelector('#skillsPhysicsScene');
        if (!stage || !window.Matter) return;

        if (typeof stage._cleanupPhysics === 'function') {
            stage._cleanupPhysics();
        }

        const {
            Engine,
            Runner,
            Bodies,
            Composite,
            Mouse,
            MouseConstraint,
            Events
        } = Matter;

        const width = stage.clientWidth;
        const height = stage.clientHeight;
        const worldPadding = 80;
        const items = [...stage.querySelectorAll('.physics-pill')];

        if (!items.length || width === 0 || height === 0) return;

        const engine = Engine.create();
        engine.gravity.y = 0.92;
        engine.gravity.x = 0;

        const runner = Runner.create();
        const world = engine.world;
        const rafState = { id: null };

        const walls = [
            Bodies.rectangle(width / 2, height + worldPadding / 2, width + worldPadding * 2, worldPadding, { isStatic: true, restitution: 0.15 }),
            Bodies.rectangle(-worldPadding / 2, height / 2, worldPadding, height * 2, { isStatic: true }),
            Bodies.rectangle(width + worldPadding / 2, height / 2, worldPadding, height * 2, { isStatic: true })
        ];
        Composite.add(world, walls);

        const paletteOffsets = [0.12, 0.38, 0.63, 0.79, 0.9];
        const bodies = items.map((item, index) => {
            const rect = item.getBoundingClientRect();
            const itemWidth = rect.width || item.offsetWidth || 120;
            const itemHeight = rect.height || item.offsetHeight || 60;
            const band = paletteOffsets[index % paletteOffsets.length];
            const jitter = (Math.random() - 0.5) * width * 0.16;
            const spawnX = Math.max(itemWidth / 2 + 8, Math.min(width - itemWidth / 2 - 8, width * band + jitter));
            const spawnY = -40 - Math.random() * (height * 0.3) - index * 36;
            const body = Bodies.rectangle(spawnX, spawnY, itemWidth, itemHeight, {
                restitution: 0.2,
                friction: 0.45,
                frictionStatic: 0.6,
                frictionAir: 0.015,
                density: item.dataset.pillSize === 'large' ? 0.0024 : 0.0019,
                chamfer: { radius: Math.min(itemHeight / 2, 40) }
            });
            body.__el = item;
            return body;
        });

        Composite.add(world, bodies);

        const mouse = Mouse.create(stage);
        mouse.pixelRatio = window.devicePixelRatio || 1;
        const mouseConstraint = MouseConstraint.create(engine, {
            mouse,
            constraint: {
                stiffness: 0.18,
                render: { visible: false }
            }
        });
        Composite.add(world, mouseConstraint);

        const renderDom = () => {
            bodies.forEach((body) => {
                const element = body.__el;
                if (!element) return;
                const x = body.position.x - element.offsetWidth / 2;
                const y = body.position.y - element.offsetHeight / 2;
                element.style.transform = `translate3d(${x}px, ${y}px, 0) rotate(${body.angle}rad)`;
            });
            rafState.id = requestAnimationFrame(renderDom);
        };

        Runner.run(runner, engine);
        renderDom();

        const onVisibility = () => {
            if (document.hidden) {
                Runner.stop(runner);
            } else {
                Runner.run(runner, engine);
            }
        };

        document.addEventListener('visibilitychange', onVisibility);

        stage._cleanupPhysics = () => {
            if (rafState.id) cancelAnimationFrame(rafState.id);
            document.removeEventListener('visibilitychange', onVisibility);
            Composite.clear(world, false);
            Engine.clear(engine);
            Runner.stop(runner);
            items.forEach((item) => {
                item.style.transform = '';
            });
        };
    };

    let skillsPhysicsResizeTimer;
    initSkillsPhysics();
    window.addEventListener('resize', () => {
        clearTimeout(skillsPhysicsResizeTimer);
        skillsPhysicsResizeTimer = window.setTimeout(initSkillsPhysics, 180);
    });

    const header = document.querySelector('.site-header');
    const navLinks = [...document.querySelectorAll('.main-nav a[href^="#"]')];
    const sections = navLinks.map((link) => document.querySelector(link.getAttribute('href'))).filter(Boolean);

    const updateNavState = () => {
        header?.classList.toggle('is-scrolled', window.scrollY > 24);
        let current = sections[0]?.id;
        sections.forEach((section) => {
            if (section.getBoundingClientRect().top < window.innerHeight * 0.42) current = section.id;
        });
        navLinks.forEach((link) => link.classList.toggle('is-active', link.getAttribute('href') === `#${current}`));
    };
    updateNavState();
    window.addEventListener('scroll', updateNavState, { passive: true });
})();


/* Separate physics engine for each skills card
   Drag + throw is handled manually instead of MouseConstraint, because
   CSS transforms, scroll position and card layout can make Matter's mouse
   mapping unreliable on this page. */
// Her yetenek panosunda Matter.js gövdeleri ve sınırları oluşturulur.
const initSingleSkillsPhysicsBoard = (stage) => {
    if (!stage || !window.Matter) return;

    const {
        Engine,
        Runner,
        Bodies,
        Body,
        Composite,
        Query,
        Events
    } = Matter;

    if (typeof stage._cleanupPhysics === 'function') {
        stage._cleanupPhysics();
    }

    const width = stage.clientWidth;
    const height = stage.clientHeight;
    const items = [...stage.querySelectorAll('.physics-pill')];
    if (!width || !height || !items.length) return;

    const engine = Engine.create();
    engine.gravity.y = 0.95;
    const runner = Runner.create();
    const world = engine.world;
    const wallSize = 80;
    // Skills may leave the card only through the TOP edge.
    // Left/right walls are tight so pills never vanish from the sides.
    const topEscapeMargin = 240;
    const rafState = { id: null };

    const walls = [
        // No ceiling: a fast upward throw can disappear above the card and fall back.
        // Side walls sit directly on the card edges, preventing side disappearance.
        Bodies.rectangle(width / 2, height + wallSize / 2, width + wallSize * 2, wallSize, { isStatic: true, restitution: 0.14 }),
        Bodies.rectangle(-wallSize / 2, height / 2, wallSize, height + topEscapeMargin * 2, { isStatic: true, restitution: 0.22 }),
        Bodies.rectangle(width + wallSize / 2, height / 2, wallSize, height + topEscapeMargin * 2, { isStatic: true, restitution: 0.22 })
    ];
    Composite.add(world, walls);

    const bodies = items.map((item, index) => {
        const rect = item.getBoundingClientRect();
        const itemWidth = rect.width || item.offsetWidth || 120;
        const itemHeight = rect.height || item.offsetHeight || 48;
        const cols = Math.max(2, Math.round(width / 112));
        const col = index % cols;
        const spawnX = Math.max(itemWidth / 2 + 6, Math.min(width - itemWidth / 2 - 6, (col + 0.5) * (width / cols)));
        const spawnY = -28 - Math.floor(index / cols) * 58 - (index % 3) * 8;
        const body = Bodies.rectangle(spawnX, spawnY, itemWidth, itemHeight, {
            restitution: 0.22,
            friction: 0.42,
            frictionStatic: 0.55,
            frictionAir: 0.012,
            density: item.dataset.pillSize === 'circle' ? 0.0016 : 0.002,
            chamfer: { radius: Math.min(itemHeight / 2, 28) }
        });
        body.__el = item;
        return body;
    });

    Composite.add(world, bodies);

    const cap = (value, min, max) => Math.max(min, Math.min(max, value));
    const toLocalPoint = (event) => {
        const rect = stage.getBoundingClientRect();
        return {
            x: (event.clientX - rect.left) * (width / Math.max(1, rect.width)),
            y: (event.clientY - rect.top) * (height / Math.max(1, rect.height))
        };
    };

    let draggedBody = null;
    let dragOffset = { x: 0, y: 0 };
    let lastPointer = { x: 0, y: 0, vx: 0, vy: 0, t: performance.now() };

    // Kullanıcı bir yetenek rozetini tuttuğunda ilgili fizik gövdesi seçilir.
    const onPointerDown = (event) => {
        const point = toLocalPoint(event);
        const hits = Query.point(bodies, point);
        if (!hits.length) return;

        event.preventDefault();
        draggedBody = hits[hits.length - 1];
        dragOffset = {
            x: draggedBody.position.x - point.x,
            y: draggedBody.position.y - point.y
        };
        lastPointer = { x: point.x, y: point.y, vx: 0, vy: 0, t: performance.now() };
        Body.setVelocity(draggedBody, { x: 0, y: 0 });
        Body.setAngularVelocity(draggedBody, 0);
        stage.classList.add('is-grabbing-skill');
        stage.setPointerCapture?.(event.pointerId);
    };

    const onPointerMove = (event) => {
        if (!draggedBody) return;
        event.preventDefault();
        const point = toLocalPoint(event);
        const now = performance.now();
        const dt = Math.max(16, now - lastPointer.t);
        const vx = ((point.x - lastPointer.x) / dt) * 16.67;
        const vy = ((point.y - lastPointer.y) / dt) * 16.67;
        const halfWidth = (draggedBody.bounds.max.x - draggedBody.bounds.min.x) / 2;
        const halfHeight = (draggedBody.bounds.max.y - draggedBody.bounds.min.y) / 2;
        const nextX = cap(point.x + dragOffset.x, halfWidth + 2, width - halfWidth - 2);
        const nextY = cap(point.y + dragOffset.y, -topEscapeMargin, height - halfHeight - 2);
        Body.setPosition(draggedBody, { x: nextX, y: nextY });
        Body.setVelocity(draggedBody, { x: 0, y: 0 });
        lastPointer = { x: point.x, y: point.y, vx, vy, t: now };
    };

    // Sürükleme bittiğinde rozetin hızı korunarak doğal bir bırakma etkisi verilir.
    const releaseDrag = (event) => {
        if (!draggedBody) return;
        event?.preventDefault?.();
        Body.setVelocity(draggedBody, {
            x: cap(lastPointer.vx, -22, 22),
            y: cap(lastPointer.vy, -36, 30)
        });
        Body.setAngularVelocity(draggedBody, cap(lastPointer.vx * 0.018, -0.35, 0.35));
        draggedBody = null;
        stage.classList.remove('is-grabbing-skill');
        if (event?.pointerId !== undefined) stage.releasePointerCapture?.(event.pointerId);
    };

    const rescueLostBodies = () => {
        bodies.forEach((body) => {
            if (body === draggedBody) return;
            const tooFarDown = body.position.y > height + 180;
            const tooFarUp = body.position.y < -topEscapeMargin - 180;
            const outsideSide = body.position.x < 24 || body.position.x > width - 24;

            if (outsideSide) {
                Body.setPosition(body, {
                    x: cap(body.position.x, 42, width - 42),
                    y: body.position.y
                });
                Body.setVelocity(body, {
                    x: cap(body.velocity.x * -0.18, -4, 4),
                    y: body.velocity.y
                });
            }

            if (tooFarDown || tooFarUp) {
                Body.setPosition(body, {
                    x: cap(body.position.x, 42, width - 42),
                    y: -72
                });
                Body.setVelocity(body, {
                    x: cap(body.velocity.x * 0.12, -5, 5),
                    y: 5
                });
                Body.setAngularVelocity(body, cap(body.angularVelocity * 0.25, -0.12, 0.12));
            }
        });
    };
    Events.on(engine, 'afterUpdate', rescueLostBodies);

    const renderDom = () => {
        bodies.forEach((body) => {
            const element = body.__el;
            if (!element) return;
            const x = body.position.x - element.offsetWidth / 2;
            const y = body.position.y - element.offsetHeight / 2;
            element.style.transform = `translate3d(${x}px, ${y}px, 0) rotate(${body.angle}rad)`;
        });
        rafState.id = requestAnimationFrame(renderDom);
    };

    stage.addEventListener('pointerdown', onPointerDown, { passive: false });
    stage.addEventListener('pointermove', onPointerMove, { passive: false });
    stage.addEventListener('pointerup', releaseDrag, { passive: false });
    stage.addEventListener('pointercancel', releaseDrag, { passive: false });
    stage.addEventListener('lostpointercapture', releaseDrag);

    Runner.run(runner, engine);
    renderDom();

    const resetButton = stage.closest('.skills-card')?.querySelector('[data-skills-reset]');
    const resetHandler = () => setTimeout(() => initSingleSkillsPhysicsBoard(stage), 10);
    resetButton?.addEventListener('click', resetHandler);

    stage._cleanupPhysics = () => {
        if (rafState.id) cancelAnimationFrame(rafState.id);
        resetButton?.removeEventListener('click', resetHandler);
        stage.removeEventListener('pointerdown', onPointerDown);
        stage.removeEventListener('pointermove', onPointerMove);
        stage.removeEventListener('pointerup', releaseDrag);
        stage.removeEventListener('pointercancel', releaseDrag);
        stage.removeEventListener('lostpointercapture', releaseDrag);
        Events.off(engine, 'afterUpdate', rescueLostBodies);
        stage.classList.remove('is-grabbing-skill');
        Composite.clear(world, false);
        Engine.clear(engine);
        Runner.stop(runner);
        items.forEach((item) => { item.style.transform = ''; });
    };
};

const initSkillsPhysicsBoards = () => {
    document.querySelectorAll('[data-skills-scene]').forEach((stage) => {
        initSingleSkillsPhysicsBoard(stage);
    });
};

window.__skillsBoardsResizeTimer && clearTimeout(window.__skillsBoardsResizeTimer);
initSkillsPhysicsBoards();
window.addEventListener('resize', () => {
    clearTimeout(window.__skillsBoardsResizeTimer);
    window.__skillsBoardsResizeTimer = setTimeout(initSkillsPhysicsBoards, 180);
});

/* Experience: scroll-driven timeline with wave transitions */
(function () {
    /* ── Intersection Observer: fade-up cards as they enter viewport ── */
    // Deneyim zaman çizelgesindeki aktif kart ve ilerleme göstergesi hesaplanır.
    const initExpTimeline = () => {
        const items = document.querySelectorAll('.exp-tl-item');
        const lineFill = document.getElementById('expTlLineFill');
        const progressFill = document.getElementById('expProgressFill');
        if (!items.length) return;

        const visibleItems = new Set();

        const updateProgress = () => {
            const total = items.length;
            const count = visibleItems.size;
            const pct = total > 0 ? Math.round((count / total) * 100) : 0;
            if (lineFill) lineFill.style.height = pct + '%';
            if (progressFill) progressFill.style.width = pct + '%';
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    visibleItems.add(entry.target);
                    updateProgress();
                }
            });
        }, { threshold: 0.18, rootMargin: '0px 0px -60px 0px' });

        items.forEach((item) => observer.observe(item));
    };

    /* ── GSAP: header parallax + card hover tilt (desktop only) ── */
    // Deneyim kartlarına kaydırma ile tetiklenen GSAP animasyonları bağlanır.
    const initExpGsap = () => {
        if (!window.gsap || !window.ScrollTrigger) return;

        const section = document.getElementById('experience');
        const header  = document.getElementById('expHeader');
        if (!section || !header) return;

        /* Subtle header parallax — header scrolls slightly slower */
        gsap.to(header, {
            yPercent: -14,
            ease: 'none',
            scrollTrigger: {
                trigger: section,
                start: 'top bottom',
                end: 'bottom top',
                scrub: true,
                invalidateOnRefresh: true,
            }
        });

        /* Orbs parallax */
        const orbs = section.querySelectorAll('.exp-orb');
        orbs.forEach((orb, i) => {
            const dir = i % 2 === 0 ? -18 : 18;
            gsap.to(orb, {
                y: dir,
                ease: 'none',
                scrollTrigger: {
                    trigger: section,
                    start: 'top bottom',
                    end: 'bottom top',
                    scrub: true,
                }
            });
        });
    };

    /* ── Mouse tilt on cards (desktop) ── */
    // Masaüstünde deneyim kartlarına fare konumuna göre hafif 3D eğim verir.
    const initCardTilt = () => {
        if (window.matchMedia('(max-width: 860px)').matches) return;

        document.querySelectorAll('.exp-tl-card').forEach((card) => {
            card.addEventListener('mousemove', (e) => {
                const r = card.getBoundingClientRect();
                const cx = r.left + r.width  / 2;
                const cy = r.top  + r.height / 2;
                const dx = (e.clientX - cx) / (r.width  / 2);
                const dy = (e.clientY - cy) / (r.height / 2);
                card.style.transform =
                    `perspective(900px) rotateY(${dx * 3.5}deg) rotateX(${-dy * 2.5}deg) translateY(-5px) translateX(3px)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });
    };

    /* ── Boot ── */
    const boot = () => {
        initExpTimeline();
        initCardTilt();
        if (window.gsap && window.ScrollTrigger) {
            initExpGsap();
        } else {
            window.addEventListener('gsap:ready', initExpGsap, { once: true });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();


/* ══════════════════════════════════════
   HERO LIVE VIDEO — full hero background with hero-only sound
══════════════════════════════════════ */
(function () {
    const hero = document.getElementById('hero');
    const video = document.getElementById('hero-live-video');

    if (!hero || !video) return;

    let heroVisible = false;
    let soundUnlocked = false;

    video.loop = true;
    video.muted = true;
    video.volume = 0;
    video.playsInline = true;
    video.preload = 'auto';

    // Hero videosunu tarayıcı izinlerine uygun şekilde oynatmayı dener.
    const playVideo = () => {
        const playPromise = video.play();
        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(() => {
                video.muted = true;
                video.volume = 0;
            });
        }
    };

    const soundOff = () => {
        video.muted = true;
        video.volume = 0;
    };

    const soundOn = () => {
        video.muted = false;
        video.volume = 1;
        const playPromise = video.play();

        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(() => {
                soundOff();
                playVideo();
            });
        }
    };

    // Hero görünürlük durumuna göre videonun oynatılıp oynatılmayacağını belirler.
    const updatePlayback = () => {
        if (heroVisible) {
            if (soundUnlocked) {
                soundOn();
            } else {
                soundOff();
                playVideo();
            }
        } else {
            soundOff();
            video.pause();
        }
    };

    const unlockSound = () => {
        soundUnlocked = true;
        updatePlayback();
    };

    ['pointerdown', 'touchstart', 'keydown', 'wheel'].forEach((eventName) => {
        document.addEventListener(eventName, unlockSound, { once: true, passive: true });
    });

    video.addEventListener('pause', () => {
        if (heroVisible) {
            playVideo();
        }
    });

    const observer = new IntersectionObserver((entries) => {
        const entry = entries[0];
        heroVisible = Boolean(entry?.isIntersecting && entry.intersectionRatio > 0.08);
        hero.classList.toggle('is-live-video-active', heroVisible);
        updatePlayback();
    }, { threshold: [0, 0.08, 0.18, 0.35, 0.6, 1] });

    observer.observe(hero);
    playVideo();
})();



// Footer etkileşimleri: bağlantılarda yumuşak kaydırma ve küçük hareket efektleri.
// Footer interactions: smooth contact scroll and playful hover motion
document.addEventListener('DOMContentLoaded', () => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.querySelectorAll('.footer-cta[href^="#"], .footer-social-link[href^="#"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const targetId = link.getAttribute('href');
            if (!targetId || targetId === '#') return;
            const target = document.querySelector(targetId);
            if (!target) return;
            event.preventDefault();
            target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
        });
    });

    const typedTarget = document.querySelector('[data-footer-typed]');
    const typedPhrase = 'Computer & Software Engineer';
    if (typedTarget) {
        typedTarget.textContent = typedPhrase;
        if (!reduceMotion) {
            const blurLoop = () => {
                typedTarget.classList.add('is-blurring');
                window.setTimeout(() => {
                    typedTarget.textContent = typedPhrase;
                    typedTarget.classList.remove('is-blurring');
                    window.setTimeout(blurLoop, 2200);
                }, 650);
            };
            window.setTimeout(blurLoop, 2200);
        }
    }

    if (!reduceMotion && window.gsap) {
        gsap.from('.site-footer', {
            y: 54,
            opacity: 0,
            duration: .9,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: '.site-footer',
                start: 'top bottom-=60'
            }
        });

        gsap.from('.footer-social-link, .footer-cta', {
            y: 20,
            opacity: 0,
            duration: .62,
            stagger: .06,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: '.site-footer',
                start: 'top bottom-=40'
            }
        });
    }
});

// Markalı özel kaydırma çubuğu: ilerleme, tıklayarak kaydırma ve sürükleme desteği.
// Branded custom scrollbar: progress, click-to-scroll and drag-to-scroll
(() => {
    const root = document.documentElement;
    const scrollOrbit = document.querySelector('.scroll-orbit');
    const track = scrollOrbit?.querySelector('.scroll-orbit-track');
    const thumb = scrollOrbit?.querySelector('.scroll-orbit-thumb');

    if (!scrollOrbit || !track || !thumb) return;

    let ticking = false;
    let isDragging = false;

    const clamp = (value, min = 0, max = 1) => Math.min(max, Math.max(min, value));

    const getMaxScroll = () => Math.max(1, root.scrollHeight - window.innerHeight);

    const setProgress = (progress) => {
        const safeProgress = clamp(progress);
        root.style.setProperty('--scroll-progress', safeProgress.toFixed(4));
        scrollOrbit.classList.toggle('is-complete', safeProgress > .985);
    };

    const updateFromScroll = () => {
        setProgress(window.scrollY / getMaxScroll());
        ticking = false;
    };

    const requestUpdate = () => {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(updateFromScroll);
    };

    // Kullanıcının tıkladığı veya sürüklediği konuma göre sayfa kaydırma oranı hesaplanır.
    const scrollToPointer = (clientY) => {
        const rect = track.getBoundingClientRect();
        const thumbHeight = thumb.offsetHeight || 30;
        const usableHeight = Math.max(1, rect.height - thumbHeight);
        const y = clientY - rect.top - thumbHeight / 2;
        const progress = clamp(y / usableHeight);
        window.scrollTo({ top: getMaxScroll() * progress, behavior: 'auto' });
        setProgress(progress);
    };

    track.addEventListener('pointerdown', (event) => {
        if (event.button !== undefined && event.button !== 0) return;
        event.preventDefault();
        isDragging = true;
        scrollOrbit.classList.add('is-dragging');
        scrollOrbit.setPointerCapture?.(event.pointerId);
        scrollToPointer(event.clientY);
    });

    scrollOrbit.addEventListener('pointermove', (event) => {
        if (!isDragging) return;
        event.preventDefault();
        scrollToPointer(event.clientY);
    });

    const stopDragging = (event) => {
        if (!isDragging) return;
        isDragging = false;
        scrollOrbit.classList.remove('is-dragging');
        scrollOrbit.releasePointerCapture?.(event.pointerId);
    };

    scrollOrbit.addEventListener('pointerup', stopDragging);
    scrollOrbit.addEventListener('pointercancel', stopDragging);
    scrollOrbit.addEventListener('lostpointercapture', () => {
        isDragging = false;
        scrollOrbit.classList.remove('is-dragging');
    });

    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', requestUpdate);
    window.addEventListener('load', requestUpdate);
    updateFromScroll();
})();

/* Proje açıklamalarındaki kelimeler alt çizgi animasyonu için ayrı span elemanlarına ayrılır. */
/* === Description underline sweep word wrapper === */
(() => {
    const descriptions = document.querySelectorAll('[data-project-description]');

    descriptions.forEach((description) => {
        if (description.dataset.wordsWrapped === 'true') return;

        const text = description.textContent.trim().replace(/\s+/g, ' ');
        if (!text) return;

        description.textContent = '';
        const words = text.split(' ');

        words.forEach((word, index) => {
            const wordSpan = document.createElement('span');
            wordSpan.className = 'project-description-word';
            wordSpan.textContent = word;
            description.appendChild(wordSpan);

            if (index < words.length - 1) {
                description.appendChild(document.createTextNode(' '));
            }
        });

        description.dataset.wordsWrapped = 'true';
    });
})();

/* İletişim başlığına gelindiğinde harfleri kısa süreli karıştıran hover efekti. */
/* === Contact title scramble hover effect === */
(() => {
    const title = document.querySelector('.contact-title');
    if (!title || title.dataset.scrambleReady === 'true') return;

    const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789#@$%&*+-<>[]{}';
    const targets = Array.from(title.querySelectorAll('span'));
    if (!targets.length) return;

    title.dataset.scrambleReady = 'true';
    title.setAttribute('tabindex', '0');
    title.setAttribute('role', 'button');

    let isRunning = false;
    let frame = 0;
    let rafId = null;

    const randomChar = () => letters[Math.floor(Math.random() * letters.length)];

    const scramble = () => {
        if (isRunning) return;
        isRunning = true;
        frame = 0;
        title.classList.add('is-scrambling');

        const originals = targets.map((span) => span.textContent);
        const maxLength = Math.max(...originals.map((text) => text.length));
        const totalFrames = 34;

        const tick = () => {
            frame += 1;
            const progress = frame / totalFrames;

            targets.forEach((span, spanIndex) => {
                const original = originals[spanIndex];
                const revealCount = Math.floor(progress * (original.length + 1));

                span.textContent = original
                    .split('')
                    .map((char, charIndex) => {
                        if (char === ' ' || char === '.' || char === "'" || char === '’') return char;
                        return charIndex < revealCount ? char : randomChar();
                    })
                    .join('');
            });

            if (frame < totalFrames) {
                rafId = window.requestAnimationFrame(tick);
                return;
            }

            targets.forEach((span, index) => { span.textContent = originals[index]; });
            title.classList.remove('is-scrambling');
            isRunning = false;
            rafId = null;
        };

        if (rafId) window.cancelAnimationFrame(rafId);
        tick();
    };

    title.addEventListener('pointerenter', scramble);
    title.addEventListener('focus', scramble);
})();

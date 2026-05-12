// Bu dosya: Admin giriş ekranındaki saat, parola göstergesi, hata ve geçiş animasyonlarını yönetir.
// Giriş ekranındaki tüm dinamik kontroller DOM hazır olduğunda bağlanır.
document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const clock = document.getElementById('clock');
    const cursorGlow = document.getElementById('cursorGlow');
    const passwordInput = document.getElementById('password');
    const togglePass = document.getElementById('togglePass');
    const strengthFill = document.getElementById('strengthFill');
    const strengthPct = document.getElementById('strengthPct');
    const rememberInput = document.getElementById('rememberInput');
    const checkBox = document.getElementById('checkBox');
    const form = document.getElementById('ownerLoginForm');
    const errMsg = document.getElementById('errMsg');
    const btn = document.getElementById('loginBtn');
    const btnText = btn?.querySelector('span');
    const radarStatus = document.getElementById('radarStatus');
    const connStatus = document.getElementById('connStatus');
    const dashboardDrawer = document.getElementById('dashboardDrawer');

    // Dil tercihini okuyabilmek için basit çerez çözümleyici kullanılır.
    const getCookie = (name) => document.cookie
        .split('; ')
        .find((row) => row.startsWith(`${name}=`))
        ?.split('=')[1];

    const currentLang = (() => {
        const saved = localStorage.getItem('portfolioLang') || getCookie('portfolio_lang') || window.ADMIN_LOGIN_LANG || document.documentElement.lang || 'en';
        return saved === 'tr' ? 'tr' : 'en';
    })();

    document.documentElement.lang = currentLang;

    // Giriş ekranı metinlerinin Türkçe ve İngilizce karşılıkları burada tutulur.
    const i18n = {
        tr: {
            showPassword: 'Şifreyi göster',
            hidePassword: 'Şifreyi gizle',
            waiting: 'ŞİFRE BEKLENİYOR • RADAR TARANIYOR...',
            signal: 'SİNYAL ALINDI • ANALİZ EDİLİYOR...',
            crypto: 'KRİPTO AKIŞ ALGILANDI • DOĞRULANIYOR...',
            strong: 'GÜÇLÜ SİNYAL • GİRİŞE HAZIR',
            accessDenied: 'ERİŞİM REDDEDİLDİ • İHLAL GÜNLÜĞE KAYDEDİLDİ',
            auditMode: 'BAĞLANTI: DENETİM MODU',
            missing: '⚠ KİMLİK BİLGİLERİ EKSİK — GİRİŞ REDDEDİLDİ',
            missingRadar: 'EKSİK VERİ • KİMLİK DOĞRULAMA DURDURULDU',
            verifying: 'DOĞRULANIYOR...',
            solving: 'KRİPTO PROTOKOLÜ ÇÖZÜLÜYOR • LÜTFEN BEKLEYİN...',
            successRadar: 'BAŞARILI • OWNER VERIFIED • PANEL AÇILIYOR...',
            ownerMode: 'BAĞLANTI: OWNER MODE',
            accessApproved: 'ERİŞİM ONAYLANDI',
            loading: 'YÜKLENİYOR...'
        },
        en: {
            showPassword: 'Show password',
            hidePassword: 'Hide password',
            waiting: 'WAITING FOR PASSWORD • RADAR SCANNING...',
            signal: 'SIGNAL RECEIVED • ANALYZING...',
            crypto: 'CRYPTO STREAM DETECTED • VERIFYING...',
            strong: 'STRONG SIGNAL • READY TO LOGIN',
            accessDenied: 'ACCESS DENIED • INCIDENT LOGGED',
            auditMode: 'CONNECTION: AUDIT MODE',
            missing: '⚠ CREDENTIALS MISSING — LOGIN DENIED',
            missingRadar: 'MISSING DATA • AUTHENTICATION PAUSED',
            verifying: 'VERIFYING...',
            solving: 'DECRYPTING CRYPTO PROTOCOL • PLEASE WAIT...',
            successRadar: 'SUCCESSFUL • OWNER VERIFIED • OPENING PANEL...',
            ownerMode: 'CONNECTION: OWNER MODE',
            accessApproved: 'ACCESS APPROVED',
            loading: 'LOADING...'
        }
    };

    const t = i18n[currentLang];

    // Sağ üstteki canlı saat ve tarih bilgisini her saniye yeniler.
    const updateClock = () => {
        if (!clock) return;
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        clock.textContent = `${h}:${m}:${s}`;
    };
    updateClock();
    setInterval(updateClock, 1000);

    document.addEventListener('mousemove', (event) => {
        if (!cursorGlow) return;
        cursorGlow.style.left = `${event.clientX}px`;
        cursorGlow.style.top = `${event.clientY}px`;
    });

    if (togglePass && passwordInput) {
        togglePass.setAttribute('aria-label', t.showPassword);
        togglePass.addEventListener('click', () => {
            const hidden = passwordInput.type === 'password';
            passwordInput.type = hidden ? 'text' : 'password';
            togglePass.classList.toggle('is-visible', hidden);
            togglePass.setAttribute('aria-label', hidden ? t.hidePassword : t.showPassword);
        });
    }

    // Girilen parolanın uzunluk ve karakter çeşitliliğine göre gücünü hesaplar.
    const passwordStrength = (value) => {
        let strength = 0;
        if (value.length > 0) strength += Math.min(55, value.length * 7);
        if (/[A-ZÇĞİÖŞÜ]/.test(value)) strength += 10;
        if (/[a-zçğıöşü]/.test(value)) strength += 8;
        if (/[0-9]/.test(value)) strength += 12;
        if (/[^A-Za-z0-9ÇĞİÖŞÜçğıöşü]/.test(value)) strength += 15;
        return Math.min(100, strength);
    };

    // Parola güç çubuğunu ve yüzde metnini güncel parola değerine göre çizer.
    const renderStrength = () => {
        if (!passwordInput || !strengthFill || !strengthPct) return;
        const strength = passwordStrength(passwordInput.value);
        strengthFill.style.width = `${strength}%`;
        strengthPct.textContent = `${strength}%`;

        const statuses = [t.waiting, t.signal, t.crypto, t.strong];
        const index = strength === 0 ? 0 : strength < 40 ? 1 : strength < 70 ? 2 : 3;
        if (radarStatus && !window.ADMIN_LOGIN_SUCCESS) radarStatus.textContent = statuses[index];
    };

    passwordInput?.addEventListener('input', renderStrength);
    renderStrength();

    rememberInput?.addEventListener('change', () => {
        checkBox?.classList.toggle('active', rememberInput.checked);
    });


    // If the browser restores this page from cache after a failed login/back navigation,
    // force the normal red login state and keep the green success/loading layer hidden.
    window.addEventListener('pageshow', () => {
        if (!window.ADMIN_LOGIN_SUCCESS) {
            body.classList.remove('is-unlocking');
            dashboardDrawer?.classList.remove('is-visible', 'is-sweeping');
            dashboardDrawer?.setAttribute('aria-hidden', 'true');
            btn?.classList.remove('loading');
        }
    });

    if (body.dataset.loginError === '1') {
        body.classList.remove('is-unlocking');
        body.classList.add('has-login-error');
        btn?.classList.remove('loading');
        form?.classList.remove('shake');
        if (btnText) btnText.textContent = window.ADMIN_LOGIN_LANG === 'tr' ? 'GİRİŞİ BAŞLAT' : 'START LOGIN';
        if (radarStatus) radarStatus.textContent = t.accessDenied;
        if (connStatus) connStatus.textContent = t.auditMode;
        if (errMsg && errMsg.textContent.trim()) {
            errMsg.style.display = 'block';
        }
        // Server-side failed login: keep original layout, just make sure the form is visible.
        if (form) {
            form.style.opacity = '1';
            form.style.visibility = 'visible';
        }
    }

    form?.addEventListener('submit', (event) => {
        const user = form.username?.value.trim() || '';
        const pass = form.password?.value || '';

        if (!user || !pass) {
            event.preventDefault();
            if (errMsg) errMsg.textContent = t.missing;
            form.classList.remove('shake');
            void form.offsetWidth;
            form.classList.add('shake');
            if (radarStatus) radarStatus.textContent = t.missingRadar;
            return;
        }

        if (errMsg) errMsg.textContent = '';
        btn?.classList.add('loading');
        if (btnText) btnText.textContent = t.verifying;
        if (radarStatus) radarStatus.textContent = t.solving;
    });

    // Başarılı giriş sonrası animasyon bitince dashboard sayfasına yönlendirilir.
    if (window.ADMIN_LOGIN_SUCCESS) {
        body.classList.add('is-unlocking');
        if (radarStatus) radarStatus.textContent = t.successRadar;
        if (connStatus) connStatus.textContent = t.ownerMode;
        if (btnText) btnText.textContent = t.accessApproved;
        btn?.classList.remove('loading');

        const startBootLoader = () => {
            const fill = document.getElementById('bootProgressFill');
            const text = document.getElementById('bootProgressText');
            const status = document.getElementById('bootStatusText');
            const progressbar = document.querySelector('.boot-loader');
            if (!fill || !text) {
                window.location.href = 'dashboard.php';
                return;
            }

            const labels = [t.loading, t.loading, t.loading, t.loading, t.loading];
            const duration = 6000;
            const startedAt = performance.now();

            const tick = (now) => {
                const raw = Math.min(1, (now - startedAt) / duration);
                const eased = 1 - Math.pow(1 - raw, 3);
                const pct = Math.min(100, Math.round(eased * 100));
                fill.style.width = `${pct}%`;
                text.textContent = `${pct}%`;
                progressbar?.setAttribute('aria-valuenow', String(pct));
                if (status) status.textContent = labels[Math.min(labels.length - 1, Math.floor(pct / 25))];
                if (raw < 1) {
                    requestAnimationFrame(tick);
                } else {
                    dashboardDrawer?.classList.add('is-sweeping');
                    setTimeout(() => { window.location.href = 'dashboard.php'; }, 950);
                }
            };
            requestAnimationFrame(tick);
        };

        setTimeout(() => {
            dashboardDrawer?.classList.add('is-visible');
            dashboardDrawer?.setAttribute('aria-hidden', 'false');
            startBootLoader();
        }, 1300);
    }
});

// Bu dosya: İletişim formunun istemci tarafı doğrulamasını, animasyonlarını ve AJAX gönderimini yönetir.
// İletişim formu hazır olduğunda alanlar, hata kutuları ve buton referansları alınır.
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('contactForm');
    if (!form) return;

    const nameEl = document.getElementById('cf-name');
    const emailEl = document.getElementById('cf-email');
    const subjectEl = document.getElementById('cf-subject');
    const msgEl = document.getElementById('cf-msg');
    const countEl = document.getElementById('cf-count');
    const btn = document.getElementById('cf-btn');
    const btnTxt = document.getElementById('cf-btn-text');
    const arrow = document.getElementById('cf-arrow');
    const success = document.getElementById('cf-success');
    const errBox = document.getElementById('cf-server-err');
    const topics = document.querySelectorAll('.cf-topic');
    const formShell = document.querySelector('.contact-form-shell');
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const t = (key, fallback) => window.portfolioI18n?.[key] || fallback;

    const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

    // Başarılı gönderim öncesi zarfın gönderilip geri dönmesini canlandırır.
    const playMailAnimation = async () => {
        if (!formShell) return;

        const caption = document.getElementById('mail-caption-text');
        if (caption) {
            caption.textContent = t('mailSealed', document.documentElement.lang === 'tr' ? 'Mühürlendi ve gönderildi ✦' : 'Sealed and sent ✦');
        }

        formShell.classList.remove('is-mailing', 'is-returning', 'is-stamping');
        void formShell.offsetWidth;
        formShell.classList.add('is-mailing');

        await wait(2120);
        formShell.classList.add('is-stamping');
        await wait(380);
        formShell.classList.remove('is-stamping');

        await wait(4200 - 2120 - 380);

        formShell.classList.remove('is-mailing');
        void formShell.offsetWidth;
        formShell.classList.add('is-returning');
        await wait(650);
        formShell.classList.remove('is-returning');
    };

    const setVisible = (element, visible) => {
        if (!element) return;
        element.style.display = visible ? 'block' : 'none';
    };

    const showErr = (el, id) => {
        el.classList.add('cf-has-error');
        setVisible(document.getElementById(id), true);
    };

    const clearErr = (el, id) => {
        el.classList.remove('cf-has-error');
        setVisible(document.getElementById(id), false);
    };

    // Tek bir form alanının geçerli olup olmadığını kontrol eder ve hata görünümünü günceller.
    const validateField = (el) => {
        const value = el.value.trim();

        if (el === nameEl) {
            value.length >= 2 ? clearErr(el, 'err-name') : showErr(el, 'err-name');
            return value.length >= 2;
        }

        if (el === emailEl) {
            emailPattern.test(value) ? clearErr(el, 'err-email') : showErr(el, 'err-email');
            return emailPattern.test(value);
        }

        if (el === subjectEl) {
            value.length >= 3 ? clearErr(el, 'err-subject') : showErr(el, 'err-subject');
            return value.length >= 3;
        }

        if (el === msgEl) {
            value.length >= 10 ? clearErr(el, 'err-msg') : showErr(el, 'err-msg');
            return value.length >= 10;
        }

        return true;
    };

    [nameEl, emailEl, subjectEl, msgEl].forEach((el) => {
        el.addEventListener('blur', () => validateField(el));
        el.addEventListener('input', () => {
            if (el.classList.contains('cf-has-error')) validateField(el);
        });
    });

    msgEl.addEventListener('input', () => {
        countEl.textContent = msgEl.value.length;
    });

    topics.forEach((topic) => {
        topic.addEventListener('click', () => {
            subjectEl.value = topic.dataset.topic || topic.textContent.trim();
            subjectEl.focus();
            clearErr(subjectEl, 'err-subject');
            topics.forEach((item) => item.classList.remove('is-active'));
            topic.classList.add('is-active');
        });
    });

    // Form gönderiminde tarayıcı yenilemesini engelleyip API’ye AJAX isteği yapılır.
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        setVisible(success, false);
        setVisible(errBox, false);

        const valid = [nameEl, emailEl, subjectEl, msgEl].every(validateField);
        if (!valid) return;

        btn.classList.add('is-loading');
        btn.disabled = true;
        btnTxt.textContent = t('sending', document.documentElement.lang === 'tr' ? 'Gönderiliyor...' : 'Sending...');
        arrow.textContent = '⟳';

        try {
            const response = await fetch('api/contact-submit.php', {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(t('errorMessage', document.documentElement.lang === 'tr' ? 'Bir hata oluştu. Lütfen tekrar dene.' : 'Something went wrong. Please try again.'));
            }

            await playMailAnimation();

            success.textContent = t('successMessage', document.documentElement.lang === 'tr'
                ? '✓ Mesajın mühürlenip gönderildi. En kısa sürede dönüş yapacağım.'
                : '✓ Your message was sealed and sent. I will reply soon.');
            setVisible(success, true);
            form.reset();
            countEl.textContent = '0';
            topics.forEach((item) => item.classList.remove('is-active'));
        } catch (error) {
            errBox.textContent = '✗ ' + (error.message || t('errorMessage', document.documentElement.lang === 'tr'
                ? 'Bir hata oluştu. Lütfen tekrar dene.'
                : 'Something went wrong. Please try again.'));
            setVisible(errBox, true);
        } finally {
            btn.classList.remove('is-loading');
            btn.disabled = false;
            btnTxt.textContent = t('sendMessage', document.documentElement.lang === 'tr' ? 'Mesaj Gönder' : 'Send Message');
            arrow.textContent = '→';
        }
    });
});

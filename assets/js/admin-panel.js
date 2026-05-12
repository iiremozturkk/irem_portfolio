// Bu dosya: Admin panelinde arama, bildirim paneli ve iki dilli metin dönüşümünü yönetir.
// Admin paneli yüklendiğinde arama, bildirim ve dil kontrolleri hazırlanır.
document.addEventListener('DOMContentLoaded', () => {
    // Global arama alanı admin modül kartlarını filtrelemek için kullanılır.
    const search = document.getElementById('globalSearch');
    const modules = [
        { label: 'Dashboard', href: 'dashboard.php', keys: 'dashboard ana sayfa panel özet istatistik ziyaretçi' },
        { label: 'Projeler', href: 'projects.php', keys: 'projeler proje ekle düzenle portfolio öne çıkar sıra' },
        { label: 'Mesajlar', href: 'messages.php', keys: 'mesajlar contact mail iletişim okunmamış' },
        { label: 'Analitik', href: 'analytics.php', keys: 'analitik ziyaretçi grafik istatistik rapor' },
        { label: 'Ayarlar', href: 'settings.php', keys: 'ayarlar tema panel başlığı eposta görünüm' },
        { label: 'Takvim', href: 'calendar.php', keys: 'takvim calendar 2026 not hatırlatma gün ay' },
        { label: 'Dosyalar', href: 'files.php', keys: 'dosyalar files cv belgeler yükleme' },
        { label: 'Ekip', href: 'team.php', keys: 'ekip team kişi üyeler' },
        { label: 'Raporlar', href: 'reports.php', keys: 'raporlar reports çıktı analiz' },
        { label: 'Entegrasyonlar', href: 'integrations.php', keys: 'entegrasyon api bağlantı sistem' },
    ];

    if (search) {
        const searchable = Array.from(document.querySelectorAll('[data-search-item], .project-table__row, .message-row, .timeline-item, .cc-card, .month-card, .cc-page-stack article, .cc-form label'));
        const getText = (el) => (el.dataset.searchItem || el.textContent || '').toLowerCase();
        const normalize = (text) => text.toLocaleLowerCase('tr-TR').trim();

        const suggestionBox = document.createElement('div');
        suggestionBox.className = 'cc-search-suggestions';
        suggestionBox.hidden = true;
        search.closest('.cc-search')?.appendChild(suggestionBox);

        const renderSuggestions = () => {
            const q = normalize(search.value);
            suggestionBox.innerHTML = '';
            if (!q) {
                suggestionBox.hidden = true;
                return;
            }

            const hits = modules
                .map((item) => ({ ...item, haystack: normalize(`${item.label} ${item.keys}`) }))
                .filter((item) => item.haystack.includes(q))
                .slice(0, 6);

            if (!hits.length) {
                const empty = document.createElement('p');
                empty.className = 'cc-search-suggestion-empty';
                empty.textContent = 'Bağlantılı kelime bulunamadı. Enter ile bu sayfada ara.';
                suggestionBox.appendChild(empty);
                suggestionBox.hidden = false;
                return;
            }

            hits.forEach((item) => {
                const link = document.createElement('button');
                link.type = 'button';
                link.className = 'cc-search-suggestion';
                link.dataset.href = item.href;
                link.innerHTML = `<strong>${item.label}</strong>`;
                link.addEventListener('click', () => {
                    window.location.href = item.href;
                });
                suggestionBox.appendChild(link);
            });
            suggestionBox.hidden = false;
        };

        const runSearch = () => {
            const q = normalize(search.value);
            searchable.forEach((el) => {
                const hit = !q || getText(el).toLocaleLowerCase('tr-TR').includes(q);
                el.classList.toggle('is-hidden', !hit);
            });
        };

        // Yazarken sadece bağlantılı kelimeler gösterilir; sayfa içeriği filtrelenmez.
        search.addEventListener('input', renderSuggestions);

        search.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                const q = normalize(search.value);
                if (!q) {
                    runSearch();
                    suggestionBox.hidden = true;
                    return;
                }

                const match = modules.find(m => normalize(`${m.label} ${m.keys}`).includes(q));
                if (match) {
                    window.location.href = match.href;
                    return;
                }

                runSearch();
                suggestionBox.hidden = true;
            }
            if (event.key === 'Escape') {
                search.value = '';
                runSearch();
                suggestionBox.hidden = true;
                search.blur();
            }
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.cc-search')) suggestionBox.hidden = true;
        });

        document.addEventListener('keydown', (event) => {
            if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                search.focus();
                search.select();
                renderSuggestions();
            }
        });
    }

    const toggle = document.getElementById('notificationToggle');
    const panel = document.getElementById('notificationPanel');
    // Bildirim butonu panelin açılıp kapanmasını yönetir.
    if (toggle && panel) {
        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            panel.hidden = !panel.hidden;
            toggle.setAttribute('aria-expanded', String(!panel.hidden));
        });
        document.addEventListener('click', (event) => {
            if (!event.target.closest('.cc-notification-wrap')) {
                panel.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    document.querySelectorAll('input[type="number"][name="sort_order"]').forEach((input) => {
        input.setAttribute('min', '0');
        input.setAttribute('step', '1');
        const normalize = () => {
            const value = Number(input.value);
            if (input.value !== '' && (!Number.isFinite(value) || value < 0)) input.value = '0';
        };
        input.addEventListener('input', normalize);
        input.addEventListener('blur', normalize);
    });
});

// v23-stable: Full TR/EN language toggle for admin pages without freezing the site
(() => {
    // Arayüzde görünen sabit metinler iki dil arasında eşleştirilir.
    const pairs = [
        ['SİSTEM DURUMU','SYSTEM STATUS'], ['Sistem Durumu','System Status'], ['OPTIMAL','OPTIMAL'], ['Sunucu','Server'], ['Çalışma Süresi','Uptime'],
        ['sistem aktif','active systems'], ['Sistem aktif','Active systems'],

        ['YÖNETİCİ PROFİLİ','ADMIN PROFILE'], ['Yönetici Profili','Admin Profile'], ['CANLI','LIVE'], ['Yönetici','Admin'], ['Admin','Admin'],
        ['YÖNETİLEN PROJE','MANAGED PROJECTS'], ['Yönetilen Proje','Managed Projects'], ['Yönetilen Projeler','Managed Projects'], ['EKİP ÜYESİ','TEAM MEMBERS'], ['Ekip Üyesi','Team Members'], ['EKİP ÜYELERİ','TEAM MEMBERS'], ['BAŞARI ORANI','SUCCESS RATE'], ['Başarı Oranı','Success Rate'],
        ['Mesaj bulunamadı.','No messages found.'], ['Henüz mesaj yok.','No messages yet.'], ['Mesajlar','Messages'], ['Mesaj','Message'], ['Okunmamış Mesaj','Unread Messages'], ['Okunmadı','Unread'], ['Okundu','Read'], ['Sil','Delete'],
        ['Yeni Proje Ekle','Add New Project'], ['YENİ PROJE EKLE','ADD NEW PROJECT'], ['Projeyi Ekle','Add Project'], ['Kaydet','Save'], ['Öne Çıkar','Featured'], ['Sıra','Order'], ['Başlık','Title'], ['Kod Adı','Code Name'], ['Kısa Açıklama','Short Description'], ['Teknolojiler','Technologies'], ['Görsel','Image'], ['Canlı URL','Live URL'], ['Açıklama','Description'], ['Title','Title'], ['Technologies','Technologies'], ['Image','Image'], ['Description','Description'], ['Featured','Featured'], ['Order','Order'],
        ['PANEL SETTINGS','PANEL SETTINGS'], ['Panel Ayarları','Panel Settings'], ['PANEL AYARLARI','PANEL SETTINGS'], ['Yönetici panelinin başlık, bildirim ve kullanım tercihlerini buradan düzenleyebilirsin.','You can manage the admin panel title, notification and usage preferences here.'],
        ['Panel Başlığı','Panel Title'], ['Bildirim E-postası','Notification Email'], ['Varsayılan Görünüm','Default View'], ['Tema','Theme'], ['Liste Sayısı','List Count'], ['İmza','Signature'], ['Hızlı Not','Quick Note'], ['Bildirim','Notification'], ['Varsayılana Döndür','Reset Defaults'], ['Ayarlar varsayılan değerlere döndürüldü.','Settings were reset to defaults.'], ['Ayarlar kaydedildi.','Settings saved.'], ['Save','Save'], ['Reset Defaults','Reset Defaults'],
        ['2026 TAKVİMİ','2026 CALENDAR'], ['kayıtlı not · Not eklemek için bir güne tıkla','saved notes · Click a day to add a note'], ['Takvim notu kaydedildi.','Calendar note saved.'], ['Seçili Gün','Selected Day'], ['Takvim Notu','Calendar Note'], ['Bu güne not, teslim tarihi, hatırlatma veya fikir yaz...','Write a note, deadline, reminder or idea for this day...'], ['Notu Kaydet','Save Note'], ['Notu silmek için alanı boş bırakıp kaydedebilirsin.','To delete the note, leave the field empty and save.'], ['Not panelini kapat','Close note panel'], ['Not ekle','Add note'],
        ['Ocak','January'], ['Şubat','February'], ['Mart','March'], ['Nisan','April'], ['Mayıs','May'], ['Haziran','June'], ['Temmuz','July'], ['Ağustos','August'], ['Eylül','September'], ['Ekim','October'], ['Kasım','November'], ['Aralık','December'],
        ['Pzt','Mon'], ['Sal','Tue'], ['Çar','Wed'], ['Per','Thu'], ['Cum','Fri'], ['Cmt','Sat'], ['Paz','Sun'],
        ['Dashboard','Dashboard'], ['Raporlar','Reports'], ['Rapor','Report'], ['Rapor Özeti','Report Summary'], ['Öne Çıkan','Featured'], ['Toplam Mesaj','Total Messages'], ['Öne Çıkan Proje','Featured Projects'], ['Yetenek','Skills'], ['Son Proje Güncellemeleri','Recent Project Updates'], ['Son mesajlar ve proje verileri rapor için hazır.','Recent messages and project data are ready for reporting.'], ['Kayıt yok.','No records.'], ['Projeler','Projects'], ['Analitik','Analytics'], ['Ayarlar','Settings'], ['Takvim','Calendar'], ['Dosyalar','Files'], ['Ekip','Team'], ['Raporlar','Reports'], ['Entegrasyonlar','Integrations'], ['Hızlı Erişim','Quick Access'],
        ['Bildirimler','Notifications'], ['Canlı panel','Live panel'], ['Yeni bildirim yok.','No new notifications.'], ['＋ Yeni Proje','＋ New Project'], ['+ Yeni Proje','+ New Project'], ['Yeni Project','New Project'], ['Arama yapın...','Search...'],
        ['Toplam Proje','Total Projects'], ['Proje Adı','Project Name'], ['PROJE ADI','PROJECT NAME'], ['Durum','Status'], ['DURUM','STATUS'], ['İlerleme','Progress'], ['İLERLEME','PROGRESS'], ['Son Güncelleme','Last Update'], ['SON GÜNCELLEME','LAST UPDATE'], ['Ekip','Team'], ['Tamamlandı','Completed'], ['Devam Ediyor','In Progress'], ['Planlama','Planning'], ['Ziyaretçi','Visitors'], ['Ziyaretçi Analitiği','Visitor Analytics'], ['Toplam Ziyaretçi','Total Visitors'], ['Son 1 Hafta','Last 1 Week'], ['Son 15 Gün','Last 15 Days'], ['Son 30 Gün','Last 30 Days'], ['Bugün','Today'], ['Tümünü Gör','View All'], ['Son Mesajlar','Recent Messages'], ['Aktivite Zaman Çizelgesi','Activity Timeline'], ['Henüz aktivite yok.','No activity yet.'], ['Dashboard','Dashboard'], ['Hedefe Ulaşım','Goal Progress'], ['Aylık Hedef','Monthly Goal'],
        ['Canlı ziyaretçi verileri','Live visitor intelligence'], ['Analitik içinde ara...','Search analytics...'], ['Portfolyo ana sayfasından canlı çekiliyor','Live data from the portfolio homepage'], ['Bugünkü gerçek giriş sayısı','Real visits recorded today'], ['Haftalık ziyaret toplamı','Total visits for the last week'], ['Canlı Ziyaretçi Verisi','Live Visitor Data'], ['Ziyaretçi aralığı','Visitor range'],
        ['Portfolyo servislerini bağla ve izle','Connect and monitor portfolio services'], ['Entegrasyonlarda ara...','Search integrations...'], ['Bağlı','Connected'], ['Beklemede','Standby'], ['Devre Dışı','Disabled'], ['Şu anda aktif servisler','Services currently active'], ['Gelecekte etkinleştirmeye hazır','Ready for future activation'], ['Duraklatılmış entegrasyonlar','Paused integrations'], ['Entegrasyon Merkezi','Integration Hub'], ['Portfolyo panelini çalıştıran servisleri buradan yönet.','Manage the services that power your portfolio dashboard.'], ['Entegrasyon durumu güncellendi.','Integration status updated.'], ['Veritabanı tablosu güncellenemedi, ancak entegrasyon paneli kullanılabilir.','Database table could not be updated, but the integration panel is available.'],
        ['Portfolyo API','Portfolio API'], ['Dinamik projeleri ve herkese açık portfolyo verilerini yükler.','Loads dynamic projects and public portfolio data.'], ['İletişim Gelen Kutusu','Contact Inbox'], ['İletişim formu mesajlarını MySQL veritabanına kaydeder.','Stores contact form messages in MySQL.'], ['Ana sayfa ziyaretlerini takip eder ve analitik panelini besler.','Tracks homepage visits and feeds the analytics dashboard.'], ['CV Dışa Aktarımları','CV Exports'], ['Türkçe ve İngilizce CV dosyalarını indirmeye hazır tutar.','Keeps Turkish and English CV files available for download.'], ['E-posta Bildirimleri','Email Notifications'], ['Gelecekteki mesaj bildirimleri için ayrılmış kanal.','Reserved channel for future message notifications.'], ['Uç nokta','Endpoint'], ['Ayarlanmamış','Not configured'],
        ['Bu modül entegre edildi ve gezinme için hazır. İçeriği daha sonra detaylandırabilirsiniz.','This module is integrated and ready for navigation. You can detail the content later.'],
        ['Dosya Merkezi','Files Hub'], ['CV, proje belgeleri ve portfolyo dosyalarını buradan yönet.','Manage CVs, project documents and portfolio files here.'], ['Toplam Dosya','Total Files'], ['Yüklenen','Uploaded'], ['Depolama','Storage'], ['Yeni Dosya Yükle','Upload New File'], ['Dosya Başlığı','File Title'], ['Kategori','Category'], ['Dosya Seç','Choose File'], ['Yükle','Upload'], ['Aktif Dosyalar','Active Files'], ['Aç','Open'], ['Dosya başarıyla eklendi.','File added successfully.'], ['Dosya kaldırıldı.','File removed.'], ['Bu dosya kaldırılsın mı?','Remove this file?'], ['Dosya yüklenemedi.','File could not be uploaded.'], ['Bu dosya türü desteklenmiyor.','This file type is not supported.'], ['Dosya 10MB sınırını aşmamalı.','File must not exceed 10MB.'], ['Dosya klasöre taşınamadı.','File could not be moved to the upload folder.'], ['Hazır','Ready'], ['Örn. Proje Özeti','e.g. Project Brief'], ['Örn. Project Brief','e.g. Project Brief'], ['Dosya seçilmedi','No file chosen'], ['CV - Türkçe','CV - Turkish'], ['CV - İngilizce','CV - English'], ['Örn. Proje Özeti','e.g. Project Brief'], ['Örn. Project Brief','e.g. Project Brief'],

        ['Portfolyo üretim ekibini yönet','Manage the portfolio production team'], ['Ekip içinde ara...','Search team...'], ['Toplam Ekip','Total Team'], ['Panelde kayıtlı üye','Members registered in the panel'], ['Aktif Modül','Active Modules'], ['Şu anda görevde','Currently on duty'], ['Ortalama Yük','Average Load'], ['Sprint yoğunluğu','Sprint intensity'], ['Ekip Kontrol Merkezi','Team Control Center'], ['Portfolyo projesindeki görevleri ve ekip durumunu buradan yönet.','Manage portfolio project roles and team status here.'], ['Proje Lideri','Project Lead'], ['Arayüz Modülü','Interface Module'], ['Sunucu Modülü','Server Module'], ['Test ve Kontrol','Testing & Review'], ['Full-stack geliştirme, tasarım sistemi ve veri tabanı yönetimi','Full-stack development, design system and database management'], ['Responsive UI, DOM etkileşimleri ve animasyonlar','Responsive UI, DOM interactions and animations'], ['PHP oturumları, MySQL kayıtları ve güvenli panel akışı','PHP sessions, MySQL records and secure panel flow'], ['Form doğrulama, bağlantı kontrolü ve son teslim hazırlığı','Form validation, link checks and final delivery readiness'], ['Aktif','Active'], ['Hazır','Standby'], ['Duraklatıldı','Paused'], ['Kaldır','Remove'], ['Yeni Ekip Üyesi','New Team Member'], ['Ad Soyad','Full Name'], ['Rol','Role'], ['Odak alanı','Focus area'], ['Ekle','Add'], ['Ekip üyesi eklendi.','Team member added.'], ['Ekip durumu güncellendi.','Team status updated.'], ['Ekip üyesi kaldırıldı.','Team member removed.'], ['Veritabanı bağlantısı kurulamadı; demo ekip listesi gösteriliyor.','Database connection could not be established; the demo team list is shown.'], ['Ekip durumu','Team status'], ['İş yükü','Workload'], ['Proje Lideri','Project Lead'], ['Portfolyo üretim ekibini yönet','Manage the portfolio production team']
    ];
    const dict = Object.fromEntries(pairs);
    const originalText = new WeakMap();
    const originalAttrs = new WeakMap();
    const langButton = document.getElementById('languageToggle');
    let applying = false;
    let scheduled = false;

    // Verilen metnin seçili dilde karşılığı varsa onu döndürür.
    function translateString(text, lang) {
        if (!text) return text;
        const reverseDict = Object.fromEntries(pairs.map(([tr, en]) => [en, tr]));
        let out = lang === 'tr' ? (reverseDict[text] || text) : (dict[text] || text);
        if (lang === 'en') {
            out = out
                .replace(/^(\d+) kayıtlı not · Not eklemek için bir güne tıkla$/i, '$1 saved notes · Click a day to add a note')
                .replace(/^(\d+) okunmamış mesaj var$/i, '$1 unread messages')
                .replace(/^Sunucu:\s*/i, 'Server: ')
                .replace(/^Çalışma Süresi:\s*(\d+)g$/i, 'Uptime: $1d')
                .replace(/(.+) \(Devam Ediyor\) projesi güncellendi$/i, '$1 (In Progress) project updated')
                .replace(/(.+) projesi güncellendi$/i, '$1 project updated')
                .replace(/(.+) güncellendi$/i, '$1 updated')
                .replace(/Yeni mesaj:/gi, 'New message:')
                .replace(/iletişim formundan yazdı/gi, 'sent a contact form message')
                .replace(/önceki 30 güne göre/gi, 'compared to previous 30 days')
                .replace(/portfolyo verilerine göre/gi, 'based on portfolio data')
                .replace(/bu hafta/gi, 'this week')
                .replace(/bu ay/gi, 'this month')
                .replace(/bu yıl/gi, 'this year')
                .replace(/Tüm Sistemler Çevrimiçi/gi, 'All Systems Online')
                .replace(/Yönetilen Proje/gi, 'Managed Projects')
                .replace(/Ekip Üyesi/gi, 'Team Members')
                .replace(/Başarı Oranı/gi, 'Success Rate');
            [...pairs].sort((a,b)=>b[0].length-a[0].length).forEach(([tr,en]) => { out = out.split(tr).join(en); });
        } else {
            out = out
                .replace(/^e\.g\. Project Brief$/i, 'Örn. Proje Özeti')
                .replace(/^CV - Turkish$/i, 'CV - Türkçe')
                .replace(/^CV - English$/i, 'CV - İngilizce')
                .replace(/No file chosen/gi, 'Dosya seçilmedi')
                .replace(/Choose File/gi, 'Dosya Seç');
            [...pairs].sort((a,b)=>b[1].length-a[1].length).forEach(([tr,en]) => { out = out.split(en).join(tr); });
        }
        return out;
    }

    function translateTextNode(node, lang) {
        const raw = node.nodeValue;
        if (!raw || !raw.trim()) return;
        if (!originalText.has(node)) originalText.set(node, raw);
        const base = originalText.get(node);
        if (lang === 'tr') { node.nodeValue = base; return; }
        const trimmed = base.trim();
        node.nodeValue = base.replace(trimmed, translateString(trimmed, lang));
    }

    function translateAttr(el, attr, lang) {
        if (!el.hasAttribute(attr)) return;
        if (!originalAttrs.has(el)) originalAttrs.set(el, {});
        const store = originalAttrs.get(el);
        if (!(attr in store)) store[attr] = el.getAttribute(attr);
        const base = store[attr];
        el.setAttribute(attr, lang === 'tr' ? base : translateString(base, lang));
    }

    function translateElement(el, lang) {
        if (!el || ['SCRIPT','STYLE'].includes(el.tagName)) return;
        if (el.dataset && el.dataset.i18nTr && el.dataset.i18nEn) {
            const translated = lang === 'tr' ? el.dataset.i18nTr : el.dataset.i18nEn;
            if (['INPUT','TEXTAREA'].includes(el.tagName)) {
                if (el.hasAttribute('placeholder')) el.setAttribute('placeholder', translated);
                if (el.hasAttribute('aria-label')) el.setAttribute('aria-label', translated);
            } else if (el.tagName === 'SELECT') {
                if (el.hasAttribute('aria-label')) el.setAttribute('aria-label', translated);
            } else {
                el.textContent = translated;
                return;
            }
        }
        if (!['INPUT','TEXTAREA','SELECT'].includes(el.tagName)) {
            el.childNodes.forEach(node => { if (node.nodeType === Node.TEXT_NODE) translateTextNode(node, lang); });
        }
        ['placeholder','title','aria-label','alt'].forEach(attr => translateAttr(el, attr, lang));
        if ((el.tagName === 'INPUT' && ['submit','button','reset'].includes((el.type || '').toLowerCase())) || el.tagName === 'BUTTON') {
            translateAttr(el, 'value', lang);
        }
    }

    // Admin arayüzündeki metin düğümlerine ve bazı attribute değerlerine dil uygular.
    function applyLanguage(lang) {
        if (applying) return;
        applying = true;
        document.documentElement.lang = lang;
        document.querySelectorAll('img.profile-card-full[src$="manager-profile-card.png"], img.profile-card-full[src$="manager-profile-card-en.png"]').forEach(img => {
            img.src = lang === 'tr' ? '../assets/images/manager-profile-card.png' : '../assets/images/manager-profile-card-en.png';
            img.alt = lang === 'tr' ? 'İrem Öztürk yönetici profili' : 'İrem Öztürk admin profile';
        });
        document.querySelectorAll('body *').forEach(el => translateElement(el, lang));
        if (langButton) {
            const span = langButton.querySelector('span');
            if (span) span.textContent = lang === 'tr' ? 'EN' : 'TR';
            langButton.title = lang === 'tr' ? 'Türkçe / English' : 'English / Türkçe';
            langButton.setAttribute('aria-label', lang === 'tr' ? 'Dil değiştir' : 'Change language');
        }
        document.querySelectorAll('.file-picker__name').forEach(nameEl => {
            const input = nameEl.closest('.file-picker')?.querySelector('input[type="file"]');
            if (!input || !input.files || !input.files.length) {
                nameEl.textContent = lang === 'tr' ? (nameEl.dataset.emptyTr || 'Dosya seçilmedi') : (nameEl.dataset.emptyEn || 'No file chosen');
            }
        });
        applying = false;
    }

    function scheduleApply() {
        if (scheduled) return;
        scheduled = true;
        window.setTimeout(() => {
            scheduled = false;
            const lang = localStorage.getItem('ccLanguage') || 'tr';
            applyLanguage(lang);
        }, 30);
    }

    window.ccApplyLanguage = applyLanguage;

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.file-picker input[type="file"]').forEach(input => {
            if (input.dataset.filePickerBound) return;
            input.dataset.filePickerBound = '1';
            input.addEventListener('change', () => {
                const nameEl = input.closest('.file-picker')?.querySelector('.file-picker__name');
                if (!nameEl) return;
                const lang = localStorage.getItem('ccLanguage') || 'tr';
                nameEl.textContent = input.files && input.files.length
                    ? input.files[0].name
                    : (lang === 'tr' ? (nameEl.dataset.emptyTr || 'Dosya seçilmedi') : (nameEl.dataset.emptyEn || 'No file chosen'));
            });
        });
        const btn = document.getElementById('languageToggle');
        if (btn && !btn.dataset.langBound) {
            btn.dataset.langBound = '1';
            btn.addEventListener('click', () => {
                const next = (localStorage.getItem('ccLanguage') || 'tr') === 'tr' ? 'en' : 'tr';
                localStorage.setItem('ccLanguage', next);
                applyLanguage(next);
            });
        }
        applyLanguage(localStorage.getItem('ccLanguage') || 'tr');

        // Takvim not penceresi gibi sonradan açılan küçük parçalar için tek seferlik, güvenli yeniden uygulama.
        document.body.addEventListener('click', scheduleApply, true);
        document.body.addEventListener('change', scheduleApply, true);
    });
})();

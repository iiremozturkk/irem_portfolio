-- Bu dosya: Portfolyo projesinin veritabanı şemasını ve örnek başlangıç verilerini oluşturur.
-- Proje için kullanılacak veritabanı UTF-8 uyumlu şekilde oluşturulur.
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET collation_connection = 'utf8mb4_unicode_ci';


-- Kurulum tekrarlanabilir olsun diye mevcut tablolar bağımlılık sırasına uygun temizlenir.
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS skills;
DROP TABLE IF EXISTS calendar_notes;
DROP TABLE IF EXISTS visitor_daily_stats;
DROP TABLE IF EXISTS visitor_stats;
DROP TABLE IF EXISTS integration_settings;
DROP TABLE IF EXISTS team_members;
DROP TABLE IF EXISTS admin_settings;
DROP TABLE IF EXISTS admins;

-- Admin kullanıcılarının giriş bilgileri bu tabloda saklanır.
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Portfolyoda gösterilecek proje kartlarının temel içerik alanları.
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    code_name VARCHAR(80) NOT NULL,
    short_description VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    tech_stack VARCHAR(255) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    github_url VARCHAR(255) DEFAULT NULL,
    live_url VARCHAR(255) DEFAULT NULL,
    featured TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Teknik yetenekler kategori, ad ve açıklama bilgisiyle tutulur.
CREATE TABLE skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- İletişim formundan gelen ziyaretçi mesajları bu tabloya kaydedilir.
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    subject VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 
 
 
 
-- Varsayılan admin hesabı için parola hash değeri eklenir.
INSERT INTO admins (username, password_hash) VALUES
('admin', '$2y$12$nUhn2j7ijYVYixnrLhfnledIt.ThWKAZq8TCEtWr7PpVG89hEREci');

-- Ana sayfada ilk kurulumda görünecek örnek proje kayıtları.
INSERT INTO projects (title, code_name, short_description, description, tech_stack, image, github_url, live_url, featured, sort_order) VALUES
('Endüstriyel Saldırı Tespit Sistemi', 'Endüstriyel Saldırı Tespit Sistemi', 'Endüstriyel sistemler için makine öğrenmesi tabanlı siber saldırı tespiti.', 'Industrial IoT ortamlarında saldırı tespiti yapan makine öğrenmesi tabanlı güvenlik sistemi. 4 farklı AI modeli ile anomali/saldırı tespiti amaçlanır.', 'Python, Machine Learning, Cybersecurity, AI', 'assets/images/project-iiot.svg', 'https://github.com/iiremozturkk/Industrial-Attack-Detection-System', '#', 1, 1),
('Go Ürün Yönetimi', 'Go Ürün Yönetimi', 'Go ile geliştirilmiş RESTful ürün yönetim sistemi.', 'Go backend ve Vue frontend kullanılan ürün yönetim sistemi. Ürün ekleme, listeleme ve yönetme işlemleri için full-stack yapıdadır.', 'Go, REST API, Backend, Database', 'assets/images/project-go.svg', 'https://github.com/iiremozturkk/GoProductManagement', '#', 1, 2),
('Akıllı Ulaşım Veritabanı', 'Akıllı Ulaşım Veritabanı', 'Akıllı ulaşım sistemleri için veritabanı tasarımı.', 'Akıllı ulaşım sistemleri için tasarlanmış veritabanı projesi. Araç, rota, durak, yolcu ve ulaşım verilerini yönetmeye odaklanır.', 'SQL, Database Design, Data Management, PHP', 'assets/images/project-transport.svg', 'https://github.com/iiremozturkk/Smart_Transportation_Database', '#', 1, 3),
('İlanpazar', 'ILANPAZAR', 'İlan oluşturma ve ürün keşfi için ikinci el pazar yeri platformu.', 'Kullanıcıların ilan oluşturup görüntüleyebildiği Next.js tabanlı ilan/pazar yeri web uygulaması.', 'PHP, MySQL, JavaScript, CSS', 'assets/images/project-portfolio.svg', 'https://github.com/iiremozturkk/ilanpazar', '#', 1, 4),
('Shopora', 'SHOPORA', 'İki kişilik ekiple geliştirilen full-stack e-ticaret pazar yeri.', 'Modern e-ticaret mantığında geliştirilmiş web uygulaması. Ürün arayüzü, kullanıcı işlemleri ve alışveriş deneyimi üzerine kuruludur.', 'Next.js, React, MySQL, JavaScript, Tailwind CSS', 'assets/images/project-shopora.svg', 'https://github.com/iiremozturkk', '#', 1, 5);

-- Yetenek bölümünü dolduran başlangıç beceri kayıtları.
INSERT INTO skills (category, name, description, sort_order) VALUES
('Core Languages', 'JavaScript', 'DOM interactivity, AJAX and UI logic', 1),
('Core Languages', 'TypeScript', 'Typed frontend and scalable code structure', 2),
('Core Languages', 'SQL', 'Database queries and relational data handling', 3),
('Core Languages', 'PHP', 'Server-side logic and dynamic pages', 4),
('Core Languages', 'HTML5', 'Semantic page structure', 5),
('Core Languages', 'CSS3', 'Responsive styling and advanced layouts', 6),
('Core Languages', 'Go', 'Backend services and API development', 7),
('Core Languages', 'Java', 'Object-oriented programming and backend fundamentals', 8),
('Core Languages', 'C++', 'Object-oriented and systems programming', 9),
('Core Languages', 'Python', 'Scripting, data analysis and machine learning', 10),
('Backend Systems', 'Node.js', 'JavaScript runtime for backend services', 11),
('Backend Systems', 'PHP', 'Server-side routing, sessions and database operations', 12),
('Backend Systems', 'REST API', 'Structured API design and integration', 13),
('Backend Systems', 'AJAX / Fetch API', 'Asynchronous client-server communication', 14),
('Backend Systems', 'Git', 'Version control and collaborative development', 15),
('Backend Systems', 'FastAPI', 'Python API development', 16),
('Backend Systems', 'Django / Flask', 'Python web application frameworks', 17),
('Frontend Interface', 'React', 'Component-based interactive interfaces', 18),
('Frontend Interface', 'Next.js', 'Modern full-stack React applications', 19),
('Frontend Interface', 'Vite', 'Fast frontend development tooling', 20),
('Frontend Interface', 'Tailwind CSS', 'Utility-first responsive styling', 21),
('Frontend Interface', 'GSAP', 'High-quality motion and timeline animations', 22),
('Frontend Interface', 'Lenis', 'Smooth scrolling and premium page feel', 23),
('Frontend Interface', 'Lucide React', 'Clean icon system for modern UI', 24),
('Frontend Interface', 'Recharts', 'Data visualization components', 25),
('Frontend Interface', 'HTML5 / CSS3', 'Semantic structure and responsive styling', 26),
('Frontend Interface', 'Vue.js', 'Reactive frontend interfaces', 27),
('Frontend Interface', 'JavaScript', 'Interactive client-side behavior', 28),
('Database & Tools', 'SQLite', 'Lightweight relational database management', 29),
('Database & Tools', 'MS SQL Server', 'Enterprise relational database systems', 30),
('Database & Tools', 'Postman', 'API testing and request debugging', 31),
('Database & Tools', 'MySQL', 'Relational database design and queries', 32),
('Database & Tools', 'Docker', 'Containerized development workflows', 33),
('AI / ML', 'Machine Learning', 'Model building and intelligent systems', 34),
('AI / ML', 'Data Analysis', 'Exploring and interpreting datasets', 35),
('AI / ML', 'Random Forest', 'Tree-based ensemble classification', 36),
('AI / ML', 'Gradient Boosting', 'Boosted ensemble modeling', 37),
('AI / ML', 'Isolation Forest', 'Anomaly detection and outlier analysis', 38),
('AI / ML', 'Scikit-learn', 'Machine learning model training and evaluation', 39),
('AI / ML', 'XGBoost', 'Gradient boosting models for tabular data', 40);


-- Admin takviminde seçilen günlere not bağlamak için kullanılan tablo.
CREATE TABLE calendar_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_date DATE NOT NULL UNIQUE,
    note_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Toplam ziyaret sayacı tek satırlık sayaç tablosunda tutulur.
CREATE TABLE visitor_stats (
    id INT PRIMARY KEY DEFAULT 1,
    total_views INT NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO visitor_stats (id, total_views) VALUES (1, 0);

 
CREATE TABLE IF NOT EXISTS admin_settings (
    setting_key VARCHAR(80) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Günlük ziyaret grafiği için tarih bazlı görüntülenme sayıları tutulur.
CREATE TABLE visitor_daily_stats (
    visit_date DATE PRIMARY KEY,
    views INT NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_name VARCHAR(120) NOT NULL,
    role_tr VARCHAR(120) NOT NULL,
    role_en VARCHAR(120) NOT NULL,
    focus_tr TEXT NULL,
    focus_en TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    workload INT NOT NULL DEFAULT 80,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS integration_settings (
    integration_key VARCHAR(80) PRIMARY KEY,
    integration_name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    endpoint VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'standby',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
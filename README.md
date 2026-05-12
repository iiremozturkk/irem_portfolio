# İrem Öztürk Creative Full-Stack Portfolio

This project is a creative PHP/MySQL-based full-stack web application that combines a personal portfolio website, dynamic project management, a contact form, visitor analytics, and an admin panel. With its cinematic hero section, animated transitions, interactive character guide, multilingual interface, and manageable content structure, the site offers a more advanced experience than classic portfolio pages.

> This README file was prepared to explain how to install and run the project, set up the database, use the admin panel, and understand the code structure.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Core Features](#core-features)
- [Technologies Used](#technologies-used)
- [Folder and File Structure](#folder-and-file-structure)
- [Database Structure](#database-structure)
- [Installation](#installation)
- [Running the Project](#running-the-project)
- [Admin Panel](#admin-panel)
- [API Endpoints](#api-endpoints)
- [Homepage Sections](#homepage-sections)
- [Security Notes](#security-notes)
- [Customization Guide](#customization-guide)
- [Pre-Deployment Checklist](#pre-deployment-checklist)
- [Troubleshooting](#troubleshooting)
- [Development Ideas](#development-ideas)

---

## Project Overview

The purpose of this project is to create a portfolio website for İrem Öztürk that is both visually strong and technically manageable. Visitors can view the personal introduction, technical skills, experience information, projects, and contact form on the homepage. The admin user can manage projects, messages, files, analytics data, calendar notes, team cards, integration statuses, and panel settings through the admin dashboard.

This project is not a classic static HTML portfolio. Using PHP and MySQL, projects, skills, messages, visitor statistics, and admin panel data are managed dynamically.

---

## Core Features

### Visitor Side

- Cinematic opening animation and large typographic hero section.
- Dark/light theme support.
- Turkish/English language switching.
- Smooth scrolling experience.
- GSAP and ScrollTrigger-based section animations.
- Interactive character guide.
- Video, binary rain effect, and live-coding atmosphere in the hero section.
- Dynamic project cards retrieved from the database.
- Technical skills displayed by category.
- Contact form.
- Form submission without page reload using AJAX/Fetch API.
- Envelope animation after successful form submission.
- Visitor counter system.

### Admin Side

- Session-based admin login.
- CSRF-protected form operations.
- Project, message, skill, and visitor metrics on the dashboard screen.
- Project create, update, and delete operations.
- Listing incoming contact messages, marking them as read, and deleting them.
- Visitor analytics and date-based chart data.
- Calendar note management.
- File upload and file center.
- Panel settings management.
- Team member management.
- Integration status panel.
- Reports and activity timeline screen.
- Turkish/English text support in the admin panel.

---

## Technologies Used

### Frontend

- HTML5
- CSS3
- JavaScript
- DOM API
- Fetch API / AJAX
- GSAP
- GSAP ScrollTrigger
- Lenis Smooth Scroll
- Matter.js
- Responsive Grid/Flexbox structures
- CSS variables and advanced animations

### Backend

- PHP
- PDO
- MySQL
- Session management
- Cookie usage
- Server-side form validation
- JSON API responses

### Database

- MySQL
- UTF-8 supported `utf8mb4` character set
- Ready-to-use SQL installation file

### External Resources

The project loads libraries via CDN for some frontend effects:

- Google Fonts
- GSAP
- ScrollTrigger
- Lenis
- Matter.js

For this reason, fonts and some animation libraries may not load in local environments without an internet connection. The PHP/MySQL side still works.

---

## Folder and File Structure

```text
irem_portfolio/
├── admin.php
├── index.php
├── admin/
│   ├── analytics.php
│   ├── calendar.php
│   ├── dashboard.php
│   ├── files.php
│   ├── integrations.php
│   ├── login.php
│   ├── logout.php
│   ├── messages.php
│   ├── page_head.php
│   ├── partials_nav.php
│   ├── projects.php
│   ├── reports.php
│   ├── settings.php
│   └── team.php
├── api/
│   ├── contact-submit.php
│   └── get-projects.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   ├── character-guide.css
│   │   └── style.css
│   ├── cv/
│   │   ├── irem-ozturk-cv-en.pdf
│   │   └── irem-ozturk-cv-tr.pdf
│   ├── images/
│   │   ├── favicon files
│   │   ├── logo/image files
│   │   └── hero-reel images
│   ├── js/
│   │   ├── admin-login.js
│   │   ├── admin-panel.js
│   │   ├── ajax.js
│   │   ├── character-guide.js
│   │   └── main.js
│   ├── uploads/
│   │   └── admin-files/
│   └── videos/
│       ├── hero-showreel.mp4
│       └── light-hero-video.mp4
├── config/
│   ├── auth.php
│   └── database.php
├── database/
│   └── portfolio.sql
└── README.md
```

### Important Files

| File | Description |
|---|---|
| `index.php` | The main portfolio page visitors see. It retrieves projects, skills, and visitor counters from the database. |
| `admin.php` | A short transition file that redirects to the admin login page. |
| `config/database.php` | Establishes the MySQL connection using PDO. |
| `config/auth.php` | Contains session initialization, admin checks, CSRF generation, and HTML escaping functions. |
| `api/contact-submit.php` | Validates the POST request coming from the contact form and saves the message to the database. |
| `api/get-projects.php` | Returns featured projects in JSON format. |
| `admin/login.php` | The admin login screen and authentication flow. |
| `admin/dashboard.php` | The main summary screen of the admin panel. |
| `admin/projects.php` | Manages project creation, editing, and deletion operations. |
| `admin/messages.php` | Manages incoming contact messages. |
| `admin/analytics.php` | Displays visitor statistics. |
| `admin/calendar.php` | A date-based note-taking screen. |
| `admin/files.php` | Manages CV and portfolio files. |
| `admin/settings.php` | Manages panel settings. |
| `admin/team.php` | Manages team members and their statuses. |
| `admin/integrations.php` | Manages integration cards and their statuses. |
| `admin/reports.php` | Generates a report screen from project and message data. |
| `assets/js/main.js` | Manages the homepage theme, language, animations, video, and interaction flows. |
| `assets/js/ajax.js` | Manages AJAX submission of the contact form and the successful submission animation. |
| `assets/js/character-guide.js` | Contains the movement, speech, and dragging logic of the interactive character guide. |
| `database/portfolio.sql` | Creates the database, tables, and initial data. |

---

## Database Structure

Database name:

```sql
irem_portfolio
```

Installation file:

```text
database/portfolio.sql
```

### Main Tables

| Table | Purpose |
|---|---|
| `admins` | Stores admin user information and password hash values. |
| `projects` | Stores project cards displayed in the portfolio. |
| `skills` | Stores technical skills by category. |
| `messages` | Stores messages received from the contact form. |
| `calendar_notes` | Stores date-based notes in the admin calendar. |
| `visitor_stats` | Stores the total visit count. |
| `visitor_daily_stats` | Stores daily visit counts. |
| `admin_settings` | Stores panel settings using a key/value structure. |
| `team_members` | Stores team cards and workload information. |
| `integration_settings` | Stores the statuses of integration cards. |

### Initial Data

The SQL file comes ready with:

- A default admin user,
- Sample project records,
- Technical skill categories,
- An initial visitor counter record.

---

## Installation

The steps below are explained using XAMPP. The same logic applies to MAMP, Laragon, or similar PHP/MySQL environments.

### 1. Move the Project Folder to the Server

Copy the project folder into the root directory of your web server.

XAMPP example:

```text
C:\xampp\htdocs\irem_portfolio
```

macOS/Linux example:

```text
/Applications/XAMPP/htdocs/irem_portfolio
```

or

```text
/var/www/html/irem_portfolio
```

### 2. Start Apache and MySQL Services

From the XAMPP Control Panel:

```text
Apache → Start
MySQL  → Start
```

### 3. Create the Database

Through phpMyAdmin:

1. Go to `http://localhost/phpmyadmin`.
2. Open the **Import** section from the top menu.
3. Select the `database/portfolio.sql` file.
4. Start the import process.

The SQL file automatically creates the `irem_portfolio` database.

### 4. Check the Database Connection

The information in `config/database.php` is ready for a local XAMPP environment:

```php
$host = 'localhost';
$dbname = 'irem_portfolio';
$username = 'root';
$password = '';
$charset = 'utf8mb4';
```

If your MySQL username or password is different, update these values.

---

## Running the Project

Main site:

```text
http://localhost/irem_portfolio/index.php
```

As a shortcut, the following address also works on most servers:

```text
http://localhost/irem_portfolio/
```

Admin login page:

```text
http://localhost/irem_portfolio/admin/login.php
```

or:

```text
http://localhost/irem_portfolio/admin.php
```

---

## Admin Panel

### Default Login Credentials

```text
Username: admin
Password: admin123
```

### Admin Panel Modules

| Page | File | Description |
|---|---|---|
| Dashboard | `admin/dashboard.php` | General metrics, recent projects, recent messages, and system summary. |
| Projects | `admin/projects.php` | Project creation, updating, deletion, and featuring operations. |
| Messages | `admin/messages.php` | Management of messages received from the contact form. |
| Analytics | `admin/analytics.php` | Total and daily visitor data. |
| Calendar | `admin/calendar.php` | Creating and deleting date-based notes. |
| Files | `admin/files.php` | Listing/uploading/deleting CV and portfolio files. |
| Reports | `admin/reports.php` | Generating report summaries from project and message data. |
| Integrations | `admin/integrations.php` | Managing API, inbox, analytics, and other service statuses. |
| Team | `admin/team.php` | Managing team/module cards. |
| Settings | `admin/settings.php` | Panel title, notification email, theme, and list settings. |
| Logout | `admin/logout.php` | Ends the admin session. |

### Project Management

When adding a new project from the admin panel, the following fields are used:

- Title
- Code name
- Short description
- Detailed description
- Technology list
- Image path
- GitHub URL
- Live demo URL
- Featured status
- Sort order value

Projects selected as `featured` are shown as featured projects on the homepage and in the API output.

---

## API Endpoints

### `api/get-projects.php`

Returns featured projects as JSON.

**Method:** `GET`

Example successful response:

```json
{
  "success": true,
  "projects": [
    {
      "id": 1,
      "title": "Industrial Attack Detection System",
      "code_name": "Industrial Attack Detection System",
      "short_description": "Machine-learning based cyber attack detection for industrial systems.",
      "description": "...",
      "tech_stack": "Python, Machine Learning, Cybersecurity, AI",
      "image": "assets/images/project-iiot.svg",
      "github_url": "https://github.com/...",
      "live_url": "#"
    }
  ]
}
```

### `api/contact-submit.php`

Processes the contact form submission and saves the message to the `messages` table.

**Method:** `POST`

Expected form fields:

| Field | Description |
|---|---|
| `name` | Sender name. Must be at least 2 characters long. |
| `email` | Must be a valid email address. |
| `subject` | Subject field. Must be at least 3 characters long. |
| `message` | Message content. Must be between 10 and 1000 characters. |
| `website` | Honeypot field. It must remain empty for bot protection. |

Successful response:

```json
{
  "success": true,
  "message": "✓ Message saved to İrem’s database. Thank you!"
}
```

For invalid requests, the API returns a JSON error message with the appropriate HTTP status code.

---

## Homepage Sections

The visitor side in `index.php` consists of the following main sections:

| Section | Description |
|---|---|
| Hero | Animated intro, video background, technology word transitions, and a strong personal branding area. |
| About | Personal/professional introduction area. |
| Skills | Category-based display of technical skills retrieved from the database. |
| Experience | Timeline of education and internship experience. |
| Projects | Card/carousel structure of featured projects retrieved from the database. |
| Contact | Contact information and AJAX-supported message form. |
| Footer | Social links, logo, and a short closing area. |

---

## JavaScript Files

| File | Purpose |
|---|---|
| `assets/js/main.js` | Homepage animations, theme/language switching, hero effects, project carousel behavior, and general interface interactions. |
| `assets/js/ajax.js` | Contact form validation, Fetch API submission, and successful submission animation. |
| `assets/js/character-guide.js` | Movement, speech, dragging, and section-detection logic of the interactive character moving around the page. |
| `assets/js/admin-login.js` | Animations and interactions on the admin login screen. |
| `assets/js/admin-panel.js` | Admin panel search, language switching, form helpers, and internal panel UI behavior. |

---

## CSS Files

| File | Purpose |
|---|---|
| `assets/css/style.css` | General design, responsive layout, hero, skills, project, experience, and contact styles of the main portfolio site. |
| `assets/css/character-guide.css` | Appearance and animation styles of the interactive character guide. |
| `assets/css/admin.css` | All visual styles of the admin login screen and admin panel. |

---

## Security Notes

Basic security measures have been implemented in the project:

- PDO is used.
- Prepared statements are preferred for admin and data update operations.
- `password_verify` is used for admin login.
- `session_regenerate_id(true)` is called after login.
- Session cookies are initialized with `httponly` and `samesite=Lax` settings.
- CSRF token checks are used in admin panel forms.
- Values printed to the screen are escaped as HTML using the `e()` function.
- The contact form has both frontend and backend validation.
- The contact form includes a honeypot field against bots.
- The file upload area applies extension checks and a 10 MB size limit.

# Student Event Management System (EMS)

A comprehensive Student Event Management System built with PHP, MySQL, and modern CSS/JavaScript for universities and educational institutions (configured for Institute of Technology, University of Moratuwa - ITUM).

---

## ✨ Features

- **Authentication & Roles**:
  - Role-based access control (`student` and `admin`).
  - Secure password hashing with PHP `password_hash()` and `password_verify()`.
  - Session protection and authentication guards (`requireLogin()`, `requireAdmin()`).
- **Student Portal**:
  - Browse upcoming events with real-time seat availability indicator.
  - Interactive search and filter (filter by event title, organizer, venue, or sort by date/title).
  - Event registration with automatic capacity validation and duplicate registration prevention.
  - Manage and cancel event registrations from personal dashboard (`my_registrations.php`).
- **Admin Management Portal**:
  - Overview metrics: Total events, upcoming events, active registrations, and registered students.
  - Event CRUD: Create new events, edit details/capacity, and delete events.
  - Registrations inspection: Filter attendees by event and status, search by name/email/student ID.
  - One-click CSV Export of event attendee rosters for offline attendance recording.
- **Client-Side Validation & Responsive Design**:
  - Responsive layouts supporting desktop, tablets, and mobile screens.
  - Form validation for signup, login, event creation, and registration.

---

## 🚀 Quick Start / Setup

You can run this project either via **Docker** (recommended) or via a local **XAMPP/WAMP/LAMP** stack.

### Option 1: Docker (Fastest)

1. Ensure Docker & Docker Compose are running on your system.
2. In the project root, start the containers:
   ```bash
   docker-compose up -d
   ```
3. The services will start:
   - **Web App**: [http://localhost:8082](http://localhost:8082)
   - **phpMyAdmin**: [http://localhost:8083](http://localhost:8083)
   - **MySQL**: `localhost:3307` (database initialized automatically via `db_structure.sql`).

### Option 2: Local Server Stack (XAMPP / WAMP)

1. Place this project folder into your web root (e.g., `C:/xampp/htdocs/EMS` or `htdocs/Event-Management-Plan`).
2. Start **Apache** and **MySQL** in your XAMPP Control Panel.
3. Open phpMyAdmin (`http://localhost/phpmyadmin`) and import [`db_structure.sql`](db_structure.sql).
4. If your MySQL credentials differ from defaults (`localhost`, user `root`, no password), adjust them in [`config/database.php`](config/database.php).
5. Open your browser:
   ```
   http://localhost/EMS/index.php
   ```

---

## 🔑 Demo Accounts

The database comes pre-seeded with sample accounts:

| Role | Email | Password |
|---|---|---|
| **Administrator** | `admin@university.edu` | `admin123` |
| **Student** | `john@student.edu` | `student123` |
| **Student** | `sarah@student.edu` | `student123` |

---

## 📁 Project Structure

```text
Event-Management-Plan/
├── admin/
│   ├── dashboard.php            # Admin dashboard: event CRUD & participation metrics
│   └── view_registrations.php   # Registrations viewer, filter & CSV exporter
├── config/
│   └── database.php             # Database PDO connection & authentication helpers
├── css/
│   └── style.css                # Responsive stylesheet & university branding
├── js/
│   ├── validation.js            # Client-side form validators
│   └── search.js                # Live event search and sorting
├── database.php                 # Root forwarder to config/database.php
├── db_structure.sql             # MySQL schema & dynamic seed data
├── docker-compose.yml           # Docker services (PHP 8.2 Apache, MySQL 8.0, phpMyAdmin)
├── Dockerfile                   # Docker image specification with PDO MySQL
├── events.php                   # Public / Student upcoming events catalogue
├── index.php                    # Landing page with hero banner & highlights
├── login.php                    # User sign-in page
├── logout.php                   # Session termination
├── my_registrations.php         # Student registration management
├── my.registrations.php         # Alias / forwarder for backward compatibility
├── register_event.php           # Event registration form with capacity checks
├── signup.php                   # Student registration / account creation
└── readme.md                    # Project documentation
```

---

## 🛡️ Security & Best Practices

- Prepared statements with parameterized PDO queries protect against SQL injection.
- Role-based session verification prevents unauthorized access to admin endpoints.
- XSS prevention via `htmlspecialchars()` on all dynamic user-facing outputs.
- Database credentials can be overridden cleanly via environment variables (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_PORT`).

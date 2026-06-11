# Student Event Management (EMS)

Student Event Management system for creating, browsing and registering for university events.

## Features
- User signup/login (student & admin)
- Browse upcoming events, search & sort 
- Event registration with capacity checks 
- Students can view/cancel registrations 
- Admin dashboard: manage events & view/export registrations 
- Client-side validation 

## Quick Start / Setup
1. Install PHP, MySQL and a server stack (XAMPP/WAMP).
2. Place the project folder in your web root (e.g. htdocs).
3. Import database:
   - Import [db_structure.sql](db_structure.sql) into MySQL to create the database and sample data.
4. Update DB settings if needed: edit [`config/database.php`](config/database.php) and adjust DB constants.
   - Key helpers: [`getDBConnection`](config/database.php), [`isLoggedIn`](config/database.php), [`isAdmin`](config/database.php), [`requireLogin`](config/database.php), [`requireAdmin`](config/database.php)
5. Open in browser: http://localhost/EMS/index.php

## Demo Accounts (from SQL)
- Admin: admin@university.com/ admin123
- Students from sample data: chathu@student.com/ student123, sarah@student.edu / student123

## How to use
- Browse events: [events.php]
- Register (requires login): [register_event.php]
- Sign up: [signup.php]
- Login: [login.php]
- Logout: [logout.php]
- Student registrations: [my.registrations.php]
- Admin pages under [admin/]

## Important files
- Database & auth helpers
  - [`config/database.php`](config/database.php) — DB connection & session helpers 
- Web pages
  - [index.php]
  - [events.php]
  - [register_event.php]
  - [signup.php]
  - [login.php]
  - [logout.php]
  - [my.registrations.php]
- Admin
  - [admin/dashboard.php]
  - [admin/view_registrations.php]
- Assets
  - Styles: [css/style.css]
  - JS validation: [js/validation.js] — includes [`validateRegistrationForm`], [`validateLoginForm`], [`validateSignupForm`], [`validateEventForm`]
  - Search: [js/js/search.js]

## File list (workspace)
- [db_structure.sql]
- [events.php]
- [index.php]
- [login.php]
- [logout.php]
- [my.registrations.php]
- [register_event.php]
- [signup.php]
- admin/
  - [admin/dashboard.php]
  - [admin/view_registrations.php]
  - [config/database.php]
- css/
  - [css/readme.md]
  - [css/style.css]
- js/
  - [js/validation.js]
  - js/js/
    - [js/js/search.js]

## Notes & Tips
- Passwords are hashed using PHP password_hash in SQL sample 
- Add input sanitization and CSRF protection for production.
- To export registrations use the Export button on [admin/view_registrations.php]
## Contributing
- Fork, make changes, and submit pull requests. Keep DB migrations in sync with `db_structure.sql`.

## License
- Add a license as needed.

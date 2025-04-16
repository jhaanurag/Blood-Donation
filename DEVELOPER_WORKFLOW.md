# Developer Workflow & Project Explanation

## Project Overview
This project is a web-based Blood Donation Management System. It connects blood donors with people in need, manages appointments, lists blood camps, and handles blood requests. The system is built with PHP (backend), MySQL/MariaDB (database), HTML/CSS (frontend), and uses PHP's mail() for notifications.

## Folder Structure & Key Files
- `index.php`: Home page, general info.
- `register.php`, `login.php`, `logout.php`: User authentication.
- `search.php`: Search for eligible donors.
- `request.php`: Submit blood requests.
- `camps.php`: List of upcoming blood donation camps.
- `dashboard/`: Donor dashboard (profile, appointments, requests, etc.).
- `includes/`: Shared PHP files (header, footer, authentication, DB connection).
- `mail/send.php`: Handles sending emails.
- `admin_cli.php`: Command-line admin tool for managing data.
- `assets/`: Static files (CSS, images).

## Typical Developer Workflow
1. **Setup**
   - Clone the repo, set up PHP, MySQL, and a web server (see README.md).
   - Import the database schema and configure `includes/db.php`.
2. **Feature Development**
   - Add/modify PHP files for new features (e.g., new page, dashboard section).
   - Use `includes/` for shared logic (e.g., authentication, DB connection).
   - Update SQL queries as needed for new data requirements.
   - For email features, edit `mail/send.php`.
3. **Testing**
   - Test in browser (http://localhost/Blood-Donation/).
   - Check all user flows: registration, login, search, request, booking, etc.
   - Use the CLI tool (`php admin_cli.php --help`) for admin tasks.
4. **Debugging**
   - Enable error reporting in `php.ini` for development.
   - Check browser console for JS/CSS issues.
   - Use `var_dump()` or `error_log()` for PHP debugging.
5. **Deployment**
   - Ensure all config (DB, email) is production-ready.
   - Set correct file permissions.
   - Optionally, use Composer for dependency management.

## Project Workflow (How the Code Works)
- **User Registration/Login:** Users register and log in. Sessions are managed in `includes/auth.php`.
- **Donor Search:** Users search for donors by blood group/location. Results are filtered by eligibility (no donation in last 3 months).
- **Blood Requests:** Users submit requests; matching donors are notified by email.
- **Appointment Booking:** Donors book appointments for camps or direct donation. Managed in `dashboard/appointments.php`.
- **Blood Camps:** Admins add camps (via CLI); users view and book via `camps.php`.
- **Dashboard:** Donors manage their info, appointments, and requests in the dashboard.
- **Admin CLI:** Admins manage all data via `admin_cli.php`.

## Best Practices
- Use `includes/` for shared code.
- Sanitize all user input (see `auth.php`, `db.php`).
- Use prepared statements for SQL (recommended for future security).
- Comment your code for clarity.
- Keep the database schema updated and versioned.

## Extending the Project
- Add new features by creating new PHP files or expanding dashboard modules.
- For new email features, consider using PHPMailer (add via Composer).
- For admin web UI, create a new `admin/` folder and build web pages for management.

---
This file is for developers to quickly understand the project structure, workflow, and how to contribute or extend the system.

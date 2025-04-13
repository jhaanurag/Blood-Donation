# LifeFlow - Blood Donation Management System 🩸

[![Build Status](https://img.shields.io/badge/build-passing-brightgreen)](https://github.com/JhaAnurag/Blood-Donation)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE) <!-- Add a LICENSE file -->

LifeFlow is a web-based platform designed to connect blood donors with individuals and organizations in need. It facilitates donor registration, searching for eligible donors, managing blood donation appointments, listing blood camps, and handling blood requests. The system also includes a command-line interface (CLI) for administrative tasks.

## ✨ Key Features

*   **User Management:** Secure registration and login for blood donors.
*   **Donor Profiles:** Donors can manage their personal information, contact details, blood group, and donation history.
*   **Donor Search:** Find eligible donors based on blood group, location (city/state), and age range. Eligibility check ensures donors haven't donated in the last 3 months.
*   **Blood Requests:** Users can submit requests for specific blood types in their location. Matching donors are notified.
*   **Appointment Booking:** Donors can book appointments for donation, potentially linked to specific blood camps.
*   **Blood Camp Listings:** View upcoming blood donation camps with details on date, location, and description.
*   **Donor Dashboard:** Personalized area for donors to view upcoming appointments, donation history, nearby blood requests, and camps.
*   **Contact Donors:** Logged-in users can view contact information for matched donors (after searching).
*   **Email Notifications:** Automated emails for registration, appointment booking/status updates, and new blood requests matching donor profiles.
*   **Admin CLI Tool (`admin_cli.php`):** Manage donors, camps, appointments, and requests via the command line.
*   **Security:** Includes CSRF token protection on forms.

## 🛠️ Technology Stack

*   **Backend:** PHP
*   **Database:** MySQL / MariaDB (using `mysqli` extension)
*   **Frontend:** HTML, Tailwind CSS, Vanilla JavaScript (for basic interactions like mobile menu)
*   **Email:** PHP `mail()` function (Requires proper server configuration)
*   **Dependency Management:** Composer (for potential future libraries, setup script included)

## 🚀 Setup and Installation

1.  **Prerequisites:**
    *   PHP (>= 7.4 recommended)
    *   MySQL or MariaDB Database Server
    *   Web Server (Apache, Nginx, etc.)
    *   Composer (Optional, but recommended for managing dependencies if added later)

2.  **Clone the Repository:**
    ```bash
    git clone https://github.com/JhaAnurag/Blood-Donation.git
    cd Blood-Donation
    ```

3.  **Database Setup:**
    *   Create a MySQL/MariaDB database (e.g., `blood_donation`).
    *   Import the database schema. **Note:** A `.sql` schema file is needed. Please create one from your development environment and add it to the repository.
    *   Configure the database connection details (host, username, password, database name) in `includes/db.php`.

4.  **Web Server Configuration:**
    *   Configure your web server (Apache Virtual Host or Nginx server block) to point the document root to the project directory.
    *   Ensure `mod_rewrite` (Apache) or equivalent is enabled if you plan to use URL rewriting (currently not strictly necessary based on the structure).

5.  **Email Configuration:**
    *   The system uses PHP's built-in `mail()` function (`mail/send.php`). This requires a correctly configured Mail Transfer Agent (MTA) like Postfix or Sendmail on your server, *or* php.ini configuration to use an external SMTP server. Mail delivery might be unreliable without proper setup, especially on local development machines. Consider using a library like PHPMailer via Composer for more robust email sending in the future.

6.  **Permissions:**
    *   Ensure the web server has the necessary read permissions for the project files.

## ⚙️ Usage

### Web Application

1.  Access the project URL through your web browser (e.g., `http://localhost/Blood-Donation/` or your configured domain).
2.  **Register:** Create a new donor account via the `/register.php` page.
3.  **Login:** Access your account using `/login.php`.
4.  **Search:** Find eligible donors using `/search.php` with various filters.
5.  **Request Blood:** Submit a request for blood via `/request.php`.
6.  **View Camps:** See upcoming blood camps on `/camps.php`.
7.  **Dashboard:** Logged-in donors can manage their profile, book/view/cancel appointments, and see relevant requests/camps in the `/dashboard/` section.
8.  **Contact Donor:** After searching and logging in, click the "Contact" link on the search results page to view donor contact details (`/contact_donor.php`).

### Admin Command-Line Interface (CLI)

The `admin_cli.php` script provides administrative functionalities. Run it from your terminal in the project's root directory.

**Basic Usage:**

```bash
php admin_cli.php --action=<action_name> [options]

Available Actions:

--action=view_donors: View registered donors.

Options: --search, --blood_group, --city, --state

--action=update_donor: Update donor details.

Required: --id=<donor_id>

Optional: --name, --phone, --age, --blood_group, --city, --state, --last_donation_date (YYYY-MM-DD or '' to clear)

--action=delete_donor: Delete a donor (requires confirmation).

Required: --id=<donor_id>

--action=view_camps: View blood camps.

Options: --search, --city, --state

--action=add_camp: Add a new blood camp.

Required: --title, --location, --city, --state, --date (YYYY-MM-DD)

Optional: --description

--action=update_camp: Update camp details.

Required: --id=<camp_id>

Optional: --title, --location, --city, --state, --date, --description

--action=delete_camp: Delete a blood camp (requires confirmation).

Required: --id=<camp_id>

--action=view_appointments: View appointments.

Options: --search (donor name), --status, --donor_id, --date (YYYY-MM-DD)

--action=update_appt_status: Update appointment status.

Required: --id=<appt_id> --status=<new_status>

Valid statuses: pending, approved, completed, rejected

--action=view_requests: View blood requests.

Options: --search (requester name), --status, --blood_group, --city, --state

--action=update_request_status: Update blood request status.

Required: --id=<request_id> --status=<new_status>

Valid statuses: pending, contacted, completed, closed

Help:

php admin_cli.php --help
# or
php admin_cli.php -h

🔮 Future Enhancements (from todo.txt & ideas)

Add LLM Chatbot for user queries.

Implement a dedicated Admin Web Panel instead of relying solely on the CLI.

Integrate a robust email library (e.g., PHPMailer) for SMTP support and better deliverability.

Add password reset functionality ("Forgot Password").

Implement unit and integration tests.

Improve UI/UX and add more visual elements (e.g., maps for camps/donors).

Add notifications within the dashboard.

Provide a .sql database schema file in the repository.

🤝 Contributing

Contributions are welcome! Please follow these steps:

Fork the repository.

Create a new branch (git checkout -b feature/your-feature-name).

Make your changes.

Commit your changes (git commit -m 'Add some feature').

Push to the branch (git push origin feature/your-feature-name).

Open a Pull Request.

Please ensure your code adheres to basic PHP standards and includes comments where necessary.

📜 License

This project is licensed under the MIT License - see the LICENSE file for details

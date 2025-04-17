# Quick Start Guide - Blood Donation System

This guide will help you get the Blood Donation System up and running quickly on your local machine.

## Prerequisites

- XAMPP installed (https://www.apachefriends.org/download.html)
- Git (optional, for cloning the repository)

## Step 1: Install XAMPP

1. Download and install XAMPP from [apachefriends.org](https://www.apachefriends.org/download.html)
2. Start the XAMPP Control Panel
3. Start the Apache and MySQL services by clicking the "Start" buttons

## Step 2: Set Up the Project

### Option A: If you downloaded the project as a ZIP file:

1. Extract the ZIP file to `C:\xampp\htdocs\Blood-Donation`
2. Continue to Step 3

### Option B: If you're using Git:

1. Open a terminal or command prompt
2. Navigate to the XAMPP htdocs directory:
   ```
   cd C:\xampp\htdocs
   ```
3. Clone the repository:
   ```
   git clone https://github.com/JhaAnurag/Blood-Donation.git
   ```
4. Navigate into the project directory:
   ```
   cd Blood-Donation
   ```

## Step 3: Create and Import the Database

1. Open your web browser and go to http://localhost/phpmyadmin
2. Click on "New" in the left sidebar
3. Enter "blood_donation" as the database name and click "Create"
4. Select the newly created "blood_donation" database from the left sidebar
5. Click on the "Import" tab at the top
6. Click "Choose File" and select the `blood_donation.sql` file from your project folder
7. Click "Go" to import the database structure and sample data

## Step 4: Configure Database Connection

1. Open the file `includes/db.php` in a text editor
2. Verify the database settings:
   ```php
   $host = "localhost";  // Your MySQL server host (default: localhost)
   $username = "root";   // Your MySQL username (default: root for XAMPP)
   $password = "";       // Your MySQL password (default: empty for XAMPP)
   $database = "blood_donation";  // The database name
   ```
3. If your XAMPP MySQL setup has a different username or password, update them accordingly

## Step 5: Fix URL Paths (Important!)

The project uses URL paths that need to be adjusted for your local environment. If you experience "Not Found" errors when clicking links, do one of the following:

### Option A: Run the fixer script

1. Open a command prompt/terminal
2. Navigate to the project directory:
   ```
   cd C:\xampp\htdocs\Blood-Donation
   ```
3. Run the link fixer script:
   ```
   php fix_links.php
   ```
4. This will automatically fix all URL paths in the project files

### Option B: Always use the full URL

When accessing the site, always use the full URL including the project folder:
```
http://localhost/Blood-Donation/
```

## Step 6: Run the Setup Check

1. Open your web browser and navigate to http://localhost/Blood-Donation/setup.php
2. The setup script will check your system configuration and database connection
3. Fix any issues that are reported before proceeding

## Step 7: Access the Website

1. Once the setup check passes, click the "Go to Homepage" button or navigate to http://localhost/Blood-Donation/

## Step 8: Use the System

### As a Visitor:
1. Browse the homepage to learn about blood donation
2. Search for donors (blood group, location)
3. View upcoming blood camps
4. Submit blood requests

### As a Donor:
1. Register a new account (http://localhost/Blood-Donation/register.php)
2. Log in with your credentials (http://localhost/Blood-Donation/login.php)
3. Access your dashboard to:
   - Update your profile
   - Book donation appointments
   - View your donation history
   - See blood requests that match your blood type

### As an Administrator:
1. Use the Command Line Interface (CLI) for administration tasks:
   ```
   cd C:\xampp\htdocs\Blood-Donation
   php admin_cli.php --help
   ```
2. Common admin commands:
   - View all donors: `php admin_cli.php --action=view_donors`
   - Add a new blood camp: `php admin_cli.php --action=add_camp --title="Camp Name" --location="Location" --city="City" --state="State" --date="YYYY-MM-DD"`
   - Update appointment status: `php admin_cli.php --action=update_appt_status --id=1 --status=completed`

## Email Configuration (Optional)

For email notifications to work (registration, appointment confirmations, etc.):

1. Configure your XAMPP to send emails by following the instructions in `mail/send.php`
2. Restart Apache after making these configuration changes

## Troubleshooting

- **Database Connection Issues**: Make sure MySQL is running and the credentials in `includes/db.php` are correct
- **Permission Errors**: Ensure the web server has read/write permissions to the project files
- **404 Errors**: Make sure the project is in the correct location (`C:\xampp\htdocs\Blood-Donation`) and you're using the full URL (http://localhost/Blood-Donation/)
- **Email Not Working**: Email configuration is complex on local environments. For testing, you might need to use a mail service or external SMTP.

## Next Steps

- Learn more about the project structure in `PROJECT_WORKFLOW_OVERVIEW.md`
- See developer specific details in `DEVELOPER_WORKFLOW.md`
- Check the full documentation in `README.md` 
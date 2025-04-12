# 🩸 Blood Donation Management System

The **Blood Donation Management System** is a web-based platform designed to connect blood donors with those in need. It allows users to register as donors, book appointments for blood donation, search for donors, request blood, and manage blood donation camps. The system also includes an admin interface for managing donors, camps, and requests.

## ✨ Features

### 👥 Donor Features
- 📝 **Register as a Donor**: Users can register and create a profile.
- 🔐 **Login/Logout**: Secure authentication for donors.
- 📊 **Dashboard**: View upcoming appointments, donation history, and matching blood requests.
- 📅 **Book Appointments**: Schedule blood donation appointments at upcoming camps.
- 🛠️ **Profile Management**: Update personal details like name, phone, and city.
- ✅ **Eligibility Check**: Ensures donors wait at least 3 months between donations.

### 🛠️ Admin Features
- 🏥 **Manage Blood Camps**: Add, update, and delete blood donation camps.
- 🔍 **View Donors**: Search and manage registered donors.
- 🩸 **Manage Blood Requests**: View and update the status of blood requests.
- 📋 **Appointment Management**: View and update the status of appointments.

### 🌐 General Features
- 🔎 **Search Donors**: Find eligible donors based on blood group, city, and state.
- 🆘 **Request Blood**: Submit a blood request and notify matching donors.
- 📧 **Email Notifications**: Send email confirmations and updates for appointments and requests.
- 📱 **Responsive Design**: Optimized for both desktop and mobile devices.

### 🔧 Backend Features
- 🗄️ **Database Integration**: MySQL database to store donor, appointment, and camp data.
- 🔒 **Secure Authentication**: Password hashing and session management for secure login.
- 🛡️ **CSRF Protection**: Prevents cross-site request forgery attacks.
- 📤 **Dynamic Email Notifications**: Sends appointment confirmations and updates using PHP mail functions.
- 🩺 **Eligibility Validation**: Backend logic to ensure donors meet eligibility criteria before booking appointments.
- 💻 **Admin CLI**: Command-line interface for managing camps, donors, and requests.

## 📂 Project Structure

```
Blood-Donation/
├── dashboard/             # Donor dashboard and related pages
├── includes/              # Common includes for the project
├── mail/                  # Email-related functionality
├── admin_cli.php          # Command-line interface for admin actions
├── camps.php              # Displays upcoming blood donation camps
├── login.php              # Login page for donors and admins
├── register.php           # Registration page for new donors
├── request.php            # Blood request submission page
├── search.php             # Donor search page
```

## 🚀 Installation

### Steps to Download and Set Up the Project

1. **📥 Clone the Repository**:
   - Open a terminal and run the following command:
     ```bash
     git clone https://github.com/your-repo/blood-donation.git
     cd blood-donation
     ```

2. **🛠️ Set Up the Database**:
   - Import the `blood_donation.sql` file into your MySQL database.
   - Update the database credentials in `includes/db.php` to match your local setup.

3. **⚙️ Configure the Web Server**:
   - Place the project in your web server's root directory (e.g., `htdocs` for XAMPP).
   - Ensure the server supports PHP and MySQL.

4. **🌐 Test the Application**:
   - Open your browser and navigate to `http://localhost/blood-donation`.

5. **🔑 Admin Access**:
   - Use the `admin_cli.php` script to manage camps, donors, and requests:
     ```bash
     php admin_cli.php --action=<action_name> [options]
     ```

## 🧑‍💻 Usage

### 👤 Donor
1. 📝 Register as a donor on the [Register Page](register.php).
2. 🔐 Log in to access the dashboard.
3. 📅 Book appointments, view donation history, and respond to blood requests.

### 🛠️ Admin
1. Use the `admin_cli.php` script for managing donors, camps, and requests:
   ```bash
   php admin_cli.php --action=<action_name> [options]
   ```
   Example:
   ```bash
   php admin_cli.php --action=add_camp --title="Camp Title" --location="Location" --city="City" --state="State" --date="YYYY-MM-DD"
   ```

## 🛠️ Technologies Used

- 🎨 **Frontend**: HTML, CSS (TailwindCSS), JavaScript
- 🖥️ **Backend**: PHP
- 🗄️ **Database**: MySQL
- 📧 **Email**: PHP `mail()` function

## 📜 License

This project is licensed under the MIT License. See the LICENSE file for details.

## 📞 Contact

For any inquiries or support, please contact:
- 📧 Email: contact@blooddonate.org
- 📞 Phone: +1-800-BLOOD-HELP
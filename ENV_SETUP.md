# Environment Variables Setup

This document explains how to configure environment variables for the Blood Donation System using the `.env` file.

## About the Environment File

The `.env` file is used to store configuration settings that might change between different environments (development, staging, production) or contain sensitive information like database credentials and API keys.

## Setup Instructions

1. The system comes with a default `.env` file with placeholder values
2. Customize the values in this file according to your environment
3. Keep the `.env` file secure and **never commit it to version control**

## Available Configuration Options

### Application Settings
- `APP_NAME`: Name of the application shown in the UI and emails
- `APP_ENV`: Current environment (development, staging, production)
- `APP_URL`: The base URL where the application is hosted
- `APP_EMAIL`: Default email address used for sending notifications

### Database Configuration
- `DB_HOST`: Database server hostname
- `DB_NAME`: Database name
- `DB_USER`: Database username
- `DB_PASS`: Database password

### Email Configuration
- `MAIL_DRIVER`: Mail driver (smtp, sendmail, etc.)
- `MAIL_HOST`: SMTP server hostname
- `MAIL_PORT`: SMTP server port
- `MAIL_USERNAME`: SMTP username
- `MAIL_PASSWORD`: SMTP password or application password
- `MAIL_ENCRYPTION`: Encryption type (tls, ssl)
- `MAIL_FROM_ADDRESS`: Default sender email address
- `MAIL_FROM_NAME`: Default sender name

### AI/Chatbot Configuration
- `GEMINI_API_KEY`: Google Gemini API key for the chatbot
- `AI_MODEL`: AI model to use for the chatbot
- `MAX_TOKENS`: Maximum response length
- `CACHE_ENABLED`: Whether to cache AI responses
- `CACHE_EXPIRY`: Cache expiration time in seconds

### Path Configuration
- `BASE_URL`: Base URL path for the application
- `DASHBOARD_URL`: URL path for the dashboard
- `ROOT_PATH`: Root filesystem path for the application

### Security
- `SESSION_LIFETIME`: Session lifetime in minutes
- `CSRF_TOKEN_TIMEOUT`: CSRF token expiration time in seconds

## Example Configuration

```
APP_NAME="Blood Donation System"
APP_ENV=production
APP_URL=https://blooddonation.example.com
DB_HOST=localhost
DB_USER=bloodadmin
DB_PASS=secure_password
```

## How It Works

The system uses the `includes/env.php` file to load environment variables from the `.env` file. These variables are then used in `includes/config.php` and throughout the application.

If a variable is not defined in the `.env` file, the system will fall back to a default value.
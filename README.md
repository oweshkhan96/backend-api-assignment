# Backend Developer Assignment – User Data Management & Twitter OAuth API

## Overview
This project is a Symfony-based backend that manages user data from a CSV file and integrates Twitter OAuth authentication. It includes API endpoints for user management, database backup/restore, and Twitter authentication.

## Technology Stack
- **Language:** PHP
- **Framework:** Symfony
- **Database:** MySQL
- **Authentication:** Twitter OAuth 1.0a
- **Email Service:** Symfony Mailer with Mailgun

---

## API Endpoints

### Part 1: User Data Management API

#### 1. Upload and Store Data API
- **Endpoint:** `POST /api/upload`
- **Description:** Allows an admin to upload a CSV file (`data.csv`) containing user details.
- **Functionality:**
  - Parses the uploaded CSV file.
  - Saves user data into the MySQL database.
  - Sends an email notification to each user asynchronously.

#### 2. View Data API
- **Endpoint:** `GET /api/users`
- **Description:** Retrieves all stored user data.

#### 3. Backup Database API
- **Endpoint:** `GET /api/backup`
- **Description:** Allows an admin to generate a backup of the database as `backup.sql`.

#### 4. Restore Database API
- **Endpoint:** `POST /api/restore`
- **Description:** Restores the database using a provided `backup.sql` file.
- **Note:** The controller expects the uploaded file with the form-data key `backup_file`.

### Part 2: Twitter OAuth Integration

#### 1. Initiate Twitter Authentication
- **Endpoint:** `GET /auth/twitter`
- **Description:** Redirects the user to Twitter for authentication.  
  **Note:** This endpoint uses OAuth 1.0a to obtain temporary credentials and redirect the user to Twitter's login page. The OAuth flow is best tested via a browser.

#### 2. Handle Twitter Callback
- **Endpoint:** `GET /auth/twitter/callback`
- **Description:** Handles the OAuth response, fetches user details, stores them in MySQL, and redirects the user back to the app.

---

## Installation & Setup

### 1. Clone the Repository
```sh
git clone <repository-url>
cd backend
```
2. Install the Dependencies

```
composer install
```
3. Set Up Environment Variables
Update your .env file with your database, mailer, and Twitter credentials:
```
DATABASE_URL="mysql://username:password@127.0.0.1:3306/db_name"
MAILER_DSN=smtp://Yourname@Yourdomain.com:Yourpassword@smtp.mailgun.org:587
TWITTER_CLIENT_ID=your_twitter_client_id
TWITTER_CLIENT_SECRET=your_twitter_client_secret
TWITTER_CALLBACK_URL=http://127.0.0.1:8000/auth/twitter/callback
```

Replace username, password, db_name, and Twitter keys with your actual credentials.

4. Run Database Migrations

```php bin/console doctrine:migrations:migrate```

5. Start the Symfony Server

```symfony server:start```

Or start the PHP built-in server:

```php -S 127.0.0.1:8000 -t public```

Email Sending with Mailgun
This project uses Mailgun via Symfony Mailer for sending email notifications.
Ensure your Mailgun domain is verified and update the MAILER_DSN in your .env file with your Mailgun credentials.
For reference, watch the video: Mailgun Setup Video
Testing with Postman
1. Upload and Store Data (POST /api/upload)
```Method: POST
URL: http://127.0.0.1:8000/api/upload
```
Body:
Select form-data
Add a key named file (or as defined by your controller)
Set its type to File and choose your data.csv file.
Expected Response
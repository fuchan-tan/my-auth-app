# **OCCAM Full Stack Laravel Developer Challenge (Part A: Authentication)**

## **🌟 Project Overview**

This project implements the required administration portal authentication layer for Part A of the OCCAM Full Stack Developer Challenge. It can Register User Account, Login, Reset Password. Control pages access with authentication.

It leverages **Laravel 10.x** with **Fortify** for robust authentication scaffolding and integrates **Google2FA** to provide Multi-Factor Authentication (MFA). The application utilizes a custom administration theme (Vuexy style) for the frontend presentation and uses **Vite** for asset compilation.

## **🛠️ 1\. Environment Requirements**

The following software is required to run the application locally:

* **PHP:** 8.1 or higher  
* **Composer:** 2.x or higher  
* **Node.js & npm:** 16.x or higher (for Vite and frontend dependencies)  
* **Database:** MySQL 5.7+ or PostgreSQL (or SQLite for development)  
* **Docker & Docker Compose** 

## **💻 2\. Project Setup Instructions**

### **A. Initial Setup**

1. **Clone the repository:**  
   git clone https://github.com/fuchan-tan/my-auth-app.git my-auth-app  
   cd my-auth-app

2. **Install PHP dependencies:**  
   composer install

3. **Set up environment variables:**  
   cp .env.example .env  
   php artisan key:generate

   Configure your database credentials, Mailhog/SMTP settings, and local application URL within the newly created .env file.

### **B. Database Setup**

The application includes migrations for Fortify's Two-Factor Authentication feature.

1. **Run Migrations:**  
   php artisan migrate

2. Seed the Database:  
   The seeder creates a default user for testing the login functionality.

| Field | Value |
| :---- | :---- |
| **Name** | User |
| **Email** | admin@admin.com |
| **Password** | pwd12345 |

3.   
   php artisan db:seed

### **C. Frontend Asset Compilation (Vite)**

1. **Install Node dependencies:**  
   npm install

2. Compile Assets:  
   The frontend assets (CSS/JS) for the admin theme views are compiled using Vite.  
   npm run dev  \# For continuous development  
   \# OR  
   npm run build \# For production deployment

## **🗄️ 3\. Database Schema**

The core authentication logic relies on the standard Laravel users table, which is extended by Laravel Fortify to support Multi-Factor Authentication (MFA).

**Table: users**

| Column Name | Data Type | Nullable | Description |
| :---- | :---- | :---- | :---- |
| id | bigint(20) | No | Primary key. |
| name | varchar(255) | No | User's full name. |
| email | varchar(255) | No | Unique email address. |
| password | varchar(255) | No | Hashed password. |
| two\_factor\_secret | text | Yes | Fortify: Stores the secret key for TOTP (Google Authenticator). |
| two\_factor\_recovery\_codes | text | Yes | Fortify: Stores encrypted list of recovery codes. |
| two\_factor\_confirmed\_at | timestamp | Yes | Fortify: Timestamp when the user confirmed 2FA setup. |
| remember\_token | varchar(100) | Yes | Standard Laravel remember token. |
| created\_at | timestamp | Yes |  |
| updated\_at | timestamp | Yes |  |

## **🌐 4\. Core Authentication Routes (API Documentation)**

Since this part focuses solely on web authentication, the "API" documentation covers the primary routes exposed by Laravel Fortify. These are standard web routes, not a RESTful API.

| Method | URI | Description | Status |
| :---- | :---- | :---- | :---- |
| POST | /auth/login | Authenticates user credentials. | Working |
| POST | /auth/logout | Invalidates the user session. | Working |
| POST | /auth/forgot-password | Sends a password reset link to the provided email. | Working |
| POST | /auth/reset-password | Resets the user's password using a valid token. | Working |
| GET/POST | /auth/two-factor-challenge | MFA challenge screen displayed after successful login (if MFA is enabled). | Working |
| POST | /auth/two-factor-setup | Enables/disables 2FA (requires QR code generation). | Working |

## **🐳 5\. Local Deployment (Option B: Docker)**

The application includes a docker-compose.yml file to facilitate consistent local development environments.

### **A. Docker Prerequisites**

Ensure **Docker** and **Docker Compose** are installed and running on your system.

### **B. Deployment Steps**

1. Build and Start Containers:  
   This command will build the custom PHP/Nginx image and start all services (App, Nginx, MySQL).  
   To build the image, installing NPM/Composer dependencies docker

	docker compose build 

        To start and run migrations/seeding	

compose up \-d

2. Access the Application:  
   Once running, the application should be accessible at: http://localhost:8000 (or the port defined in your docker-compose.yml).  
   The login credentials are: admin@admin.com / pwd12345.

**Thank you for reviewing my submission for Part A: Authentication.**
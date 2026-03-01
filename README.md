# Sistem Manajemen Perpustakaan (Library Management System)

A comprehensive library management system built with CodeIgniter 4 and SQL database, designed to manage books, users, and lending transactions efficiently.

## Features

### 📚 Book Management

- Add, edit, and delete books from the catalog
- Categorize books by genre with caching
- Track book availability and stock
- Detailed book information and descriptions

### 👥 User Management

- User registration and authentication
- Role-based access control (Admin, Librarian, Student/Member)
- User profile management
- Password reset and security features

### 📋 Transaction Management

- Library borrowing and return transactions
- Automated transaction processing
- Class-based book lending (group lending for classes)
- Transaction history and reports

### 🎓 Class Management

- Create and manage student classes
- Assign books to classes
- Track class borrowing activities

### 🌐 Website Management

- CMS for library website
- Configurable website settings
- Responsive design for mobile and desktop

## Tech Stack

- **Framework**: CodeIgniter 4
- **Backend**: PHP 8.1+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap
- **Authentication**: Session-based with password hashing

## Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL/MariaDB database
- Web server (Apache, Nginx, etc.)

### Setup Instructions

1. **Clone or extract the project**

   ```bash
   cd /path/to/project-root
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

3. **Configure environment**

   ```bash
   cp env .env
   ```

   Edit `.env` and configure:
   - `app.baseURL`: Your application URL
   - Database credentials (host, database, user, password)
   - Other application settings

4. **Create database**

   ```bash
   # Create a new database in MySQL
   mysql -u root -p -e "CREATE DATABASE perpustakaan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

5. **Run migrations**

   ```bash
   php spark migrate
   ```

6. **Run seeders (optional)**

   ```bash
   php spark db:seed
   ```

7. **Start the development server**
   ```bash
   php spark serve
   ```
   Access the application at `http://localhost:8080`

## Future Enhancements

- Mobile app integration
- Email notifications for due dates
- Advanced analytics and reports
- Barcode/QR code integration
- Multi-language support
- REST API improvements

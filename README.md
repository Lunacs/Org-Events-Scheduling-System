# Organization Events Scheduling System

A comprehensive event scheduling and management system built with Laravel for managing student organization events, approvals, and notifications.

## Features

-   **Multi-role System**: SuperAdmin, OSA Admin, GSO Admin, and Student Organization roles
-   **Event Request Management**: Submit, track, and manage event requests
-   **Approval Workflow**: Multi-level approval system (OSA & GSO)
-   **Calendar Integration**: Visual event calendar with scheduling
-   **Notifications Center**: Real-time notifications for event updates
-   **Reschedule Requests**: Handle event rescheduling with approval workflow
-   **Reports & Analytics**: Generate reports on events and activities
-   **Archive Management**: Maintain historical event records

## Tech Stack

-   **Framework**: Laravel 11.x
-   **Frontend**: Livewire 3, TailwindCSS 4, DaisyUI
-   **UI Components**: Mary UI
-   **Database**: MySQL/MariaDB or SQLite
-   **Build Tool**: Vite

## Prerequisites

Before you begin, ensure you have the following installed:

-   PHP 8.2 or higher
-   Composer
-   Node.js 18+ and npm
-   MySQL 8.0+ or MariaDB 10.3+ (or use SQLite for development)
-   Git

## Installation & Setup

### 1. Clone the Repository

```bash
git clone <repository-url>
cd org-events-scheduling-system
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Edit the `.env` file with your configuration:

```env
APP_NAME="PLV Event Scheduling System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Manila
APP_URL=http://localhost

# Database Configuration
# Option 1: MySQL/MariaDB
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=org_events_db
DB_USERNAME=root
DB_PASSWORD=

# Option 2: SQLite (for development)
# DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Database Setup

#### For MySQL/MariaDB:

Create the database:

```bash
mysql -u root -p
CREATE DATABASE org_events_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 7. Run Migrations

```bash
php artisan migrate
```

### 8. Seed the Database (Optional)

If seeders are available, run:

```bash
php artisan db:seed
```

### 9. Create Storage Link

```bash
php artisan storage:link
```

### 10. Build Frontend Assets

For development:

```bash
npm run dev
```

For production:

```bash
npm run build
```

### 11. Start the Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Default User Accounts

After running seeders, you can login with these default accounts:

-   **SuperAdmin**: superadmin@plv.edu.ph / password
-   **OSA Admin**: osa@plv.edu.ph / password
-   **GSO Admin**: gso@plv.edu.ph / password
-   **Student Org**: student@plv.edu.ph / password

> **Note**: Change these passwords immediately in production!

## Running in Development

### Concurrent Development Servers

For the best development experience, run both Laravel and Vite servers:

```bash
# Terminal 1 - Laravel server
php artisan serve

# Terminal 2 - Vite dev server
npm run dev
```

### Alternative: Single Command

If using Laragon or similar, the servers may start automatically.

## Project Structure

```
org-events-scheduling-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   └── Providers/
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   └── images/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── layouts/
│       ├── osa/
│       ├── student-orgs/
│       └── superadmin/
├── routes/
│   └── web.php
├── storage/
├── tests/
└── vite.config.js
```

## Available Routes

### Public Routes

-   `/` - Landing page
-   `/login` - Login page
-   `/register` - Registration page

### Student Organization Routes

-   `/student-org/dashboard` - Dashboard
-   `/student-org/submit-ticket` - Submit event request
-   `/student-org/my-tickets` - View submitted tickets
-   `/student-org/calendar` - Event calendar
-   `/student-org/notifications` - Notifications center
-   `/student-org/reschedule` - Reschedule requests
-   `/student-org/history` - Event history

### OSA Admin Routes

-   `/admin/dashboard` - OSA dashboard
-   `/admin/event-req` - Manage event requests
-   `/admin/calendar` - Calendar view
-   `/admin/archive` - Archived events
-   `/admin/reports` - Reports and analytics
-   `/admin/accounts` - User management

### SuperAdmin Routes

-   `/superadmin/dashboard` - SuperAdmin dashboard
-   `/superadmin/users` - User management
-   `/superadmin/roles` - Roles & permissions
-   `/superadmin/settings` - System settings
-   `/superadmin/logs` - Transaction logs

## Troubleshooting

### Issue: "Vite manifest not found"

```bash
npm run build
```

### Issue: "Class not found"

```bash
composer dump-autoload
```

### Issue: "Permission denied" on storage

```bash
chmod -R 775 storage bootstrap/cache
```

### Issue: Database connection error

-   Verify database credentials in `.env`
-   Ensure database server is running
-   Check if database exists

### Issue: NPM packages not installing

```bash
rm -rf node_modules package-lock.json
npm install
```

## Development Guidelines

### Code Style

-   Follow PSR-12 coding standards for PHP
-   Use Laravel's conventions and best practices
-   Keep components modular and reusable

### Git Workflow

1. Create a feature branch from `main`
2. Make your changes
3. Test thoroughly
4. Submit a pull request

### Committing

```bash
git add .
git commit -m "feat: add new feature description"
git push origin feature-branch-name
```

## Testing

Run tests with:

```bash
php artisan test
```

## Deployment

### Production Checklist

-   [ ] Set `APP_ENV=production` in `.env`
-   [ ] Set `APP_DEBUG=false` in `.env`
-   [ ] Generate production app key
-   [ ] Run `composer install --optimize-autoloader --no-dev`
-   [ ] Run `npm run build`
-   [ ] Run migrations: `php artisan migrate --force`
-   [ ] Clear and cache config: `php artisan config:cache`
-   [ ] Clear and cache routes: `php artisan route:cache`
-   [ ] Clear and cache views: `php artisan view:cache`
-   [ ] Clear all cache: `php artisan optimize:clear`
-   [ ] Set proper file permissions
-   [ ] Configure SSL certificate
-   [ ] Set up backup system
-   [ ] Configure queue workers if using queues

## License

This project is proprietary software developed for Pamantasan ng Lungsod ng Valenzuela.

## Credits

Developed by PARENTECH - 2025

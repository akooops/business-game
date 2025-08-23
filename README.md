# Business Game

A comprehensive business simulation game built with Laravel 10 and Svelte 5, featuring company management, production systems, employee management, and economic simulation.

## 🎯 Project Overview

This is a business simulation game where players can:

-   **Manage Companies**: Create and manage virtual companies with various business operations
-   **Production Systems**: Handle production orders, manage machines, and optimize manufacturing processes
-   **Employee Management**: Recruit, manage, and promote employees with different skill sets
-   **Financial Operations**: Manage loans, transactions, and company finances through banks
-   **Supply Chain**: Work with suppliers, manage inventory, and handle purchasing/sales
-   **Technology & Innovation**: Research and implement new technologies to improve operations
-   **Market Dynamics**: Navigate product demands, pricing strategies, and market competition
-   **Geographic Expansion**: Operate across different countries and regions (wilayas)

## 🛠️ Tech Stack

-   **Backend**: Laravel 10 (PHP 8.1+)
-   **Frontend**: Svelte 5 with Vite
-   **Database**: MySQL 8.0
-   **Authentication**: Laravel Sanctum
-   **Styling**: Keenthemes UI components
-   **Development**: Laravel Sail (Docker)

## 📋 Prerequisites

Before you begin, ensure you have the following installed on your machine:

-   **PHP 8.1 or higher**
-   **Composer** (PHP package manager)
-   **Node.js 18+ and npm**
-   **Docker and Docker Compose** (for Laravel Sail)
-   **Git**

## 🚀 Quick Start

### 1. Clone the Repository

```bash
git clone <repository-url>
cd business-game
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Environment Variables

Edit the `.env` file with your database and application settings:

```env
APP_NAME="Business Game"
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=business_game
DB_USERNAME=root
DB_PASSWORD=password

VITE_PORT=5173
```

### 5. Start the Application with Laravel Sail

```bash
# Start all services (Laravel + MySQL)
./vendor/bin/sail up -d

# Or use the alias if configured
sail up -d
```

### 6. Run Database Migrations and Seeders

```bash
# Run migrations
./vendor/bin/sail artisan migrate

# Seed the database with initial data
./vendor/bin/sail artisan db:seed
```

### 7. Build Frontend Assets

```bash
# Development mode
./vendor/bin/sail npm run dev

# Production build
./vendor/bin/sail npm run build
```

## 🌐 Access the Application

-   **Main Application**: http://localhost:8080
-   **Vite Dev Server**: http://localhost:5173

## 🐳 Using Laravel Sail (Docker)

Laravel Sail provides a Docker-based development environment:

```bash
# Start services
sail up -d

# Stop services
sail down

# View logs
sail logs

# Run artisan commands
sail artisan migrate
sail artisan db:seed

# Run tests
sail test

# Access MySQL
sail mysql
```

## 📚 Available Commands

### Artisan Commands

```bash
# Database
php artisan migrate
php artisan migrate:rollback
php artisan migrate:fresh --seed

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Development
php artisan serve
php artisan tinker
php artisan make:controller ControllerName
php artisan make:model ModelName -m
```

### NPM Scripts

```bash
# Development
npm run dev

# Production build
npm run build

# Watch for changes
npm run watch
```

## 🗄️ Database Structure

The application includes several key entities:

-   **Companies**: Core business entities
-   **Users**: System users and authentication
-   **Employees**: Company staff with profiles
-   **Products**: Goods and services
-   **Machines**: Production equipment
-   **Suppliers**: External vendors
-   **Banks**: Financial institutions
-   **Transactions**: Financial records
-   **Inventory**: Stock management
-   **Technologies**: Research and development

## 🔧 Development Workflow

### 1. Feature Development

```bash
# Create a new feature branch
git checkout -b feature/new-feature

# Make your changes
# Run tests
sail test

# Commit and push
git add .
git commit -m "Add new feature"
git push origin feature/new-feature
```

### 2. Testing

```bash
# Run all tests
sail test

# Run specific test file
sail test --filter TestClassName

# Run with coverage
sail test --coverage
```

### 3. Code Quality

```bash
# Format code with Laravel Pint
sail pint

# Check for issues
sail pint --test
```

## 📁 Project Structure

```
business-game/
├── app/                    # Laravel application logic
│   ├── Console/           # Artisan commands
│   ├── Http/              # Controllers, Middleware, Requests
│   ├── Models/            # Eloquent models
│   ├── Services/          # Business logic services
│   └── Traits/            # Reusable traits
├── database/              # Migrations, seeders, factories
├── resources/             # Frontend assets (Svelte components)
├── routes/                # Application routes
├── storage/               # File storage, logs, cache
└── tests/                 # Test files
```

## 🚨 Troubleshooting

### Common Issues

1. **Port conflicts**: Ensure ports 8080 and 5173 are available
2. **Permission issues**: Run `chmod -R 777 storage bootstrap/cache` if needed
3. **Database connection**: Verify MySQL is running and credentials are correct
4. **Composer issues**: Clear composer cache with `composer clear-cache`

### Reset Development Environment

```bash
# Stop all services
sail down

# Remove volumes (WARNING: This will delete all data)
sail down -v

# Rebuild containers
sail build --no-cache

# Start fresh
sail up -d
sail artisan migrate:fresh --seed
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Ensure all tests pass
6. Submit a pull request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🆘 Support

For support and questions:

-   Check the Laravel documentation: https://laravel.com/docs
-   Review Svelte documentation: https://svelte.dev/docs
-   Open an issue in the repository

---

**Happy coding! 🎮💼**

# CalcTek Calculator

A modern API-driven calculator built with Laravel 12, Vue 3, Inertia.js, and Tailwind CSS.

## Features
- Basic arithmetic and advanced math functions (sqrt, ^, sin, cos, tan, log, abs, PI, E)
- Calculation history with delete and clear-all
- Responsive, beautiful UI
- Fully tested backend and frontend

## Prerequisites
- PHP >= 8.4
- Composer
- Node.js >= 18 & npm
- SQLite (default) or MySQL/Postgres

## Setup Instructions

### 1. Clone the repository
```sh
git clone <your-repo-url>
cd calctek-bryan
```

### 2. Install PHP dependencies
```sh
composer install
```

### 3. Install Node dependencies
```sh
npm install
```

### 4. Copy and configure environment
```sh
cp .env.example .env
php artisan key:generate
```

### 5. Set up the database
- By default, uses SQLite. To use MySQL/Postgres, update `.env` accordingly.
```sh
touch database/database.sqlite
php artisan migrate
```

### 6. Build frontend assets
```sh
npm run build
```

### 7. (Optional) Run in development mode
- Start Laravel backend:
```sh
php artisan serve
```
- Start Vite dev server (for hot reload):
```sh
npm run dev
```

### 8. Run tests
```sh
php artisan test
```

## Usage
- Visit [http://localhost:8000](http://localhost:8000) in your browser.
- Use the calculator and see your calculation history update in real time.

## Troubleshooting
- If you see a Vite manifest error, run `npm run build` or `npm run dev`.
- For any issues, check browser console and Laravel logs.

## License
MIT

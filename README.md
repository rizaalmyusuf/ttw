# TTW

TTW is a Laravel 12 application with Filament and Livewire. It uses Vite for frontend assets and is configured for a MySQL-backed development environment.

## Requirements

Make sure the following tools are installed before you start:

- PHP 8.2 or newer
- Composer
- Node.js and npm
- MySQL

## Install

1. Install the PHP dependencies.

```bash
composer install
```

2. Install the frontend dependencies.

```bash
npm install
```

3. Create your local environment file and update the database credentials.

```bash
copy .env.example .env
```

4. Generate the application key.

```bash
php artisan key:generate
```

5. Create and run the database tables.

```bash
php artisan migrate
```

## Build

Build the production frontend assets with Vite:

```bash
npm run build
```

## Start

For local development, the fastest option is the bundled Composer script. It starts the Laravel server, queue listener, and Vite dev server together.

```bash
composer run dev
```

If you prefer to run each process separately, use these commands in different terminals:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

## Project Stack

- Laravel 12
- Filament admin panel
- Livewire components
- Vite build pipeline
- MySQL database

## Notes

- The default environment file uses MySQL connection settings; update `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` for your machine.
- `composer run dev` is the recommended development entry point because it keeps the application server, queue worker, and asset pipeline in sync.

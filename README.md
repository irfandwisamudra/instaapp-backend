# InstaApp Backend API

Robust RESTful API backend for **InstaApp**, built with **Laravel**, **Laravel Sanctum**, **MySQL**, and **PHP**.

---

## Live Demo & Endpoints

- **API Base URL (Backend)**: [https://instaapp-backend.webcoder.id](https://instaapp-backend.webcoder.id)
- **Live Application (Frontend)**: [https://instaapp.webcoder.id](https://instaapp.webcoder.id)

---

## Key Features

- **SPA Authentication**: First-party cookie-based authentication via Laravel Sanctum (CSRF cookie protection).
- **Post & Media Management**: Post creation, image uploads, post updates, and image deletion.
- **Social Feed & Interactivity**: Paginated post feed, post likes/unlikes, and threaded comments.
- **User Profiles**: User bio, profile avatar uploads, and user-specific post timelines.
- **RESTful Endpoints**: Clean API resources with consistent JSON error envelopes and status codes.

---

## Getting Started

### Prerequisites

- PHP `^8.2` or `^8.3`
- Composer `v2.x`
- MySQL `8.0+`

### 1. Environment Setup

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Configure your `.env` settings:

```env
APP_NAME=InstaApp
APP_ENV=local
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=instaapp
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
CORS_ALLOWED_ORIGINS=http://localhost:3000
```

### 2. Install Dependencies & Generate Key

```bash
composer install
php artisan key:generate
```

### 3. Run Migrations & Seeders

```bash
php artisan migrate --seed
php artisan storage:link
```

### 4. Start Local Development Server

```bash
php artisan serve
```

The API will be available at [http://localhost:8000](http://localhost:8000).

---

## Testing

Run Pest / PHPUnit tests:

```bash
php artisan test
```

---

## License

The InstaApp Backend is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

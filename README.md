# Ylmz PHP Framework

A lightweight PHP MVC framework.

## Requirements

- PHP >= 8.0
- Composer

## Quick Start

```bash
composer install
cp .env.example .env
# Edit .env with your database settings
php -S localhost:8000
```

## Directory Structure

```
├── core/          # Framework core
│   ├── Http/      # Request / Response / Middleware
│   ├── Cache/     # Cache drivers
│   ├── Log/       # Log drivers
│   └── ...
├── app/           # Application
│   ├── Ctrl/      # Controllers
│   ├── Model/     # Models
│   ├── Api/       # API controllers
│   └── view/      # Twig templates
├── runtime/       # Runtime data (logs, cache, twig)
└── public/        # Static assets
```

## Routing

- **Explicit routes**: `$app->getRouter()->get('/path', [Controller::class, 'method']);`
- **Auto routing**: `/controller/method` → `App\Ctrl\ControllerCtrl::method()`
- **Middleware**: `$app->getRouter()->group([AuthMiddleware::class], fn() => ...);`

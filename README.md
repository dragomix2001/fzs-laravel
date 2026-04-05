# FZS Laravel Application

Факултет за спорт - Студентски информациони систем

Laravel-based student information system for Faculty of Sports and Physical Education. Handles student applications, enrollment, exams, grading, and administrative workflows.

## 📋 Business Domain

This application manages the complete lifecycle of student candidates:
- **Kandidat (Applicant)**: Student candidates applying to the faculty
- **Prijava (Application)**: Two-step application process
- **Ispit (Exam)**: Entrance exams and course exams
- **Upis (Enrollment)**: Student enrollment by academic year
- **Bodovanje (Scoring)**: Combined scoring from exam + grades + sports

For detailed domain concepts, see [docs/DOMAIN.md](docs/DOMAIN.md).

## 🏗️ Architecture

**Application Structure:**
- Controllers: HTTP request handlers (thin layer)
- Services: Business logic orchestrators (KandidatService, IspitService)
- Models: 57 Eloquent ORM models
- Requests: 22 Form validators
- Policies: Authorization

**Known Technical Debt:**
- God Services: KandidatService (935 lines), IspitService (723 lines)
- Request coupling in some services
- Direct Facade usage (Cache, Storage, DB)

See [docs/ADR/](docs/ADR/) for architectural decision records.

## 📊 Test Coverage

- **60% code coverage** (3,721/6,207 executable lines)
- **468 feature tests** covering controllers and business logic
- CI/CD: GitHub Actions (Laravel CI/CD + CodeQL Advanced)

## Технички стек

- **Backend**: Laravel 10.x
- **Frontend**: Bootstrap 5, jQuery, Tailwind CSS
- **Database**: MySQL 8.0
- **Authentication**: Laravel built-in auth
- **Testing**: PHPUnit, PHPStan, Pint

## Инсталација

```bash
# Инсталирај зависности
composer install
npm install

# Копирај .env фајл
cp .env.example .env

# Генериши кључ
php artisan key:generate

# Покрени миграције
php artisan migrate

# Покрени сеeder (тест подаци)
php artisan db:seed
```

## Тест корисник

| Параметар | Вредност |
|-----------|----------|
| Email | fzs@fzs.rs |
| Лозинка | fzs123 |

## Код форматирање

```bash
# Инсталирај Pint (већ је инсталиран)
composer require laravel/pint --dev

# Форматирај код
./vendor/bin/pint
```

## Telescope (опционо)

За debug и monitoring:

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

## Развој

```bash
# Покрени dev сервер
npm run dev
php artisan serve

# Покрени тестове
./vendor/bin/phpunit
```

## Тестови

Тренутно: **468 testova**, 60% code coverage

```bash
# Покрени тестове (SEQUENTIALLY ONLY - shared MySQL DB)
./vendor/bin/phpunit

# Покрени са извештајем
./vendor/bin/phpunit --testdox

# PHPStan static analysis
./vendor/bin/phpstan analyse

# Pint code style
./vendor/bin/pint
```

## Роуте

Главне руте:
- `/` - Home (захтева ауторизацију)
- `/login` - Пријава
- `/dashboard` - Контролна табла
- `/student/*` - Студентске руте
- `/kandidat/*` - Кандидати
- `/ispit/*` - Ispiti
- `/predmet/*` - Predmeti
- `/profesor/*` - Profesori

## Безбедност

- Све руте осим `/login` захтевају ауторизацију
- CSRF заштита укључена
- SQL инјекција - заштићено кроз Eloquent
- XSS заштита - Blade template engine
- FormRequest validation на свим input-има

## 📖 Documentation

- [Domain Glossary](docs/DOMAIN.md) - Business entities and workflows
- [Architecture Decision Records](docs/ADR/) - Technical debt documentation
  - [ADR-001: God Services](docs/ADR/001-god-services.md)
  - [ADR-002: Request Coupling](docs/ADR/002-request-coupling.md)
  - [ADR-003: Facade Usage](docs/ADR/003-facade-usage.md)

## Лиценца

MIT

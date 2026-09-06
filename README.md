# FZS Laravel Application

Факултет за спорт - Студентски информациони систем

Laravel-based student information system for Faculty of Sports and Physical Education. Handles student applications, enrollment, exams, grading, and administrative workflows.

## 📋 Business Domain

This application manages the complete lifecycle of student candidates:
- **Kandidat (Applicant)**: Student candidates applying to the faculty
- **Prijava (Application)**: Two-step application process
- **Dokumentacija kandidata**: Per-document upload with admin review, approval, rejection, and revision requests
- **Ispit (Exam)**: Entrance exams and course exams
- **Upis (Enrollment)**: Student enrollment by academic year
- **Bodovanje (Scoring)**: Combined scoring from exam + grades + sports

For detailed domain concepts, see [docs/DOMAIN.md](docs/DOMAIN.md).

## 🏗️ Architecture

**Application Structure:**
- Controllers: HTTP request handlers
- Services: domain services split by candidate, enrollment, exam, registration, PDF, document and notification concerns
- Models: Eloquent ORM models for candidates, programs, exams, enrollment and documents
- Requests: 31 Form Request validators
- Policies: Authorization

**Known Technical Debt:**
- KandidatService — decomposed with dedicated grade, sports, document and enrollment services
- IspitService — decomposed with dedicated zapisnik creation, cleanup, membership, result, listing and PDF services
- Direct Facade usage (Cache, Storage, DB) — accepted, see ADR-003

See [docs/ADR/](docs/ADR/) for architectural decision records.

## 📊 Test Coverage

- **1814 PHPUnit tests, 4640 assertions** — 0 errors, 0 failures
- **89 Playwright E2E tests** — authenticated, mobile, AJAX, API, PDF, menu and operational flows
- **PHPStan level 5** — 0 errors (empty baseline)
- **Pint** code style: pass
- CI/CD: GitHub Actions (PHPUnit, Playwright E2E, Pint, PHPStan and CodeQL)

## Технички стек

- **Backend**: Laravel 13.x
- **PHP**: 8.3+
- **Frontend**: Laravel Blade, Tailwind CSS, Alpine.js, jQuery and selected legacy Bootstrap styles
- **Database**: MySQL 8.0
- **Authentication**: Laravel built-in auth
- **Testing**: PHPUnit, Playwright, PHPStan, Pint

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

## Тест корисници

| Улога | Email | Лозинка |
|------|-------|---------|
| Admin | admin.test@fzs.rs | AdminTest123 |
| Admin | fzs@fzs.rs | fzs123 |

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

Тренутно: **1814 PHPUnit тестова**, 4640 асерција и **89 Playwright E2E тестова**.

```bash
# Покрени све PHPUnit тестове
./vendor/bin/phpunit

# Покрени samo Unit ili Feature suite
composer test:unit
composer test:feature

# Покрени са извештајем
./vendor/bin/phpunit --testdox

# Frontend/browser E2E testovi
npm run test:e2e

# Detaljna dokumentacija testiranja
# docs/TESTING.md

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
- `/kandidat-dokumentacija` - Admin преглед кандидата са непотпуном документацијом
- `/kandidat/{kandidat}/documents/review` - Admin review по кандидату
- `/ispit/*` - Ispiti
- `/predmet/*` - Predmeti
- `/profesor/*` - Profesori

## Документација кандидата

- Кандидат при upload-у може да приложи фајл по појединачном документу
- Метаподаци о фајлу се чувају у `kandidat_prilozena_dokumenta` (`file_path`, `file_name`, `mime_type`, `file_size`)
- Сваки приложени документ добија review статус: `pending`, `approved`, `rejected`, `needs_revision`
- Admin workflow је доступан кроз мени `Кандидати -> Преглед документације`
- При одобравању свих обавезних докумената кандидат добија нотификацију да је документација комплетна

## Безбедност

- Све руте осим `/login` захтевају ауторизацију
- CSRF заштита укључена
- SQL инјекција - заштићено кроз Eloquent
- XSS заштита - Blade template engine
- FormRequest validation на свим input-има

## 📖 Documentation

- [Domain Glossary](docs/DOMAIN.md) - Business entities and workflows
- [Improvements Roadmap](README_IMPROVEMENTS.md) - Remaining architectural improvements
- [Architecture Decision Records](docs/ADR/) - Technical debt documentation
  - [ADR-001: God Services](docs/ADR/001-god-services.md)
  - [ADR-002: Request Coupling](docs/ADR/002-request-coupling.md)
  - [ADR-003: Facade Usage](docs/ADR/003-facade-usage.md)
- [God Services Refactoring Roadmap](docs/ROADMAP/god-services-refactoring.md)

## Лиценца

MIT

# FZS Laravel Application

Факултет за спорт - Студентски информациони систем

## Технички стек

- **Backend**: Laravel 11.x
- **Frontend**: Bootstrap 5, jQuery 4.0, Tailwind CSS 4.x
- **Database**: MySQL
- **Authentication**: Laravel Breeze/Jetstream

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

Тренутно: **52 теста**, сви пролазе (3 прескочена због постојећих багова у view-овима)

```bash
# Покрени само route тестове
./vendor/bin/phpunit --filter=RouteTest

# Покрени са извештајем
./vendor/bin/phpunit --testdox
```

## Роуте

Главне руте:
- `/` - Home (захтева ауторизацију)
- `/login` - Пријава
- `/dashboard` - Контролна табла
- `/student/*` - Студентске руте
- `/kandidat/*` - Кандидати

## Безбедност

- Све руте осим `/login` захтевају ауторизацију
- CSRF заштита укључена
- SQL инјекција - заштићено кроз Eloquent
- XSS заштита - Blade template engine

## Лиценца

MIT

# HOAI Offer System

Laravel app for **offers** made of **groups**, each with **manual lines** (quantity × unit price) and **HOAI-style** lines (simplified fee calculation from costs, zone, rate, phases, markups, and VAT).

## Requirements

- PHP **8.4+**
- [Composer](https://getcomposer.org/)
- Node.js **20+** (for Vite / frontend)

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # if the file does not exist yet
php artisan migrate --seed
npm install
npm run dev                        # terminal 1
php artisan serve                  # terminal 2
```

Open the URL `php artisan serve` prints (often `http://127.0.0.1:8000`). Default DB in `.env.example` is **SQLite** (`DB_DATABASE` path).

Or use the project script (installs deps, migrates, builds assets):

```bash
composer run setup
```

## What’s inside

| Area | Notes |
|------|--------|
| **UI** | Livewire **Volt** — offer list (`/`) and offer workspace (`/offers/{offer}`), Flux + Tailwind |
| **HOAI math** | `config/hoai.php` + `App\Services\HoaiService\HoaiCalculatorService` (`HoaiCalculatorContract`) |
| **Writes** | `App\Actions\*` for creating/updating manual and HOAI positions |
| **Demo data** | `OfferDemoSeeder` (10 sample offers) via `php artisan migrate --seed` |

More detail: **[SPEC.md](SPEC.md)**.

## Tests

```bash
php artisan test --compact
```

Browser tests (under `resources/views/...`) need Playwright installed and up to date:

```bash
npm install playwright@latest && npx playwright install
```

## Code style (PHP)

```bash
vendor/bin/pint --dirty
```

## License

MIT (see `composer.json`).

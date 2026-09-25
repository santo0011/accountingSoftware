# BizSetu — Business Service Platform

A business-services website, customer portal, CRM and compliance system built with Laravel 12, Blade, Bootstrap 5 and MySQL. It covers company registration, GST, trademark, licences, legal, HR and technology services.

## Quick start (XAMPP, Windows)

Requirements: PHP 8.2+, Composer and MySQL/MariaDB (XAMPP). Node is **not** required; Bootstrap, Bootstrap Icons and Chart.js are bundled in `public/vendor`.

```powershell
cd "C:\xampp\htdocs\Accounting Software\platform"
composer install                      # only needed on a fresh copy
php artisan migrate:fresh --seed      # creates all tables + demo data in the `accounting` DB
php artisan storage:link              # only needed once
php artisan serve                     # → http://127.0.0.1:8000
```

The database settings are in `.env`: `DB_DATABASE=accounting`, `DB_USERNAME=root` and an empty password.

### Demo logins (password `Password@123`)

| Role | Login |
|---|---|
| Super Admin | `admin@bizsetu.test` |
| Admin | `admin.ops@bizsetu.test` |
| Staff | `rahul.staff@bizsetu.test`, `kavya.staff@bizsetu.test` |
| Accountant | `accounts@bizsetu.test` |
| HR | `hr@bizsetu.test` |
| Tax / Legal Professional | `vikram.ca@bizsetu.test`, `pooja.cs@bizsetu.test` |
| Customer | `customer@bizsetu.test` or mobile `9876500001` |

Customers log in with their **email or mobile number**. Staff and professionals go to `/admin`; customers go to `/account`.

## Areas

| URL | What it is |
|---|---|
| `/` | Public website: services, categories, pricing, FAQ, contact, policies, sitemap |
| `/services/{slug}` | Service detail page: overview, documents, process, pricing, FAQ, Apply Now |
| `/account` | Customer portal: dashboard, apply wizard, documents, payments, invoices, compliance calendar, support |
| `/admin` | Admin panel: CRM, applications, documents, payments, invoices, staff, professionals, roles, tasks, compliance, support, reports, website content, settings, audit log |

## How the key workflows run

1. **Apply**: the customer picks a service and completes a 3-step wizard (details, documents, review). The system creates application `APP-2026-000001` and an unpaid GST invoice (CGST+SGST if the customer is in the same state as the company, IGST otherwise).
2. **Pay**: payment goes through the `PaymentGateway` interface (`app/Contracts/Payments`).
   - `manual`: the customer pays by UPI or bank transfer and enters the reference number. Staff then confirm it in **Admin → Payments**.
   - `sandbox`: payment succeeds instantly, for testing only. Set `PAYMENT_SANDBOX_ENABLED=false` in production.
   - To add Razorpay, implement the interface and register the class in `config/payments.php`.
3. **Process**: staff assign a relationship manager and a professional, verify or reject documents (the customer re-uploads rejected ones), request extra documents, add notes and move the status through its stages. The customer sees a live timeline.
4. **Complete**: staff upload the final certificates. For recurring services (GST return, TDS, bookkeeping, annual compliance and so on) the system automatically creates a subscription and the first compliance due date. Marking a due date "Done" creates the next period's record.
5. **Reminders**: `php artisan compliance:run` updates the Upcoming, Due Soon and Overdue statuses and sends reminders. The scheduler runs it every day at 07:00.

## Production checklist

- `.env`: set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://yourdomain`, `QUEUE_CONNECTION=database` and `PAYMENT_SANDBOX_ENABLED=false`.
- Seed without demo data: `php artisan migrate --force && php artisan db:seed --force`. The demo seeder only runs when `APP_ENV=local`.
- Change every demo password, or delete the demo users.
- Scheduler: run `php artisan schedule:run` every minute (cron, or Windows Task Scheduler).
- Queue worker: `php artisan queue:work`, which sends email notifications in the background.
- Email: set SMTP in **Admin → Settings → Notifications & email**, or in `.env`. Until then, emails are written to `storage/logs/laravel.log`.
- Point the web server's document root at `platform/public`, add your domain to `public/robots.txt`, and run `php artisan optimize`.

## Development

```powershell
php artisan test          # 16 feature tests: every page, permissions, full apply→pay→verify→complete→compliance flow
php artisan route:list --except-vendor
```

Code map:

- `app/Services`: business logic (Application, Document, Payment, Invoice, Compliance, Lead, Ticket, Report). Controllers stay thin.
- `app/Policies`: record-level access. Customers see only their own records; staff see only what is assigned to them unless their role has *view all*.
- `config/rbac.php`: permissions and default roles. Roles can be edited later in **Admin → Roles & Permissions**.
- `database/seeders/data/catalog.php`: the demo catalogue (15 categories, 60 services). All site content comes from the database and can be edited in the admin panel.
- `public/assets/css/app.css`: design tokens. Change the brand colours in `:root`.

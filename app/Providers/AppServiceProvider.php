<?php

namespace App\Providers;

use App\Models\ServiceCategory;
use App\Services\SettingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public const MENU_CACHE_KEY = 'site.menu_categories';

    public function register(): void
    {
        $this->app->singleton(SettingService::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Paginator::defaultView('pagination.panel'); // numbered pages with first / prev / next / last

        Password::defaults(fn () => Password::min(6));

        // Super Admin passes every permission check.
        Gate::before(fn ($user) => $user->hasRole(config('rbac.super_admin_role')) ? true : null);

        RateLimiter::for('forms', fn (Request $request) => Limit::perMinute(6)->by($request->ip()));
        RateLimiter::for('uploads', fn (Request $request) => Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()));

        $this->applyMailSettings();

        // Mega-menu / footer categories for every public page.
        View::composer(['layouts.site', 'site.partials.*'], function ($view) {
            $view->with('menuCategories', Cache::remember(self::MENU_CACHE_KEY, now()->addHour(), fn () => ServiceCategory::active()
                ->with(['activeServices' => fn ($q) => $q->select('id', 'service_category_id', 'name', 'slug', 'icon', 'is_featured', 'short_description', 'tagline')])
                ->get(['id', 'name', 'slug', 'icon', 'tagline', 'banner_image'])));
        });

        // Unread notifications for the portal / admin top bar.
        View::composer(['layouts.portal', 'layouts.admin'], function ($view) {
            if ($user = auth()->user()) {
                $view->with('unreadNotifications', $user->unreadNotifications()->latest()->limit(6)->get())
                    ->with('unreadCount', $user->unreadNotifications()->count());
            }
        });
    }

    /** SMTP settings saved in Admin → Settings override .env values. */
    private function applyMailSettings(): void
    {
        $settings = $this->app->make(SettingService::class);

        if ($host = $settings->get('mail_host')) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => (int) $settings->get('mail_port', 587),
                'mail.mailers.smtp.username' => $settings->get('mail_username'),
                'mail.mailers.smtp.password' => $settings->get('mail_password') ? rescue(fn () => decrypt($settings->get('mail_password')), null, false) : null,
                'mail.mailers.smtp.scheme' => $settings->get('mail_encryption') === 'ssl' ? 'smtps' : null,
            ]);
        }

        if ($from = $settings->get('mail_from_address')) {
            config(['mail.from.address' => $from, 'mail.from.name' => $settings->get('mail_from_name') ?: $settings->get('company_name', config('app.name'))]);
        }
    }
}

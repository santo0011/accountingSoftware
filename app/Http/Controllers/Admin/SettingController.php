<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use App\Support\FileTypes;
use App\Support\SiteCache;
use Database\Seeders\SettingSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:settings.manage')];
    }

    public function __construct(private SettingService $settings) {}

    public function edit(): View
    {
        return view('admin.settings.edit', ['s' => $this->settings->all()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:100'],
            'company_legal_name' => ['nullable', 'string', 'max:200'],
            'tagline' => ['nullable', 'string', 'max:200'],
            'company_email' => ['required', 'email', 'max:255'],
            'company_phone' => ['required', 'string', 'max:30'],
            'company_whatsapp' => ['nullable', 'digits_between:10,15'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_state' => ['required', 'string', 'max:100'],
            'company_gstin' => ['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/'],
            'company_pan' => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'business_hours' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'file', FileTypes::rule(FileTypes::WEB_IMAGES), 'max:2048'], // 2 MB
            'favicon' => ['nullable', 'file', FileTypes::rule(['png', 'ico', 'jpg', 'jpeg', 'jfif', 'gif']), 'max:256'],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_linkedin' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_x' => ['nullable', 'url', 'max:255'],
            'social_youtube' => ['nullable', 'url', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'currency_symbol' => ['required', 'string', 'max:5'],
            'default_tax' => ['required', 'numeric', Rule::in([0, 5, 12, 18, 28])],
            'default_sac_code' => ['nullable', 'string', 'max:10'],
            'invoice_prefix' => ['required', 'alpha_num', 'max:10'],
            'application_prefix' => ['required', 'alpha_num', 'max:10'],
            'payment_prefix' => ['required', 'alpha_num', 'max:10'],
            'ticket_prefix' => ['required', 'alpha_num', 'max:10'],
            'invoice_terms' => ['nullable', 'string', 'max:1000'],
            'bank_details' => ['nullable', 'string', 'max:1000'],
            'notify_email_enabled' => ['boolean'],
            'compliance_reminder_days' => ['required', 'integer', 'between:1,60'],
            'admin_notification_email' => ['nullable', 'email'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'between:1,65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', Rule::in(['tls', 'ssl'])],
            'mail_from_address' => ['nullable', 'email'],
            'mail_from_name' => ['nullable', 'string', 'max:100'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
            'google_analytics_id' => ['nullable', 'string', 'max:30', 'regex:/^[A-Z0-9\-]+$/'],
            'hero_image' => ['nullable', 'file', FileTypes::rule(FileTypes::WEB_IMAGES), 'max:2048'],
            'hero_image_reset' => ['boolean'],
            'stat_customers' => ['nullable', 'string', 'max:20'],
            'stat_services' => ['nullable', 'string', 'max:20'],
            'stat_experts' => ['nullable', 'string', 'max:20'],
            'stat_rating' => ['nullable', 'string', 'max:20'],
        ]);

        unset($data['hero_image_reset']);

        foreach (['logo' => 'branding', 'favicon' => 'branding', 'hero_image' => 'site'] as $field => $dir) {
            unset($data[$field]);
            if ($field === 'hero_image' && $request->boolean('hero_image_reset') && ! $request->hasFile($field)) {
                // "Use default image" drops the upload so the bundled hero shows again.
                if ($old = $this->settings->get($field)) {
                    Storage::disk('public')->delete($old);
                }
                $data[$field] = null;
            }
            if ($request->hasFile($field)) {
                if ($old = $this->settings->get($field)) {
                    Storage::disk('public')->delete($old);
                }
                $data[$field] = $request->file($field)->store($dir, 'public');
            }
        }

        // Keep the stored SMTP password unless a new one is typed; store it encrypted.
        if (filled($data['mail_password'] ?? null)) {
            $data['mail_password'] = encrypt($data['mail_password']);
        } else {
            unset($data['mail_password']);
        }

        foreach ($this->groupOf($data) as $group => $values) {
            $this->settings->set($values, $group);
        }
        SiteCache::flush();

        activity('settings')->causedBy($request->user())->withProperties(['keys' => array_keys($data)])->log('Updated settings');

        return back()->with('success', 'Settings saved.');
    }

    /** Group submitted keys the same way as the seeder defaults. */
    /** Send a test email using the SMTP settings currently saved (applied at boot by AppServiceProvider). */
    public function testMail(Request $request): RedirectResponse
    {
        $data = $request->validate(['test_email' => ['required', 'email']]);

        try {
            Mail::raw(
                "This is a test email from {$this->settings->get('company_name', config('app.name'))}.\n\n"
                ."If you are reading this, your SMTP settings are working.\n\nSent: ".now()->format('d M Y, h:i A'),
                fn ($m) => $m->to($data['test_email'])->subject('Test email — SMTP is working')
            );
        } catch (\Throwable $e) {
            return redirect()->to(route('admin.settings.edit').'#email')
                ->with('error', 'Test email failed: '.Str::limit($e->getMessage(), 220));
        }

        $mailer = config('mail.default');
        $note = $mailer === 'smtp' ? '' : " (mailer is \"{$mailer}\", so it was not really delivered — fill in the SMTP host to send real emails)";

        return redirect()->to(route('admin.settings.edit').'#email')
            ->with('success', "Test email sent to {$data['test_email']}{$note}.");
    }

    private function groupOf(array $data): array
    {
        $groups = [];
        foreach (SettingSeeder::DEFAULTS as $group => $defaults) {
            $values = array_intersect_key($data, $defaults);
            if ($values) {
                $groups[$group] = $values;
            }
        }

        return $groups;
    }
}

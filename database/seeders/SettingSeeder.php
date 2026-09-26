<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public const DEFAULTS = [
        'general' => [
            'company_name' => 'BizSetu',
            'company_legal_name' => 'BizSetu Business Services Pvt. Ltd.',
            'tagline' => 'Start, Manage & Grow Your Business',
            'company_email' => 'hello@bizsetu.in',
            'company_phone' => '+91 98765 43210',
            'company_whatsapp' => '919876543210',
            'company_address' => '4th Floor, Business Hub, Andheri East, Mumbai 400069',
            'company_state' => 'Maharashtra',
            'company_gstin' => '27ABCDE1234F1Z5',
            'company_pan' => 'ABCDE1234F',
            'business_hours' => 'Mon – Sat, 9:30 AM – 7:00 PM',
            'logo' => null,
            'favicon' => null,
        ],
        'social' => [
            'social_facebook' => 'https://facebook.com/',
            'social_linkedin' => 'https://linkedin.com/',
            'social_instagram' => 'https://instagram.com/',
            'social_x' => 'https://x.com/',
            'social_youtube' => 'https://youtube.com/',
        ],
        'billing' => [
            'currency' => 'INR',
            'currency_symbol' => '₹',
            'default_tax' => '18',
            'default_sac_code' => '998399',
            'invoice_prefix' => 'INV',
            'application_prefix' => 'APP',
            'payment_prefix' => 'PAY',
            'ticket_prefix' => 'TKT',
            'invoice_terms' => 'This is a computer-generated invoice. Government fees, if any, are payable at actuals.',
            'bank_details' => "Bank: HDFC Bank\nA/C Name: BizSetu Business Services Pvt. Ltd.\nA/C No: 50200012345678\nIFSC: HDFC0001234\nUPI: bizsetu@hdfcbank",
        ],
        'notifications' => [
            'notify_email_enabled' => '1',
            'compliance_reminder_days' => '7',
            'admin_notification_email' => 'operations@bizsetu.in',
        ],
        'mail' => [
            'mail_host' => null,
            'mail_port' => null,
            'mail_username' => null,
            'mail_password' => null,
            'mail_encryption' => null,
            'mail_from_address' => null,
        ],
        'seo' => [
            'seo_title' => 'BizSetu – Company Registration, GST, Trademark & Compliance Services',
            'seo_description' => 'Register your company, file GST returns, protect your trademark and stay compliant — online, with expert CAs, CSs and lawyers. Transparent pricing across India.',
            'seo_keywords' => 'company registration, GST registration, trademark registration, FSSAI, IEC, compliance',
            'google_analytics_id' => null,
        ],
        'homepage' => [
            'hero_image' => null,
            'stat_customers' => '25,000+',
            'stat_services' => '70+',
            'stat_experts' => '150+',
            'stat_rating' => '4.8/5',
        ],
    ];

    public function run(): void
    {
        foreach (self::DEFAULTS as $group => $values) {
            foreach ($values as $key => $value) {
                Setting::firstOrCreate(['key' => $key], ['group' => $group, 'value' => $value]);
            }
        }

        app(SettingService::class)->flush();
    }
}

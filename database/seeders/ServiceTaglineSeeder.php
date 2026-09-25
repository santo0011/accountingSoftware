<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

/** Short one-line texts for service and category cards (editable in the admin panel). */
class ServiceTaglineSeeder extends Seeder
{
    public const SERVICES = [
        'private-limited-company-registration' => 'Start your company with the right registration.',
        'llp-registration' => 'Limited liability with flexible management.',
        'one-person-company-registration' => 'A company for a single founder.',
        'public-limited-company-registration' => 'Raise capital from the public.',
        'section-8-company-registration' => 'Register your non-profit company.',
        'nidhi-company-registration' => 'Mutual lending among members.',
        'indian-subsidiary-registration' => 'Set up your Indian subsidiary.',
        'proprietorship-partnership-registration' => 'Start as a proprietor or partnership.',
        'trademark-registration' => 'Protect your brand with professional trademark support.',
        'trademark-renewal' => 'Keep your trademark protected.',
        'trademark-objection-reply' => 'Strong replies to examiner objections.',
        'trademark-opposition' => 'Defend your brand in oppositions.',
        'trademark-assignment' => 'Transfer trademark ownership legally.',
        'series-trademark' => 'Protect a series of similar marks.',
        'logo-design' => 'A professional logo for your brand.',
        'gst-registration' => 'Get your GST registration done easily.',
        'gst-return-filing' => 'Monthly GST returns filed on time.',
        'gst-nil-return' => 'Quick nil returns, no late fees.',
        'gst-modification' => 'Update your GST registration details.',
        'gstr-9-annual-return' => 'Your GST annual return, reconciled.',
        'gst-lut-filing' => 'Export without paying IGST.',
        'gst-e-way-bill' => 'E-way bills for moving goods.',
        'tds-return-filing' => 'Quarterly TDS returns made simple.',
        'tax-planning-consultancy' => 'Plan your taxes with a CA.',
        'online-bookkeeping' => 'Accurate books, every month.',
        'project-report' => 'Bank-ready reports for your loan.',
        'fssai-registration' => 'Get the required food business registration.',
        'fssai-renewal' => 'Renew your food licence on time.',
        'fssai-modification' => 'Update your FSSAI licence.',
        'fssai-annual-return' => 'File your FSSAI annual return.',
        'import-export-code' => 'Start importing and exporting.',
        'iec-modification' => 'Update your IEC details.',
        'iso-certification' => 'Certify your quality standards.',
        'other-business-licenses' => 'Trade, shop and other licences.',
        'annual-compliance-package' => 'All your yearly ROC filings.',
        'compliance-calendar' => 'Every due date in one place.',
        'compliance-reminder' => 'Never miss a filing deadline.',
        '12a-registration' => 'Tax exemption for your NGO.',
        '80g-registration' => 'Tax benefits for your donors.',
        'csr-services' => 'Get ready for CSR funding.',
        'ngo-darpan-registration' => 'Get your NGO Darpan ID.',
        'consumer-dispute' => 'Resolve consumer complaints.',
        'online-dispute-resolution' => 'Settle disputes online, faster.',
        'legal-support' => 'Notices, contracts and legal advice.',
        'business-consultancy-services' => 'Expert advice to grow your business.',
        'virtual-cxo-services' => 'Senior leadership, on demand.',
        'corporate-advisory-services' => 'Valuation, due diligence and more.',
        'website-development' => 'Professional websites built for your business.',
        'software-development' => 'Custom software for your processes.',
        'erp-software' => 'Manage your business operations in one system.',
        'crm-software' => 'Manage leads and customers easily.',
        'accounting-software' => 'GST-ready accounting made simple.',
        'mobile-app-development' => 'Android and iOS apps for your business.',
        'it-support' => 'Reliable IT support every month.',
    ];

    public const CATEGORIES = [
        'business-registration' => 'Register your company the right way.',
        'trademark-ipr' => 'Protect your brand and ideas.',
        'gst-tax' => 'GST and tax filings, done right.',
        'accounting-bookkeeping' => 'Clean books and clear reports.',
        'licenses-certification' => 'Every licence your business needs.',
        'annual-compliance' => 'Stay compliant, all year round.',
        'import-export' => 'Trade across borders with ease.',
        'ngo-services' => 'Registrations for NGOs and trusts.',
        'legal-odr' => 'Legal help when you need it.',
        'business-consultancy' => 'Expert advice for smart decisions.',
        'virtual-cxo' => 'Senior leadership, part-time.',
        'corporate-advisory' => 'Funding, valuation and deals.',
        'business-technology' => 'Software and apps to grow faster.',
    ];

    public function run(): void
    {
        foreach (self::SERVICES as $slug => $tagline) {
            Service::where('slug', $slug)->whereNull('tagline')->update(['tagline' => $tagline]);
        }

        foreach (self::CATEGORIES as $slug => $tagline) {
            ServiceCategory::where('slug', $slug)->update(['tagline' => $tagline]);
        }

        \App\Support\SiteCache::flush();
    }
}

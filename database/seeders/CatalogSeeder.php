<?php

namespace Database\Seeders;

use App\Models\ComplianceType;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedComplianceTypes();

        $complianceIds = ComplianceType::pluck('id', 'code');

        foreach (require __DIR__.'/data/catalog.php' as $categoryOrder => $cat) {
            $category = ServiceCategory::updateOrCreate(['slug' => $cat['slug']], [
                'name' => $cat['name'],
                'tagline' => $cat['tagline'],
                'description' => $cat['description'],
                'icon' => $cat['icon'],
                'status' => true,
                'sort_order' => $categoryOrder + 1,
                'seo_title' => $cat['name'].' Services Online | '.setting('company_name', 'BizSetu'),
                'seo_description' => $cat['description'],
            ]);

            foreach ($cat['services'] as $order => $s) {
                $defaults = $cat['defaults'];
                $documents = $s['documents'] ?? $defaults['documents'];

                $service = Service::updateOrCreate(['slug' => $s['slug']], [
                    'service_category_id' => $category->id,
                    'name' => $s['name'],
                    'icon' => $s['icon'] ?? $cat['icon'],
                    'short_description' => $s['short'],
                    'full_description' => $this->overview($s, $cat),
                    'who_needs' => $defaults['who_needs'],
                    'benefits' => $defaults['benefits'],
                    'price' => $s['price'],
                    'discount_price' => ! empty($s['discount']) ? $s['discount'] : null,
                    'gst_rate' => 18,
                    'sac_code' => '998399',
                    'processing_time' => $s['time'],
                    'billing_type' => $s['billing'] ?? 'one_time',
                    'recurring_interval' => $s['interval'] ?? null,
                    'compliance_type_id' => isset($s['compliance']) ? $complianceIds[$s['compliance']] ?? null : null,
                    'is_featured' => $s['featured'] ?? false,
                    'status' => true,
                    'sort_order' => $order + 1,
                    'seo_title' => $s['name'].' Online in India | '.setting('company_name', 'BizSetu'),
                    'seo_description' => $s['short'].' Expert assistance, transparent pricing and 100% online process.',
                ]);

                $service->documents()->delete();
                foreach ($documents as $i => $doc) {
                    $service->documents()->create(['name' => $doc, 'is_mandatory' => $i < 3, 'sort_order' => $i]);
                }

                $service->steps()->delete();
                foreach ($defaults['steps'] as $i => [$title, $description, $duration]) {
                    $service->steps()->create(compact('title', 'description', 'duration') + ['sort_order' => $i]);
                }

                $service->fields()->delete();
                foreach ($defaults['fields'] as $i => $f) {
                    $service->fields()->create([
                        'label' => $f[0], 'name' => $f[1], 'type' => $f[2], 'is_required' => $f[3],
                        'options' => $f[4] ?? null, 'sort_order' => $i,
                    ]);
                }

                $service->faqs()->delete();
                foreach ($this->faqs($s, $documents) as $i => [$question, $answer]) {
                    $service->faqs()->create(compact('question', 'answer') + ['sort_order' => $i]);
                }
            }
        }
    }

    private function overview(array $s, array $cat): string
    {
        $company = setting('company_name', 'BizSetu');

        return "<p>{$s['short']}</p>"
            ."<p>{$company} handles the complete {$s['name']} process for you — from document preparation and expert review to government filing and follow-ups. "
            .'You get a dedicated relationship manager, real-time status updates in your customer portal and all final documents delivered online.</p>'
            ."<p>{$cat['description']}</p>";
    }

    /** @return list<array{0: string, 1: string}> */
    private function faqs(array $s, array $documents): array
    {
        $recurring = ($s['billing'] ?? 'one_time') === 'recurring';

        return [
            ["What is included in the {$s['name']} package?", "Our fee covers expert consultation, document preparation, filing and follow-up until completion. Government fees and stamp duty, where applicable, are charged at actuals and shown before you pay."],
            ["How long does {$s['name']} take?", "Typically {$s['time']}, subject to document availability and government processing time. You can track every step in your customer portal."],
            ['What documents are required?', 'You will need: '.implode(', ', array_slice($documents, 0, 4)).'. Your relationship manager will confirm the final list after reviewing your case.'],
            ['Is the entire process online?', 'Yes. You apply, upload documents, pay and download final documents from your customer portal — no office visit needed.'],
            $recurring
                ? ['Will I get reminders before due dates?', 'Yes. This is a recurring service, so it is added to your compliance calendar and you receive reminders before every due date.']
                : ['Can I get a refund if I change my mind?', 'Yes, as per our refund policy — the fee is fully refundable if work has not started, and partially refundable thereafter.'],
        ];
    }

    private function seedComplianceTypes(): void
    {
        $types = [
            ['GST Return (GSTR-3B)', 'gstr3b', 'monthly', 20, 1, 5, 'Monthly summary GST return with tax payment.'],
            ['GST Annual Return (GSTR-9)', 'gstr9', 'yearly', 31, 9, 15, 'Annual GST return, due 31 December after the financial year.'],
            ['GST LUT', 'gst_lut', 'yearly', 31, 1, 15, 'Letter of Undertaking for exports, filed before the start of each financial year.'],
            ['TDS Return', 'tds_return', 'quarterly', 31, 1, 7, 'Quarterly TDS statements (24Q / 26Q).'],
            ['Bookkeeping Close', 'bookkeeping', 'monthly', 10, 1, 3, 'Monthly books closure and MIS.'],
            ['Annual ROC Compliance', 'annual_roc', 'yearly', 30, 7, 30, 'AOC-4, MGT-7 / LLP Form 8 & 11 and DIR-3 KYC.'],
            ['License Renewal', 'license_renewal', 'yearly', 1, 0, 30, 'Renewal of business licences such as FSSAI.'],
            ['FSSAI Annual Return', 'fssai_return', 'yearly', 31, 2, 15, 'Form D-1 annual return, due 31 May.'],
            ['Trademark Renewal', 'trademark_renewal', 'yearly', 1, 0, 60, 'Trademark renewal every 10 years.'],
            ['IEC Annual Update', 'iec_update', 'yearly', 30, 3, 15, 'Mandatory annual update of IEC between April and June.'],
            ['Advisory Review', 'advisory_review', 'monthly', 5, 1, 3, 'Monthly review meeting / support cycle.'],
        ];

        foreach ($types as [$name, $code, $frequency, $dueDay, $offset, $reminder, $description]) {
            ComplianceType::updateOrCreate(['code' => $code], [
                'name' => $name, 'frequency' => $frequency, 'due_day' => $dueDay, 'due_month_offset' => $offset,
                'reminder_days_before' => $reminder, 'description' => $description, 'status' => true,
            ]);
        }
    }
}

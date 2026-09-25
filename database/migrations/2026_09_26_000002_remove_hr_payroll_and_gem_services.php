<?php

use App\Models\ComplianceType;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\SiteCache;
use Illuminate\Database\Migrations\Migration;

/**
 * Removes the HR & Payroll Compliance and GeM Registration offering (plus HR/Payroll software).
 * Records that are still referenced by applications or subscriptions are archived (soft-deleted)
 * so past applications and invoices keep working; everything else is deleted.
 */
return new class extends Migration
{
    private const CATEGORIES = ['hr-payroll', 'gem-registration'];

    private const SERVICES = [
        'pf-esic-registration', 'pf-esic-return-filing', 'payroll-processing',
        'gem-seller-registration', 'hr-software', 'payroll-software',
    ];

    private const COMPLIANCE_TYPES = ['pf_return', 'esic_return', 'payroll'];

    public function up(): void
    {
        $categoryIds = ServiceCategory::withTrashed()->whereIn('slug', self::CATEGORIES)->pluck('id');

        Service::withTrashed()
            ->where(fn ($q) => $q->whereIn('slug', self::SERVICES)->orWhereIn('service_category_id', $categoryIds))
            ->get()
            ->each(function (Service $service) {
                $inUse = $service->applications()->withTrashed()->exists()
                    || \App\Models\CustomerService::where('service_id', $service->id)->exists();

                if ($inUse) {
                    $service->update(['status' => false, 'is_featured' => false]);
                    $service->delete();
                } else {
                    $service->forceDelete();
                }
            });

        ServiceCategory::withTrashed()->whereIn('slug', self::CATEGORIES)->get()->each(function (ServiceCategory $category) {
            $category->update(['status' => false]);
            Service::withTrashed()->where('service_category_id', $category->id)->exists()
                ? $category->delete()
                : $category->forceDelete();
        });

        ComplianceType::whereIn('code', self::COMPLIANCE_TYPES)->get()->each(function (ComplianceType $type) {
            $type->records()->exists() ? $type->update(['status' => false]) : $type->delete();
        });

        SiteCache::flush();
    }

    public function down(): void
    {
        // Intentionally irreversible: re-run the catalogue seeder to restore services if ever needed.
    }
};

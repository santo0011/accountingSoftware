<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Professional;
use App\Models\User;
use App\Services\NumberGenerator;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'admin@bizsetu.test';
    public const DEFAULT_PASSWORD = 'Password@123';

    public function run(): void
    {
        $staff = [
            ['Super Admin', self::ADMIN_EMAIL, '9000000001', 'super-admin', 'Management', 'Managing Director'],
            ['Anita Desai', 'admin.ops@bizsetu.test', '9000000002', 'admin', 'Operations', 'Operations Head'],
            ['Rahul Mehta', 'rahul.staff@bizsetu.test', '9000000003', 'staff', 'Operations', 'Relationship Manager'],
            ['Kavya Reddy', 'kavya.staff@bizsetu.test', '9000000004', 'staff', 'Tax & GST', 'GST Executive'],
            ['Suresh Iyer', 'accounts@bizsetu.test', '9000000005', 'accountant', 'Accounts', 'Senior Accountant'],
            ['Neha Kapoor', 'hr@bizsetu.test', '9000000006', 'hr', 'HR', 'HR Manager'],
        ];

        foreach ($staff as $i => [$name, $email, $mobile, $role, $department, $designation]) {
            $user = User::updateOrCreate(['email' => $email], [
                'name' => $name, 'mobile' => $mobile, 'user_type' => User::TYPE_STAFF, 'status' => 'active',
                'password' => self::DEFAULT_PASSWORD, 'email_verified_at' => now(),
            ]);
            $user->syncRoles([$role]);
            $user->staffProfile()->updateOrCreate([], [
                'employee_code' => sprintf('EMP%03d', $i + 1), 'department' => $department,
                'designation' => $designation, 'joined_on' => now()->subMonths(18 - $i),
            ]);
        }

        $professionals = [
            ['CA Vikram Joshi', 'vikram.ca@bizsetu.test', '9000000011', 'ca', 'ICAI M.No. 123456', 'GST, Income Tax, Audit', 'tax-professional'],
            ['CS Pooja Bansal', 'pooja.cs@bizsetu.test', '9000000012', 'cs', 'ICSI ACS 45678', 'Company Law, ROC Compliance', 'legal-professional'],
            ['Adv. Arjun Menon', 'arjun.law@bizsetu.test', '9000000013', 'lawyer', 'BAR/MAH/1234/2012', 'Trademark, IPR, Consumer Disputes', 'legal-professional'],
        ];

        foreach ($professionals as [$name, $email, $phone, $type, $regNo, $specialization, $role]) {
            $userId = null;
            if ($email) {
                $user = User::updateOrCreate(['email' => $email], [
                    'name' => $name, 'mobile' => $phone, 'user_type' => User::TYPE_PROFESSIONAL, 'status' => 'active',
                    'password' => self::DEFAULT_PASSWORD, 'email_verified_at' => now(),
                ]);
                $user->syncRoles([$role]);
                $userId = $user->id;
            }

            Professional::updateOrCreate(['phone' => $phone], [
                'user_id' => $userId, 'name' => $name, 'email' => $email, 'professional_type' => $type,
                'registration_no' => $regNo, 'specialization' => $specialization, 'status' => 'active',
            ]);
        }

        // Demo customer used in the README / login screen hint.
        $user = User::updateOrCreate(['email' => 'customer@bizsetu.test'], [
            'name' => 'Aarav Patel', 'mobile' => '9876500001', 'user_type' => User::TYPE_CUSTOMER, 'status' => 'active',
            'password' => self::DEFAULT_PASSWORD, 'email_verified_at' => now(),
        ]);
        $user->syncRoles(['customer']);
        $customer = Customer::firstOrNew(['user_id' => $user->id]);
        $customer->customer_code ??= app(NumberGenerator::class)->next('customer');
        $customer->fill([
            'city' => 'Mumbai', 'state' => 'Maharashtra', 'pincode' => '400069',
            'address' => '12, Sunrise Apartments, Andheri West', 'pan' => 'ABCPP1234K', 'source' => 'website',
        ])->save();
        $customer->businesses()->updateOrCreate(['name' => 'Patel Innovations Pvt Ltd'], [
            'business_type' => 'private_limited', 'gstin' => '27ABCPP1234K1Z2', 'state' => 'Maharashtra',
            'city' => 'Mumbai', 'is_primary' => true,
        ]);
    }
}

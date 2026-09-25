<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/** Creates customer accounts (self-registration, admin-created, lead conversion). */
class CustomerAccountService
{
    public function __construct(private NumberGenerator $numbers) {}

    /**
     * @param array{name: string, email: string, mobile?: ?string, password?: ?string,
     *              business_name?: ?string, business_type?: ?string, state?: ?string, city?: ?string, source?: ?string} $data
     */
    public function create(array $data, bool $verified = false): Customer
    {
        return DB::transaction(function () use ($data, $verified) {
            $user = User::create([
                'name' => $data['name'],
                'email' => Str::lower($data['email']),
                'mobile' => $data['mobile'] ?? null,
                'user_type' => User::TYPE_CUSTOMER,
                'status' => 'active',
                'password' => $data['password'] ?? Str::password(16),
                'email_verified_at' => $verified ? now() : null,
            ]);
            $user->assignRole('customer');

            $customer = $user->customer()->create([
                'customer_code' => $this->numbers->next('customer'),
                'state' => $data['state'] ?? null,
                'city' => $data['city'] ?? null,
                'source' => $data['source'] ?? 'website',
            ]);

            if (! empty($data['business_name'])) {
                $customer->businesses()->create([
                    'name' => $data['business_name'],
                    'business_type' => $data['business_type'] ?? null,
                    'state' => $data['state'] ?? null,
                    'city' => $data['city'] ?? null,
                    'is_primary' => true,
                ]);
            }

            return $customer;
        });
    }

    /** Email the customer a link to set their own password (for accounts created by staff). */
    public function sendSetPasswordLink(User $user): void
    {
        Password::broker()->sendResetLink(['email' => $user->email]);
    }
}

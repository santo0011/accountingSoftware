<?php

namespace App\Actions\Fortify;

use App\Models\Business;
use App\Models\User;
use App\Services\CustomerAccountService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/** Public self-registration — always creates a customer account. */
class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(private CustomerAccountService $accounts) {}

    public function create(array $input): User
    {
        $input['mobile'] = preg_replace('/\D/', '', (string) ($input['mobile'] ?? ''));

        Validator::make($input, [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique(User::class)],
            'mobile' => ['required', 'regex:/^[6-9][0-9]{9}$/', Rule::unique(User::class)],
            'business_name' => ['nullable', 'string', 'max:150'],
            'business_type' => ['nullable', Rule::in(array_keys(Business::TYPES))],
            'password' => $this->passwordRules(),
            'terms' => ['accepted'],
        ], [
            'mobile.regex' => 'Enter a valid 10-digit Indian mobile number.',
            'terms.accepted' => 'Please accept the terms and privacy policy.',
        ])->validate();

        return $this->accounts->create([
            'name' => $input['name'],
            'email' => $input['email'],
            'mobile' => $input['mobile'],
            'password' => $input['password'],
            'business_name' => $input['business_name'] ?? null,
            'business_type' => $input['business_type'] ?? null,
            'source' => 'website',
        ], verified: true)->user; // no email verification step: customers go straight to their account
    }
}

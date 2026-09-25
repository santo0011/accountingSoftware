<?php

/*
|--------------------------------------------------------------------------
| Payment gateways
|--------------------------------------------------------------------------
| Every gateway implements App\Contracts\Payments\PaymentGateway.
| To add Razorpay later: create App\Services\Payments\RazorpayGateway
| implementing the contract, add it below with its keys, and enable it.
*/

return [

    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'manual'),

    'gateways' => [
        'manual' => [
            'enabled' => true,
            'class' => App\Services\Payments\ManualGateway::class,
        ],

        'sandbox' => [
            'enabled' => (bool) env('PAYMENT_SANDBOX_ENABLED', false),
            'class' => App\Services\Payments\SandboxGateway::class,
        ],

        // 'razorpay' => [
        //     'enabled' => (bool) env('RAZORPAY_ENABLED', false),
        //     'class' => App\Services\Payments\RazorpayGateway::class,
        //     'key' => env('RAZORPAY_KEY'),
        //     'secret' => env('RAZORPAY_SECRET'),
        //     'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
        // ],
    ],
];

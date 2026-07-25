<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentGateway;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'Cash on Delivery',
                'slug' => 'cod',
                'description' => 'Pay with cash upon delivery of your order',
                'is_active' => true,
                'test_mode' => false,
                'is_default' => true,
                'sort_order' => 1,
                'credentials' => [
                    'instructions' => 'Pay with cash when your order is delivered to your address.'
                ]
            ],
            [
                'name' => 'bKash',
                'slug' => 'bkash',
                'description' => 'Pay using your bKash mobile account',
                'is_active' => false,
                'test_mode' => true,
                'is_default' => false,
                'sort_order' => 2,
                'credentials' => [
                    'username' => '',
                    'password' => '',
                    'merchant_number' => '',
                    'app_key' => '',
                    'app_secret' => ''
                ]
            ],
            [
                'name' => 'SSLCommerz',
                'slug' => 'sslcommerz',
                'description' => 'Pay using SSLCommerz payment gateway',
                'is_active' => false,
                'test_mode' => true,
                'is_default' => false,
                'sort_order' => 3,
                'credentials' => [
                    'store_id' => '',
                    'store_password' => ''
                ]
            ],
            [
                'name' => 'Nagad',
                'slug' => 'nagad',
                'description' => 'Pay using Nagad digital wallet',
                'is_active' => false,
                'test_mode' => true,
                'is_default' => false,
                'sort_order' => 4,
                'credentials' => [
                    'merchant_id' => '',
                    'merchant_key' => '',
                    'public_key' => ''
                ]
            ],
            [
                'name' => 'Rocket',
                'slug' => 'rocket',
                'description' => 'Pay using Rocket mobile banking',
                'is_active' => false,
                'test_mode' => true,
                'is_default' => false,
                'sort_order' => 5,
                'credentials' => [
                    'merchant_id' => '',
                    'merchant_number' => '',
                    'password' => '',
                    'api_key' => ''
                ]
            ],
            [
                'name' => 'Stripe',
                'slug' => 'stripe',
                'description' => 'Pay using Stripe credit/debit card',
                'is_active' => false,
                'test_mode' => true,
                'is_default' => false,
                'sort_order' => 6,
                'credentials' => [
                    'client_id' => '',
                    'client_secret' => ''
                ]
            ],
            [
                'name' => 'PayPal',
                'slug' => 'paypal',
                'description' => 'Pay using PayPal',
                'is_active' => false,
                'test_mode' => true,
                'is_default' => false,
                'sort_order' => 7,
                'credentials' => [
                    'client_id' => '',
                    'client_secret' => ''
                ]
            ],
        ];

        foreach ($gateways as $gateway) {
            PaymentGateway::updateOrCreate(
                ['slug' => $gateway['slug']],
                $gateway
            );
        }
    }
}

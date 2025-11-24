<?php

require_once __DIR__ . '/../vendor/autoload.php';

use SingaPay\SingaPay;
use SingaPay\Exceptions\SingaPayException;

// Initialize SDK
$singapay = new SingaPay([
    'client_id' => '',
    'client_secret' => '',
    'api_key' => '',
    'hmac_validation_key' => '',
    'environment' => 'sandbox', // or 'production'
]);

try {
    // Example 1: Create Account
    echo "Creating account...\n";
    $account = $singapay->account->create([
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'phone' => '081234567890'
    ]);
    echo "Account created: {$account['id']}\n\n";

    // Example 2: List Accounts
    echo "Listing accounts...\n";
    $accounts = $singapay->account->list();
    echo "Found " . count($accounts) . " accounts\n\n";

    // Example 3: Create Payment Link
    echo "Creating payment link...\n";
    $paymentLink = $singapay->paymentLink->create($account['id'], [
        'reff_no' => 'PL' . time(),
        'title' => 'Test Payment',
        'total_amount' => 100000,
        'required_customer_detail' => true,
        'max_usage' => 1,
        'expired_at' => (time() + 86400) * 1000, // 24 hours from now in milliseconds
        'items' => [
            [
                'name' => 'Product A',
                'quantity' => 1,
                'unit_price' => 100000
            ]
        ],
        'whitelisted_payment_method' => ['VA_BRI', 'QRIS']
    ]);
    echo "Payment link created: {$paymentLink['payment_url']}\n\n";

    // Example 4: Create Virtual Account
    echo "Creating virtual account...\n";
    $va = $singapay->virtualAccount->create($account['id'], [
        'bank_code' => 'BRI',
        'amount' => 50000,
        'kind' => 'permanent',
        'name' => 'Test VA'
    ]);
    echo "Virtual account created: {$va['number']}\n\n";

    // Example 5: Check Beneficiary
    echo "Checking beneficiary...\n";
    $beneficiary = $singapay->disbursement->checkBeneficiary(
        '091701064838533',
        'BRINIDJA'
    );
    echo "Beneficiary: {$beneficiary['bank_account_name']}\n\n";

    // Example 6: Check Transfer Fee
    echo "Checking transfer fee...\n";
    $feeInfo = $singapay->disbursement->checkFee($account['id'], 50000, 'BRINIDJA');
    echo "Transfer fee: {$feeInfo['transfer_fee']}\n\n";
} catch (SingaPayException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if ($e instanceof \SingaPay\Exceptions\ValidationException) {
        echo "Validation errors:\n";
        print_r($e->getErrors());
    }
}

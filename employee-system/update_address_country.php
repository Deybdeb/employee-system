<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Address;

$address = Address::find(1);

if ($address) {
    $address->country = 'Philippines';
    $address->save();
    echo "✓ Address updated with country: {$address->country}\n";
} else {
    echo "Address not found\n";
}

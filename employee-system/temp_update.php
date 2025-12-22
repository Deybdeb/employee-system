<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$addr = App\Models\Address::find(1);
$addr->update(['country' => 'Philippines', 'postal_code' => '1747']);
echo "✓ Updated address with postal code and country\n";

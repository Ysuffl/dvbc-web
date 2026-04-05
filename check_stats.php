<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

$customers = Customer::orderBy('created_at', 'desc')->take(20)->get();
echo "Top 20 Recent Customers Spending:\n";
foreach ($customers as $c) {
    echo "- {$c->name}: total_spending={$c->total_spending}\n";
}

$sum = Customer::sum('total_spending');
echo "\nTotal Sum: {$sum}\n";

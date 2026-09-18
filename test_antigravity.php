<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Features\Antigravity\Services\AntigravityAccountManager;

// 1. Get an existing user (pick ID 1 or first admin)
$user = User::first(); // Assuming ID 1 exists
if (!$user) { echo "No user found\n"; exit; }

echo "Testing for user: " . $user->email . "\n";

$manager = new AntigravityAccountManager();

// 2. Test fetching active account
$account = $manager->getActiveAccount($user);
if ($account) {
    echo "Active Account: " . $account->email . " (ID: " . $account->id . ")\n";
    
    // 3. Test token retrieval
    $token = $manager->getActiveToken($user);
    echo "Bearer Token available: " . ($token ? 'Yes (' . substr($token, 0, 5) . '...)' : 'No') . "\n";
} else {
    echo "No active account found. Please link an account via UI or add a manual key.\n";
}
?>
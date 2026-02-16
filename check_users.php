<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$providedUsers = [
    ['email' => 'it.rayandra@gmail.com', 'password' => '@R4y4ndr4'],
    ['email' => 'admin@gmail.com', 'password' => 'password'],
    ['email' => 'sales@gmail.com', 'password' => 'password'],
];

foreach ($providedUsers as $pu) {
    $user = User::where('email', $pu['email'])->first();
    if ($user) {
        echo "User found: {$user->email}\n";
        if (Hash::check($pu['password'], $user->password)) {
            echo "  --> Password matches for {$pu['email']}\n";
        }
        else {
            echo "  --> Password DOES NOT match for {$pu['email']}\n";
        }
    }
    else {
        echo "User NOT found: {$pu['email']}\n";
    }
}

echo "\nAll Users in DB:\n";
foreach (User::all() as $u) {
    echo "{$u->email} - {$u->role}\n";
}

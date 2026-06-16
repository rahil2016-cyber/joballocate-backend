<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = 'temp_' . uniqid() . '@example.com';
$password = 'Secret123!';

$user = User::create([
    'name' => 'Temp User',
    'email' => $email,
    'password' => Hash::make($password),
    'role' => 'job_seeker',
]);

echo 'Initially saved password hash: ' . $user->password . PHP_EOL;
echo 'Check with Hash::check (raw): ' . (Hash::check($password, $user->password) ? 'TRUE' : 'FALSE') . PHP_EOL;

// Now set password using Hash::make
$user->password = Hash::make($password);
$user->save();

$userFresh = $user->fresh();
echo 'After update password hash: ' . $userFresh->password . PHP_EOL;
echo 'Check with Hash::check (raw): ' . (Hash::check($password, $userFresh->password) ? 'TRUE' : 'FALSE') . PHP_EOL;

$user->delete();

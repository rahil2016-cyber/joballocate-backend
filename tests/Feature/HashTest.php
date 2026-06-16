<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HashTest extends TestCase
{
    use RefreshDatabase;

    public function test_hash_update_behavior(): void
    {
        $password = 'Secret123!';
        
        // Let's create a user
        $user = User::create([
            'name' => 'User One',
            'email' => 'one@example.com',
            'password' => 'initial_password',
            'role' => 'job_seeker',
        ]);
        
        // Retrieve from DB
        $user = User::find($user->id);
        
        // Now set password using Hash::make
        $user->password = Hash::make($password);
        $user->save();
        
        // Retrieve again to see what is in DB
        $userFresh = User::find($user->id);
        
        $savedPassword = $userFresh->password;
        $check = Hash::check($password, $savedPassword);
        
        echo "\nSaved password after update: " . $savedPassword;
        echo "\nCheck with raw password after update: " . ($check ? 'TRUE' : 'FALSE');
        
        // Let's try direct DB query to see what's written
        $dbPassword = \DB::table('users')->where('id', $user->id)->value('password');
        echo "\nDB password: " . $dbPassword;
        echo "\nCheck raw against DB: " . (Hash::check($password, $dbPassword) ? 'TRUE' : 'FALSE');
        echo "\n";
    }
}

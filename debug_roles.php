<?php

require_once 'bootstrap/app.php';
app()->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$user = User::where('email', 'superadmin@enmaschool.com')->first();

if ($user) {
    echo "User found: " . $user->name . PHP_EOL;
    echo "Email: " . $user->email . PHP_EOL;
    echo "User ID: " . $user->id . PHP_EOL;
    echo "Roles: " . implode(', ', $user->getRoleNames()->toArray()) . PHP_EOL;
    echo "Has super_admin role: " . ($user->hasRole('super_admin') ? 'YES' : 'NO') . PHP_EOL;
    echo "Has any admin roles: " . ($user->hasAnyRole(['super_admin', 'admin', 'directeur']) ? 'YES' : 'NO') . PHP_EOL;
    
    // Test the middleware method directly
    echo "\n--- Testing middleware logic ---\n";
    $hasAccess = $user->hasAnyRole(['super_admin', 'admin', 'directeur']);
    echo "Direct role check result: " . ($hasAccess ? 'TRUE' : 'FALSE') . PHP_EOL;
} else {
    echo "User not found!" . PHP_EOL;
    
    // List all users
    echo "\nAll users:\n";
    $users = User::all();
    foreach($users as $u) {
        echo "- " . $u->email . " (" . $u->name . ")\n";
    }
}
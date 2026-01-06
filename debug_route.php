Route::get('/debug-user', function () {
    $user = \App\Models\User::where('email', 'superadmin@enmaschool.com')->first();
    
    if (!$user) {
        return response()->json(['error' => 'User not found']);
    }
    
    return response()->json([
        'user' => $user->name,
        'email' => $user->email,
        'roles' => $user->getRoleNames()->toArray(),
        'has_super_admin' => $user->hasRole('super_admin'),
        'has_any_admin_role' => $user->hasAnyRole(['super_admin', 'admin', 'directeur']),
        'user_id' => $user->id,
    ]);
})->middleware(['auth', 'verified']);
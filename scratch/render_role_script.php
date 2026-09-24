<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User\User::first();
Illuminate\Support\Facades\Auth::login($user);

// View rendering
$roles = App\Models\User\Role::with('parentRole')->get();
$view = view('admin.daftar.roleindex', [
    'daftarRole' => $roles,
    'errors' => new Illuminate\Support\ViewErrorBag()
]);
$html = $view->render();

// Extract script
preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $html, $matches);
foreach ($matches[1] as $idx => $script) {
    if (str_contains($script, 'rawRolesData')) {
        echo "=== SCRIPT FOUND ===\n";
        echo $script;
        echo "\n=== END SCRIPT ===\n";
    }
}

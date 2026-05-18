<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\User;
use Spatie\Permission\Models\Role;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create user and roles
$app->make(Illuminate\Contracts\Console\Kernel::class)->call('migrate:fresh');

Role::firstOrCreate(['name' => 'Auditee', 'guard_name' => 'web']);
$user = User::factory()->create();
$user->assignRole('Auditee');

// Log user in
auth()->login($user);

try {
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::create('/pelaksanaan/evaluasi-diri', 'GET')
    );
    echo "Response status: " . $response->getStatusCode() . "\n";
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

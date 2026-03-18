<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('roles', function (Blueprint $table) {
    if (Schema::hasColumn('roles', 'tenant_id')) {
        $table->dropForeign(['tenant_id']);
        $table->dropColumn('tenant_id');
    }
});
echo "Dropped\n";

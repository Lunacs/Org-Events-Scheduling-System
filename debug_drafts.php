<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $drafts = \App\Models\TicketDraft::all();
    echo "Found " . $drafts->count() . " drafts.\n";
    foreach ($drafts as $d) {
        $user = \App\Models\User::find($d->user_id);
        $email = $user ? $user->email : 'Unknown';
        echo "Draft ID: " . $d->id . " | User ID: " . $d->user_id . " (" . $email . ") | Current Step: " . $d->current_step . " | Updated At: " . $d->updated_at . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

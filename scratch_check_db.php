<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\KnowledgeTopic;

foreach (KnowledgeTopic::all() as $topic) {
    echo "Topic: {$topic->topic} | Keywords: " . json_encode($topic->keywords) . "\n";
}

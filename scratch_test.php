<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Ai\Agents\BookingAssistant;
use Laravel\Ai\Enums\Lab;

try {
    $agent = BookingAssistant::make();
    echo "Prompting agent with 'do i need to bring documents ?'...\n";
    $response = $agent->prompt(
        'do i need to bring documents ?',
        provider: Lab::Groq,
        model: 'llama-3.3-70b-versatile'
    );
    echo "Success!\n";
    echo "Final Response: " . $response . "\n\n";
    
    echo "=== CONVERSATION STEPS ===\n";
    foreach ($response->steps as $index => $step) {
        echo "Step #{$index}:\n";
        echo "  Text: " . $step->text . "\n";
        echo "  Tool Calls: " . json_encode($step->toolCalls) . "\n";
        echo "  Tool Results: " . json_encode($step->toolResults) . "\n";
        echo "--------------------------\n";
    }
} catch (\Throwable $e) {
    echo "Exception class: " . get_class($e) . "\n";
    echo "Error message: " . $e->getMessage() . "\n";
    if (isset($e->response)) {
        echo "=== GROQ RESPONSE BODY ===\n";
        echo $e->response->body() . "\n";
        echo "==========================\n";
    }
}

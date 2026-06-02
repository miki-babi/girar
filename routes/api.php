<?php

use App\Http\Controllers\BookingLinkController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\KnowledgeTopicController;
use Illuminate\Support\Facades\Route;

Route::post('mcp/knowledge-bases/search', [KnowledgeBaseController::class, 'search']);
Route::get('mcp/knowledge-bases/{id}', [KnowledgeBaseController::class, 'showForAgent']);

Route::middleware('auth.basic')->group(function () {
    Route::apiResource('booking-links', BookingLinkController::class)
        ->whereNumber('booking_link');
    Route::apiResource('knowledge-topics', KnowledgeTopicController::class);
    Route::apiResource('knowledge-bases', KnowledgeBaseController::class);
});

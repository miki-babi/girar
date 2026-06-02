<?php

use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\KnowledgeTopicController;
use App\Http\Controllers\BookingLinkController;
use App\Http\Controllers\SuggestionController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('booking-links/manage', [BookingLinkController::class, 'manage'])
        ->name('booking-links.manage');
    Route::post('booking-links/manage', [BookingLinkController::class, 'storeFromPage'])
        ->name('booking-links.manage.store');
    Route::put('booking-links/manage/{bookingLink}', [BookingLinkController::class, 'updateFromPage'])
        ->name('booking-links.manage.update');
    Route::delete('booking-links/manage/{bookingLink}', [BookingLinkController::class, 'destroyFromPage'])
        ->name('booking-links.manage.destroy');
});

Route::get('business-services/manage', [KnowledgeTopicController::class, 'manage'])
    ->name('business-services.manage');

Route::post('business-services/manage/topics', [KnowledgeTopicController::class, 'storeFromPage'])
    ->name('business-services.manage.topics.store');
Route::put('business-services/manage/topics/{knowledgeTopic}', [KnowledgeTopicController::class, 'updateFromPage'])
    ->name('business-services.manage.topics.update');
Route::delete('business-services/manage/topics/{knowledgeTopic}', [KnowledgeTopicController::class, 'destroyFromPage'])
    ->name('business-services.manage.topics.destroy');

Route::post('business-services/manage/subtopics', [KnowledgeBaseController::class, 'storeFromPage'])
    ->name('business-services.manage.subtopics.store');
Route::put('business-services/manage/subtopics/{knowledgeBase}', [KnowledgeBaseController::class, 'updateFromPage'])
    ->name('business-services.manage.subtopics.update');
Route::delete('business-services/manage/subtopics/{knowledgeBase}', [KnowledgeBaseController::class, 'destroyFromPage'])
    ->name('business-services.manage.subtopics.destroy');

Route::get('suggestions/manage', [SuggestionController::class, 'manage'])
    ->name('suggestions.manage');
Route::post('suggestions/manage/{suggestion}/promote', [SuggestionController::class, 'promote'])
    ->name('suggestions.manage.promote');
Route::post('suggestions/manage/{suggestion}/dismiss', [SuggestionController::class, 'dismiss'])
    ->name('suggestions.manage.dismiss');
Route::post('suggestions/manage/dismiss-all', [SuggestionController::class, 'dismissAll'])
    ->name('suggestions.manage.dismiss-all');

require __DIR__.'/settings.php';

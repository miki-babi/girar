<?php

use App\Http\Controllers\BusinessServiceController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Log::debug('routes/web.php loaded for business services routes');

Route::matched(function ($event): void {
    if (! str_starts_with($event->request->path(), 'business-services')) {
        return;
    }

    Log::info('Business services route matched from web debug listener', [
        'method' => $event->request->method(),
        'path' => $event->request->path(),
        'route_name' => $event->route->getName(),
        'route_uri' => $event->route->uri(),
        'action' => $event->route->getActionName(),
        'parameters' => $event->route->parameters(),
        'middleware' => $event->route->gatherMiddleware(),
    ]);
});

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('business-services/manage', [BusinessServiceController::class, 'manage'])
        ->name('business-services.manage');

    Route::post('business-services/manage', [BusinessServiceController::class, 'storeFromPage'])
        ->name('business-services.manage.store');
    Route::put('business-services/manage/{businessService}', [BusinessServiceController::class, 'updateFromPage'])
        ->name('business-services.manage.update');
    Route::delete('business-services/manage/{businessService}', [BusinessServiceController::class, 'destroyFromPage'])
        ->name('business-services.manage.destroy');
});

require __DIR__.'/settings.php';

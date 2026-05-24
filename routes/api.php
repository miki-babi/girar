<?php

use App\Http\Controllers\BusinessServiceController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Log::debug('routes/api.php loaded for business services routes', [
    'api_prefix' => '',
]);

Route::matched(function ($event): void {
    $middleware = $event->route->gatherMiddleware();

    if (! str_starts_with($event->request->path(), 'business-services') || ! in_array('api', $middleware, true)) {
        return;
    }

    Log::info('Business services API route matched from api debug listener', [
        'method' => $event->request->method(),
        'path' => $event->request->path(),
        'route_name' => $event->route->getName(),
        'route_uri' => $event->route->uri(),
        'action' => $event->route->getActionName(),
        'parameters' => $event->route->parameters(),
        'middleware' => $middleware,
    ]);
});

Route::post('mcp/business-services/search', [BusinessServiceController::class, 'search']);
Route::get('mcp/business-services/{id}', [BusinessServiceController::class, 'showForAgent']);

Route::apiResource('business-services', BusinessServiceController::class)
    ->middleware('auth.basic')
    ->whereUuid('business_service');

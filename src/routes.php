<?php
use Illuminate\Support\Facades\Route;
use Actinity\Actinite\Http\Controllers\TypeController;
use Actinity\Actinite\Http\Controllers\NodeController;
use Actinity\Actinite\Http\Controllers\TreeController;
use Actinity\Actinite\Http\Controllers\AssetController;
use Actinity\Actinite\Http\Controllers\ImageController;
use Actinity\Actinite\Http\Controllers\UserController;
use Actinity\Actinite\Http\Controllers\PublishController;
use Actinity\Actinite\Http\Controllers\ResourceController;

Route::prefix('actinite')->group(function() {

    Route::middleware([
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        // \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'auth'
    ])->group(function() {

        Route::get('logout',[UserController::class,'logout']);

		Route::prefix('resources')->group(function() {
			Route::get('app.css',[ResourceController::class, 'css']);
			Route::get('app.js',[ResourceController::class, 'js']);
			Route::get('tinymce/{plugin}',[ResourceController::class,'tinymce']);
		});

        Route::prefix('api')->group(function() {

            Route::get('keepalive',[NodeController::class, 'keepalive']);

            Route::get('types', [TypeController::class, 'index']);

            Route::get('tree', [TreeController::class, 'from']);
            Route::get('tree/from/{node}', [TreeController::class, 'from']);
            Route::get('tree/to/{node}', [TreeController::class, 'to']);

            Route::post('nodes', [NodeController::class, 'store']);
            Route::get('nodes/{node}', [NodeController::class, 'show']);
            Route::put('nodes/{node}', [NodeController::class, 'update']);

            Route::delete('nodes/{node}',[NodeController::class, 'trash']);

            Route::post('nodes/{node}/order',[NodeController::class,'order']);

            Route::get('assets', [AssetController::class, 'index']);
            Route::get('assets/{asset}', [AssetController::class, 'show']);
            Route::post('assets', [AssetController::class, 'upload']);

			Route::put('assets/{asset}', [AssetController::class,'update']);

            Route::get('users', [UserController::class,'index']);
            Route::get('users/me', [UserController::class, 'me']);
            Route::get('users/{user}', [UserController::class, 'show']);
            Route::post('users', [UserController::class, 'store']);
            Route::put('users/{user}', [UserController::class, 'update']);

            Route::post('publish',[PublishController::class,'publish']);
            Route::delete('publish/{node}',[PublishController::class,'unpublish']);

            Route::post('move-node',[NodeController::class,'move']);

        });

        Route::get('switch-mode/{mode}',[PublishController::class, 'switchMode']);

        Route::get('publish-all',[PublishController::class,'publishAll']);

    });


    Route::get('img/{path}', [ImageController::class, 'show'])
        ->where('path', '.*');
});

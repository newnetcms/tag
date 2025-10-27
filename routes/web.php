<?php

use Newnet\Tag\Http\Controllers\Web\TagController;


Route::prefix(LaravelLocalization::setLocale())
    ->middleware([
        'localizationRedirect',
    ])
    ->group(function () {
        Route::prefix('tags')->group(function () {
            Route::get('{slug}-{id}', [TagController::class, 'detail'])
                ->where([
                    // ✅ Cho phép chữ cái (mọi ngôn ngữ), số và dấu gạch ngang
                    'slug' => '[\pL\pN\-\_]+',
                    'id' => '[0-9]+',
                ])
                ->name('tag.web.tag.detail');
        });
    });

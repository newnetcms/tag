<?php

use Newnet\Tag\Http\Controllers\Web\TagController;

Route::prefix('tags')->group(function () {
    Route::get('{slug}', [TagController::class, 'detail'])->name('tag.web.tag.detail');
});

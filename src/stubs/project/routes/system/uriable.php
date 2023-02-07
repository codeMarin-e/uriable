<?php
Route::fallback([\App\Http\Controllers\FallbackController::class, 'slugs'])->name('fallback');

<?php

use App\Http\Controllers\ManuscriptController;
use App\Http\Controllers\ManuscriptViewerController;
use App\Http\Controllers\SimilarityController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard')->name('dashboard');
Route::view('/submit-idea', 'submit-idea')->name('submit-idea');
Route::view('/feasibility-result', 'feasibility-result')->name('feasibility-result');
Route::get('/similarity-result', [SimilarityController::class, 'result'])->name('similarity-result');
Route::post('/similarity/upload', [SimilarityController::class, 'upload'])->name('similarity.upload');
Route::post('/similarity/analyze', [SimilarityController::class, 'analyze'])->name('similarity.analyze');
Route::view('/profile', 'profile')->name('profile');
Route::get('/repository', [ManuscriptController::class, 'index'])->name('repository.index');
Route::get('/repository/{manuscript}/viewer', [ManuscriptViewerController::class, 'show'])->name('repository.viewer');
Route::get('/repository/{manuscript}/viewer/pages/{page}', [ManuscriptViewerController::class, 'page'])
    ->whereNumber('page')
    ->name('repository.viewer.page');
Route::get('/repository/{manuscript}', [ManuscriptController::class, 'show'])->name('repository.show');

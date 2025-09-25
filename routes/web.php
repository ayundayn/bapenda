<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HasilTagihanController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\tables\Basic as TablesBasic;

// Halaman dashboard (hanya untuk user login)
Route::get('/', [Analytics::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard-analytics');

// form elements
Route::middleware(['auth'])->group(function () {
    Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
    Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

    // tables
    Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');

    // upload & hasil
    Route::get('/upload', [TagihanController::class, 'index'])->name('upload.form');
    Route::post('/proses', [TagihanController::class, 'proses'])->name('upload.proses');
    Route::get('/hasil', [TagihanController::class, 'hasil'])->name('hasil.view');
    Route::get('/download-excel', [HasilTagihanController::class, 'downloadExcel'])->name('download.excel');
});

require __DIR__.'/auth.php';

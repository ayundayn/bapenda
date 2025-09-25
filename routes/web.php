<?php
use App\Http\Controllers\HasilTagihanController;
use App\Http\Controllers\TagihanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\tables\Basic as TablesBasic;

// Main Page Route
Route::get('/', [Analytics::class, 'index'])->name('dashboard-analytics');

// form elements
Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

// tables
Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');

Route::get('/upload', [TagihanController::class, 'index'])->name('upload.form');
Route::post('/proses', [TagihanController::class, 'proses'])->name('upload.proses');
// Route::get('/download', [TagihanController::class, 'download'])->name('download.excel');
Route::get('/hasil', [TagihanController::class, 'hasil'])->name('hasil.view');
//Route::post('/proses', [TagihanController::class, 'proses'])->name('proses.excel');
// Route::get('/download', [TagihanController::class, 'download'])->name('download.excel'); // ⬅ INI WAJIB ADA
Route::get('/download-excel', [HasilTagihanController::class, 'downloadExcel'])->name('download.excel');

<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagihanController;

Route::get('/', [TagihanController::class, 'index'])->name('upload.form');
Route::post('/proses', [TagihanController::class, 'proses'])->name('upload.proses');
Route::get('/download', [TagihanController::class, 'download'])->name('upload.download');

//Route::post('/proses', [TagihanController::class, 'proses'])->name('proses.excel');
Route::get('/download', [TagihanController::class, 'download'])->name('download.excel'); // ⬅️ INI WAJIB ADA

<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EbookController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Politeknik Besut FlipBook Platform
|--------------------------------------------------------------------------
*/

// Public Pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/library', [EbookController::class, 'index'])->name('library');
Route::view('/about', 'about')->name('about');

// AI Academic Tutor
Route::get('/ai-tutor', [AiController::class, 'tutorPage'])->name('ai.tutor');
Route::post('/ai/chat', [AiController::class, 'chat'])->name('ai.chat');

// Authentication Routes (Guests only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // E-Book Management
    Route::get('/ebooks/create', [EbookController::class, 'create'])->name('ebooks.create');
    Route::post('/ebooks', [EbookController::class, 'store'])->name('ebooks.store');
    Route::get('/ebooks/{ebook}/edit', [EbookController::class, 'edit'])->name('ebooks.edit');
    Route::put('/ebooks/{ebook}', [EbookController::class, 'update'])->name('ebooks.update');
    Route::delete('/ebooks/{ebook}', [EbookController::class, 'destroy'])->name('ebooks.destroy');
    Route::post('/ebooks/{ebook}/generate-ai', [EbookController::class, 'generateAi'])->name('ebooks.generate-ai');
});

// Public E-Book Viewing & Streaming
Route::get('/ebooks/{ebook}', [EbookController::class, 'show'])->name('ebooks.show');
Route::get('/read/{ebook}', [EbookController::class, 'read'])->name('ebooks.read');
Route::get('/ebooks/{ebook}/file', [EbookController::class, 'file'])->name('ebooks.file');
Route::get('/ebooks/{ebook}/cover', [EbookController::class, 'cover'])->name('ebooks.cover');

// Backward Compatibility Aliases for APIs & Direct Links
Route::get('/api/ebooks/{ebook}/file', [EbookController::class, 'file']);
Route::get('/api/ebooks/{ebook}/cover', [EbookController::class, 'cover']);
Route::post('/api/ai/chat', [AiController::class, 'chat']);

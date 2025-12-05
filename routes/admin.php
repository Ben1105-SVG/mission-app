<?php

use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\QuestionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['auth', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin dashboard redirect
    Route::get('/', function () {
        $user = auth()->user();
        if ($user && $user->can('manage-inquiries')) {
            return redirect()->route('admin.inquiries.index');
        }
        return redirect()->route('admin.questions.index');
    })->name('index');

    // Inquiry management routes (Admin only)
    Route::middleware('can:manage-inquiries')->group(function () {
        Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
        Route::patch('/inquiries/{inquiry}', [InquiryController::class, 'update'])->name('inquiries.update');
        Route::post('/inquiries/{inquiry}/send-email', [InquiryController::class, 'sendEmail'])->name('inquiries.sendEmail');
        Route::delete('/inquiries/{inquiry}', [InquiryController::class, 'destroy'])->name('inquiries.destroy');
    });

    // Question management routes (Admin & Moderator)
    Route::middleware('can:manage-questions')->group(function () {
        Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
        Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
        Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
        Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
        Route::patch('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    });
});


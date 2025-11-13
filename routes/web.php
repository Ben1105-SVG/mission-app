<?php

use App\Http\Controllers\Admin\InquiryController as AdminInquiryController;
use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::middleware(['auth', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        $user = auth()->user();
        if ($user && $user->can('manage-inquiries')) {
            return redirect()->route('admin.inquiries.index');
        }
        return redirect()->route('admin.questions.index');
    });

    Route::middleware('can:manage-inquiries')->group(function () {
        Route::get('/inquiries', [AdminInquiryController::class, 'index'])->name('inquiries.index');
        Route::patch('/inquiries/{inquiry}', [AdminInquiryController::class, 'update'])->name('inquiries.update');
        Route::delete('/inquiries/{inquiry}', [AdminInquiryController::class, 'destroy'])->name('inquiries.destroy');
    });

    Route::middleware('can:manage-questions')->group(function () {
        Route::get('/questions', [AdminQuestionController::class, 'index'])->name('questions.index');
        Route::post('/questions', [AdminQuestionController::class, 'store'])->name('questions.store');
        Route::delete('/questions/{question}', [AdminQuestionController::class, 'destroy'])->name('questions.destroy');
    });
});

Route::view('/', 'welcome');

Route::view('/{any}', 'welcome')->where('any', '^(?!admin|login|logout).*$');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\InquiryController;

Route::get('/questions', [QuestionController::class, 'index']);
Route::post('/inquiries', [InquiryController::class, 'store']);


<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\NewsLetterController;
use App\Http\Controllers\CustomerAuthController;
use Statamic\Facades\Site;

Site::all()->each(function (Statamic\Sites\Site $site) {
	Route::prefix($site->url())->group(function () {
		Route::statamic('/class/category/{category_slug}', 'class-category');
	});
});

Route::get('/blog-search', [BlogController::class, 'search'])->name('blog.search');
Route::get('/header-search', [BlogController::class, 'headerSearch'])->name('header.search');
Route::get('/classes-search', [BlogController::class, 'classesSearch'])->name('classes.search');

// Route to handle newsletter subscription form submissions
Route::post('/newsLetter', [NewsLetterController::class, 'newsLetter'])->name('newsLetter');
Route::post('/customer/register', [CustomerAuthController::class, 'register']);
Route::post('/customer/login', [CustomerAuthController::class, 'login']);
Route::post('/customer/logout', [CustomerAuthController::class, 'logout']);
Route::post('/customer/forgot', [CustomerAuthController::class, 'forgot']);
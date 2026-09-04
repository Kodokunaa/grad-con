<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'loginForm'])->name('login');
Route::post('/', [AuthController::class, 'login'])->middleware('throttle:login');
foreach (['index.php', 'apply_login.php', 'auth/admin_login.php', 'auth/alumni_login.php', 'auth/login.php', 'auth/alumni_officer_auth.php'] as $path) {
    Route::get('/'.$path, [AuthController::class, 'loginForm']);
    Route::post('/'.$path, [AuthController::class, 'login'])->middleware('throttle:login');
}
Route::get('/register.php', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register.php', [AuthController::class, 'register'])->middleware('throttle:recovery');
Route::get('/forgot_password.php', [AuthController::class, 'forgotForm'])->name('password.request');
Route::post('/forgot_password.php', [AuthController::class, 'forgot'])->middleware('throttle:recovery');
Route::get('/reset_password.php', [AuthController::class, 'resetForm'])->name('password.reset');
Route::post('/reset_password.php', [AuthController::class, 'reset'])->middleware('throttle:recovery');
Route::get('/auth/logout.php', fn () => redirect('/'))->middleware('account');
Route::post('/auth/logout.php', [AuthController::class, 'logout'])->middleware('account')->name('logout');
Route::get('/admin/view_resume.php', [FileController::class, 'resume'])->middleware('account:admin');
Route::get('/uploads/{path}', [FileController::class, 'upload'])->where('path', '.*')->middleware('account');
Route::get('/admin/employer_list.php', fn () => redirect('/admin/create_employer.php'))->middleware('account:admin');
Route::get('/employer/my_jobs.php', fn () => redirect('/employer/posted_job.php'))->middleware('account:employer');
Route::get('/employer/job_list.php', fn () => redirect('/employer/posted_job.php'))->middleware('account:employer');
Route::get('/employer/jobl_list.php', fn () => redirect('/employer/posted_job.php'))->middleware('account:employer');
require __DIR__.'/pages.php';

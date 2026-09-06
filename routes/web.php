<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'loginForm'])->name('login');
Route::post('/', [AuthController::class, 'login'])->middleware('throttle:login');
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:recovery');
Route::get('/forgot-password', [AuthController::class, 'forgotForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'forgot'])->middleware('throttle:recovery');
Route::get('/reset-password', [AuthController::class, 'resetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'reset'])->middleware('throttle:recovery');
Route::get('/auth/logout', fn () => redirect('/'))->middleware('account');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('account')->name('logout');
Route::get('/admin/view-resume', [FileController::class, 'resume'])->middleware('account:admin');
Route::get('/applications/{application}/application-letter', [FileController::class, 'applicationLetter'])->middleware('account:admin,employer')->name('applications.letter');
Route::get('/uploads/{path}', [FileController::class, 'upload'])->where('path', '.*')->middleware('account');
Route::get('/admin/employer-list', fn () => to_route('admin.create_employer'))->middleware('account:admin');
Route::get('/employer/my-jobs', fn () => to_route('employer.posted_job'))->middleware('account:employer');
Route::get('/employer/job-list', fn () => to_route('employer.posted_job'))->middleware('account:employer');
require __DIR__.'/pages.php';

foreach (['index.php', 'apply_login.php', 'auth/admin_login.php', 'auth/alumni_login.php', 'auth/login.php', 'auth/alumni_officer_auth.php'] as $path) {
    Route::get('/'.$path, fn () => redirect('/', 301));
    Route::post('/'.$path, [AuthController::class, 'login'])->middleware('throttle:login');
}

foreach (['register.php' => '/register', 'forgot_password.php' => '/forgot-password', 'reset_password.php' => '/reset-password'] as $legacy => $target) {
    Route::get('/'.$legacy, fn (Request $request) => redirect($target.($request->getQueryString() ? '?'.$request->getQueryString() : ''), 301));
}

Route::post('/register.php', [AuthController::class, 'register'])->middleware('throttle:recovery');
Route::post('/forgot_password.php', [AuthController::class, 'forgot'])->middleware('throttle:recovery');
Route::post('/reset_password.php', [AuthController::class, 'reset'])->middleware('throttle:recovery');
Route::post('/auth/logout.php', [AuthController::class, 'logout'])->middleware('account');

Route::get('/admin/employer_list.php', fn () => to_route('admin.create_employer', status: 301))->middleware('account:admin');
foreach (['my_jobs.php', 'job_list.php', 'jobl_list.php'] as $legacy) {
    Route::get('/employer/'.$legacy, fn () => to_route('employer.posted_job', status: 301))->middleware('account:employer');
}

Route::get('/{legacy}.php', function (Request $request, string $legacy) {
    $target = '/'.$legacy;
    $query = $request->getQueryString();

    return redirect($target.($query ? '?'.$query : ''), 301);
})->where('legacy', '.*');

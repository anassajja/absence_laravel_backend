<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json([
        'message' => 'Laravel version ' . app()->version(),
        'status' => 200
    ]);
});

// Auth::routes(['verify' => true]); // Enable email verification for the authentication routes

Route::get('/api/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'status' => 200
    ]);
});

Route::get('/test-mail', function () {
    try {
        Mail::raw('This is a test email', function ($message) {
            $message->to('anass.ajja2020@gmail.com')
                    ->subject('Test Email');
        });
        return response()->json([
            'message' => 'Mail sent successfully',
            'status' => 200
        ]);
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/database-test', function () {
    try {
        $users = DB::table('users')->get();
        return response()->json([
            'message' => 'Database connection successful',
            'users' => $users,
            'status' => 200
        ]);
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

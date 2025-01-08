<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        Log::info('Register method called');
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'role' => 'required|string|in:student,teacher,admin',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
    
        Log::info('Validation passed', $validated);
    
        $user = User::create([
            'username' => $request->name,
            'role' => $request->role,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
    
        Log::info('User created', ['user' => $user]);
    
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'role' => 'required|string|in:student,teacher,admin',
            'login' => 'required|string', // This can be either email or username
            'password' => 'required|string',
        ]);
    
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username'; // Check if the login input is an email or username
    
        if (!Auth::attempt([$loginType => $request->login, 'password' => $request->password])) { // Attempt to authenticate the user
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    
        /** @var User $user */
        $user = Auth::user(); // Get the authenticated user 
        $sanctumToken = $user->createToken('auth_token')->plainTextToken; // Create a token using Sanctum
    
        return response()->json([
            'message' => 'Login successful',
            'sanctum_token' => $sanctumToken,
            'user' => $user,
        ]);
    }
    
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete(); // Revoke the token on logout

        return response()->json(['message' => 'User logged out']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([ // Validate the email field
            'email' => 'required|email',
        ]);

        try {
            $status = Password::sendResetLink( // Send password reset link
                $request->only('email') // Only send to the email
            );

            return $status === Password::RESET_LINK_SENT
                        ? response()->json(['message' => 'Reset link sent to your email.'])
                        : response()->json(['message' => 'Unable to send reset link.'], 500);
        } catch (\Exception $e) {
            Log::error('Error sending password reset link: ' . $e->getMessage());
            return response()->json(['message' => 'Server error. Please try again later.'], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([ // Validate the request
            'token' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);
    
        $passwordReset = DB::table('password_resets')->where('token', $request->token)->first(); // Get the token data from database 
    
        if (!$passwordReset) { // Check if the token is valid or not 
            return response()->json(['message' => 'Invalid token.'], 400); // Return error response
        }
    
        $user = User::where('email', $passwordReset->email)->first(); // Get the user by the email address
     
        if (!$user) { // Check if the user exists or not
            return response()->json(['message' => 'User not found.'], 404); // Return error response
        }
    
        $user->forceFill([ // Update the user password 
            'password' => Hash::make($request->password) // Hash the new password
        ])->save();
    
        // Delete the password reset token after successful password reset
        DB::table('password_resets')->where('token', $request->token)->delete();
    
        return response()->json(['message' => 'Password reset successful']);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        Log::info('Register method called');
        $validated = $request->validate([ // Validate the request data
            'username' => 'required|string|max:255',
            'role' => 'required|string|in:student,teacher,admin',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|confirmed|string|min:6',
        ]);
    
        Log::info('Validation passed', $validated);
    
        try {
            $isAdmin = $request->role === 'admin' ? true : false; // Check if the role is admin or not and set the is_admin field accordingly
            $user = User::create([
                'username' => $request->username, // Create a new user
                'role' => $request->role,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => $isAdmin, // Set the is_admin field to false if not provided
            ]);
        
            Log::info('User created', ['user' => $user]);
        
            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return response()->json(['message' => 'User registration failed!'], 409);
        }
    }
    
    public function login(Request $request)
    {
        $request->validate([ // Validate the request data 
            'role' => 'nullable|string|in:student,teacher,admin',
            'login' => 'required|string', // This can be either email or username
            'password' => 'required|string',
        ]);
    
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username'; // Check if the login input is an email or username
    
        if (!Auth::attempt([$loginType => $request->login, 'password' => $request->password])) { // Attempt to authenticate the user using the login input and password
            log::info('Invalid credentials', ['login' => $request->login]);
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    
        /** @var User $user */
        $user = Auth::user(); // Get the authenticated user 
        $sanctumToken = $user->createToken('auth_token')->plainTextToken; // Create a token using Sanctum
    
        log::info('User logged in', ['user' => $user]);

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
        // Validate the email field
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
    
        try {
            // Generate a random token
            $token = Str::random(64);
            log::info('Token', ['Token Generated : ' => $token]);
    
            // Store the token in the password_resets table as plaintext
            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => $token,
                    'created_at' => Carbon::now(),
                ]
            );
    
            // Construct the reset password link
            $resetLink = url('/api/password/reset-token?token=' . $token . '&email=' . urlencode($request->email));
    
            // Send the email with the reset link to the user using the reset-password view
            Mail::send('emails.reset-password', ['resetLink' => $resetLink], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Reset Your Password');
            });
    
            return response()->json(['message' => 'Reset password link sent successfully.'], 200);
    
        } catch (\Exception $e) {
            Log::error('Error sending password reset link: ' . $e->getMessage());
            return response()->json(['message' => 'Unable to send reset link.'], 500);
        }
    }
    
    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);
    
        try {
            // Get the reset entry from the password_resets table using the plaintext token
            $passwordReset = DB::table('password_resets')->where('token', $request->token)->first();
            Log::info('Password Reset', ['Password Reset : ' => $passwordReset]);
    
            if (!$passwordReset) { // Check if the reset token is valid or expired
                return response()->json(['message' => 'Invalid token.'], 400);
            }

            // Check if the token has expired (default expiration is 60 minutes)
            $tokenExpiration = config('auth.passwords.users.expire');
            if (Carbon::parse($passwordReset->created_at)->addMinutes($tokenExpiration)->isPast()) {
                return response()->json(['message' => 'Reset token has expired.'], 400);
            }
    
            // Retrieve the user associated with the email stored in the password_resets table
            $user = User::where('email', $passwordReset->email)->first();
    
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }
    
            // Update the user's password using forceFill
            $user->forceFill([
                'password' => Hash::make($request->password),
            ])->save();
    
            // Delete the password reset entry
            DB::table('password_resets')->where('email', $passwordReset->email)->delete();
    
            return response()->json(['message' => 'Password updated successfully.'], 200);
    
        } catch (\Exception $e) {
            Log::error('Error updating password: ' . $e->getMessage());
            return response()->json(['message' => 'Unable to update password.'], 500);
        }
    }

    public function getUserAuthenticated(Request $request)
    {
        // Get the authenticated user using the 'Auth' facade
        $user = Auth::user();
    
        // Check if the user is authenticated
        if ($user) {
            return response()->json([
                'message' => 'Authenticated user retrieved successfully',
                'user' => $user
            ], 200);
        }
    
        return response()->json(['message' => 'User not authenticated'], 401);
    }

    public function getUsers(Request $request)
    {
        // Get all users from the database
        $users = User::all();
    
        return response()->json([
            'message' => 'Users retrieved successfully',
            'users' => $users
        ], 200);
    }

    public function deleteUser(Request $request, $id)
    {
        // Find the user by ID
        $user = User::find($id);
    
        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        // Delete the user
        $user->delete();
    
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
    
}
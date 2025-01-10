<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        Log::info('updateProfile method called', ['user_id' => Auth::id()]);

        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'username' => 'nullable|string|max:255|unique:users,username,' . Auth::id(),
                'phone_number' => 'required|string|max:15',
                'email' => 'nullable|string|email|max:255|unique:users,email,' . Auth::id(),
                'password' => 'nullable|string|min:8|confirmed',
                'birth_date' => 'required|date',
            ]);

            Log::info('Validation passed', ['request_data' => $request->all()]);

            /** @var User $user */
            $user = Auth::user();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            if ($request->has('username')) {
                $user->username = $request->username;
            }
            $user->phone_number = $request->phone_number;
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->password) {
                $user->password = Hash::make($request->password);
            }
            $user->birth_date = $request->birth_date;
            $user->save();

            Log::info('User profile updated successfully', ['user' => $user]);

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating profile', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error updating profile'], 500);
        }
    }
}
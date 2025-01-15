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
                'role' => 'nullable|string|in:student,teacher,admin',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', 
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
            if ($request->has('role')) {
                $user->role = $request->role;
            }
    
            // Handle profile image upload
            if ($request->hasFile('profile_photo')) {   // Check if a file was uploaded in the request
                $file = $request->file('profile_photo'); // Get the file from the request object 
                $path = $file->store('profile_photos', 'public'); // Save in the 'public/profile_photos' directory
                $user->profile_photo_path = $path; // Save the file path in the database column
            }
    
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

    public function getProfilePhotoUrlAttribute()
    {
        $user = Auth::user(); // Get the authenticated user
        if ($user->profile_photo_path) { // Check if the user has a profile photo
            return asset('storage/' . $user->profile_photo_path); // Return the path to the user's profile photo
        }
        return asset('images/default_avatar.png'); // Path to your default avatar image
    }

    
}
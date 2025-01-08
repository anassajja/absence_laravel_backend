<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        $request->validate([ // Validate the request data using the following rules
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users, username,' . Auth::id(),
            'phone_number' => 'required|string|max:15',
            'email' => 'nullable|string|email|max:255|unique:users, email,' . Auth::id(),
            'password' => 'nullable|string|min:8|confirmed',
            'birth_date' => 'required|date', // Birth date is optional and should be a date
        ]);
 
        /** @var User $user */
        $user = Auth::user(); // Get the authenticated user
        $user->first_name = $request->first_name; // Update the first name of the user
        $user->last_name = $request->last_name; // Update the last name of the user
        $user->username = $request->username; // Update the username of the user
        $user->phone_number = $request->phone_number; // Update the phone number of the user
        $user->email = $request->email; // Update the email of the user
        if ($request->password) { // Check if the password is being updated
            $user->password = Hash::make($request->password); // Update the password of the user
        }
        $user->birth_date = $request->birth_date; // Update the birth date of the user
        $user->save(); // Save the changes to the user

        return response()->json([ // Return a JSON response with a message and the updated user
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{
    //
    public function index()

    {
        $users = DB::table('users')->get();

        return view('welcome', ['users' => $users]);
    }
    public function update(Request $request)
    {
        // Validate the request

        $userId = $request->input('id');

        // Prepare the data to be updated
        $userData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
        ];

        // Fetch the existing user to manage the old photo
        $existingUser = DB::table('users')->where('id', $userId)->first();

        if ($request->hasFile('upload')) {
            // Handle file upload
            $fileName = time() . '.' . $request->upload->extension();
            $request->upload->move(public_path('uploads'), $fileName);

            // Update user data with new photo
            $userData['upload'] = $fileName;

            // Delete old photo if it exists
            if ($existingUser->upload && file_exists(public_path('uploads/' . $existingUser->upload))) {
                unlink(public_path('uploads/' . $existingUser->upload));
            }
        }

        // Update the user record in the database
        DB::table('users')->where('id', $userId)->update($userData);

        return response()->json(['success' => 'User updated successfully']);
    }

    public function store(Request $request)
    {
        // Custom validation messages
        $messages = [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'Email has already been taken.',
            'phone.required' => 'Phone number is required.',
            'upload.required' => 'Image is required.',
            'upload.image' => 'File must be an image.',
            'upload.mimes' => 'Image must be a file of type: jpeg, png, jpg, gif.',
            'upload.max' => 'Image size must be less than 2MB.',
        ];

        // Validate the request
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required',
            'upload' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], $messages);

        if ($request->hasFile('upload')) {
            $fileName = time() . '.' . $request->upload->extension();
            $request->upload->move(public_path('uploads'), $fileName);

            DB::table('users')->insert([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'upload' => $fileName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => 'User added successfully']);
        }

        return response()->json(['error' => 'File upload failed'], 500);
    }
    public function destroy($id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if ($user) {
            // Delete the user's photo if it exists
            if ($user->upload && file_exists(public_path('uploads/' . $user->upload))) {
                unlink(public_path('uploads/' . $user->upload));
            }

            // Delete the user
            DB::table('users')->where('id', $id)->delete();

            return response()->json(['success' => 'User deleted successfully']);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    public function show($id)
    {
        // Fetch user data
        $user = DB::table('users')->where('id', $id)->first();

        // Generate HTML form with pre-filled data
        $html = '


    <form id="editUserForm" enctype="multipart/form-data" method="POST">
    <input type="hidden" name="_token" value="' . csrf_token() . '">
    <input type="hidden" name="_method" value="PUT">
        <input type="hidden" id="editUserId" name="id" value="' . $user->id . '">
        <div class="mb-3">
            <label for="editName" class="form-label">Name</label>
            <input type="text" class="form-control" id="editName" name="name" value="' . $user->name . '">
        </div>
        <div class="mb-3">
            <label for="editEmail" class="form-label">Email</label>
            <input type="email" class="form-control" id="editEmail" name="email" value="' . $user->email . '">
        </div>
        <div class="mb-3">
            <label for="editPhone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="editPhone" name="phone" value="' . $user->phone . '">
        </div>
        <div class="mb-3">
            <label for="editUpload" class="form-label">Image</label>
            <input type="file" class="form-control" id="editUpload" name="upload">
            <img src="' . asset('uploads/' . $user->upload) . '" width="50" id="currentImage">
        </div>
        <button type="submit" class="btn btn-primary">Save changes</button>
    </form>
';

        return response()->json(['html' => $html]);
    }
}

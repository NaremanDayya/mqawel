<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['sometimes', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('backend.login_failed')],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => [__('backend.account_inactive')],
            ]);
        }

        $token = $user->createToken($data['device_name'] ?? 'mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->load('role')),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('backend.logged_out')]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user()->load('role'));
    }

    public function updateMe(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes', 'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'picture' => ['nullable', 'image', 'max:10240'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'job_description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('picture')) {
            $data['picture'] = $request->file('picture')->store('form-attachments', 'public');
        }

        $user->update($data);

        return new UserResource($user->refresh()->load('role'));
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8'],
        ]);

        if (! Hash::check($data['old_password'], $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => [__('backend.old_password_incorrect')],
            ]);
        }

        $user->update(['password' => bcrypt($data['new_password'])]);

        return response()->json(['message' => __('backend.password_updated')]);
    }
}

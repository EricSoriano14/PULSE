<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    // ✅ GET /settings
    public function index(Request $request)
    {
        return view('settings', [
            'user' => $request->user(),
        ]);
    }

    // ✅ POST /settings (profile update)
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'info' => ['nullable', 'string', 'max:2000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if (!empty($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $path;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->address = $validated['address'] ?? null;
        $user->info = $validated['info'] ?? null;

        $user->save();

        return redirect()
            ->route('settings')
            ->with('success', 'Profile updated successfully.');
    }

    // ✅ POST /settings/change-password
    public function changePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()
                ->route('settings')
                ->withErrors([
                    'current_password' => 'Current password is incorrect.',
                ])
                ->withInput();
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()
            ->route('settings')
            ->with('success', 'Password updated successfully.');
    }
}
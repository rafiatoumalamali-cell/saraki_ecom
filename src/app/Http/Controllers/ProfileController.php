<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeasurementUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\CustomerMeasurement;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'measurements' => $request->user()->measurements ?? new CustomerMeasurement,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        $request->session()->put('locale', $request->user()->locale);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Replace the profile photo. Deliberately a separate endpoint so a text-only
     * profile save can never re-upload or drop the current file.
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', ...User::AVATAR_RULES],
        ]);

        $request->user()->storeAvatar($request->file('avatar'));

        return back()->with('status', 'photo-updated');
    }

    public function removePhoto(Request $request): RedirectResponse
    {
        $request->user()->removeAvatar();

        return back()->with('status', 'photo-removed');
    }

    /**
     * Upsert the tailor's measurement sheet: one row per customer, updated in place.
     */
    public function updateMeasurements(MeasurementUpdateRequest $request): RedirectResponse
    {
        $request->user()->measurements()->updateOrCreate([], $request->validated());

        return back()->with('status', 'measurements-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->removeAvatar();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

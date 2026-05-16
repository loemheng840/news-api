<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    /**
     * Get authenticated user's profile.
     * GET /api/profile
     */
    public function show(Request $request)
    {
        $profile = $request->user()->profile;

        if (!$profile) {
            $profile = UserProfile::create(['user_id' => $request->user()->id]);
        }

        return response()->json($profile);
    }

    /**
     * Update authenticated user's profile.
     * PUT /api/profile
     */
    public function update(Request $request)
    {
        $request->validate([
            'bio' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'social_links' => 'nullable|array',
            'social_links.*' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,gif|max:2048',
        ]);

        // Validate social_links keys
        if ($request->has('social_links') && $request->social_links !== null) {
            $allowedKeys = ['twitter', 'facebook', 'linkedin'];
            $providedKeys = array_keys($request->social_links);
            $invalidKeys = array_diff($providedKeys, $allowedKeys);

            if (!empty($invalidKeys)) {
                return response()->json([
                    'message' => 'Invalid social_links format. Only twitter, facebook, and linkedin keys are allowed.',
                ], 422);
            }
        }

        $profile = $request->user()->profile;

        if (!$profile) {
            $profile = UserProfile::create(['user_id' => $request->user()->id]);
        }

        $data = $request->only(['bio', 'website', 'location', 'social_links']);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->update($data);

        return response()->json($profile->fresh());
    }

    /**
     * Get a user's public profile.
     * GET /api/users/{id}/profile
     */
    public function showPublic($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $profile = $user->profile;

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return response()->json($profile);
    }
}

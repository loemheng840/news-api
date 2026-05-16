<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FOLLOW
    |--------------------------------------------------------------------------
    */

    public function follow(Request $request, int $userId)
    {
        $targetUser = User::find($userId);

        if (!$targetUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($request->user()->id === $targetUser->id) {
            return response()->json(['message' => 'You cannot follow yourself'], 422);
        }

        $follow = Follow::firstOrCreate(
            [
                'follower_id' => $request->user()->id,
                'following_id' => $targetUser->id,
            ],
            [
                'created_at' => now(),
            ]
        );

        // Send notification if this is a new follow
        if ($follow->wasRecentlyCreated) {
            app(NotificationService::class)->notifyNewFollower($follow);
        }

        return response()->json([
            'data' => [
                'following_id' => $targetUser->id,
                'following_name' => $targetUser->name,
                'followed_at' => $follow->created_at->toISOString(),
            ]
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | UNFOLLOW
    |--------------------------------------------------------------------------
    */

    public function unfollow(Request $request, int $userId)
    {
        $targetUser = User::find($userId);

        if (!$targetUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        Follow::where('follower_id', $request->user()->id)
            ->where('following_id', $targetUser->id)
            ->delete();

        return response()->json(['message' => 'Unfollowed successfully'], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | FOLLOWING (list users the current user follows)
    |--------------------------------------------------------------------------
    */

    public function following(Request $request)
    {
        $following = $request->user()
            ->following()
            ->orderByPivot('created_at', 'desc')
            ->paginate(10);

        $following->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'followed_at' => $user->pivot->created_at,
            ];
        });

        return response()->json($following);
    }

    /*
    |--------------------------------------------------------------------------
    | FOLLOWERS (list followers of the current user)
    |--------------------------------------------------------------------------
    */

    public function followers(Request $request)
    {
        $followers = $request->user()
            ->followers()
            ->orderByPivot('created_at', 'desc')
            ->paginate(15);

        $followers->getCollection()->transform(function ($follower) {
            return [
                'id' => $follower->id,
                'name' => $follower->name,
                'followed_at' => $follower->pivot->created_at,
            ];
        });

        return response()->json($followers);
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK FOLLOW STATUS
    |--------------------------------------------------------------------------
    */

    public function checkStatus(Request $request, int $userId)
    {
        $targetUser = User::find($userId);

        if (!$targetUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $follow = Follow::where('follower_id', $request->user()->id)
            ->where('following_id', $targetUser->id)
            ->first();

        return response()->json([
            'data' => [
                'is_following' => $follow !== null,
                'followed_at' => $follow?->created_at?->toISOString(),
            ]
        ]);
    }
}

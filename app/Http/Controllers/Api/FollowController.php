<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FOLLOW
    |--------------------------------------------------------------------------
    */

    public function follow(Request $request, int $authorId)
    {
        $author = User::find($authorId);

        if (!$author) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (!in_array($author->role, ['AUTHOR', 'ADMIN'])) {
            return response()->json(['message' => 'Target user is not an author'], 422);
        }

        if ($request->user()->id === $author->id) {
            return response()->json(['message' => 'You cannot follow yourself'], 422);
        }

        $follow = Follow::firstOrCreate(
            [
                'follower_id' => $request->user()->id,
                'author_id' => $author->id,
            ],
            [
                'created_at' => now(),
            ]
        );

        return response()->json([
            'data' => [
                'author_id' => $author->id,
                'author_name' => $author->name,
                'followed_at' => $follow->created_at->toISOString(),
            ]
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | UNFOLLOW
    |--------------------------------------------------------------------------
    */

    public function unfollow(Request $request, int $authorId)
    {
        $author = User::find($authorId);

        if (!$author) {
            return response()->json(['message' => 'User not found'], 404);
        }

        Follow::where('follower_id', $request->user()->id)
            ->where('author_id', $author->id)
            ->delete();

        return response()->json(['message' => 'Unfollowed successfully'], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | FOLLOWING (list authors the current user follows)
    |--------------------------------------------------------------------------
    */

    public function following(Request $request)
    {
        $following = $request->user()
            ->following()
            ->orderByPivot('created_at', 'desc')
            ->paginate(10);

        $following->getCollection()->transform(function ($author) {
            return [
                'id' => $author->id,
                'name' => $author->name,
                'email' => $author->email,
                'followed_at' => $author->pivot->created_at,
            ];
        });

        return response()->json($following);
    }

    /*
    |--------------------------------------------------------------------------
    | FOLLOWERS (list followers of the current author)
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

    public function checkStatus(Request $request, int $authorId)
    {
        $author = User::find($authorId);

        if (!$author) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (!in_array($author->role, ['AUTHOR', 'ADMIN'])) {
            return response()->json(['message' => 'Target user is not an author'], 404);
        }

        $follow = Follow::where('follower_id', $request->user()->id)
            ->where('author_id', $author->id)
            ->first();

        return response()->json([
            'data' => [
                'is_following' => $follow !== null,
                'followed_at' => $follow?->created_at?->toISOString(),
            ]
        ]);
    }
}

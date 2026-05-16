<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    /**
     * Get authenticated user's notification settings.
     * GET /api/notification-settings
     */
    public function show(Request $request)
    {
        $settings = $request->user()->notificationSetting;

        if (!$settings) {
            $settings = NotificationSetting::create(['user_id' => $request->user()->id]);
        }

        return response()->json($settings);
    }

    /**
     * Update authenticated user's notification settings.
     * PUT /api/notification-settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'email_notifications' => 'sometimes|boolean',
            'push_notifications' => 'sometimes|boolean',
            'follow_notifications' => 'sometimes|boolean',
            'comment_notifications' => 'sometimes|boolean',
            'like_notifications' => 'sometimes|boolean',
        ]);

        $settings = $request->user()->notificationSetting;

        if (!$settings) {
            $settings = NotificationSetting::create(['user_id' => $request->user()->id]);
        }

        $settings->update($request->only([
            'email_notifications',
            'push_notifications',
            'follow_notifications',
            'comment_notifications',
            'like_notifications',
        ]));

        return response()->json($settings->fresh());
    }
}

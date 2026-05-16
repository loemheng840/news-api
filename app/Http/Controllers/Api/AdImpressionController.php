<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdImpression;
use App\Models\AdPlacement;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdImpressionController extends Controller
{
    /**
     * Record an ad impression.
     * POST /api/ad-impressions
     */
    public function store(Request $request)
    {
        $request->validate([
            'placement_id' => 'required|exists:ad_placements,id',
            'article_id' => 'nullable|exists:articles,id',
            'ip_address' => 'required|ip|max:45',
        ]);

        // Check if placement is active
        $placement = AdPlacement::find($request->placement_id);
        if (!$placement->is_active) {
            return response()->json(['message' => 'Ad placement is inactive'], 422);
        }

        $impression = AdImpression::create([
            'placement_id' => $request->placement_id,
            'article_id' => $request->article_id,
            'user_id' => $request->user()?->id,
            'clicked' => false,
            'ip_address' => $request->ip_address,
        ]);

        return response()->json($impression, 201);
    }

    /**
     * Record a click on an impression.
     * PATCH /api/ad-impressions/{id}/click
     */
    public function click($id)
    {
        $impression = AdImpression::find($id);

        if (!$impression) {
            return response()->json(['message' => 'Impression not found'], 404);
        }

        $impression->clicked = true;
        $impression->save();

        return response()->json($impression);
    }

    /**
     * Get ad analytics (admin only).
     * GET /api/admin/ad-analytics
     */
    public function analytics(Request $request)
    {
        $query = AdImpression::query()
            ->select(
                'placement_id',
                DB::raw('COUNT(*) as total_impressions'),
                DB::raw('SUM(CASE WHEN clicked = true THEN 1 ELSE 0 END) as total_clicks'),
                DB::raw('ROUND(SUM(CASE WHEN clicked = true THEN 1 ELSE 0 END)::numeric / NULLIF(COUNT(*), 0), 4) as click_through_rate')
            )
            ->groupBy('placement_id');

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $analytics = $query->with('placement')->get();

        return response()->json($analytics);
    }
}

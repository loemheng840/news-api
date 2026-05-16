<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdPlacement;
use Illuminate\Http\Request;

class AdPlacementController extends Controller
{
    /**
     * List all ad placements (admin).
     * GET /api/ad-placements
     */
    public function index()
    {
        return response()->json(AdPlacement::latest()->paginate(15));
    }

    /**
     * Create an ad placement.
     * POST /api/ad-placements
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|in:HEADER,SIDEBAR,IN_ARTICLE,FOOTER',
            'type' => 'required|in:BANNER,NATIVE,VIDEO',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        $placement = AdPlacement::create($request->only([
            'name', 'position', 'type', 'start_date', 'end_date', 'is_active',
        ]));

        return response()->json($placement, 201);
    }

    /**
     * Show a single ad placement.
     * GET /api/ad-placements/{id}
     */
    public function show($id)
    {
        $placement = AdPlacement::find($id);

        if (!$placement) {
            return response()->json(['message' => 'Ad placement not found'], 404);
        }

        return response()->json($placement);
    }

    /**
     * Update an ad placement.
     * PUT /api/ad-placements/{id}
     */
    public function update(Request $request, $id)
    {
        $placement = AdPlacement::find($id);

        if (!$placement) {
            return response()->json(['message' => 'Ad placement not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'position' => 'sometimes|in:HEADER,SIDEBAR,IN_ARTICLE,FOOTER',
            'type' => 'sometimes|in:BANNER,NATIVE,VIDEO',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        $placement->update($request->only([
            'name', 'position', 'type', 'start_date', 'end_date', 'is_active',
        ]));

        return response()->json($placement);
    }

    /**
     * Delete an ad placement.
     * DELETE /api/ad-placements/{id}
     */
    public function destroy($id)
    {
        $placement = AdPlacement::find($id);

        if (!$placement) {
            return response()->json(['message' => 'Ad placement not found'], 404);
        }

        $placement->delete();

        return response()->json(['message' => 'Ad placement deleted successfully']);
    }

    /**
     * Get active ad placements (public).
     * GET /api/ads/active
     */
    public function active()
    {
        $today = now()->toDateString();

        $placements = AdPlacement::where('is_active', true)
            ->where(function ($query) use ($today) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            })
            ->get();

        return response()->json($placements);
    }
}

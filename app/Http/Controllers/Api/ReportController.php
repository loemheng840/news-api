<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Create a report.
     * POST /api/reports
     */
    public function store(Request $request)
    {
        $request->validate([
            'target_type' => 'required|in:article,comment,user',
            'target_id' => 'required|integer',
            'reason' => 'required|string|min:1|max:2000',
        ]);

        // Validate target exists
        $targetExists = match ($request->target_type) {
            'article' => Article::where('id', $request->target_id)->exists(),
            'comment' => Comment::where('id', $request->target_id)->exists(),
            'user' => User::where('id', $request->target_id)->exists(),
            default => false,
        };

        if (!$targetExists) {
            return response()->json(['message' => 'Target not found'], 422);
        }

        // Check for duplicate report
        $existingReport = Report::where('user_id', $request->user()->id)
            ->where('target_type', $request->target_type)
            ->where('target_id', $request->target_id)
            ->first();

        if ($existingReport) {
            return response()->json(['message' => 'You have already reported this content'], 422);
        }

        $report = Report::create([
            'user_id' => $request->user()->id,
            'target_type' => $request->target_type,
            'target_id' => $request->target_id,
            'reason' => $request->reason,
            'status' => 'PENDING',
        ]);

        return response()->json($report, 201);
    }

    /**
     * List all reports (admin only).
     * GET /api/admin/reports
     */
    public function index(Request $request)
    {
        $query = Report::query()->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $request->validate(['status' => 'in:PENDING,REVIEWED,REJECTED']);
            $query->where('status', $request->status);
        }

        if ($request->has('target_type')) {
            $request->validate(['target_type' => 'in:article,comment,user']);
            $query->where('target_type', $request->target_type);
        }

        $perPage = min($request->input('per_page', 15), 50);

        return response()->json($query->paginate($perPage));
    }

    /**
     * Review a report (admin only).
     * PATCH /api/admin/reports/{id}
     */
    public function review(Request $request, $id)
    {
        $report = Report::find($id);

        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $request->validate([
            'status' => 'required|in:REVIEWED,REJECTED',
        ]);

        $report->update([
            'status' => $request->status,
            'reviewed_by' => $request->user()->id,
        ]);

        return response()->json($report);
    }
}

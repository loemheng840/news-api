<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SearchLog;
use Illuminate\Http\Request;

class SearchLogController extends Controller
{
    /**
     * List search logs (admin only).
     * GET /api/admin/search-logs
     */
    public function index(Request $request)
    {
        $query = SearchLog::query()->orderBy('created_at', 'desc');

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        return response()->json($query->paginate(20));
    }
}

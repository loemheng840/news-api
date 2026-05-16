<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaLibraryController extends Controller
{
    /**
     * Upload a media file.
     * POST /api/media
     */
    public function store(Request $request)
    {
        if (!in_array($request->user()->role, ['AUTHOR', 'ADMIN'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpeg,png,gif,webp,pdf,mp4',
            'alt_text' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $path = $file->store('media', 'public');

        $media = MediaLibrary::create([
            'uploader_id' => $request->user()->id,
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'alt_text' => $request->alt_text,
        ]);

        return response()->json($media, 201);
    }

    /**
     * List media files.
     * GET /api/media
     */
    public function index(Request $request)
    {
        $query = MediaLibrary::query();

        // Non-admin users only see their own uploads
        if ($request->user()->role !== 'ADMIN') {
            $query->where('uploader_id', $request->user()->id);
        }

        return response()->json($query->latest()->paginate(15));
    }

    /**
     * Delete a media file.
     * DELETE /api/media/{id}
     */
    public function destroy(Request $request, $id)
    {
        $media = MediaLibrary::find($id);

        if (!$media) {
            return response()->json(['message' => 'Media not found'], 404);
        }

        // Only owner or admin can delete
        if ($request->user()->id !== $media->uploader_id && $request->user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        Storage::disk('public')->delete($media->path);
        $media->delete();

        return response()->json(['message' => 'Media deleted successfully']);
    }
}

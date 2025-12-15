<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorageController extends Controller
{
    /**
     * Serve files from storage/app/public without requiring symlinks
     * This is a workaround for servers where symlink() function is disabled
     * 
     * @param Request $request
     * @param string $path
     * @return BinaryFileResponse|\Illuminate\Http\Response
     */
    public function serve(Request $request, $path = '')
    {
        // Security: Prevent directory traversal
        $path = str_replace('..', '', $path);
        $path = ltrim($path, '/');
        
        // Check if file exists in public storage
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found');
        }
        
        // Get the full file path
        $filePath = Storage::disk('public')->path($path);
        
        // Check if file actually exists on filesystem
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }
        
        // Get file mime type
        $mimeType = Storage::disk('public')->mimeType($path);
        
        // Return file response with proper headers
        return response()->file($filePath, [
            'Content-Type' => $mimeType ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=31536000', // Cache for 1 year
        ]);
    }
}







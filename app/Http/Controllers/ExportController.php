<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function download(Request $request, $user_id, $filename)
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        $path = "exports/{$user_id}/{$filename}";

        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path, $filename);
    }
}

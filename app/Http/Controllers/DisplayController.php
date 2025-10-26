<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Display;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;

class DisplayController extends BaseController {
    public function index(Request $request): JsonResponse {
        $userId = $request->user()->id ?? null;
        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Return only displays table data for the current user (no related joins)
        $displays = Display::where('user_id', $userId)->get();

        return response()->json(['displays' => $displays]);
    }

    public function get(Display $display): Response {
        // TODO: render the display's current image using its modules.
        $body = $display->token; // placeholder image data
        $contentType = 'image/png';

        // Example: you can access $display->id, $display->token, etc.
        return response($body, 200)
            ->header('Content-Type', $contentType)
            ->header('Content-Length', strlen($body));
    }

    public function getData(Request $request, Display $displayId): JsonResponse {
        $userId = $request->user()->id ?? null;
        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($displayId->user_id !== $userId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json([
            "display" => $displayId,
            "modules" => $displayId->modules,
        ]);
    }

    public function setData(Request $request, Display $displayId): JsonResponse {
        $userId = $request->user()->id ?? null;
        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($displayId->user_id !== $userId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // TODO: implement set logic (update modules or data)
        return response()->json([], 204);
    }

    public function create(Request $request): JsonResponse {
        $userId = $request->user()->id ?? null;
        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Current numeric timestamp (seconds since epoch).
        $timestamp = now()->getTimestamp();

        $display = Display::create([
            'user_id' => $userId,
            'name' => 'New Display',
            'token' => (string) $timestamp . '-' . (string) Str::uuid(),
        ]);

        return response()->json($display, 201);
    }
}

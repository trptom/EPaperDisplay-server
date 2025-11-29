<?php

namespace App\Http\Controllers;

use App\Modules\_Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Display;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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

    public function get(Request $request, Display $display): Response {
        if (!$display) {
            return response('Display not found', 404);
        }
        if ($display->ip_filter) {
            $requestIp = $request->ip();

            if (!$display->allowedIps()->where('ip', $requestIp)->exists()) {
                return response('Forbidden', 403);
            }
        }

        $img = $this->getDisplayImage($display);
        if ($img === false) {
            return response('Failed to create image', 500);
        }

        // Example: you can access $display->id, $display->token, etc.
        return response($img, 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Length', strlen($img));
    }

    public function getData(Request $request, Display $displayId): JsonResponse {
        $userId = $request->user()->id ?? null;
        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($displayId->user_id !== $userId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Load modules relationship
        $displayId->load('modules');

        return response()->json($displayId);
    }

    public function setData(Request $request, Display $displayId): JsonResponse {
        $userId = $request->user()->id ?? null;
        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($displayId->user_id !== $userId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:64',
            'model' => 'sometimes|integer',
            'width' => 'sometimes|integer',
            'height' => 'sometimes|integer',
            'language' => 'sometimes|string|max:5|nullable',
            'timezone' => 'sometimes|string|max:50|nullable',
            'latitude' => 'sometimes|numeric|nullable',
            'longitude' => 'sometimes|numeric|nullable',
            'ip_filter' => 'boolean',
            'modules' => 'array',
            'modules.*.position' => 'required|integer',
            'modules.*.type' => 'required|integer',
            'modules.*.x' => 'required|integer',
            'modules.*.y' => 'required|integer',
            'modules.*.width' => 'required|integer',
            'modules.*.height' => 'required|integer',
            'modules.*.border' => 'required|integer',
            'modules.*.data' => 'sometimes|array|nullable',
        ]);

        // Convert 'modules.*.data' => 'sometimes|string|nullable' from Array to String.
        foreach ($validated['modules'] as $index => $module) {
            if (isset($module['data'])) {
                $validated['modules'][$index]['data'] = json_encode($module['data']);
            }
        }

        try {
            DB::transaction(function () use ($displayId, $request, $validated) {
                $displayId->update($validated);

                // Remove all existing modules
                $displayId->modules()->delete();

                // Add new modules
                foreach ($validated['modules'] as $moduleData) {
                    $displayId->modules()->create($moduleData);
                }
            });

            // Success: No Content
            return response()->json([], 204);
        } catch (\Throwable $e) {
            // Failure: Internal Server Error
            return response()->json([
                'message' => 'Failed to update display data',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
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

    public function getImage(Request $request, Display $displayId): Response|JsonResponse {
        $userId = $request->user()->id ?? null;
        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($displayId->user_id !== $userId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $img = $this->getDisplayImage($displayId);
        if ($img === false) {
            return response()->json(['message' => 'Failed to create image'], 500);
        }

        // Return response with correct headers
        return response($img, 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Length', strlen($img));
    }

    private function getDisplayImage(Display $display): string|null {
        $img = imagecreatetruecolor($display->width, $display->height);
        if ($img === false) {
            return null;
        }

        // Fill with white background
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefilledrectangle($img, 0, 0, $display->width, $display->height, $white);

        $display->modules->each(function ($module) use ($img, $display, $black) {
            for ($i=0; $i<$module->border; $i++) {
                // Draw border
                imagerectangle(
                    $img,
                    $module->x + $i,
                    $module->y + $i,
                    $module->x + $module->width - $i - 1,
                    $module->y + $module->height - $i - 1,
                    $black
                );
            }

            $w = $display->width - 2 * $module->border;
            $h = $display->height - 2 * $module->border;

            $moduleImpl = _Module::getModule($module->id);
            $moduleImg = $moduleImpl->getImage(
                $display->language,
                $w,
                $h,
                $module->data);
            if ($moduleImg !== null) {
                // Merge the module image into the display image
                imagecopy($img, $moduleImg,
                        $module->x + $module->border,
                        $module->y + $module->border,
                        0,
                        0,
                        $w,
                        $h);
            }
        });

        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();

        // Free image resource
        imagedestroy($img);

        return $pngData;
    }
}

<?php

namespace App\Modules;

use App\Modules\_Module;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaticImageModule extends _Module
{
    public function info(Request $request): JsonResponse
    {
        // Return an empty JSON object for now; payload will be implemented later.
        return response()->json((object)[]);
    }

    public function set(Request $request): JsonResponse
    {
        // Not implemented for this module.
        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function get(Request $request): Response
    {
        $url = 'http://www.google.cz/intl/en_ALL/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png';

        try {
            $res = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders(['Accept' => 'image/png'])
                ->get($url);

            if ($res->successful()) {
                $body = $res->body();
                $contentType = $res->header('Content-Type') ?? 'image/png';

                return response($body, 200)
                    ->header('Content-Type', $contentType)
                    ->header('Content-Length', strlen($body));
            }

            return response('Failed to fetch image', 502)
                ->header('Content-Type', 'text/plain');
        } catch (\Exception $e) {
            return response("Error fetching image 2: {$e->getMessage()}", 502)
                ->header('Content-Type', 'text/plain');
        }
    }
}

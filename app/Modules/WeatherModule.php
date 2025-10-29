<?php

namespace App\Modules;

use App\Modules\_Module;
use GdImage;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherModule extends _Module
{
    public function getAttsDef(): array {
        return [
        ];
    }

    public function getImage(string $lang, int $w, int $h, array $atts): GdImage|null {
        $img = imagecreatetruecolor($w, $h);
        if ($img == false) {
            return null;
        }

        // TODO weather forecast implementation.

        return $img;
    }
}

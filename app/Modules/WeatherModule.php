<?php

namespace App\Modules;

use App\Models\Display;
use App\Modules\_Module;
use GdImage;

class WeatherModule extends _Module
{
    public function getAttsDef(): array {
        return [
        ];
    }

    public function getImage(Display &$display, int $w, int $h, array $atts): GdImage|null {
        $location = null;
        if ($display->latitude !== null && $display->longitude !== null) {
            $location = [$display->latitude, $display->longitude];
        }
        if (!$location && isset($atts['latitude']) && isset($atts['longitude'])) {
            $location = [$atts['latitude'], $atts['longitude']];
        }

        $img = imagecreatetruecolor($w, $h);
        if ($img == false) {
            return null;
        }

        if (!$location) {
            $location = [0, 0]; // Default location
        }

        return $img;
    }
}

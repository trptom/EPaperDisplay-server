<?php

namespace App\Modules;

use App\Models\Display;
use App\Modules\_Module;
use GdImage;

class CalendarModule extends _Module
{
    public function getAttsDef(): array {
        return [
        ];
    }

    public function getImage(Display &$display, int $w, int $h, array $atts): GdImage|null {
        $img = imagecreatetruecolor($w, $h);
        if ($img == false) {
            return null;
        }

        // TODO calendar implementation.

        return $img;
    }
}

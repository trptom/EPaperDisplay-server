<?php

namespace App\Modules;

use App\Models\Display;
use App\Modules\_Module;
use GdImage;

class SimpleTextModule extends _Module
{
    public function getAttsDef(): array {
        return [
            new ModuleAttrDefinition('text', MODULE_ATTR_TYPE_STRING, null),
            new ModuleAttrDefinition('alignment_x', MODULE_ATTR_TYPE_STRING, ['left', 'center', 'right']),
            new ModuleAttrDefinition('alignment_y', MODULE_ATTR_TYPE_STRING, ['top', 'middle', 'bottom']),
            new ModuleAttrDefinition('font_family', MODULE_ATTR_TYPE_STRING, ['arial', 'verdana', 'times',]),
            new ModuleAttrDefinition('font_size', MODULE_ATTR_TYPE_INTEGER, null)
        ];
    }

    public function getImage(Display &$display, int $w, int $h, array $atts): GdImage|null {
        $text = $atts['text'] ?? null;
        $alignment_x = $atts['alignment_x'] ?? null;
        $alignment_y = $atts['alignment_y'] ?? null;
        $font_family = $atts['font_family'] ?? null;
        $font_size = $atts['font_size'] ?? null;

        $img = imagecreatetruecolor($w, $h);
        if ($img == false) {
            return null;
        }

        // Fill white background
        imagefilledrectangle($img, 0, 0, $w, $h, COLOR_WHITE);

        if ($text) {

            // Use built-in font
            $textBox = imagettfbbox($font_size, 0, __DIR__ . '/fonts/' . $font_family . '.ttf', $text);
            $textWidth = abs($textBox[4] - $textBox[0]);
            $textHeight = abs($textBox[5] - $textBox[1]);

            // Calculate position based on alignment
            switch ($alignment_x) {
                case 'center':
                    $x = ($w - $textWidth) / 2;
                    break;
                case 'right':
                    $x = $w - $textWidth;
                    break;
                case 'left':
                default:
                    $x = 0;
                    break;
            }

            switch ($alignment_y) {
                case 'middle':
                    $y = ($h + $textHeight) / 2;
                    break;
                case 'bottom':
                    $y = $h;
                    break;
                case 'top':
                default:
                    $y = $textHeight;
                    break;
            }

            // Render the text onto the image
            imagettftext($img, $font_size, 0, (int)$x, (int)$y, COLOR_BLACK, __DIR__ . '/fonts/' . $font_family . '.ttf', $text);
        }

        return $img;
    }
}

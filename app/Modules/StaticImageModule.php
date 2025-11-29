<?php

namespace App\Modules;

use App\Models\Display;
use App\Modules\_Module;
use GdImage;

class StaticImageModule extends _Module
{
    public function getAttsDef(): array {
        return [
            new ModuleAttrDefinition('url', MODULE_ATTR_TYPE_STRING, null),
            new ModuleAttrDefinition('aspect_ratio', MODULE_ATTR_TYPE_BOOLEAN, null)
        ];
    }

    public function getImage(Display &$display, int $w, int $h, array $atts): GdImage|null {
        $url = $atts['url'] ?? null;
        $aspectRatio = $atts['aspect_ratio'] ?? null;

        $img = null;

        if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
            // Use a short timeout when fetching external images. Suppress warnings.
            $context = stream_context_create([
                'http' => ['timeout' => 5],
                'https' => ['timeout' => 5],
            ]);

            $data = @file_get_contents($url, false, $context);
            if ($data !== false) {
                $external = @imagecreatefromstring($data);
                if ($external !== false) {
                    // Create destination canvas and center-fit the external image
                    $img = imagecreatetruecolor($w, $h);
                    if ($img !== false) {
                        // Fill white background
                        imagefilledrectangle($img, 0, 0, $w, $h, COLOR_WHITE);

                        $ew = imagesx($external);
                        $eh = imagesy($external);

                        if ($ew > 0 && $eh > 0) {
                            if ($aspectRatio) {
                                // Fit while preserving aspect ratio
                                $scale = min($w / $ew, $h / $eh, 1);
                                $tw = (int) round($ew * $scale);
                                $th = (int) round($eh * $scale);
                                $dstX = (int) round(($w - $tw) / 2);
                                $dstY = (int) round(($h - $th) / 2);
                            } else {
                                // Stretch to fill
                                $tw = $w;
                                $th = $h;
                                $dstX = 0;
                                $dstY = 0;
                            }

                            // Preserve transparency for PNG/GIF
                            if (imageistruecolor($external) === false) {
                                $tmp = imagecreatetruecolor($tw, $th);
                                $trans = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
                                imagefill($tmp, 0, 0, $trans);
                                imagesavealpha($tmp, true);
                                imagecopyresampled($tmp, $external, 0, 0, 0, 0, $tw, $th, $ew, $eh);
                                imagecopy($img, $tmp, $dstX, $dstY, 0, 0, $tw, $th);
                                imagedestroy($tmp);
                            } else {
                                imagecopyresampled($img, $external, $dstX, $dstY, 0, 0, $tw, $th, $ew, $eh);
                            }
                        }
                    } else {
                        $img = null;
                    }

                    imagedestroy($external);
                }
            }
        }

        return $img;
    }
}

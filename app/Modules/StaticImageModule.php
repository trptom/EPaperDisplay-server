<?php

namespace App\Modules;

use App\Modules\_Module;
use GdImage;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaticImageModule extends _Module
{
    public function getAttsDef(): array {
        return [
            new ModuleAttrDefinition('url', MODULE_ATTR_TYPE_STRING, null)
        ];
    }

    public function getImage(int $w, int $h, array $atts): GdImage|null {
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
                        $white = imagecolorallocate($img, 255, 255, 255);
                        imagefilledrectangle($img, 0, 0, $w, $h, $white);

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

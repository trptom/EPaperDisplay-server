<?php

namespace App\Modules;

use App\Models\Display;
use GdImage;

const MODULE_ID_STATIC_IMAGE = 1;
const MODULE_ID_SIMPLE_TEXT = 2;
const MODULE_ID_WEATHER = 3;
const MODULE_ID_CALENDAR = 4;

const MODULE_ATTR_TYPE_STRING = 1;
const MODULE_ATTR_TYPE_INTEGER = 2;
const MODULE_ATTR_TYPE_BOOLEAN = 3;

const COLOR_BLACK = imagecolorallocate($img, 0, 0, 0);
const COLOR_WHITE = imagecolorallocate($img, 255, 255, 255);

class ModuleAttrDefinition {

    public string $name;
    public int $type;
    public array|null $values;

    public function __construct(string $name, int $type, array|null $values) {
        $this->name = $name;
        $this->type = $type;
        $this->values = $values;
    }
}

/**
 * General display module class. Every module must extend this class.
 */
abstract class _Module {
    public static function getModules(int $id): array {
        return [
            'static_image' => (new StaticImageModule())->getAttsDef(),
            'simple_text' => (new SimpleTextModule())->getAttsDef(),
            'weather' => (new WeatherModule())->getAttsDef(),
            'calendar' => (new CalendarModule())->getAttsDef()
        ];
    }

    public static function getModule(int $id): _Module|null {
        switch ($id) {
            case MODULE_ID_STATIC_IMAGE:
                return new StaticImageModule();
            case MODULE_ID_SIMPLE_TEXT:
                return new SimpleTextModule();
            case MODULE_ID_WEATHER:
                // return new WeatherModule();
                return null;
            case MODULE_ID_CALENDAR:
                // return new CalendarModule();
                return null;
            default:
                return null;
        }
    }

    /**
     * @return ModuleAttrDefinition[]
     */
    abstract public static function getAttsDef(): array;

    abstract public function getImage(Display &$display, int $w, int $h, array $atts): GdImage|null;


}

<?php

namespace App\Modules;

use GdImage;

const MODULE_ID_STATIC_IMAGE = 1;
const MODULE_ID_WEATHER = 2;
const MODULE_ID_CALENDAR = 2;

const MODULE_ATTR_TYPE_STRING = 1;
const MODULE_ATTR_TYPE_INTEGER = 2;
const MODULE_ATTR_TYPE_BOOLEAN = 3;

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
    public static function getModule(int $id): _Module|null {
        switch ($id) {
            case MODULE_ID_STATIC_IMAGE:
                return new StaticImageModule();
            // case MODULE_ID_EXAMPLE:
            //     return new ExampleModule();
            default:
                return null;
        }
    }

    /**
     * @return ModuleAttrDefinition[]
     */
    abstract public function getAttsDef(): array;

    abstract public function getImage(int $w, int $h, array $atts): GdImage|null;


}

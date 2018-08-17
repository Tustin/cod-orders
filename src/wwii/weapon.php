<?php

class Weapon {
    public $localized;
    public $image;

    public function __construct($localized, $image) {
        $this->localized = $localized;
        $this->image = $image;
    }

    public static function easy(string $name): ?self {
        global $weapons;
        $asset_name = substr($name, 0, strpos($name, '_mp'));
        if (!array_key_exists($asset_name, $weapons)) {
            return null;
        } 
        $found_weapon = $weapons[$asset_name];
        $image = imagecreatefrompng('weapons/' . $asset_name . '.png');
        return new self($asset_name, $image);
    }
}

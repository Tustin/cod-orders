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

    public static function hard(string $name): ?self {
        global $weapons, $weapon_icon_files;
        $search = $name;
        if (strpos($name, 'loot') !== false)    $search = substr($name, 0, strpos($name, 'loot'));
        if (strpos($name, '_mp') !== false)     $search = substr($name, 0, strpos($name, '_mp'));

        $shortest = -1;
        foreach ($weapons as $localized => $friendly) {
            if (strpos($localized, $search) === false) continue;
            $new_name = substr_replace($name, '_', strpos($name, 'loot'), 0);
            $match = levenshtein($new_name, $localized);

            if ($match === 0) {
                $closest = $localized;
                $shortest = $match;
                break;
            }

            if ($match <= $shortest || $shortest === -1) {
                $closest = $localized;
                $shortest = $match;
            }
        }

        $shortest = -1;
        foreach ($weapon_icon_files as $file) {
            if (strpos($file, $search) === false) continue;
            $match = levenshtein($closest, $file);

            if ($match === 0) {
                $closest_file = $file;
                $shortest = $match;
                break;
            }

            if ($match <= $shortest || $shortest === -1) {
                $closest_file = $file;
                $shortest = $match;
            }
        }
        $image = imagecreatefrompng('weapons/' . $closest_file);
        return new self($closest, $image);
    }
}

<?php

const CURRENCY_XP = "1"; // Only for contracts that give immediate XP drops
const CURRENCY_CREDITS = "6";
const CURRENCY_SOCIAL = "7";

const PRODUCT_SD = "1";
const PRODUCT_RARE_SD = "2";
const PRODUCT_BLITZKRIEG_BRIBE = "95";
const PRODUCT_XP = "2147483674";

class Reward {
    public $image;
    public $type;
    public $label;

    public function __construct($image, $type, $label) {
        $this->image = $image;
        $this->type = $type;
        $this->label = $label;
    }

    public static function parse(object $reward): ?self {
        global $assets;

        switch ($reward->type) {
            case "grant_currency": {
                switch ($reward->currency->id) {
                    case CURRENCY_CREDITS:
                    return new self(imagecreatefrompng($assets . "/rewards/credits.png"), "currency" , $reward->currency->amount . " " . $reward->currency->label);
                    case CURRENCY_SOCIAL:
                    return new self(imagecreatefrompng($assets . "/rewards/social.png"), "currency", $reward->currency->amount . " " . $reward->currency->label);
                    default:
                    return new self(null, "currency", ($reward->currency->label && $reward->currency->amount)  ? $reward->currency->amount . " " . $reward->currency->label : "Unknown currency");
                }
            }
            case "grant_product": {
                switch ($reward->product->id) {
                    case PRODUCT_XP:
                    return new self(imagecreatefrompng($assets . "/rewards/xp.png"), "product", $reward->product->label ?? $reward->product->name);
                    case PRODUCT_SD:
                    return new self(imagecreatefrompng($assets . "/rewards/sd.png"), "product", $reward->product->label ?? $reward->product->name);
                    case PRODUCT_RARE_SD:
                    return new self(imagecreatefrompng($assets . "/rewards/rsd.png"), "product", $reward->product->label ?? $reward->product->name);
                    case PRODUCT_BLITZKRIEG_BRIBE:
                    return new self(imagecreatefrompng($assets . "/rewards/supplydrop_warmachine_bribe.png"), "product", $reward->product->label ?? $reward->product->name);
                    default:
                    return new self(null, "product", $reward->product->label ?? $reward->product->name ?? "Unknown product");
                }
            }
             // Update new items here
            default:
                return null;
        }
    }
}

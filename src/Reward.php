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

    public static function parse(object $reward): Reward {
    global $supply_drop_image, $rare_supply_drop_image, $credits_image, $social_image, $credits_image, $xp_image;

    switch ($reward->type) {
        case "grant_currency": {
            switch ($reward->currency->id) {
                case CURRENCY_CREDITS:
                return new self($credits_image, "currency" , $reward->currency->amount . " " . $reward->currency->label);
                case CURRENCY_SOCIAL:
                return new self($social_image, "currency", $reward->currency->amount . " " . $reward->currency->label);
                default:
                return new self(null, "currency", ($reward->currency->label && $reward->currency->amount)  ? $reward->currency->amount . " " . $reward->currency->label : "Unknown currency");
            }
        }
        case "grant_product": {
            switch ($reward->product->id) {
                case PRODUCT_XP:
                return new self($xp_image, "product", $reward->product->label ?? $reward->product->name);
                case PRODUCT_SD:
                return new self($supply_drop_image, "product", $reward->product->label ?? $reward->product->name);
                case PRODUCT_RARE_SD:
                return new self($rare_supply_drop_image, "product", $reward->product->label ?? $reward->product->name);
                default:
                return new self(null, "product", $reward->product->label ?? $reward->product->name ?? "Unkown product");
            }
        }
        // Update new items here
        default:
        return null;
    }
}
}

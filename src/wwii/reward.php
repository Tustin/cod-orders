<?php
// Currencies
const CURRENCY_XP = '1';
const CURRENCY_CREDITS = '6';
const CURRENCY_SOCIAL = '7';

// Supply drops
    // Multiplayer
const PRODUCT_SD = '1';
const PRODUCT_RARE_SD = '2';
const PRODUCT_BLITZKRIEG_BRIBE_SD = '95';
const PRODUCT_SUMMER_BRIBE_SD = '127';
const PRODUCT_SUMMER_SOLSTICE_SD = '2147483961';

    // Zombies
const PRODUCT_ZOMBIE_SD = '5';
const PRODUCT_ZOMBIE_RARE_SD = '6';
const PRODUCT_ZOMBIES_CONSUMABLE_SD = '122';




// Misc
const PRODUCT_XP = '2147483674';
const PRODUCT_SD_AND_SOCIAL = '2147483851';

class ProductType {
    public const SD = 1;
    public const WEAPON = 2;
    public const CURRENCY = 4;
    public const XP = 8;
    public const MISC = 16;
    public const UNK = 32;
}

class Reward {

    public $image;
    public $type;
    public $label;

    public function __construct($image, int $type, string $label) {
        $this->image = $image;
        $this->type = $type;
        $this->label = $label;
    }

    public static function parse(object $reward): ?self {
        switch ($reward->type) {
            case 'grant_currency': {
                switch ($reward->currency->id) {
                    case CURRENCY_CREDITS:
                    return new self(imagecreatefrompng('rewards/credits.png'), ProductType::CURRENCY, $reward->currency->amount . ' ' . $reward->currency->label);
                    case CURRENCY_SOCIAL:
                    return new self(imagecreatefrompng('rewards/social.png'), ProductType::CURRENCY, $reward->currency->amount . ' ' . $reward->currency->label);
                    default:
                    return new self(null, ProductType::CURRENCY, ($reward->currency->label && $reward->currency->amount)  ? $reward->currency->amount . ' ' . $reward->currency->label : 'Unknown currency');
                }
            }
            case 'grant_product': {
                switch ($reward->product->id) {
                    case PRODUCT_XP:
                    return new self(imagecreatefrompng('rewards/xp.png'), ProductType::XP, $reward->product->label ?? $reward->product->name);
                    case PRODUCT_SD:
                    return new self(imagecreatefrompng('rewards/supplydrop_common.png'), ProductType::SD, $reward->product->label ?? $reward->product->name);
                    case PRODUCT_RARE_SD:
                    return new self(imagecreatefrompng('rewards/supplydrop_advanced.png'), ProductType::SD, $reward->product->label ?? $reward->product->name);
                    case PRODUCT_BLITZKRIEG_BRIBE_SD:
                    return new self(imagecreatefrompng('rewards/supplydrop_warmachine_bribe.png'), ProductType::SD, $reward->product->label ?? $reward->product->name);
                    case PRODUCT_SUMMER_BRIBE_SD:
                    return new self(imagecreatefrompng('rewards/supplydrop_common.png'), ProductType::SD, $reward->product->label ?? $reward->product->name);
                    case PRODUCT_SUMMER_SOLSTICE_SD:
                    return new self(imagecreatefrompng('rewards/supplydrop_common.png'), ProductType::SD, $reward->product->label ?? $reward->product->name);
                    case PRODUCT_SD_AND_SOCIAL:
                    return new self(imagecreatefrompng('rewards/supplydrop_common.png'), ProductType::MISC, $reward->product->label ?? $reward->product->name);
                   
                    case PRODUCT_ZOMBIE_SD:
                    return new self(imagecreatefrompng('rewards/supplydrop_zombie.png'), ProductType::SD, $reward->product->label ?? $reward->product->name);
                    case PRODUCT_ZOMBIE_RARE_SD:
                    return new self(imagecreatefrompng('rewards/supplydrop_zombie_advanced.png'), ProductType::SD, $reward->product->label ?? $reward->product->name);
                    case PRODUCT_ZOMBIES_CONSUMABLE_SD:
                    return new self(imagecreatefrompng('rewards/supplydrop_zombie.png'), ProductType::SD, $reward->product->label ?? $reward->product->name);
                    default:
                    return new self(null, ProductType::UNK, $reward->product->label ?? $reward->product->name ?? 'Unknown product');
                }
            }
             // Update new items here
            default:
                return null;
        }
    }
}

<?php
class Order {
    /*
    * Constants
    */
    const ORDER_DAILY = 1;
    const ORDER_WEEKLY = 2;
    const ORDER_SPECIAL = 3; // Just a guess, there's no special order as I made this
    const CONTRACT = 4;
    // 5 = nothing
    // 6 = something that can have 4 orders active at once
    // 7 = something that can only have one active
    // 8 = something that can have 3 orders active at once
    const ORDER_ZOMBIES_WEEKLY = 9;
    //10 = something that can only have one active. could also be ORDER_ZOMBIES_SPECIAL instead of 12?
    const ORDER_ZOMBIES_DAILY = 11;
    const ORDER_ZOMBIES_SPECIAL = 12; // Also a guess
    const ORDER_COMMUNITY = 13;

    const WEAPON_VARIANT_STANDARD = 0;
    const WEAPON_VARIANT_RARE = 1;
    const WEAPON_VARIANT_HEROIC = 2;

    private $template;
    private $order;

    private $black;
    private $white;


    public function __construct($template, object $order) {
        $this->template = $template;
        $this->order = $order;
        $this->black = imagecolorallocate($template, 0, 0, 0);
        $this->white = imagecolorallocate($template, 255, 255, 255);
    }

    public function title($font, int $textSize, int $x, int $y) {
        $order_name = $this->order->label ?? $this->order->name ?? "Unknown order name";
        $resized = $this->resizer($textSize, 24, strlen($order_name));
        imagettftext($this->template, $resized, 0, $x, $y, $this->white, $font, $order_name);
    }

    public function reward(Reward $reward, $font, int $textSize, $x, $y) {
        global $weapons;
        $reward_item = $reward->label;
        $has_image = false;
        $hard = false;
        if ($reward->image != null) {
            // Create a new small and transparent image of the reward item
            $new_w = 15;
            $new_h = 15;
            $new = imagecreatetruecolor($new_w, $new_h);
            imagecolortransparent($new, $this->black);
            imagecopyresampled($new, $reward->image, 0, 0, 0, 0, $new_w, $new_h, imagesx($reward->image), imagesy($reward->image));

            imagecopy($this->template, $new, $x, $y, 0, 0, imagesx($new), imagesy($new));
            $has_image = true;
        } else if ($reward->type == "product") {
                // daily_ch_winchesterloot0_2
                $parts = explode("_", $this->order->name);
                $weapon_name_check = substr($parts[2], 0);
                $weapon_variant_type = $parts[3];
            if (!$this->order->successRewards[0]->product->name) {
                $weapon = Weapon::hard($weapon_name_check);
                $hard = true;
            } else {
                $weapon = Weapon::easy($this->order->successRewards[0]->product->name);
            }

            if ($weapon && $weapon->localized && $weapon->image) {
                $new_w = 45;
                $new_h = 45;

                $new = imagecreatetruecolor($new_w, $new_h);
                imagecolortransparent($new, $this->black);
                imagecopyresampled($new, $weapon->image, 0, 0, 0, 0, $new_w, $new_h, imagesx($weapon->image), imagesy($weapon->image));

                imagecopy($this->template, $new, $x + 240, $y - 5, 0, 0, imagesx($new), imagesy($new));

                $reward_item = ($this->order->successRewards[0]->product->name) ? $this->order->successRewards[0]->product->label : $weapons[$weapon->localized];
                $is_heroic = (strpos($reward_item, "II") !== false);
                
                if (!$is_heroic && intval($weapon_variant_type) == 2) {
                    $reward_item = $reward_item . " II";
                }
            }
        }

        $reward_text_pos = $has_image ? $x + 18 : $x;
        imagettftext($this->template, $textSize, 0, $reward_text_pos, $y + 12, $this->white, $font, $reward_item);
    }

    public function criteria($font, int $textSize, int $x, int $y, int $wrap = 40) {
        $order_critera = $this->order->descriptionLabel ?? "No criteria given.";
        $order_critera = wordwrap($order_critera, $wrap);
        imagettftext($this->template, $textSize, 0, $x, $y, $this->white, $font, $order_critera);
    }

    private function resizer($baseFontSize, $textLengthMax, $textLength): int {
        $div = $textLength / $textLengthMax;
        return ($div < 1) ? $baseFontSize : $baseFontSize - $div;
    }
}
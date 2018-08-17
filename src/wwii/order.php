<?php
class Order {
    /*
    * Constants
    */
    const ORDER_DAILY = 1;
    const ORDER_WEEKLY = 2;
    const ORDER_SPECIAL = 3;
    const CONTRACT = 4;
    // 5 = nothing
    // 6 = something that can have 4 orders active at once
    // 7 = something that can only have one active
    const ORDER_ZOMBIES_DAILY = 8;
    const ORDER_ZOMBIES_WEEKLY = 9;
    const ORDER_ZOMBIES_SPECIAL = 10;
    const CONTRACT_ZOMBIES = 11;
    // 12 = something that can only have one active
    const ORDER_COMMUNITY = 13;

    const WEAPON_VARIANT_STANDARD = 0;
    const WEAPON_VARIANT_RARE = 1;
    const WEAPON_VARIANT_HEROIC = 2;

    private $template;
    private $order;

    private $black;
    private $white;
    private $orange;
    private $red;

    const REWARD_IMAGE_W = 512 / 7;
    const REWARD_IMAGE_H = 512 / 7;

    const WEAPON_IMAGE_W = 512 / 2;
    const WEAPON_IMAGE_H = 512 / 2;

    const WEAPON_X_DIFFERENCE = 750;
    const WEAPON_Y_DIFFERENCE = 155;

    const REWARD_TEXT_POSITION_X_DIFFERENCE = 85;
    const REWARD_TEXT_POSITION_Y_DIFFERENCE = 0;

    public function __construct($template, object $order) {
        $this->template = $template;
        $this->order = $order;
        $this->black = imagecolorallocate($template, 0, 0, 0);
        $this->white = imagecolorallocate($template, 255, 255, 255);
        $this->orange = imagecolorallocate($template, 255, 165, 0);
        $this->red = imagecolorallocate($template, 255, 0, 0);
    }

    public function title($font, int $textSize, int $x, int $y) : void {
        $order_name = $this->order->label ?? $this->order->name ?? "Unknown order name";
        $resized = $this->resizer($textSize, 24, strlen($order_name));
        imagettftext($this->template, $resized, 0, $x, $y, $this->white, $font, $order_name);
    }

    public function reward(Reward $reward, $font, int $textSize, $x, $y, $isSpecial = false) : void {
        global $weapons;
        $reward_item = $reward->label;
        $has_image = false;
        if ($reward->image != null) {
            $new_w = self::REWARD_IMAGE_W;
            $new_h = self::REWARD_IMAGE_H;
            $new = imagecreatetruecolor($new_w, $new_h);
            imagecolortransparent($new, $this->black);
            imagecopyresampled($new, $reward->image, 0, 0, 0, 0, $new_w, $new_h, imagesx($reward->image), imagesy($reward->image));

            imagecopy($this->template, $new, $x, $y - 50, 0, 0, imagesx($new), imagesy($new));
            $has_image = true;
        } else if ($reward->type & ProductType::UNK) {
            // Example: daily_ch_winchesterloot0_2
            $parts = explode("_", $this->order->name);
            $weapon_name_check = substr($parts[2], 0);
            $weapon_variant_type = $parts[3];

            if (!$isSpecial) {
                $weapon = Weapon::easy($this->order->successRewards[0]->product->name);
            }

            if (isset($weapon) && isset($weapon->localized) && isset($weapon->image)) {
                $new_w = self::WEAPON_IMAGE_W;
                $new_h = self::WEAPON_IMAGE_H;

                $new = imagecreatetruecolor($new_w, $new_h);
                imagecolortransparent($new, $this->black);
                imagecopyresampled($new, $weapon->image, 0, 0, 0, 0, $new_w, $new_h, imagesx($weapon->image), imagesy($weapon->image));

                imagecopy($this->template, $new, $x + self::WEAPON_X_DIFFERENCE, $y - self::WEAPON_Y_DIFFERENCE, 0, 0, imagesx($new), imagesy($new));

                $reward_item = ($this->order->successRewards[0]->product->name) ? $this->order->successRewards[0]->product->label : $weapons[$weapon->localized];
                $is_heroic = (strpos($reward_item, "II") !== false);
                
                if (!$is_heroic && intval($weapon_variant_type) == 2) {
                    $reward_item = $reward_item . " II";
                }

                $weapon_type = "";
                $weapon_type_color = $this->white;
                $pieces = explode('_', $this->order->name);
                switch (intval(end($pieces))) {
                    case 1:
                    $weapon_type = "Epic";
                    $weapon_type_color = $this->orange;
                    break;
                    case 2:
                    $weapon_type = "Heroic";
                    $weapon_type_color = $this->red;
                    break;
                }

                $pieces = explode('_', $weapon->localized);
                if (isset($pieces[0]) && !empty($pieces[0])) {
                    if (isset($weapons[$pieces[0]])) {
                        $variant_text = sprintf('%s %s', $weapon_type, $weapons[$pieces[0]]);
                        $resized = $this->resizer($textSize, 24, strlen($variant_text));
                        imagettftext($this->template, $resized, 0, $x, $y + ORDER_REWARD_POSITION_Y_DIFFERENCE, $weapon_type_color, $font, $variant_text);
                    }
                } 
            }
        }

        $reward_text_pos = $has_image ? $x + self::REWARD_TEXT_POSITION_X_DIFFERENCE : $x;
        imagettftext($this->template, $textSize, 0, $reward_text_pos, $y, $this->white, $font, $reward_item);
    }

    public function criteria($font, int $textSize, int $x, int $y, int $wrap = 40) {
        $order_critera = $this->order->descriptionLabel ?? "No criteria given.";
        $order_critera = wordwrap($order_critera, $wrap);
        imagettftext($this->template, $textSize, 0, $x, $y, $this->white, $font, $order_critera);
        return $order_critera;
    }

    private function resizer($baseFontSize, $textLengthMax, $textLength): int {
        $div = $textLength / $textLengthMax;
        return ($div < 1) ? $baseFontSize : $baseFontSize - $div;
    }
}
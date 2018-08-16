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

    const REWARD_IMAGE_W = 256;
    const REWARD_IMAGE_H = 256;

    const WEAPON_IMAGE_W = 512;
    const WEAPON_IMAGE_H = 512;

    const WEAPON_X_DIFFERENCE = 240;
    const WEAPON_Y_DIFFERENCE = 5;

    const REWARD_TEXT_POSITION_X_DIFFERENCE = 18;
    const REWARD_TEXT_POSITION_Y_DIFFERENCE = 12;

    public function __construct($template, object $order) {
        $this->template = $template;
        $this->order = $order;
        $this->black = imagecolorallocate($template, 0, 0, 0);
        $this->white = imagecolorallocate($template, 255, 255, 255);
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
            // Create a new small and transparent image of the reward item
            $new_w = self::REWARD_IMAGE_W;
            $new_h = self::REWARD_IMAGE_H;
            $new = imagecreatetruecolor($new_w, $new_h);
            imagecolortransparent($new, $this->black);
            imagecopyresampled($new, $reward->image, 0, 0, 0, 0, $new_w, $new_h, imagesx($reward->image), imagesy($reward->image));

            imagecopy($this->template, $new, $x, $y, 0, 0, imagesx($new), imagesy($new));
            $has_image = true;
        } else if ($reward->type & ProductType::UNK) {
                // Example: daily_ch_winchesterloot0_2
                $parts = explode("_", $this->order->name);
                $weapon_name_check = substr($parts[2], 0);
                $weapon_variant_type = $parts[3];

            if (!$this->order->successRewards[0]->product->name && !$isSpecial) {
                $weapon = Weapon::hard($weapon_name_check);
            } else if (!$isSpecial){
                $weapon = Weapon::easy($this->order->successRewards[0]->product->name);
            }

            if (isset($weapon) && $weapon->localized && $weapon->image) {
                $new_w = WEAPON_IMAGE_W;
                $new_h = WEAPON_IMAGE_H;

                $new = imagecreatetruecolor($new_w, $new_h);
                imagecolortransparent($new, $this->black);
                imagecopyresampled($new, $weapon->image, 0, 0, 0, 0, $new_w, $new_h, imagesx($weapon->image), imagesy($weapon->image));

                imagecopy($this->template, $new, $x + self::WEAPON_X_DIFFERENCE, $y - self::WEAPON_Y_DIFFERENCE, 0, 0, imagesx($new), imagesy($new));

                $reward_item = ($this->order->successRewards[0]->product->name) ? $this->order->successRewards[0]->product->label : $weapons[$weapon->localized];
                $is_heroic = (strpos($reward_item, "II") !== false);
                
                if (!$is_heroic && intval($weapon_variant_type) == 2) {
                    $reward_item = $reward_item . " II";
                }
            }
        }

        $reward_text_pos = $has_image ? $x + self::REWARD_TEXT_POSITION_X_DIFFERENCE : $x;
        imagettftext($this->template, $textSize, 0, $reward_text_pos, $y + self::REWARD_TEXT_POSITION_Y_DIFFERENCE, $this->white, $font, $reward_item);
    }

    public function criteria($font, int $textSize, int $x, int $y, int $wrap = 40) {
        $order_critera = $this->order->descriptionLabel ?? "No criteria given.";
        $order_critera = wordwrap($order_critera, $wrap);
        //$this->colorizer($x, $y, $order_critera, $textSize, $font);
        imagettftext($this->template, $textSize, 0, $x, $y, $this->white, $font, $order_critera);
        return $order_critera;
    }

    private function resizer($baseFontSize, $textLengthMax, $textLength): int {
        $div = $textLength / $textLengthMax;
        return ($div < 1) ? $baseFontSize : $baseFontSize - $div;
    }

    private function colorizer($x, $y, $text, $textSize, $font) : void {
        $colors = [
            "^1" => [255, 0, 0],
            "^3" => [255, 255, 0],
            "^7" => [255, 255, 255],
        ];


        $font_color = $this->white;
        $base_x = $x;
        for ($i = 0; $i < strlen($text); $i++) { 

            // Custom wordwrap
            if ($i != 0 && $i % 40 == 0) {
                $x = $base_x;
                $y += 20;
                imagettftext($this->template, $textSize, 0, $x, $y, $font_color, $font, "\n");
            }
            if ($text[$i] != '^') goto write;

            // Lol
            $color_code = $colors[$text[$i] . $text[$i + 1]];
            if (!$color_code) goto write;
            $i += 2;

            $font_color = imagecolorallocate($this->template, ...$color_code);

            write:
            $bbox = imagettftext($this->template, $textSize, 0, intval($x), $y, $font_color, $font, $text[$i]);
            $x = $bbox[2];

        }
    }
}
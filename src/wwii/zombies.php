<?php

error_reporting(E_ERROR | E_PARSE);

require 'shared.php';
require 'order.php';
require 'reward.php';
require 'weapon.php';

$orders = get_orders();

file_put_contents('orders/wwii/zombies/' . date("mdY") . '.json', $orders);

$image = imagecreatefrompng('templates/orders_zombies.png');

$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);

$parsed_orders = [];
foreach ($orders->data->Achievements as $achievement) {
    $parsed_orders[$achievement->kind][] = $achievement;
}


$index = 0;
$row = 0;

$order_title_position_x = 77;
$order_title_position_y = 27;

$order_critera_position_x = 12;
$order_critera_position_y = 100;

// Daily
foreach ($parsed_orders[Order::ORDER_ZOMBIES_DAILY] as $order) { 
    // Something is wrong if this is being looped over more than 6 times. Leave.
    if ($row >= 2) break;
    $order_name = $order->label ?? $order->name;
    $reward = Reward::parse($order->successRewards[0]);
    $panel = new Order($image, $order);
    $panel->title($order_font_file, ORDER_TITLE_FONT_SIZE, $order_title_position_x + ($index * 367), $order_title_position_y + ($row * 156));
    $panel->reward($reward, $order_critera_font_file, ORDER_REWARD_FONT_SIZE, $order_title_position_x + ($index * 367), ($order_title_position_y + 10) + ($row * 156));
    $panel->criteria($order_critera_font_file, ORDER_CRITERIA_FONT_SIZE, $order_critera_position_x + ($index * 367), $order_critera_position_y + ($row * 156));

    if (++$index % 3 == 0) {
        $row++;
        $index = 0;
    }
}

$index = 0;

$order_title_position_x = 77;
$order_title_position_y = 334;

$order_critera_position_x = 12;
$order_critera_position_y = 416;

foreach ($parsed_orders[Order::ORDER_ZOMBIES_WEEKLY] as $order) {
    // Something is wrong if this is being looped over more than 3 times. Leave.
    if ($index >= 3) break;
    $order_name = $order->label ?? $order->name;
    $reward = Reward::parse($order->successRewards[0]);

    $panel = new Order($image, $order);
    $panel->title($order_font_file, ORDER_TITLE_FONT_SIZE, $order_title_position_x + ($index * 367), $order_title_position_y);
    $panel->reward($reward, $order_critera_font_file, ORDER_REWARD_FONT_SIZE, $order_title_position_x + ($index * 367), $order_title_position_y + 10);
    $panel->criteria($order_critera_font_file, ORDER_CRITERIA_FONT_SIZE, $order_critera_position_x + ($index * 367), $order_critera_position_y);

    $index++;
}

$order_title_position_x = 338;
$order_title_position_y = 491;

$order_critera_position_x = 265;
$order_critera_position_y = 565;

if (array_key_exists(Order::ORDER_ZOMBIES_SPECIAL, $parsed_orders)) {
    $order = $parsed_orders[Order::ORDER_ZOMBIES_SPECIAL][0];
    $order_name = $order->label ?? $order->name;
    $reward = Reward::parse($order->successRewards[0]);

    $panel = new Order($image, $order);
    $panel->title($order_font_file, ORDER_TITLE_FONT_SIZE, $order_title_position_x, $order_title_position_y);
    $panel->reward($reward, $order_critera_font_file, ORDER_REWARD_FONT_SIZE, $order_title_position_x, $order_title_position_y + 10, true);
    $panel->criteria($order_critera_font_file, ORDER_CRITERIA_FONT_SIZE, $order_critera_position_x , $order_critera_position_y);
} else {
    imagettftext($image, ORDER_TITLE_FONT_SIZE, 0, $order_title_position_x, $order_title_position_y, $white, $order_font_file, "No Special Order Available");
}

$order_image_name = 'orders/zombies/' . date("mdY") . '.png';
imagepng($image, $order_image_name);

<?php

error_reporting(E_ERROR | E_PARSE);

require 'shared.php';
require 'order.php';
require 'reward.php';
require 'weapon.php';

setup();

$orders = get_orders();
$weapons = get_weapons_csv(); 

file_put_contents('orders/wwii/multiplayer/' . date("mdY") . '.json', $orders);

$image = imagecreatefrompng('templates/orders.png');

$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);


$parsed_orders = [];
$orders_text = [];

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
foreach ($parsed_orders[Order::ORDER_DAILY] as $order) { 
    // Something is wrong if this is being looped over more than 6 times. Leave.
    if ($row >= 2) break;
    $order_name = $order->label ?? $order->name;
    $reward = Reward::parse($order->successRewards[0]);
    $panel = new Order($image, $order);
    $panel->title($order_font_file, ORDER_TITLE_FONT_SIZE, $order_title_position_x + ($index * 367), $order_title_position_y + ($row * 156));
    $panel->reward($reward, $order_critera_font_file, ORDER_REWARD_FONT_SIZE, $order_title_position_x + ($index * 367), ($order_title_position_y + 10) + ($row * 156));
    $criteria = $panel->criteria($order_critera_font_file, ORDER_CRITERIA_FONT_SIZE, $order_critera_position_x + ($index * 367), $order_critera_position_y + ($row * 156));

    if (++$index % 3 == 0) {
        $row++;
        $index = 0;
    }

    $orders_text[Order::ORDER_DAILY][] = [
        "title" => $order_name,
        "criteria" => $criteria,
        "reward" => $reward->label
    ];
}

$index = 0;

$order_title_position_x = 77;
$order_title_position_y = 334;

$order_critera_position_x = 12;
$order_critera_position_y = 416;

foreach ($parsed_orders[Order::ORDER_WEEKLY] as $order) {
    // Something is wrong if this is being looped over more than 3 times. Leave.
    if ($index >= 3) break;
    $order_name = $order->label ?? $order->name;
    $reward = Reward::parse($order->successRewards[0]);

    $panel = new Order($image, $order);
    $panel->title($order_font_file, ORDER_TITLE_FONT_SIZE, $order_title_position_x + ($index * 367), $order_title_position_y);
    $panel->reward($reward, $order_critera_font_file, ORDER_REWARD_FONT_SIZE, $order_title_position_x + ($index * 367), $order_title_position_y + 10);
    $criteria = $panel->criteria($order_critera_font_file, ORDER_CRITERIA_FONT_SIZE, $order_critera_position_x + ($index * 367), $order_critera_position_y);

    $index++;

    $orders_text[Order::ORDER_WEEKLY][] = [
        "title" => $order_name,
        "criteria" => $criteria,
        "reward" => $reward->label
    ];
}

$order_title_position_x = 338;
$order_title_position_y = 491;

$order_critera_position_x = 265;
$order_critera_position_y = 565;

if (array_key_exists(Order::ORDER_SPECIAL, $parsed_orders)) {
    $order = $parsed_orders[Order::ORDER_SPECIAL][0];
    $order_name = $order->label ?? $order->name;
    $reward = Reward::parse($order->successRewards[0]);

    $panel = new Order($image, $order);
    $panel->title($order_font_file, ORDER_TITLE_FONT_SIZE, $order_title_position_x, $order_title_position_y);
    $panel->reward($reward, $order_critera_font_file, ORDER_REWARD_FONT_SIZE, $order_title_position_x, $order_title_position_y + 10, true);
    $criteria = $panel->criteria($order_critera_font_file, ORDER_CRITERIA_FONT_SIZE, $order_critera_position_x , $order_critera_position_y);

    $orders_text[Order::ORDER_SPECIAL][] = [
        "title" => $order_name,
        "criteria" => $criteria,
        "reward" => $reward->label
    ];
} else {
    imagettftext($image, ORDER_TITLE_FONT_SIZE, 0, $order_title_position_x, $order_title_position_y, $white, $order_font_file, "No Special Order Available");
}

$order_image_name = 'orders/multiplayer/' . date("mdY") . '.png';
$orders_text["image"] = $order_image_name;

imagepng($image, $order_image_name);

echo json_encode($orders_text);
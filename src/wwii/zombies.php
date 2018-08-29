<?php

error_reporting(E_ERROR | E_PARSE);

require 'shared.php';
require 'order.php';
require 'reward.php';
require 'weapon.php';

setup();

$orders = get_orders();

file_put_contents('orders/wwii/zombies/' . date("mdY") . '.json', $orders);

$image = imagecreatefrompng('templates/orders_zombies.png');

$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);

$parsed_orders = [];
$orders_text = [];

foreach ($orders->data->Achievements as $achievement) {
    $parsed_orders[$achievement->kind][] = $achievement;
}


$index = 0;
$row = 0;

// Daily
foreach ($parsed_orders[Order::ORDER_ZOMBIES_DAILY] as $order) { 
    // Something is wrong if this is being looped over more than 6 times. Leave.
    if ($row >= 2) break;
    $order_name = $order->label ?? $order->name;
    $reward = Reward::parse($order->successRewards[0]);
    $panel = new Order($image, $order);
    $panel->title($order_font_file, ORDER_TITLE_FONT_SIZE, DAILY_ORDER_TITLE_POSITION_X + ($index * ORDER_X_DIFFERENCE), DAILY_ORDER_TITLE_POSITION_Y + ($row * ORDER_Y_DIFFERENCE));
    $panel->reward($reward, $order_critera_font_file, ORDER_REWARD_FONT_SIZE, DAILY_ORDER_TITLE_POSITION_X + ($index * ORDER_X_DIFFERENCE), (DAILY_ORDER_TITLE_POSITION_Y + ORDER_REWARD_POSITION_Y_DIFFERENCE) + ($row * ORDER_Y_DIFFERENCE));
    $criteria = $panel->criteria($order_critera_font_file, ORDER_CRITERIA_FONT_SIZE, DAILY_ORDER_CRITERIA_POSITION_X + ($index * ORDER_X_DIFFERENCE), DAILY_ORDER_CRITERIA_POSITION_Y + ($row * ORDER_Y_DIFFERENCE));

    if (++$index % 3 == 0) {
        $row++;
        $index = 0;
    }

    $orders_text[Order::ORDER_ZOMBIES_DAILY][] = [
        "title" => $order_name,
        "criteria" => $criteria,
        "reward" => $reward->label
    ];
}

$index = 0;

foreach ($parsed_orders[Order::ORDER_ZOMBIES_WEEKLY] as $order) {
    // Something is wrong if this is being looped over more than 3 times. Leave.
    if ($index >= 3) break;
    $order_name = $order->label ?? $order->name;
    $reward = Reward::parse($order->successRewards[0]);

    $panel = new Order($image, $order);
    $panel->title($order_font_file, ORDER_TITLE_FONT_SIZE, WEEKLY_ORDER_TITLE_POSITION_X + ($index * ORDER_X_DIFFERENCE), WEEKLY_ORDER_TITLE_POSITION_Y);
    $panel->reward($reward, $order_critera_font_file, ORDER_REWARD_FONT_SIZE, WEEKLY_ORDER_TITLE_POSITION_X + ($index * ORDER_X_DIFFERENCE), WEEKLY_ORDER_TITLE_POSITION_Y + ORDER_REWARD_POSITION_Y_DIFFERENCE);
    $criteria = $panel->criteria($order_critera_font_file, ORDER_CRITERIA_FONT_SIZE, WEEKLY_ORDER_CRITERIA_POSITION_X + ($index * ORDER_X_DIFFERENCE), WEEKLY_ORDER_CRITERIA_POSITION_Y);

    $index++;

    $orders_text[Order::ORDER_ZOMBIES_WEEKLY][] = [
        "title" => $order_name,
        "criteria" => $criteria,
        "reward" => $reward->label
    ];
}

if (array_key_exists(Order::ORDER_ZOMBIES_SPECIAL, $parsed_orders)) {
    $order = $parsed_orders[Order::ORDER_ZOMBIES_SPECIAL][0];
    $order_name = $order->label ?? $order->name;
    $reward = Reward::parse($order->successRewards[0]);

    $panel = new Order($image, $order);
    $panel->title($order_font_file, ORDER_TITLE_FONT_SIZE, SPECIAL_ORDER_TITLE_POSITION_X, SPECIAL_ORDER_TITLE_POSITION_Y);
    $panel->reward($reward, $order_critera_font_file, ORDER_REWARD_FONT_SIZE, SPECIAL_ORDER_TITLE_POSITION_X, SPECIAL_ORDER_TITLE_POSITION_Y + ORDER_REWARD_POSITION_Y_DIFFERENCE, true);
    $criteria = $panel->criteria($order_critera_font_file, ORDER_CRITERIA_FONT_SIZE, SPECIAL_ORDER_CRITERIA_POSITION_X, SPECIAL_ORDER_CRITERIA_POSITION_Y, 70);

    $orders_text[Order::ORDER_ZOMBIES_WEEKLY][] = [
        "title" => $order_name,
        "criteria" => $criteria,
        "reward" => $reward->label
    ];
} else {
    imagettftext($image, ORDER_TITLE_FONT_SIZE, 0, SPECIAL_ORDER_TITLE_POSITION_X, SPECIAL_ORDER_TITLE_POSITION_Y, $white, $order_font_file, "No Special Order Available");
}

$order_image_name = 'orders/zombies/' . date("mdY") . '.png';
$orders_text["image"] = $order_image_name;

imagepng($image, $order_image_name);

echo json_encode($orders_text);
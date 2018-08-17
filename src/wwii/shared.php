<?php

$order_critera_font_file = dirname(__FILE__, 3) . '/assets/fonts/sans.ttf';
$order_font_file = dirname(__FILE__, 3) . '/assets/fonts/arvo.ttf';

const ORDER_TITLE_FONT_SIZE = 48;
const ORDER_REWARD_FONT_SIZE = 40;
const ORDER_CRITERIA_FONT_SIZE = 40;


const ORDER_X_DIFFERENCE = 1275;
const ORDER_Y_DIFFERENCE = 530;

const ORDER_REWARD_POSITION_Y_DIFFERENCE = 75;

const DAILY_ORDER_TITLE_POSITION_X = 265;
const DAILY_ORDER_TITLE_POSITION_Y = 88;

const DAILY_ORDER_CRITERIA_POSITION_X = 50;
const DAILY_ORDER_CRITERIA_POSITION_Y = 345;

const WEEKLY_ORDER_TITLE_POSITION_X = DAILY_ORDER_TITLE_POSITION_X;
const WEEKLY_ORDER_TITLE_POSITION_Y = 1155;

const WEEKLY_ORDER_CRITERIA_POSITION_X = DAILY_ORDER_CRITERIA_POSITION_X;
const WEEKLY_ORDER_CRITERIA_POSITION_Y = 1425;

const SPECIAL_ORDER_TITLE_POSITION_X = 1264;
const SPECIAL_ORDER_TITLE_POSITION_Y = 1690;

const SPECIAL_ORDER_CRITERIA_POSITION_X = 1021;
const SPECIAL_ORDER_CRITERIA_POSITION_Y = 1940;

$order_title_position_x = 265;
$order_title_position_y = 88;


function setup() : void {
    if (!file_exists('orders/')) mkdir('orders/');
    if (!file_exists('orders/multiplayer/')) mkdir('orders/multiplayer/');
    if (!file_exists('orders/zombies/')) mkdir('orders/zombies/');
}

function get_orders() : object {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://my.callofduty.com/api/papi-client/crm/cod/v2/title/wwii/platform/psn/achievements/scheduled/gamer/tustin25/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $orders = curl_exec($ch);
    curl_close($ch);

    $orders = json_decode($orders);

    if (!$orders->data || $orders->status != "success" || !count($orders->data->Achievements)) throw new Exception("bad order data");

    return $orders;
}

function get_weapons_csv() : array {
    $weapon_csv = file(dirname(__FILE__, 3) . '/src/wwii/weaponsTable.csv');
    $weapons = [];
    $weapon_icon_files = array_diff(scandir(dirname(__FILE__, 3) . '/src/wwii/weapons'), ['..', '.']);

    if (!$weapon_csv) throw new Exception("weapon_csv is null");

    foreach ($weapon_csv as $line) {
        $parts = str_getcsv($line);
        if (count($parts) !== 2 || empty($parts[1])) continue;
        $fixed_localized = substr($parts[0], 0, strpos($parts[0], "_mp"));
        $weapons[$fixed_localized] = $parts[1];
    }

    return $weapons;
}
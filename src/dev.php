<?php
$root = dirname(__FILE__, 2);
require  $root . '/vendor/autoload.php';
require 'env.php';

$assets = $root . '/assets';

use Abraham\TwitterOAuth\TwitterOAuth;

date_default_timezone_set("America/Los_Angeles");

$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_SECRET);

$weapon_csv = file($assets . "/weaponTable.csv");
$weapons = [];
$weapon_icon_files = array_diff(scandir($assets . '/weapons'), ['..', '.']);

if (!$weapon_csv) throw new exception("weapon_csv is null");

foreach ($weapon_csv as $line) {
    $parts = str_getcsv($line);
    if (count($parts) !== 2 || empty($parts[1])) continue;
    $fixed_localized = substr($parts[0], 0, strpos($parts[0], "_mp"));
    $weapons[$fixed_localized] = $parts[1];
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://my.callofduty.com/api/papi-client/crm/cod/v2/title/wwii/platform/psn/achievements/scheduled/gamer/tustin25/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$orders = curl_exec($ch);
curl_close($ch);

file_put_contents($root . '/orders/' . date("mdY") . '.json', $orders);

$orders = json_decode($orders);

if (!$orders->data || $orders->status != "success" || !count($orders->data->Achievements)) throw new exception("bad order data");

$image = imagecreatefrompng($assets . "/templates/orders.png");

$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);

$order_critera_font_file = $assets . '/fonts/sans.ttf';
$order_font_file = $assets . '/fonts/arvo.ttf';

$parsed_orders = [];
foreach ($orders->data->Achievements as $achievement) {
    $parsed_orders[$achievement->kind][] = $achievement;
}

const ORDER_TITLE_FONT_SIZE = 16;
const ORDER_REWARD_FONT_SIZE = 12;
const ORDER_CRITERIA_FONT_SIZE = 12;


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

foreach ($parsed_orders[Order::ORDER_WEEKLY] as $order) {
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

if (array_key_exists(Order::ORDER_SPECIAL, $parsed_orders)) {
    $order = $parsed_orders[Order::ORDER_SPECIAL][0];
    $order_name = $order->label ?? $order->name;
    $reward = Reward::parse($order->successRewards[0]);

    $panel = new Order($image, $order);
    $panel->title($order_font_file, ORDER_TITLE_FONT_SIZE, $order_title_position_x, $order_title_position_y);
    $panel->reward($reward, $order_critera_font_file, ORDER_REWARD_FONT_SIZE, $order_title_position_x, $order_title_position_y + 10);
    $panel->criteria($order_critera_font_file, ORDER_CRITERIA_FONT_SIZE, $order_critera_position_x , $order_critera_position_y);
} else {
    imagettftext($image, ORDER_TITLE_FONT_SIZE, 0, $order_title_position_x, $order_title_position_y, $white, $order_font_file, "No Special Order Available");
}
$order_image_name = $root . '/orders/' . date("mdY") . '.png';
imagepng($image, $order_image_name);

if (file_exists($order_image_name)) {
    die("stopped");
    $tweet_text = "WWII Orders for ". date("F j, Y");
    $media = $connection->upload('media/upload', ['media' => $order_image_name]);
    $parameters = [
        'status' => $tweet_text,
        'media_ids' => implode(',', [$media->media_id_string])
    ];
    $result = $connection->post('statuses/update', $parameters);
}
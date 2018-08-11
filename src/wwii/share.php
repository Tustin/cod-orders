<?php
date_default_timezone_set('America/Los_Angeles');

require dirname(__FILE__, 3) . '/vendor/autoload.php';
// Need to properly autoload these in the future.
require 'order.php';
require '../Reddit.php';

use Abraham\TwitterOAuth\TwitterOAuth;

$connection = new TwitterOAuth(
    getenv('ORDERS_TWITTER_CONSUMER_KEY'), 
    getenv('ORDERS_TWITTER_CONSUMER_SECRET'), 
    getenv('ORDERS_TWITTER_ACCESS_TOKEN'),
    getenv('ORDERS_TWITTER_ACCESS_SECRET')
);

$date = date('l, F jS, Y');

// Multiplayer
$data = json_decode(shell_exec('php multiplayer.php'));

if ($data === null && json_last_error() !== JSON_ERROR_NONE) die("bad json data");

// $tweet_text = '#CODWWII Orders for '. $date;
// $media = $connection->upload('media/upload', ['media' => $data->image]);
// $parameters = [
//     'status' => $tweet_text,
//     'media_ids' => implode(',', [$media->media_id_string])
// ];

// $result = $connection->post('statuses/update', $parameters);


$body = "Daily Orders:\n\n";

foreach ($data->{Order::ORDER_DAILY} as $daily) {
    $body .= sprintf("**%s** - *%s* - %s\n\n", $daily->title, $daily->criteria, $daily->reward);
}

$body .= "Weekly Orders:\n\n";

foreach ($data->{Order::ORDER_WEEKLY} as $weekly) {
    $body .= sprintf("**%s** - *%s* - %s\n\n", $weekly->title, $weekly->criteria, $weekly->reward);
}

if (isset($data->{Order::ORDER_SPECIAL})) {
    $body .= "Special Order:\n\n";
    $special = $data->{Order::ORDER_SPECIAL};
    $body .= sprintf("**%s** - *%s* - %s\n\n", $special->title, $special->criteria, $special->reward);
}

$reddit = new Reddit(
    getenv('ORDERS_REDDIT_CLIENT_ID'), 
    getenv('ORDERS_REDDIT_CLIENT_SECRET'), 
    getenv('ORDERS_REDDIT_USERNAME'), 
    getenv('ORDERS_REDDIT_PASSWORD')
);

$redditInfo = $reddit->postText('NotAWWIITest', sprintf('Orders for %s', $date), $body);

if (isset($redditInfo->name)) {
    $reddit->postComment($redditInfo->name, $body);
}

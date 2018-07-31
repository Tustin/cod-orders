<?php
date_default_timezone_set('America/Los_Angeles');

require dirname(__FILE__, 2) . '/vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

$connection = new TwitterOAuth(
    getenv('ORDERS_TWITTER_CONSUMER_KEY'), 
    getenv('ORDERS_TWITTER_CONSUMER_SECRET'), 
    getenv('ORDERS_TWITTER_ACCESS_TOKEN'),
    getenv('ORDERS_TWITTER_ACCESS_SECRET')
);

die();


$tweet_text = '#CODWWII Orders for '. date('l, F jS, Y');
$media = $connection->upload('media/upload', ['media' => $order_image_name]);
$parameters = [
    'status' => $tweet_text,
    'media_ids' => implode(',', [$media->media_id_string])
];
$result = $connection->post('statuses/update', $parameters);

$reddit = new Reddit(
    env('ORDERS_REDDIT_CLIENT_ID'), 
    env('ORDERS_REDDIT_CLIENT_SECRET'), 
    env('ORDERS_REDDIT_USERNAME'), 
    env('ORDERS_REDDIT_PASSWORD')
);

$reddit->postText('NotAWWIITest', 'Testing', 'This is a test.');
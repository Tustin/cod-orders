<?php

require 'wwii/order.php';

$orders = shell_exec("php wwii/multiplayer.php");

$data = json_decode($orders);

if (!$data) throw new Exception("Failed to parse orders");

$body = "Daily Orders:\n\n";

foreach ($data->Order::ORDER_DAILY as $daily) {
    $body += "**$daily->title** - $daily->criteria - $daily->reward\n\n";
}

echo $body;
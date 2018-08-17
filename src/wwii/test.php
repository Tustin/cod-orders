<?php

//require 'wwii/order.php';

$files = glob('C:\Users\Josh\Desktop\orders\*.json');

foreach ($files as $file) {
    echo shell_exec('php multiplayer.php ' . $file). "\n\n\n";
}
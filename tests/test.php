<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

use Utilities\Arr;

$arr = new Arr(["item2"=>"one"]);
$arr->insert(["two"]);
$arr->item3 = "three";

echo $arr."\n";

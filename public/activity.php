<?php

$filename = "../storage/activity.json";

if(isset($_GET['log'])) {
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json; charset=utf-8');

	if(file_exists($filename)) die(file_get_contents($filename));
	else die("{}");
}


if(file_exists($filename)) {
	$activity = json_decode(file_get_contents($filename));
}
else $activity = (object) array();


$address = $_SERVER['REMOTE_ADDR'];
$request = $_SERVER['REQUEST_URI'];
$today = date("Ymd");

if(!isset($activity->$address)){
	$activity->$address = (object) array();
}

if(!isset($activity->$address->$today)){
	$activity->$address->$today = (object) array();
}

if(!isset($activity->$address->$today->$request)){
	$activity->$address->$today->$request = 0;
}

$activity->$address->$today->$request += 1;

file_put_contents($filename, json_encode($activity));

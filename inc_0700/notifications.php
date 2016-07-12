<?php
include_once 'Validate.php';

date_default_timezone_set('America/Los_Angeles');
$hour = date('G');
$day = date('j');
$hoursToRun = [];

$run = false;
switch ($day){
	case 1:
		$hoursToRun = array (0,12);
		break;
	case 2:
		$hoursToRun = array(0,6,12,18);
		break;
	case 3:
		$hoursToRun = array (0,3,6,9,12,15,18,21);
		break;
}


if($day == 1 && in_array($hour, $hoursToRun)){
	$config = new Validate();
	$config->resetUsersStatus();
	$run = true;
} else if ($day == 2 && in_array($hour, $hoursToRun)){
	$run = true;
} else if ($day == 3 && in_array($hour, $hoursToRun)){
	$run = true;
} else if ($day == 4){
	$run = true;
} else {
	$run = false;
}

if($run == true){
	$config = new Validate();
	$config->sendNotifications();
	unset($config);
} else {
	header('Location: index.php');
}
header('Location: index.php');
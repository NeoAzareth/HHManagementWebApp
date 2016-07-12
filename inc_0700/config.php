<?php
include_once 'Bill.php';
include_once 'Member.php';
include_once 'Admin.php';
include_once 'Household.php';
include_once 'Page.php';
include_once 'Validate.php';
include_once 'spreadsheet.php';
session_start();

$currentMonth = date('m');
$_SESSION['currentMonth'] = $currentMonth;

//past month is calculated used current
$month = (int)$currentMonth;
if($month == 1){
	$pastMonth = '12';
} else if ($month > 1 && $month <= 10){
	$pastMonth = '0' .  (string)($month - 1);
} else {
	$pastMonth = $month - 1;
}

if (isset($_SESSION['user']) && !(empty($_SESSION['user']))) {
	$info = $_SESSION['user'];
	$userid = filter_var($info['userid'],FILTER_SANITIZE_NUMBER_INT);
	$level = filter_var($info['userlevel'],FILTER_SANITIZE_STRING);
	$hhid = filter_var($info['householdid'],FILTER_SANITIZE_NUMBER_INT);
	$config = new Validate();
	$tempPDO = $config->pdoConnection();
	unset($config);
	$household = new Household($hhid, $currentMonth, $tempPDO);
	if ($level == 'admin') {
		$user = new Admin($userid,$currentMonth,$tempPDO);
		unset($tempPDO);
	} else {
		$user = new Member($userid, $currentMonth, $tempPDO);
		unset($tempPDO);
	}
}
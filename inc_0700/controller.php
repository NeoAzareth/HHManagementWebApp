<?php
/**
 * this file contains the logic
 * adds all includes as well as any variable 
 * that is to be used by the program
 * 
 * this file is to be included in all webpages 
 * so that every time the page is loaded it refreshes the
 * info from the database 
 */

$GLOBALS['ROOT_PATH'] = '';
include_once $GLOBALS['ROOT_PATH'] . 'inc_0700/User.php';
include_once $GLOBALS['ROOT_PATH'] . 'inc_0700/Member.php';
include_once $GLOBALS['ROOT_PATH'] . 'inc_0700/Admin.php';
include_once $GLOBALS['ROOT_PATH'] . 'inc_0700/Household.php';
include_once $GLOBALS['ROOT_PATH'] . 'inc_0700/Bill.php';
include_once $GLOBALS['ROOT_PATH'] . 'inc_0700/Validate.php';
include_once $GLOBALS['ROOT_PATH'] . 'inc_0700/Page.php';
include_once $GLOBALS['ROOT_PATH'] . 'inc_0700/Connection.php';

session_start();

$GLOBALS['currentMonth'] = date('Y-m');
$GLOBALS['pastMonth'] = '';

$month = (int)date('m');
$year = (int)date('Y');

if($month == 1){
	$pastMonth = (string)($year - 1) .'-12';
} else if($month > 1 && $month <= 10 ) {
	$pastMonth = (string)$year . '-0' . (string)($month - 1);
} else {
	$pastMonth = (string)$year . '-' . (string)($month - 1);
}

if (isset($_SESSION['user']) && !(empty($_SESSION['user']))) {
	$hashPW = $_SESSION['user'];
	$userid = filter_var($hashPW['userid'],FILTER_SANITIZE_NUMBER_INT);
	$level = filter_var($hashPW['userlevel'],FILTER_SANITIZE_STRING);
	$hhid = filter_var($hashPW['householdid'],FILTER_SANITIZE_NUMBER_INT);
	
	$connection = new Connection();
	$pdo = $connection->getConnection();
	unset($connection);
	
	//all the info in the database is hold into two objects
	//household and user
	$household = new Household($hhid, $pdo);
	if ($level == 'admin') {
		$user = new Admin($userid, $pdo);
	} else {
		$user = new Member($userid, $pdo);
	}
	unset($hashPW,$userid,$hhid,$level,$pdo);
}
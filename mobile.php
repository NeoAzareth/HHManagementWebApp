<?php
include_once 'inc_0700/Validate.php';
include_once 'inc_0700/Household.php';
include_once 'inc_0700/Bill.php';
include_once 'inc_0700/Member.php';
include_once 'inc_0700/Admin.php';

$config = new Validate();

$tempCon = $config->pdoConnection();

//$username= $_POST['username'];
//$userPW= $_POST['password'];

$username = 'Isra';
$userPW = 'IsraPass';


$getPassQuery =
$tempCon->prepare('SELECT UserPW FROM sp16_users
					WHERE Username =:username');

if ($getPassQuery->execute(array('username'=>"$username"))) {
	$info = $getPassQuery->fetch(PDO::FETCH_ASSOC);

	if (password_verify($userPW, $info["UserPW"])) {

		/*
		unset($info);
		$getUserInfo =
		$tempCon->prepare('
 							SELECT UserLevel
 							FROM sp16_users Where Username =:username');

		$getUserInfo->execute(array('username'=> $username));
		$info = $getUserInfo->fetch(PDO::FETCH_ASSOC);
		*/
		
		$currentMonth = date('m');
		//$_SESSION['currentMonth'] = $currentMonth;
		
		//past month is calculated used current
		$month = (int)$currentMonth;
		if($month == 1){
			$pastMonth = '12';
		} else if ($month > 1 && $month <= 10){
			$pastMonth = '0' .  (string)($month - 1);
		} else {
			$pastMonth = $month - 1;
		}
		
		$household = new Household($hhid, $currentMonth, $tempCon);
		
		/*
		if ($level == 'admin') {
			$user = new Admin($userid,$currentMonth,$tempPDO);
			unset($tempPDO);
		} else {
			$user = new Member($userid, $currentMonth, $tempPDO);
			unset($tempPDO);
		}
		*/
		$household = serialize($household);
		
		if ($household) {
			echo $household;
		}
		
		
		/*
		$data = $info['UserLevel'];
		
		if($data){
			echo $data;
		}*/
		unset($tempCon);
		unset($config);
	}
}


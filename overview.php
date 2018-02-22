<?php
/***
 * overview.php main page after login, provides different vies and info about the current household
 * as well as the means to join or create a household if the user does not belong to a household
 */
//includes
include_once 'inc_0700/controller.php';

//validates access
if(!(isset($_SESSION["user"])) || $_SESSION['user'] == NULL){
	header('Location: index.php');
}

//logic
if($user->getUserStatus() == 'not in') {//diplays join or create options if user not in household
	$results = [];
	if(isset($_POST['joinHh'])){//join household
		$results = $user->joinHousehold($_POST);
	} else if(isset($_POST['createHh'])){//create household
		$results = $user->createHousehold($_POST);
	}
	$body = Page::createOrJoinOverview($user,$results);
} else if ($user->getUserStatus() == 'pending'){//displays a message to noify of user pending status
	if(isset($_POST['pending'])){//cancel pending status
		$user->cancelPending();
	}
	$body = Page::cancelPendingOverview($user);
	
} else {//default view
	$body = Page::defaultOverview($user, $household);
}

echo Page::header();

echo '<div class="container">';//open main container

echo Page::navBar($user);

echo $body;
unset($GLOBALS['ROOT_PATH']);
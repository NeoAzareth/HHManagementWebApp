<?php
/***
 * admin.php displays and controls the logic behind the admim html page 
 */

//incldues
include_once 'inc_0700/controller.php';

//validates access to the page
if(!(isset($_SESSION["user"])) || $_SESSION['user'] == NULL //redirects to index if the user object is not set
		|| $user->getUserLevel() != 'admin'
		|| $household->getHhID() == NULL){
	header('Location: index.php');
}

//variable used to temporarely save user input
$results = [];

//logic for different acctions
if (isset($_POST['remove'])){//remove users
	$results = $user->removeUsers($_POST, $household);
	$form = Page::displayMemberManagementForm($results, $household);
} else if (isset($_POST['change'])){//change household rent amount
	$feedback = $user->updateRent($_POST['rent'], $household);
	$form = Page::displayMemberManagementForm($results, $household);
} else if (isset($_GET['reset']) && (int)($_GET['reset']) != 0) {//reset user status
	$resetID = filter_var($_GET['reset'],FILTER_SANITIZE_NUMBER_INT);
	$form = Page::displayMemberManagementForm($results, $household);
	$feedback = $user->resetUserStatus($household, $resetID);
} else {//default view
	$form = Page::displayMemberManagementForm($results, $household);
	$feedback = '';
}

//echo header
echo Page::header();

echo '<div class="container">';

//echo navigation bar
echo Page::navBar($user);

echo '<div class="row">
		<div class="col-lg-6 col-lg-offset-3">
			<h2>Admin page</h2>
		</div>
	  </div>
		<div class="row">
			<div class="col-lg-10 col-lg-offset-1">';

//echo form as the current action
echo $form;

//displays a form to change the rent amount
echo Page::displayChangeRentAmountForm($household);

//used to show some pop window java scripts
echo $feedback;

echo '</div>
	</div>
	</div>
	</body>
	</html>';

unset($GLOBALS['ROOT_PATH']);
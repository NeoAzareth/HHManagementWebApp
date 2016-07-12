<?php
include_once 'inc_0700/config.php';

if(!(isset($_SESSION["user"])) && $_SESSION['user'] == NULL && $user->getUserLevel() != 'admin'){
	header('Location: index.php');
}

if(isset($_POST['add'])){
	$results = $user->sendRegistrationLink($_POST, $household);
	$form = Page::displayMemberManagementForm($results, $household);
} else if (isset($_POST['delete'])){
	$results = $user->deleteUsers($_POST, $household, $currentMonth);
	$form = Page::displayMemberManagementForm($results, $household);
} else if (isset($_POST['change'])){
	$feedback = Validate::validateChangeRentForm($_POST['rent'], $household, $user);
	$results = [];
	$form = Page::displayMemberManagementForm($results, $household);
} else if (isset($_GET['reset']) && (int)($_GET['reset']) != 0) {
	$resetID = filter_var($_GET['reset'],FILTER_SANITIZE_NUMBER_INT);
	$results = [];
	$form = Page::displayMemberManagementForm($results, $household);
	$feedback = Validate::validateResetID($household, $user, $resetID);
} else {
	$results = [];
	$form = Page::displayMemberManagementForm($results, $household);
	$feedback = '';
}

echo Page::header();

echo '<div class="container">';

echo Page::navBar($user);

echo '<div class="row">
		<div class="col-lg-6 col-lg-offset-3">
			<h2>Admin page</h2>
		</div>
	  </div>
		<div class="row">
			<div class="col-lg-10 col-lg-offset-1">';

echo $form;

echo Page::displayChangeRentAmountForm($household);

echo $feedback;

echo '</div>
	</div>
	</div>
	</body>
	</html>';
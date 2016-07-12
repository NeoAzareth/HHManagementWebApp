<?php
include_once 'inc_0700/config.php';

if(!(isset($_SESSION["user"])) && $_SESSION['user'] == NULL){
	header('Location: index.php');
}

if(isset($_GET['editcontact']) && (int)($_GET['editcontact']) > 0){
	$_SESSION['editcontact']= $_GET['editcontact'];
} else if(isset($_POST['cancel'])){
	$_SESSION['editcontact'] = 0;
}

if(isset($_POST['updatepass'])){
	$results = [];
	$result = Validate::validateUpdatePassForm($_POST, $user);
	$form = Page::updatePasswordForm($result);
	$form .= Page::notificationSettingsForm($results,$user);
} else if (isset($_POST['update'])){
	$result = '';
	$results = Validate::validateUpdateContactInfo($_POST, $user);
	$form = Page::updatePasswordForm($result);
	$form .= Page::notificationSettingsForm($results,$user);
} else if (isset($_POST['addoption'])){
	$results = Validate::validateAddContacInfoForm($_POST, $user);
	$result = '';
	$form = Page::updatePasswordForm($result);
	$form .= Page::notificationSettingsForm($results,$user);
	
} else if (isset($_GET['delete']) && (int)($_GET['delete']) > 0){
	$id = $_GET['delete'];
	$results['result'] = Validate::validateDeleteContactOption($id, $user);
	$result = '';
	$form = Page::updatePasswordForm($result);
	$form .= Page::notificationSettingsForm($results,$user);
} else if (isset($_POST['setprimary'])){
	$results = Validate::validateSetPrimary($_POST, $user);
	$result = '';
	$form = Page::updatePasswordForm($result);
	$form .= Page::notificationSettingsForm($results,$user);
} else {
	$results = [];
	$result = '';
	$form = Page::updatePasswordForm($result);
	$form .= Page::notificationSettingsForm($results,$user);
}

echo Page::header();

echo '<div class="container">';

echo Page::navBar($user);

echo '   <div class="row">
			<div class="col-lg-6 col-lg-offset-3">
				<h2>Settings Page</h2>
			</div>
		</div>
		<div class="row">
		';

echo $form;

echo '</div>
	</div>
	</body>
	</html>';
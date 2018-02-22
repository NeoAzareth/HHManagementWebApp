<?php
/***
 * settings.php allows user to reset their password and leave or delete their current household
 */
include_once 'inc_0700/controller.php';

if(!(isset($_SESSION["user"])) || $_SESSION['user'] == NULL){
	header('Location: index.php');
}

$result = '';
if(isset($_POST['updatepass'])){
	$result = $user->updatePassword($_POST);
	$form = Page::updatePasswordForm($result);
	$form .= Page::leaveOrDeleteForm($user, $household);
} else if(isset($_POST['leaveHh'])) {
	$result = $user->leaveHousehold();
	$form = Page::updatePasswordForm($result);
	$form .= Page::leaveOrDeleteForm($user, $household);
} else if(isset($_POST['deleteHh'])) {
	$result = $user->deleteHousehold($household->getHhID());
	$form = Page::updatePasswordForm($result);
	$form .= Page::leaveOrDeleteForm($user, $household);
} else {
	$form = Page::updatePasswordForm($result);
	$form .= Page::leaveOrDeleteForm($user, $household);
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
unset($GLOBALS['ROOT_PATH']);
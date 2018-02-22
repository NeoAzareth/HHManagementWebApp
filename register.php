<?php
/***
 * register.php this page was buld as an excercise. It doesn't actually register and user!
 */

include_once 'inc_0700/controller.php';
$results = array();

if(isset($_POST['register'])){
    $results = User::registerUser($_POST);
    $form = Page::displayNewMemberRegistrationForm($results);
} else {
    $form = Page::displayNewMemberRegistrationForm($results);
}


echo Page::header();

echo '<div class="container">';

echo '<div class="row">
		<div class="col-lg-6 col-lg-offset-3">
			<h2 class="text-center">New member registration form</h2>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-6 col-lg-offset-3">
		';

echo $form;

echo '</div>
		</div>
		</div>
	</body>
	</html>';
unset($GLOBALS['ROOT_PATH']);
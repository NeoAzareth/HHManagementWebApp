<?php
/***
 * index.php is the main page of the application
 * 
 * it allows login/password reset and provides links to a register mock page
 */
//includes
include_once 'inc_0700/controller.php';

//logic of the page
if (isset($_SESSION["user"]) && $_SESSION["user"] != NULL){//redirects if a session is in place
	header('Location: overview.php');
} else if (isset($_POST['login'])){//process login
	$error = User::processLogin($_POST['em'], $_POST['pw']);
} else if (isset($_POST['resetPass'])){//process password reset
	echo User::proccessResetPassForm($_POST['passRe']);
} else {//default view

	$error = '';
}

//greets the user by name if a cookie exists
if (isset($_COOKIE['name'])){
	$greeting = '<div class="row">
					<div class="col-lg-6 col-lg-offset-3">
						<h2>Welcome back ' . $_COOKIE['name'] . '!</h2>
					</div>
				</div>';
} else {
	$greeting = '<div class="row">
					<div class="col-lg-6 col-lg-offset-3">
						<h2>Hello users!</h2>
					</div>
				</div>';
}

echo Page::header();

echo '<div class="container">';

echo $greeting;

echo Page::logInForm($error);

echo '</div>
	</body>
	</html>';

unset($GLOBALS['ROOT_PATH']);


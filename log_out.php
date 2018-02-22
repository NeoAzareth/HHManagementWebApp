<?php
/***
 * log_out.php destroys the session to terminate access to the application
 */
include_once 'inc_0700/controller.php';
session_destroy();
$_SESSION = array();
unset($household,$user);

echo Page::header();

echo '<div class="container">';

echo '<div class="row">
			<div class="col-lg-8 col-lg-offset-2">
				<h2>You have been succesfully logged out ' . $_COOKIE['name'].'!</h2>
				<a href="index.php" class="btn btn-info">Back to login page</a>
			</div>
		</div>';

echo '
	</div>
	</body>
	</html>';
unset($GLOBALS['ROOT_PATH']);
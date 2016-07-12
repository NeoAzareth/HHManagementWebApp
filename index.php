<?php
include_once 'inc_0700/config.php';

if (isset($_SESSION["user"]) && $_SESSION["user"] != NULL){
	header('Location: overview.php');
} else if (isset($_POST['login'])){
	$error = Validate::proccessLogin($_POST['un'], $_POST['pw']);
} else {
	$error = '';
}

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




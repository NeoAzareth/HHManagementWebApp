<?php
/***
 * reset_pw.php displays a form to reset a password given that there is a valid code in the
 * database to permit such request
 * @todo revise if logic work in case a failure to validate password
 */
include_once 'inc_0700/controller.php';

if (isset($_SESSION["user"]) && $_SESSION["user"] != NULL){//redirect to overview if there is a valid session
	header('Location: settings.php');
} else if(isset($_GET['upsc'])){//if the $_GET['uspc'] varialbe is set...
	//extracts the variable
    $upsc = $_GET['upsc'];
	$connection = new Connection();
	$pdo = $connection->getConnection();
	unset($connection);
	//attemps validation
	if(!Validate::isTokenAvailable(
			$upsc, 'CodeValid', 'CodeNum', 'hhm_codes', $pdo)){//if so, displays a form to reset password
		$form = Page::resetPwForm($upsc);
	} else {//otherwise redirects to index.php
		unset($pdo);
		header('Location: index.php');
	}
} else if(isset($_POST['resetpass'])){//attemps password reset
	echo User::resetPassword($_POST);
	
}else {//if no uspc code or post to reset pass is given redirects to index.php
	header('Location: index.php');
}

echo Page::header();

echo '<div class="container">';

echo $form;

echo '</div>
	</body>
	</html>';

unset($GLOBALS['ROOT_PATH']);

<?php
include_once 'inc_0700/config.php';

if(!(isset($_POST['register'])) && (!(isset($_GET['reg'])) || strlen($_GET['reg']) != 32)) {
	echo '<script>
		alert("Invalid data!");
		window.location.href="index.php";
		</script>';
} else if (isset($_POST['register'])){
	$array = Validate::validateNewMemberRegistrationForm($_POST);
	$form = Page::displayNewMemberRegistrationForm($array);
} else {
	$validator = new Validate();
	if($validator->validateRegistrationCode($_GET['reg'])){
		$array =[];
		$form = Page::displayNewMemberRegistrationForm($array);;
		unset($validator);
	} else {
		echo '<script>
		alert("You are already registered");
		window.location.href="index.php";
		</script>';
	}
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
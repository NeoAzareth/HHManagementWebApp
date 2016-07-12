<?php
include_once 'inc_0700/config.php';

if(!(isset($_SESSION["user"])) && $_SESSION['user'] == NULL){
	header('Location: index.php');
}

if (isset($_POST['get_custom'])){
	$report = Validate::validateReportForm($_POST, $household);
} else {
	$results = [];
	$report = Validate::validateReportForm($results, $household);
}

echo Page::header();

echo '<div class="container">';

echo Page::navBar($user);

echo '<div class="row">
		<div class="col-lg-6 col-lg-offset-3">
			<h2>Report Page</h2>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-10 col-lg-offset-1">
		';

echo Page::reportForm($household);

echo $report;

echo '</div>
		</div>
		</div>
	</body>
	</html>';
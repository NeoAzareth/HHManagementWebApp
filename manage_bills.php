<?php
include_once 'inc_0700/config.php';

if(!(isset($_SESSION["user"])) && $_SESSION['user'] == NULL){
	header('Location: index.php');
}

if(isset($_GET['editbill']) && (int)($_GET['editbill']) > 0){
	$_SESSION['editbill']= $_GET['editbill'];
} else if(isset($_POST['cancel'])){
	$_SESSION['editbill'] = 0;
}

if(isset($_POST['add'])){
	$results = Validate::validateAddBillForm($_POST,$user,$household);
	$user->getUserBills($currentMonth);
} else if (isset($_POST['delete'])){
	$results = Validate::validateDeleteBillForm($_POST,$user);
	$user->getUserBills($currentMonth);
} else if (isset($_POST['status'])){
	echo $user->changeUserStatus('manage_bills.php','done');
} else if (isset($_POST['update'])){
	$results = Validate::validateUpdateBillForm($_POST,$user);
	$user->getUserBills($currentMonth);
} else {
	$results = '';
}

if ($user->getUserStatus() == 'done') {
	$doneMessage = '<h4 class="text-danger">You have set you status to "Done", </br>
			if you need to add, update or change bills  contact your admin.</h4>';
	$table = $user->displayUserBills() . $doneMessage;
} else {
	$table = Page::manageBillsForm($results,$user); 
}

$nameWithS = $user->getUserFirstName();

if ($nameWithS[strlen($nameWithS) - 1] == 's'){
	$nameWithS = $nameWithS . '\'';
} else {
	$nameWithS = $nameWithS . '\'s';
}

echo Page::header();

echo '<div class="container">';//open container

echo Page::navBar($user);

echo '			<div class="row">
				<div class="col-lg-6 col-lg-offset-3">
					<h2>Manage Bills page.</h2>
				</div>
				</div>
		
				<!--2 divs open and close -->
		<div class="row">
			<div class="col-lg-10 col-lg-offset-1">
				<h3><em class="text-primary">'.$nameWithS . '</em> bills</h3>
			';

echo $table;

echo '</div>
		</div>
		</div> <!--closes container -->
	</body>
	</html>';
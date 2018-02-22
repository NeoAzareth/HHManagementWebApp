<?php
/**
 * manage_bills.php allows registerd users to add, update or delete bills
 */
//includes
include_once 'inc_0700/controller.php';

//validates access
if(!(isset($_SESSION["user"])) || $_SESSION['user'] == NULL
		|| $household->getHhID() == NULL){
	header('Location: index.php');
}

//logic
//shows the edit bill form option
if(isset($_GET['editbill']) && (int)($_GET['editbill']) > 0){
	$_SESSION['editbill']= $_GET['editbill'];
} else if(isset($_POST['cancel'])){
	$_SESSION['editbill'] = 0;//or hides if this variable is set to 0
}


if(isset($_POST['add'])){//add bill
	$results = $user->addBill($_POST);
} else if (isset($_POST['delete'])){//delete bills
	$results = $user->deleteBills($_POST);
} else if (isset($_POST['status'])){//set user status to done
	echo $user->changeUserStatus('manage_bills.php','done');
} else if (isset($_POST['update'])){//update bills
	$results = $user->updateBill($_POST);
} else {//default view
	$results = '';
}

//restrains user from modifying bills by hiding some features if the user status = 'done'
if ($user->getUserStatus() == 'done') {
	$doneMessage = '<h4 class="text-danger">You have set you status to "Done", </br>
			if you need to add, update or change bills  contact your admin.</h4>';
	$table = $user->displayUserBills() . $doneMessage;
} else {
	$table = Page::manageBillsForm($results,$user);
}

//formats a string with an s or an ' at the end if the user's name ends with s
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
unset($GLOBALS['ROOT_PATH']);
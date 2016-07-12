<?php
include_once 'inc_0700/config.php';

if(!(isset($_SESSION["user"]))&& $_SESSION['user'] == NULL){
	header('Location: index.php');
}

echo Page::header();

echo '<div class="container">';//open main container

echo Page::navBar($user);

echo ' 		<div class="row">
			<div class="col-lg-6 col-lg-offset-3">
							<h1 class="text-center">Overview page.</h1>
			</div>
		   	</div>

			<!--2 divs open and close -->

			<div class="row">
			<div class="col-lg-10 col-lg-offset-1">
				<h2>Hello <em class="text-primary">'. $user->getUserFullName().'</em>!</h2>
			</div>
			</div>

			<!--2 divs open and close -->

			<div class="row">
			<div class="col-lg-10 col-lg-offset-1">
			<h4> Welcome to <em class="text-success">'
						. $household->getHhName(). '</em> overview page.</h4>
			<h4> Rent as of '
								. date('F jS\, Y') . ': <em class="text-danger">$' 
										. $household->getHhRent().'</em></h4>
			</div>
			</div>
			<!--2 divs open and close -->';

echo '	<div class="row">
			<div class="col-lg-5 col-lg-offset-1">
			<h2 class="text-center">My bills</h2>';

echo $user->displayUserBills();

echo '		</div>
			<div class="col-lg-5">
		<h2 class="text-center">Member\'s Status</h2>';

echo $household->showUsersStatus($user);

echo '		</div>
		</div>
		<div class="row">
			<div class="col-lg-10 col-lg-offset-1">';

echo $household->expensesDistribution('current');

echo '</div>
	<div class="col-lg-10 col-lg-offset-1">';

echo $household->expensesDistribution($pastMonth);
		
echo '	<div>
	</div>
	</div> <!--closes container -->
	</body>
	</html>';

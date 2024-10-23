<?php
include_once "../init.php";

// User login checker
if ($getFromU->loggedIn() === false) {
	header('Location: ../index.php');
}

include_once 'skeleton.php';

// Create an expense record
if (isset($_POST['addexpense'])) {
	$dt = date("Y-m-d H:i:s", strtotime($_POST["dateexpense"]));
	$category = $_POST['category'];
	$itemcost = $_POST['costitem'];

	// Insert the expense into the database
	$getFromE->create("expense", array('UserId' => $_SESSION['UserId'], 'Category' => $category, 'Cost' => $itemcost, 'Date' => $dt));

	// After inserting into the database, redirect to the same page
	echo '<script>
        Swal.fire({
            title: "Done!",
            text: "Records Updated Successfully",
            icon: "success",
            confirmButtonText: "Close"
        }).then(function() {
			window.location = window.location.href; // Redirect to the same page
		});
    </script>';
	exit(); // Stop further execution after redirect
}

// Check if category and costitem are set to prevent undefined array key errors
if (isset($_POST['category']) && isset($_POST['costitem'])) {
	$category = $_POST['category'];
	$itemcost = $_POST['costitem'];

	// Check if the expense exceeds the remaining budget
	$remainingBudget = $getFromB->getRemainingBudget($_SESSION['UserId'], $category);
	if ($remainingBudget < $itemcost) {
		echo '<script>
			Swal.fire({
				title: "Budget Warning!",
				text: "This expense exceeds your remaining budget for ' . $category . '.",
				icon: "warning",
				confirmButtonText: "OK"
			});
		</script>';
	}
}
?>

<div class="wrapper">
	<div class="row">
		<div class="col-12 col-m-12 col-sm-12">
			<div class="card">
				<div class="counter" style="height: 60vh; display: flex; align-items: center; justify-content: center;">
					<form action="" method="post">
						<div>
							<label style="font-family: 'Source Sans Pro'; font-size: 1.3em;">Date of Expense:</label><br><br>
							<input class="text-input" type="datetime-local" value="" name="dateexpense" required="true" style="width: 100%; padding-top: 8px;"><br><br>
						</div>
						<div>
							<label style="font-family: 'Source Sans Pro'; font-size: 1.3em;">Category:</label><br>
							<select name="category" class="text-input" style="width: 100%; padding-top: 10px;" required="true">
								<option value="Food">Food</option>
								<option value="Transport">Transport</option>
								<option value="Entertainment">Entertainment</option>
								<option value="Healthcare">Healthcare</option>
								<option value="Education">Education</option>
								<option value="Others">Others</option>
							</select><br><br>
						</div>
						<div>
							<label style="font-family: 'Source Sans Pro'; font-size: 1.3em;">Cost:</label><br>
							<input class="text-input" type="text" value="" required="true" name="costitem" onkeypress='validate(event)' style="width: 100%; padding-top: 10px;"><br><br>
						</div>
						<div><br>
							<button type="submit" class="pressbutton" name="addexpense">Add</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="../static/js/4-Set-Budget.js"></script>
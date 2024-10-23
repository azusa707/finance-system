<?php
include_once "../init.php";

// User login checker
if ($getFromU->loggedIn() === false) {
	header('Location: ../index.php');
}

include_once 'skeleton.php';

// Deletes expense record
if (isset($_POST['delrec'])) {
	$getFromE->delexp($_POST['ID']);
	echo "<script>
				Swal.fire({
					title: 'Done!',
					text: 'Record deleted successfully',
					icon: 'success',
					confirmButtonText: 'Close'
				})
				</script>";
}
if (isset($_POST['updaterec'])) {
	$getFromE->updateExpense($_POST['ID'], $_POST['Category'], $_POST['Cost'], $_POST['Date']);
	echo "<script>
            Swal.fire({
                title: 'Updated!',
                text: 'Record updated successfully',
                icon: 'success',
                confirmButtonText: 'Close'
            })
          </script>";
}

// Predefined categories
$categories = ['Food', 'Transport', 'Entertainment', 'Education', 'Healthcare', 'Others'];
?>

?>

<div class="wrapper">
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<i class="fas fa-ellipsis-h"></i>
					<h3 style="font-family:'Source Sans Pro'; font-size: 1.5em;">Expenses</h3>
				</div>
				<div class="card-content">
					<table>
						<thead>
							<tr>
								<th>#</th>
								<th>Category</th>
								<th>Cost</th>
								<th>Date</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$totexp = $getFromE->allexp($_SESSION['UserId']);
							if ($totexp !== NULL) {
								$len = count($totexp);
								for ($x = 1; $x <= $len; $x++) {
									$expense = $totexp[$x - 1];
									echo "<tr>
                                            <form action='' method='post'>
                                                <td>{$x}</td>
                                                <td>
                                                    <select name='Category'>
                                                        <option disabled>Select a category</option>";
									foreach ($categories as $category) {
										// Preselect the current category
										$selected = ($category == $expense->Category) ? 'selected' : '';
										echo "<option value='$category' $selected>$category</option>";
									}
									echo "              </select>
                                                </td>
                                                <td><input type='number' name='Cost' step='0.01' value='{$expense->Cost}' /></td>
                                                <td><input type='date' name='Date' value='" . date("Y-m-d", strtotime($expense->Date)) . "' /></td>
                                                <td>
                                                    <input type='hidden' name='ID' value='{$expense->ID}' />
                                                    <button type='submit' name='updaterec' class='btn btn-default'>Update</button>
                                                    <button type='submit' name='delrec' class='btn btn-danger'>Delete</button>
                                                </td>
                                            </form>
                                          </tr>";
								}
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
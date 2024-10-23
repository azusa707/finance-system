<?php
include_once "../init.php";

// User login checker
if ($getFromU->loggedIn() === false) {
    header('Location: ../index.php');
}

$incomeManager = new Income($pdo);
include_once 'skeleton.php';

// Deletes income record
if (isset($_POST['delrec'])) {
    $incomeManager->deleteIncome($_POST['ID']);
    echo "<script>
            Swal.fire({
                title: 'Done!',
                text: 'Income record deleted successfully',
                icon: 'success',
                confirmButtonText: 'Close'
            });
          </script>";
}

// Updates income record
if (isset($_POST['updaterec'])) {
    $incomeManager->updateIncome($_POST['ID'], $_POST['Amount'], $_POST['Date']);
    echo "<script>
            Swal.fire({
                title: 'Updated!',
                text: 'Income record updated successfully',
                icon: 'success',
                confirmButtonText: 'Close'
            });
          </script>";
}

// Retrieve all income records
$allIncome = $incomeManager->allIncome($_SESSION['UserId']);
?>

<div class="wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-dollar-sign"></i>
                    <h3 style="font-family:'Source Sans Pro'; font-size: 1.5em;">Manage Income</h3>
                </div>
                <div class="card-content">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($allIncome !== NULL) {
                                $len = count($allIncome);
                                for ($x = 1; $x <= $len; $x++) {
                                    $income = $allIncome[$x - 1];
                                    echo "<tr>
                                            <form action='' method='post'>
                                                <td>{$x}</td>
                                                <td><input type='number' name='Amount' step='0.01' value='{$income->Amount}' required /></td>
                                                <td><input type='date' name='Date' value='" . date("Y-m-d", strtotime($income->Date)) . "' required /></td>
                                                <td>
                                                    <input type='hidden' name='ID' value='{$income->ID}' />
                                                    <button type='submit' name='updaterec' class='btn btn-default'>Update</button>
                                                    <button type='submit' name='delrec' class='btn btn-danger'>Delete</button>
                                                </td>
                                            </form>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No income records found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include_once "../init.php";

// User login checker
if ($getFromU->loggedIn() === false) {
    header('Location: ../index.php');
}

include_once 'skeleton.php';

// Create an income record
if (isset($_POST['addincome'])) {
    $dt = date("Y-m-d H:i:s", strtotime($_POST["dateincome"]));
    $amount = $_POST['incomeamount'];
    $getFromE->create("income", array('UserId' => $_SESSION['UserId'], 'Amount' => $amount, 'Date' => $dt));

    // After inserting into the database, redirect to the same page
    echo '<script>
        Swal.fire({
            title: "Done!",
            text: "Record Added Successfully",
            icon: "success",
            confirmButtonText: "Close"
        }).then(function() {
            window.location = window.location.href; // Redirect to the same page
        });
    </script>';
    exit(); // Stop further execution after redirect
}

// Fetch all income records for the user
$allIncome = $getFromE->allIncome($_SESSION['UserId']);
?>

<div class="wrapper">
    <div class="row">
        <div class="col-12 col-m-12 col-sm-12">
            <div class="card">
                <div class="counter" style="height: 60vh; display: flex; align-items: center; justify-content: center;">
                    <form action="" method="post">
                        <div>
                            <label style="font-family: 'Source Sans Pro'; font-size: 1.3em;">Date of Income:</label><br><br>
                            <input class="text-input" type="datetime-local" value="" name="dateincome" required="true" style="width: 100%; padding-top: 8px;"><br><br>
                        </div>
                        <div>
                            <label style="font-family: 'Source Sans Pro'; font-size: 1.3em;">Amount:</label><br>
                            <input class="text-input" type="text" value="" required="true" name="incomeamount" onkeypress='validate(event)' style="width: 100%; padding-top: 10px;"><br><br>
                        </div>
                        <div><br>
                            <button type="submit" class="pressbutton" name="addincome">Add</button>
                        </div>
                    </form>
                </div>

                <div class="income-records">
                    <h3 style="font-family: 'Source Sans Pro';">Your Income Records:</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($allIncome) {
                                foreach ($allIncome as $index => $record) {
                                    echo "<tr>
                                        <td>" . ($index + 1) . "</td>
                                        <td>$" . htmlspecialchars($record->Amount) . "</td>
                                        <td>" . date("d-m-Y", strtotime($record->Date)) . "</td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>No income records found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../static/js/4-Set-Budget.js"></script>
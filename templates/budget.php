<?php
include_once "../init.php";

// User login checker
if ($getFromU->loggedIn() === false) {
    header('Location: ../index.php');
}

include_once 'skeleton.php';

// Handle form submission
if (isset($_POST['setbudget'])) {
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $date = date("Y-m-d", strtotime($_POST["date"]));

    // Add budget to database
    $result = $getFromB->setBudget($_SESSION['UserId'], $category, $amount, $date);

    if ($result) {
        echo '<script>
            Swal.fire({
                title: "Success!",
                text: "Budget set successfully",
                icon: "success",
                confirmButtonText: "Close"
            }).then(function() {
                window.location = window.location.href;
            });
        </script>';
    } else {
        echo '<script>
            Swal.fire({
                title: "Error!",
                text: "Failed to set budget",
                icon: "error",
                confirmButtonText: "Close"
            });
        </script>';
    }
}

// Fetch current budgets
$budgets = $getFromB->getCurrentBudgets($_SESSION['UserId']);
?>

<div class="wrapper">
    <div class="row">
        <div class="col-12 col-m-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3 style="font-family:'Source Sans Pro'; font-size: 1.5em;">Set Budget</h3>
                </div>
                <div class="card-content">
                    <form action="" method="post">
                        <div>
                            <label style="font-family: 'Source Sans Pro'; font-size: 1.3em;">Date:</label><br><br>
                            <input class="text-input" type="date" name="date" required="true" style="width: 100%; padding-top: 8px;"><br><br>
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
                            <label style="font-family: 'Source Sans Pro'; font-size: 1.3em;">Budget Amount:</label><br>
                            <input class="text-input" type="number" step="0.01" required="true" name="amount" style="width: 100%; padding-top: 10px;"><br><br>
                        </div>
                        <div><br>
                            <button type="submit" class="pressbutton" name="setbudget">Set Budget</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-m-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list"></i>
                    <h3 style="font-family:'Source Sans Pro'; font-size: 1.5em;">Current Budgets</h3>
                </div>


                <div class="card-content">
                    <table class="table-bordered">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Budget Amount</th>
                                <th>Remaining Budget</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($budgets as $budget): ?>
                                <?php $remaining = $getFromB->getRemainingBudget($_SESSION['UserId'], $budget->Category); ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($budget->Category); ?></td>
                                    <td>$<?php echo number_format($budget->Amount, 2); ?></td>
                                    <td>$<?php echo number_format($remaining, 2); ?></td>
                                    <td><?php echo $budget->Date; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
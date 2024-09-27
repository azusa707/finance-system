<?php
include_once "../init.php";

//User login check
if ($getFromU->loggedIn() === false) {
    header('Location: ../index.php');
}

include_once 'skeleton.php';
$current_month = date('m');  // Get the current month in numerical format (e.g., 09 for September)
$current_year = date('Y');
if (isset($_SESSION['swal'])) {
    echo $_SESSION['swal'];
    unset($_SESSION['swal']);
}
$monthly_income = $getFromI->monthlyIncome($_SESSION['UserId'], $current_month, $current_year);
$monthly_expense = $getFromE->monthlyExpenses($_SESSION['UserId'], $current_month, $current_year);




// Last 30 Days' Expenses

if ($monthly_expense == NULL) {
    $monthly_expense_display = "No Expenses This Month";
} else {
    $monthly_expense_display = "$ " . $monthly_expense->total;
}
// Last 30 Days' Income
if ($monthly_income == NULL) {
    $monthly_income_display = "No Income This Month";
} else {
    $monthly_income_display = " " . $monthly_income->total;
}


$totalIncome = $getFromI->getTotalIncome($_SESSION['UserId']);
$totalExpense = $getFromE->getTotalExpense($_SESSION['UserId']);
$balance = $totalIncome - $totalExpense;



?>
<div class="wrapper">
    <div class="row">

        <!-- <div class="col-4 col-m-4 col-sm-4">
            <div class="card">
                <div class="counter bg-vio" style="color:white;">
                    <p><i class="fas fa-calendar"></i></p>
                    <h3>Last 30 day's Expenses</h3>
                    <p style="font-size: 1.2em;">
                        <?php echo $monthly_expense_display; ?>
                    </p>
                </div>
            </div>
        </div> -->

        <div class="col-4 col-m-4 col-sm-4">
            <div class="card">
                <div class="counter bg-vio" style="color:white;">
                    <p><i class="fas fa-calendar"></i></p>
                    <h3>Last 30 day's Income</h3>
                    <p style="font-size: 1.2em;">
                        <?php echo $monthly_income_display; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-4 col-m-4 col-sm-4">
            <div class="card">
                <div class="counter bg-yell" style="color:white;">
                    <p><i class="fas fa-file-invoice-dollar" aria-hidden="true"></i></p>
                    <h3>Total Balance</h3>
                    <p style="font-size: 1.2em;">
                        <?php echo " " . ($totalIncome - $totalExpense); ?>
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
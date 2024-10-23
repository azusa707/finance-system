<?php
include_once "../init.php";

// User login check
if ($getFromU->loggedIn() === false) {
    header('Location: ../index.php');
}

include_once 'skeleton.php';

$current_month = date('m');
$current_year = date('Y');
if (isset($_SESSION['swal'])) {
    echo $_SESSION['swal'];
    unset($_SESSION['swal']);
}

$monthly_income = $getFromI->monthlyIncome($_SESSION['UserId'], $current_month, $current_year);
$monthly_expense = $getFromE->monthlyExpenses($_SESSION['UserId'], $current_month, $current_year);

// Format monthly income display
if ($monthly_income === NULL || $monthly_income->total === NULL) {
    $monthly_income_display = "Rs0";
} else {
    $monthly_income_display = "Rs" . number_format($monthly_income->total, 2);
}

// Format monthly expense display
if ($monthly_expense === NULL || $monthly_expense->total === NULL) {
    $monthly_expense_display = "Rs0";
} else {
    $monthly_expense_display = "Rs" . number_format($monthly_expense->total, 2);
}

// Get historical data
$monthlyIncomeData = $getFromI->getLastSixMonthsIncome($_SESSION['UserId']);
$monthlyExpenseData = $getFromE->getLastSixMonthsExpenses($_SESSION['UserId']);

// Initialize arrays for the last 6 months
$months = [];
$incomeData = [];
$expenseData = [];

// Get the last 6 months in reverse order (most recent first)
for ($i = 5; $i >= 0; $i--) {
    $monthNum = date('n', strtotime("-$i months")); // Get month number (1-12)
    $monthName = date('M', strtotime("-$i months")); // Get month name (Jan-Dec)
    $months[] = $monthName;
    $incomeData[$monthNum] = 0; // Initialize with zero
    $expenseData[$monthNum] = 0;
}

// Process income data
foreach ($monthlyIncomeData as $row) {
    $monthNum = intval($row['month']);
    if (isset($incomeData[$monthNum])) {
        $incomeData[$monthNum] = floatval($row['total']);
    }
}

// Process expense data
foreach ($monthlyExpenseData as $row) {
    $monthNum = intval($row['month']);
    if (isset($expenseData[$monthNum])) {
        $expenseData[$monthNum] = floatval($row['total']);
    }
}

// Convert to sequential arrays for the chart
$finalIncomeData = array_values($incomeData);
$finalExpenseData = array_values($expenseData);

$totalIncome = $getFromI->getTotalIncome($_SESSION['UserId']);
$totalExpense = $getFromE->getTotalExpense($_SESSION['UserId']);
?>

<!-- Add this div where you want the chart to appear -->
<div class="wrapper" style="margin-left: 250px; padding: 20px;">
    <div class="row">
        <!-- Summary Cards -->
        <div class="col-4 col-m-4 col-sm-4">
            <div class="card">
                <div class="counter bg-vio" style="color:white;">
                    <p><i class="fas fa-wallet"></i></p>
                    <h3>Monthly Income</h3>
                    <p style="font-size: 1.2em;"><?php echo $monthly_income_display; ?></p>
                </div>
            </div>
        </div>

        <div class="col-4 col-m-4 col-sm-4">
            <div class="card">
                <div class="counter bg-red" style="color:white;">
                    <p><i class="fas fa-shopping-cart"></i></p>
                    <h3>Monthly Expenses</h3>
                    <p style="font-size: 1.2em;"><?php echo $monthly_expense_display; ?></p>
                </div>
            </div>
        </div>

        <div class="col-4 col-m-4 col-sm-4">
            <div class="card">
                <div class="counter bg-yell" style="color:white;">
                    <p><i class="fas fa-balance-scale"></i></p>
                    <h3>Net Balance</h3>
                    <p style="font-size: 1.2em;">Rs<?php echo number_format($totalIncome - $totalExpense, 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="col-6 col-m-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h3>Income Trend (Last 6 Months)</h3>
                </div>
                <div class="counter">
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-6 col-m-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h3>Expense Trend (Last 6 Months)</h3>
                </div>
                <div class="counter">
                    <canvas id="expenseChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Comparison Chart -->
        <div class="col-12 col-m-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h3>Income vs Expenses Overview</h3>
                </div>
                <div class="counter">
                    <canvas id="comparisonChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        padding: 15px;
    }

    .card-header {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
        margin-bottom: 15px;
    }

    .counter {
        padding: 16px;
        text-align: center;
        border-radius: 8px;
    }

    .bg-vio {
        background: #6b5b95;
    }

    .bg-red {
        background: #ff6b6b;
    }

    .bg-yell {
        background: #4CAF50;
    }

    .wrapper {
        background: #f5f6fa;
        min-height: 100vh;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the data from PHP
        const months = <?php echo json_encode($months); ?>;
        const incomeData = <?php echo json_encode($finalIncomeData); ?>;
        const expenseData = <?php echo json_encode($finalExpenseData); ?>;

        // Income Chart
        const incomeCtx = document.getElementById('incomeChart').getContext('2d');
        new Chart(incomeCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Income',
                    data: incomeData,
                    borderColor: '#6b5b95',
                    backgroundColor: 'rgba(107, 91, 149, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Expense Chart
        const expenseCtx = document.getElementById('expenseChart').getContext('2d');
        new Chart(expenseCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Expenses',
                    data: expenseData,
                    borderColor: '#ff6b6b',
                    backgroundColor: 'rgba(255, 107, 107, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Comparison Chart
        const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
        new Chart(comparisonCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                        label: 'Income',
                        data: incomeData,
                        backgroundColor: 'rgba(107, 91, 149, 0.7)',
                        borderRadius: 5
                    },
                    {
                        label: 'Expenses',
                        data: expenseData,
                        backgroundColor: 'rgba(255, 107, 107, 0.7)',
                        borderRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                barPercentage: 0.7
            }
        });
    });
</script>
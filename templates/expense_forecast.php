<?php
include_once "../init.php";

// User login checker
if ($getFromU->loggedIn() === false) {
    header('Location: ../index.php');
}

include_once 'skeleton.php';

// Get the forecasted expenses
$forecastedExpenses = $getFromE->forecastExpenses($_SESSION['UserId'], 3);

// Get the last 12 months of actual expenses for comparison
$lastTwelveMonths = $getFromE->getLastTwelveMonthsExpenses($_SESSION['UserId']);

?>

<div class="wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line"></i>
                    <h3 style="font-family:'Source Sans Pro'; font-size: 1.5em;">Expense Forecast</h3>
                </div>
                <div class="card-content">
                    <canvas id="forecastChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('forecastChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    <?php
                    foreach ($lastTwelveMonths as $month) {
                        echo "'" . date('M Y', strtotime($month['year'] . '-' . $month['month'] . '-01')) . "',";
                    }
                    for ($i = 1; $i <= 3; $i++) {
                        echo "'" . date('M Y', strtotime("+$i month")) . "',";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Actual Expenses',
                    data: [<?php echo implode(',', array_column($lastTwelveMonths, 'total')); ?>],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }, {
                    label: 'Forecasted Expenses',
                    data: [<?php
                            // Pad with null values for actual expense months
                            for ($i = 0; $i < count($lastTwelveMonths); $i++) {
                                echo "null,";
                            }
                            echo implode(',', $forecastedExpenses);
                            ?>],
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>

<?php include_once 'footer.php'; ?>
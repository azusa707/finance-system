<?php
include_once "../init.php";

// User login checker
if ($getFromU->loggedIn() === false) {
    header('Location: ../index.php');
}

include_once 'skeleton.php';

// Get the forecasted expenses for each category
$forecastedExpenses = $getFromE->holtWintersForecastByCategory($_SESSION['UserId'], 3);

// Debug: Log forecasted expenses
error_log("Forecasted Expenses: " . json_encode($forecastedExpenses));

// Get the last 12 months of actual expenses for comparison
$lastTwelveMonths = $getFromE->getLastTwelveMonthsExpensesByCategory($_SESSION['UserId']);

// Debug: Log last twelve months data
error_log("Last Twelve Months Data: " . json_encode($lastTwelveMonths));

// Prepare data for JavaScript
$categories = array_keys($forecastedExpenses);
$lastTwelveMonthsData = [];
$forecastData = [];

// Generate an array of the last 12 months
$lastTwelveMonthsArray = [];
for ($i = 11; $i >= 0; $i--) {
    $date = new DateTime();
    $date->modify("-$i month");
    $lastTwelveMonthsArray[] = $date->format('Y-m');
}

foreach ($categories as $category) {
    $categoryData = array_fill_keys($lastTwelveMonthsArray, 0);
    foreach ($lastTwelveMonths as $expense) {
        if ($expense['Category'] == $category) {
            $yearMonth = $expense['year'] . '-' . str_pad($expense['month'], 2, '0', STR_PAD_LEFT);
            if (isset($categoryData[$yearMonth])) {
                $categoryData[$yearMonth] = floatval($expense['total']);
            }
        }
    }
    $lastTwelveMonthsData[$category] = array_values($categoryData);
    $forecastData[$category] = $forecastedExpenses[$category];
}

// Debug: Log prepared data
error_log("Prepared Last Twelve Months Data: " . json_encode($lastTwelveMonthsData));
error_log("Prepared Forecast Data: " . json_encode($forecastData));

$monthLabels = array_map(function ($ym) {
    return date('M Y', strtotime($ym . '-01'));
}, $lastTwelveMonthsArray);

// Add three more months for forecast
for ($i = 1; $i <= 3; $i++) {
    $monthLabels[] = date('M Y', strtotime("+$i month"));
}

// Prepare the data for JSON encoding
$chartData = [
    'categories' => $categories,
    'monthLabels' => $monthLabels,
    'lastTwelveMonthsData' => $lastTwelveMonthsData,
    'forecastData' => $forecastData
];

// JSON encode the data for use in JavaScript
$chartDataJSON = json_encode($chartData);

// Debug: Log final JSON data
error_log("Final Chart Data JSON: " . $chartDataJSON);
?>

<div class="wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line"></i>
                    <h3 style="font-family:'Source Sans Pro'; font-size: 1.5em;">Categorical Expense Forecast</h3>
                </div>
                <div class="card-content">
                    <div class="form-group">
                        <label for="categorySelect">Select Category:</label>
                        <select id="categorySelect" class="form-control">
                            <option value="all">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <canvas id="forecastChart" width="400" height="200"></canvas>
                    <div class="text-center mt-4">
                        <button id="toggleReport" class="btn btn-outline-primary">
                            <i class="fas fa-table"></i> Show Detailed Report
                        </button>
                    </div>
                    <div id="reportSection" style="display: none; margin-top: 20px;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Category</th>
                                        <?php
                                        // Get next 3 months for forecast
                                        for ($i = 1; $i <= 3; $i++) {
                                            echo '<th>' . date('M Y', strtotime("+$i month")) . '</th>';
                                        }
                                        ?>
                                        <th>Average</th>
                                    </tr>
                                </thead>
                                <tbody id="reportBody">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('forecastChart').getContext('2d');
            var chartData = <?php echo $chartDataJSON; ?>;
            var chart;

            function getRandomColor() {
                var letters = '0123456789ABCDEF';
                var color = '#';
                for (var i = 0; i < 6; i++) {
                    color += letters[Math.floor(Math.random() * 16)];
                }
                return color;
            }

            function updateChart(selectedCategory) {
                var datasets = [];
                var categoriesToShow = selectedCategory === 'all' ? chartData.categories : [selectedCategory];

                categoriesToShow.forEach(category => {
                    var color = getRandomColor();
                    var actualData = chartData.lastTwelveMonthsData[category];
                    var forecastData = new Array(12).fill(null).concat(chartData.forecastData[category]);

                    datasets.push({
                        label: category + ' (Actual)',
                        data: actualData,
                        borderColor: color,
                        backgroundColor: color + '4D',
                        tension: 0.1
                    });

                    datasets.push({
                        label: category + ' (Forecast)',
                        data: forecastData,
                        borderColor: color,
                        backgroundColor: color + '4D',
                        borderDash: [5, 5],
                        tension: 0.1
                    });
                });

                if (chart) {
                    chart.data.datasets = datasets;
                    chart.update();
                } else {
                    chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: chartData.monthLabels,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Expense Amount'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Month'
                                    }
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Expense Forecast by Category'
                                },
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }
            }

            // Toggle Report Functionality
            const toggleButton = document.getElementById('toggleReport');
            const reportSection = document.getElementById('reportSection');

            toggleButton.addEventListener('click', function() {
                const isHidden = reportSection.style.display === 'none';
                reportSection.style.display = isHidden ? 'block' : 'none';
                toggleButton.innerHTML = isHidden ?
                    '<i class="fas fa-table"></i> Hide Detailed Report' :
                    '<i class="fas fa-table"></i> Show Detailed Report';
                if (isHidden) {
                    updateReport(document.getElementById('categorySelect').value);
                }
            });

            function updateReport(selectedCategory) {
                const reportBody = document.getElementById('reportBody');
                reportBody.innerHTML = '';

                const categoriesToShow = selectedCategory === 'all' ?
                    chartData.categories : [selectedCategory];

                let grandTotal = [0, 0, 0];

                categoriesToShow.forEach(category => {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td>${category}</td>`;

                    const forecastValues = chartData.forecastData[category];
                    let categoryTotal = 0;

                    forecastValues.forEach((value, index) => {
                        categoryTotal += value;
                        grandTotal[index] += value;
                        row.innerHTML += `<td>${value.toLocaleString('en-US', {
                    style: 'currency',
                    currency: 'NPR'
                })}</td>`;
                    });

                    const average = categoryTotal / forecastValues.length;
                    row.innerHTML += `<td>${average.toLocaleString('en-US', {
                style: 'currency',
                currency: 'USD'
            })}</td>`;

                    reportBody.appendChild(row);
                });

                if (selectedCategory === 'all') {
                    const totalRow = document.createElement('tr');
                    totalRow.className = 'table-active font-weight-bold';
                    totalRow.innerHTML = '<td>Monthly Total</td>';

                    grandTotal.forEach(total => {
                        totalRow.innerHTML += `<td>${total.toLocaleString('en-US', {
                    style: 'currency',
                    currency: 'NPR'
                })}</td>`;
                    });

                    const totalAverage = grandTotal.reduce((a, b) => a + b) / grandTotal.length;
                    totalRow.innerHTML += `<td>${totalAverage.toLocaleString('en-US', {
                style: 'currency',
                currency: 'NPR'
            })}</td>`;

                    reportBody.appendChild(totalRow);
                }
            }

            // Category select event listener
            document.getElementById('categorySelect').addEventListener('change', function() {
                updateChart(this.value);
                if (reportSection.style.display !== 'none') {
                    updateReport(this.value);
                }
            });

            // Initial chart render
            updateChart('all');
        });
    </script>
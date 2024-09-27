<?php

include_once '../init.php';

// User login checker
if ($getFromU->loggedIn() === false) {
    header('Location: ../index.php');
}

include_once 'skeleton.php';

// Fetch last three months' expenses for the logged-in user
$userId = $_SESSION['UserId'];
$lastThreeMonthsExpenses = $getFromE->getLastThreeMonthsExpenses($userId);

// Extract total expenses for each month
$expensesArray = [];
foreach ($lastThreeMonthsExpenses as $expense) {
    $expensesArray[] = $expense->total;
}

// Calculate the 3-month moving average
$movingAverage = $getFromE->calculateMovingAverage($expensesArray, 3);

// Pass the data to JavaScript
?>
<script type="text/javascript">
    // PHP to JavaScript: Convert PHP arrays to JS arrays
    var expenses = <?php echo json_encode($expensesArray); ?>;
    var movingAvg = <?php echo json_encode($movingAverage); ?>;
</script>

<div class="wrapper">
    <h3>3-Month Moving Average of Your Expenses</h3>
    <canvas id="expenseChart" width="600" height="400"></canvas>
</div>

<script>
    // JavaScript to create the graph
    var canvas = document.getElementById("expenseChart");
    var ctx = canvas.getContext("2d");

    // Graph Dimensions
    var width = canvas.width;
    var height = canvas.height;
    var padding = 50;

    // Data Range
    var maxExpense = Math.max(...expenses, ...movingAvg);
    var minExpense = 0; // Assuming the lowest value is 0

    // Calculate scale
    function scaleValue(value, minData, maxData, minRange, maxRange) {
        return ((value - minData) / (maxData - minData)) * (maxRange - minRange) + minRange;
    }

    // Draw Line
    function drawLine(data, color) {
        ctx.beginPath();
        ctx.strokeStyle = color;
        for (var i = 0; i < data.length; i++) {
            var x = padding + i * ((width - 2 * padding) / (data.length - 1));
            var y = height - padding - scaleValue(data[i], minExpense, maxExpense, 0, height - 2 * padding);
            if (i === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        }
        ctx.stroke();
    }

    // Draw the graph (lines)
    function drawGraph() {
        // Draw background grid
        ctx.clearRect(0, 0, width, height);
        ctx.strokeStyle = "#ddd";
        ctx.lineWidth = 1;
        for (var i = 0; i <= 5; i++) {
            var y = padding + (i * (height - 2 * padding)) / 5;
            ctx.beginPath();
            ctx.moveTo(padding, y);
            ctx.lineTo(width - padding, y);
            ctx.stroke();
        }

        // Draw expenses
        drawLine(expenses, "blue");

        // Draw moving average
        drawLine(movingAvg, "red");

        // Add labels
        ctx.fillStyle = "black";
        ctx.font = "12px Arial";
        for (var i = 0; i <= 5; i++) {
            var y = height - padding - (i * (height - 2 * padding)) / 5;
            var label = (maxExpense / 5) * i;
            ctx.fillText(label.toFixed(2), 10, y + 4);
        }

        // X-axis labels (Month 1, 2, 3)
        var labels = ['Month 1', 'Month 2', 'Month 3'];
        for (var i = 0; i < labels.length; i++) {
            var x = padding + i * ((width - 2 * padding) / (labels.length - 1));
            ctx.fillText(labels[i], x - 20, height - padding + 20);
        }
    }

    drawGraph();
</script>
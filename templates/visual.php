<?php

include_once "../init.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// User login checker
if ($getFromU->loggedIn() === false) {
    header('Location: ../index.php');
}

// Include skeleton if necessary (optional)
include_once 'skeleton.php';

// Bubble Sort Function
function bubbleSort(&$data)
{
    $n = count($data);
    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($data[$j]->total < $data[$j + 1]->total) { // Sort by total descending
                // Swap
                $temp = $data[$j];
                $data[$j] = $data[$j + 1];
                $data[$j + 1] = $temp;
            }
        }
    }
}

// Fetch daily or monthly expenses based on a parameter
$data = [];
$view = isset($_GET['view']) ? $_GET['view'] : '';

if ($view === 'daily') {
    $specific_date = date('Y-m-d'); // Example: Today
    $data = $getFromE->dailyExpenses($_SESSION['UserId'], $specific_date);
} else if ($view === 'monthly' && isset($_GET['month']) && isset($_GET['year'])) {
    $selected_month = $_GET['month'];
    $selected_year = $_GET['year'];
    $data = $getFromE->monthlyExpenses($_SESSION['UserId'], $selected_month, $selected_year);
}

bubbleSort($data);
?>

<div class="wrapper">
    <h2>Expense Visualization</h2>

    <?php if ($view === 'monthly'): ?>
        <form action="" method="get">
            <input type="hidden" name="view" value="monthly">
            <label for="month">Select Month:</label>
            <select name="month" id="month" required>
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    echo "<option value='$m'>" . date('F', mktime(0, 0, 0, $m, 1)) . "</option>";
                }
                ?>
            </select>

            <label for="year">Select Year:</label>
            <select name="year" id="year" required>
                <?php
                $currentYear = date("Y");
                for ($y = $currentYear - 5; $y <= $currentYear; $y++) {
                    echo "<option value='$y'>$y</option>";
                }
                ?>
            </select>

            <button type="submit">View Monthly Expenses</button>
        </form>
    <?php endif; ?>

    <div id="visualization"></div>

    <script>
        const data = <?php echo json_encode($data); ?>;

        if (data.length > 0) {
            visualizeExpenses(data);
        } else {
            console.log("No expenses found for the selected month.");
        }

        function visualizeExpenses(data) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = 800;
            canvas.height = 400;
            document.getElementById('visualization').appendChild(canvas);

            const categories = [...new Set(data.map(exp => exp.Category))];
            const totals = categories.map(category => {
                return data
                    .filter(exp => exp.Category === category)
                    .reduce((sum, exp) => sum + parseFloat(exp.total), 0);
            });

            const barWidth = canvas.width / categories.length;
            const colors = ['blue', 'green', 'orange', 'red', 'purple'];
            categories.forEacsh((category, index) => {
                ctx.fillStyle = colors[index % colors.length]; // Set color for each category
                ctx.fillRect(index * barWidth, canvas.height - totals[index], barWidth - 10, totals[index]);
                ctx.fillStyle = 'black'; // Color for the text
                ctx.fillText(category, index * barWidth + 10, canvas.height - 5);
            });
        }
    </script>

    <a href="visual.php?view=daily&rand=<?php echo time(); ?>">View Daily Expenses</a>
    <a href="visual.php?view=monthly&rand=<?php echo time(); ?>">View Monthly Expenses</a>
</div>
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
if (isset($_GET['view'])) {
    $view = $_GET['view'];

    if ($view === 'daily') {
        $specific_date = date('Y-m-d'); // Example: Today
        $data = $getFromE->dailyExpenses($_SESSION['UserId'], $specific_date);
    } else if ($view === 'monthly') {
        $specific_month = date('m'); // Example: Current month
        $data = $getFromE->monthlyExpenses($_SESSION['UserId'], $specific_month);
    }
}
bubbleSort($data);
?>

<div class="wrapper">
    <h2>Expense Visualization</h2>
    <div id="visualization"></div>
    <script>
        const data = <?php echo json_encode($data); ?>;

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
            categories.forEach((category, index) => {
                ctx.fillStyle = 'blue'; // Color for the bars
                ctx.fillRect(index * barWidth, canvas.height - totals[index], barWidth - 10, totals[index]);
                ctx.fillStyle = 'black';
                ctx.fillText(category, index * barWidth + 10, canvas.height - 5);
            });
        }

        visualizeExpenses(data);
        var_dump($data);
    </script>

    <a href="visual.php?view=daily&rand=<?php echo time(); ?>">View Daily Expenses</a>
    <a href="visual.php?view=monthly&rand=<?php echo time(); ?>">View Monthly Expenses</a>

</div>
<?php
include_once "../init.php";

$pic = $getFromU->Photofetch($_SESSION['UserId']);
$pic = '"' . $pic . '"';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../static/images/expenseic.png" sizes="16x16" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css">
    <link rel="stylesheet" href="../static/css/skeleton.css">
    <link rel="stylesheet" href="../static/css/changepass.css">
    <link rel="stylesheet" href="../static/css/7-Datewise.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../static/css/yearpicker.css">
    <link rel="stylesheet" href="../static/css/Manage-Expenses.css">
    <script src="../static/js/jquery-3.3.1.slim.min.js"></script>
    <script src="../static/js/yearpicker.js" async></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <title>Personal Finance Management</title>

</head>

<body class="overlay-scrollbar sidebar-expand">
    <!-- Navbar -->
    <div class="navbar">
        <!-- nav-left -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class='nav-link'>
                    <i class="fas fa-bars" onclick="collapseSidebar()"></i>
                </a>
            </li>
            <li class="nav-item">
                <img src="../static/images/piggy.png" alt="" class="logo">
            </li>
        </ul>

        <!-- end nav left  -->
        <h1 class="navbar-text">Personal Finance Management System</h1>
        <!-- nav right  -->
        <ul class="navbar-nav nav-right">
            <!-- <li class="nav-item">
                <a class="nav-link" href="#" onclick = "switchTheme()">
                    <i class="fas fa-moon dark-icon"></i>
                    <i class="fas fa-sun light-icon"></i>
                </a>
            </li> -->
            <li class="nav-item">
                <div class="avt dropdown">
                    <img src=<?php echo $pic ?> alt="" class="dropdown-toggle" data-toggle="user-menu">
                    <ul id="user-menu" class="dropdown-menu">
                        <li class="dropdown-menu-item">
                            <a href="Profile.php" class="dropdown-menu-link">
                                <div>
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <span>Profile</span>
                            </a>
                        </li>
                        <li class="dropdown-menu-item">
                            <a href="logout.php" class="dropdown-menu-link">
                                <div>
                                    <i class="fas fa-sign-out-alt"></i>
                                </div>
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
    <!-- end navbar -->
    <!-- sidebar -->
    <div class="sidebar">
        <ul class="sidebar-nav" id="sidebar-nav">
            <li class="sidebar-nav-item">
                <a href="Dashboard.php" class="sidebar-nav-link">
                    <div>
                        <i class="fas fa-home"></i>
                    </div>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="sidebar-nav-item" id="Income" onclick="toggleDropdown('incomeDropdown')">
                <a href="#" class="sidebar-nav-link">
                    <div>
                        <i class="fa fa-plus-circle"></i>
                    </div>
                    <span>Income</span>
                </a>
                <ul id="incomeDropdown" class="dropdown-content" style="display: none;">
                    <li class="sidebar-nav-item">
                        <a href="add-income.php" class="sidebar-nav-link">
                            <div>
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <span>Add Income</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="manage-income.php" class="sidebar-nav-link">
                            <div>
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <span>Manage Income</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-nav-item" id="Expense" onclick="toggleDropdown('expenseDropdown')">
                <a href="#" class="sidebar-nav-link">
                    <div>
                        <i class="fa fa-minus-circle "></i>
                    </div>
                    <span>Expenses</span>
                </a>
                <ul id="expenseDropdown" class="dropdown-content" style="display: none;">
                    <li class="sidebar-nav-item">
                        <a href="Add-Expenses.php" class="sidebar-nav-link">
                            <div>
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <span>Add Expenses</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="Manage-Expenses.php" class="sidebar-nav-link">
                            <div>
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <span>Manage Expenses</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!-- <li><a href="expense_forecast.php"><i class="fas fa-chart-line"></i> Expense Forecast</a></li>
            <li><a href="budget_suggestion.php"><i class="fas fa-chart-line"></i> budget Forecast</a></li> -->
            <li class="sidebar-nav-item" id="ER" onclick="toggleDropdown('reportDropdown')">
                <a href="#" class="sidebar-nav-link">
                    <div>
                        <i class="fas fa-line-chart"></i>
                    </div>
                    <span>Forecasting Report</span>
                </a>
                <ul id="reportDropdown" class="dropdown-content" style="display: none;">
                    <li class="sidebar-nav-item">
                        <a href="c_forecast.php" class="sidebar-nav-link">
                            <div>
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <span>Expense Report</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    <!-- end sidebar-->
    <!-- Main Content -->
    <!-- end main content -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
    <script src="../static/js/skeleton.js"></script>
</body>
<?php
class Expense extends Base
{
  function __construct($pdo)
  {
    $this->pdo = $pdo;
  }
  public function allIncome($userId)
  {
    $stmt = $this->pdo->prepare("SELECT * FROM Income WHERE UserId = :userId");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ); // Fetch all records as objects
  }

  // Update Expense Function
  public function updateExpense($id, $category, $cost, $date)
  {
    $stmt = $this->pdo->prepare("UPDATE expense SET Category = :Category, Cost = :Cost, Date = :Date WHERE ID = :ID");
    $stmt->bindParam(":Category", $category, PDO::PARAM_STR);
    $stmt->bindParam(":Cost", $cost, PDO::PARAM_STR);
    $stmt->bindParam(":Date", $date, PDO::PARAM_STR);
    $stmt->bindParam(":ID", $id, PDO::PARAM_INT);
    $stmt->execute();
  }

  public function forecastExpenses($userId, $periodsToForecast = 3)
  {
    // Fetch the last 12 months of expenses
    $stmt = $this->pdo->prepare("
          SELECT YEAR(Date) as year, MONTH(Date) as month, SUM(Cost) as total
          FROM expense 
          WHERE UserId = :userId 
          AND Date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
          GROUP BY YEAR(Date), MONTH(Date)
          ORDER BY YEAR(Date), MONTH(Date)
      ");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Extract just the expense values
    $expenseValues = array_column($expenses, 'total');

    // Calculate moving average
    $movingAveragePeriods = 3;
    $movingAverages = $this->calculateMovingAverage($expenseValues, $movingAveragePeriods);

    // Calculate the average change in moving averages
    $changes = [];
    for ($i = 1; $i < count($movingAverages); $i++) {
      $changes[] = $movingAverages[$i] - $movingAverages[$i - 1];
    }
    $avgChange = array_sum($changes) / count($changes);

    // Forecast future expenses
    $forecast = [];
    $lastMA = end($movingAverages);
    for ($i = 0; $i < $periodsToForecast; $i++) {
      $forecast[] = $lastMA + ($avgChange * ($i + 1));
    }

    return $forecast;
  }


  public function getLastTwelveMonthsExpenses($userId)
  {
    $stmt = $this->pdo->prepare("
          SELECT YEAR(Date) as year, MONTH(Date) as month, SUM(Cost) as total
          FROM expense 
          WHERE UserId = :userId 
          AND Date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
          GROUP BY YEAR(Date), MONTH(Date)
          ORDER BY YEAR(Date), MONTH(Date)
      ");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Fetch monthly expense data for the last few months
  public function getLastThreeMonthsExpenses($userId)
  {
    // Fetch the last 3 months' expenses
    $stmt = $this->pdo->prepare("
        SELECT MONTH(Date) as expense_month, SUM(Cost) as total
        FROM expense 
        WHERE UserId = :UserId 
        AND Date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) 
        GROUP BY expense_month
        ORDER BY expense_month
    ");
    $stmt->bindParam(":UserId", $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
  }
  // Returns daily expenses by category
  public function dailyExpenses($UserId, $specific_date)
  {
    $stmt = $this->pdo->prepare("SELECT DATE(Date) as expense_date, Category, SUM(Cost) as total 
                                FROM expense WHERE UserId = :UserId AND DATE(Date) = :specific_date 
                                GROUP BY expense_date, Category");
    $stmt->bindParam(":UserId", $UserId, PDO::PARAM_INT);
    $stmt->bindParam(":specific_date", $specific_date, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
  }

  // Returns monthly expenses by category
  public function monthlyExpenses($UserId, $specific_month, $specific_year)
  {
    $stmt = $this->pdo->prepare("SELECT MONTH(Date) as expense_month, Category, SUM(Cost) as total 
                                  FROM expense 
                                  WHERE UserId = :UserId 
                                  AND MONTH(Date) = :specific_month 
                                  AND YEAR(Date) = :specific_year 
                                  GROUP BY expense_month, Category");
    $stmt->bindParam(":UserId", $UserId, PDO::PARAM_INT);
    $stmt->bindParam(":specific_month", $specific_month, PDO::PARAM_INT);
    $stmt->bindParam(":specific_year", $specific_year, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
  }

  // Returns total expense amount till date
  public function totalexp($UserId)
  {
    $stmt = $this->pdo->prepare("SELECT SUM(Cost) AS TOTAL FROM expense WHERE UserId = :UserId");
    $stmt->bindParam(":UserId", $UserId, PDO::PARAM_INT);
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_OBJ);
    if ($total == NULL) {
      return NULL;
    } else
      return $total->TOTAL;
  }

  public function getTotalExpense($userId)
  {
    $stmt = $this->pdo->prepare("SELECT SUM(cost) AS total FROM expense WHERE UserId = :UserId");
    $stmt->execute(['UserId' => $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?: 0; // Return 0 if no records found
  }
  // Expenses of Current Month(Datewise)
  public function Current_month_expenses($UserId)
  {
    $stmt = $this->pdo->prepare("SELECT EXTRACT(MONTH FROM CURRENT_TIMESTAMP()) AS CurrentMonth");
    $stmt->execute();
    $rows1 = $stmt->fetch(PDO::FETCH_OBJ);
    $val = $rows1->CurrentMonth;

    $stmt = $this->pdo->prepare("SELECT SUM(Cost) AS exp1 FROM expense WHERE UserId = :UserId AND MONTH(Date) = :currmon");
    $stmt->bindParam(":UserId", $UserId, PDO::PARAM_INT);
    $stmt->bindParam(":currmon", $val, PDO::PARAM_INT);
    $stmt->execute();
    $rows2 = $stmt->fetch(PDO::FETCH_OBJ);
    if ($rows2 == NULL) {
      return NULL;
    } else {
      return $rows2->exp1;
    }
  }





  // Returns all rows from expense table
  public function allexp($UserId)
  {
    $stmt = $this->pdo->prepare("SELECT * FROM expense WHERE UserId = :UserId ORDER BY Date");
    $stmt->bindParam(":UserId", $UserId, PDO::PARAM_INT);
    $stmt->execute();
    $total = $stmt->fetchall(PDO::FETCH_OBJ);
    if ($total == NULL) {
      return NULL;
    } else
      return $total;
  }

  // Returns a particular expense record(with given expense id)
  public function delexp($ID)
  {
    $stmt = $this->pdo->prepare("DELETE FROM expense WHERE ID = :id");
    $stmt->bindParam(":id", $ID, PDO::PARAM_INT);
    $stmt->execute();
  }
  public function arimaForecast($userId, $periodsToForecast = 3)
  {
    // Fetch the last 24 months of expenses (we need more data for ARIMA)
    $stmt = $this->pdo->prepare("
          SELECT YEAR(Date) as year, MONTH(Date) as month, SUM(Cost) as total
          FROM expense 
          WHERE UserId = :userId 
          AND Date >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
          GROUP BY YEAR(Date), MONTH(Date)
          ORDER BY YEAR(Date), MONTH(Date)
      ");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Extract just the expense values
    $expenseValues = array_column($expenses, 'total');

    // Implement ARIMA(1,1,1) model
    $differenced = $this->difference($expenseValues);
    $phi = $this->estimateAR($differenced);
    $theta = $this->estimateMA($differenced);

    // Forecast
    $forecast = [];
    $lastValue = end($expenseValues);
    $lastDiff = end($differenced);
    for ($i = 0; $i < $periodsToForecast; $i++) {
      $prediction = $lastValue + $phi * $lastDiff + $theta * ($lastDiff - $phi * ($i > 0 ? $differenced[count($differenced) - 2] : 0));
      $forecast[] = $prediction;
      $lastValue = $prediction;
      $lastDiff = $prediction - $lastValue;
    }

    return $forecast;
  }

  private function difference($series)
  {
    $diff = [];
    for ($i = 1; $i < count($series); $i++) {
      $diff[] = $series[$i] - $series[$i - 1];
    }
    return $diff;
  }

  private function estimateAR($series)
  {
    // Simple AR(1) coefficient estimation
    $n = count($series);
    $mean = array_sum($series) / $n;
    $numerator = 0;
    $denominator = 0;
    for ($i = 1; $i < $n; $i++) {
      $numerator += ($series[$i] - $mean) * ($series[$i - 1] - $mean);
      $denominator += pow($series[$i - 1] - $mean, 2);
    }
    return $numerator / $denominator;
  }

  private function estimateMA($series)
  {
    // Simple MA(1) coefficient estimation
    // This is a very basic approximation
    $n = count($series);
    $sum = 0;
    for ($i = 1; $i < $n; $i++) {
      $sum += $series[$i] * $series[$i - 1];
    }
    return $sum / (($n - 1) * array_sum(array_map('pow', $series, array_fill(0, $n, 2))));
  }
  public function forecastBudget($userId, $category, $months = 6)
  {
    // Fetch historical expense data
    $stmt = $this->pdo->prepare("
          SELECT YEAR(Date) as year, MONTH(Date) as month, SUM(Cost) as total
          FROM expense 
          WHERE UserId = :userId AND Category = :category
          AND Date >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
          GROUP BY YEAR(Date), MONTH(Date)
          ORDER BY YEAR(Date), MONTH(Date)
      ");
    $stmt->execute(['userId' => $userId, 'category' => $category]);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Extract expense values
    $values = array_column($expenses, 'total');

    // Calculate trend using simple moving average
    $trendPeriod = 3;
    $trend = $this->calculateMovingAverage($values, $trendPeriod);

    // Calculate seasonality
    $seasonality = $this->calculateSeasonality($values);

    // Forecast future values
    $forecast = $this->forecastValues($values, $trend, $seasonality, $months);

    return $forecast;
  }

  private function calculateMovingAverage($values, $period)
  {
    $result = [];
    for ($i = 0; $i < count($values) - $period + 1; $i++) {
      $result[] = array_sum(array_slice($values, $i, $period)) / $period;
    }
    return $result;
  }

  private function calculateSeasonality($values)
  {
    $seasons = 12; // Assuming monthly data
    $seasonalAvg = array_fill(0, $seasons, 0);
    $seasonalCount = array_fill(0, $seasons, 0);

    for ($i = 0; $i < count($values); $i++) {
      $season = $i % $seasons;
      $seasonalAvg[$season] += $values[$i];
      $seasonalCount[$season]++;
    }

    for ($i = 0; $i < $seasons; $i++) {
      if ($seasonalCount[$i] > 0) {
        $seasonalAvg[$i] /= $seasonalCount[$i];
      }
    }

    $totalAvg = array_sum($seasonalAvg) / $seasons;
    $seasonalIndices = [];
    for ($i = 0; $i < $seasons; $i++) {
      $seasonalIndices[$i] = $seasonalAvg[$i] / $totalAvg;
    }

    return $seasonalIndices;
  }

  private function forecastValues($values, $trend, $seasonality, $months)
  {
    $forecast = [];
    $seasons = count($seasonality);
    $lastValue = end($values);
    $lastTrend = end($trend);

    for ($i = 0; $i < $months; $i++) {
      $forecastValue = ($lastValue + $lastTrend) * $seasonality[$i % $seasons];
      $forecast[] = max(0, round($forecastValue, 2)); // Ensure non-negative values
      $lastValue = $forecastValue;
    }

    return $forecast;
  }

  public function suggestBudgetWithForecast($userId, $months = 6)
  {
    $categories = $this->getExpenseCategories($userId);
    $budgetSuggestion = [];

    foreach ($categories as $category) {
      $forecast = $this->forecastBudget($userId, $category, $months);
      $averageForecast = array_sum($forecast) / count($forecast);
      $budgetSuggestion[$category] = ceil($averageForecast); // Round up to nearest integer
    }

    return $budgetSuggestion;
  }

  private function getExpenseCategories($userId)
  {
    $stmt = $this->pdo->prepare("
          SELECT DISTINCT Category
          FROM expense
          WHERE UserId = :userId
      ");
    $stmt->execute(['userId' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }
}

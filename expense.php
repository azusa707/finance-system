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

  private function calculateMovingAverage($expenses, $periods)
  {
    $movingAverages = [];
    $total = array_sum(array_slice($expenses, 0, $periods));
    $movingAverages[] = $total / $periods;

    for ($i = $periods; $i < count($expenses); $i++) {
      $total = $total - $expenses[$i - $periods] + $expenses[$i];
      $movingAverages[] = $total / $periods;
    }

    return $movingAverages;
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
}

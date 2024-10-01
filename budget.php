<?php

class Budget extends Base
{
  function __construct($pdo)
  {
    $this->pdo = $pdo;
  }

  public function setBudget($userId, $category, $amount, $date)
  {
    $stmt = $this->pdo->prepare("INSERT INTO budgets (UserId, Category, Amount, Date) VALUES (:userId, :category, :amount, :date)");
    return $stmt->execute([
      'userId' => $userId,
      'category' => $category,
      'amount' => $amount,
      'date' => $date
    ]);
  }

  public function getCurrentBudgets($userId)
  {
    $stmt = $this->pdo->prepare("
            SELECT * FROM budgets 
            WHERE UserId = :userId 
            AND Date = (SELECT MAX(Date) FROM budgets WHERE UserId = :userId2 AND Category = budgets.Category)
            ORDER BY Date DESC, Category
        ");
    $stmt->execute(['userId' => $userId, 'userId2' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_OBJ);
  }
  public function getCurrentBudgetForCategory($userId, $category)
  {
    $stmt = $this->pdo->prepare("
        SELECT Amount FROM budgets 
        WHERE UserId = :userId AND Category = :category
        ORDER BY Date DESC LIMIT 1
    ");
    $stmt->execute(['userId' => $userId, 'category' => $category]);
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    return $result ? $result->Amount : 0;
  }
  public function getRemainingBudget($userId, $category)
  {
    $budget = $this->getCurrentBudgetForCategory($userId, $category);

    // Get total expenses for this category this month
    $stmt = $this->pdo->prepare("
        SELECT SUM(Cost) as total FROM expense 
        WHERE UserId = :userId AND Category = :category
        AND MONTH(Date) = MONTH(CURRENT_DATE()) AND YEAR(Date) = YEAR(CURRENT_DATE())
    ");
    $stmt->execute(['userId' => $userId, 'category' => $category]);
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    $expenses = $result ? $result->total : 0;

    return $budget - $expenses;
  }
}

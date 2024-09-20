<?php
class Income extends Base
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
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function addIncome($userId, $amount, $date)
    {
        $stmt = $this->pdo->prepare("INSERT INTO Income (UserId, Amount, Date) VALUES (:userId, :amount, :date)");
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':date', $date);
        return $stmt->execute();
    }


    public function updateIncome($id, $amount, $date)
    {
        $stmt = $this->pdo->prepare("UPDATE income SET amount = ?, date = ? WHERE id = ?");
        return $stmt->execute([$amount, $date, $id]);
    }

    // Method to delete an income record
    public function deleteIncome($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM income WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getTotalIncome($userId)
    {
        $stmt = $this->pdo->prepare("SELECT SUM(amount) AS total FROM income WHERE Userid = :UserId");
        $stmt->execute(['UserId' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?: 0;  // Return 0 if no income
    }
}

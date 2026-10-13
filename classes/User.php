<?php
class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($username, $email, $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $hashed]);
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateBalance($userId, $coinsDelta, $realDelta = 0) {
        $stmt = $this->db->prepare("UPDATE users SET coins_balance = coins_balance + ?, real_balance = real_balance + ? WHERE id = ?");
        return $stmt->execute([$coinsDelta, $realDelta, $userId]);
    }

    public function addCoins($userId, $amount) {
        return $this->updateBalance($userId, $amount, 0);
    }

    public function exchangeCoinsToMoney($userId, $coinsAmount, $rate = 10) {
        if ($coinsAmount <= 0) return false;
        $real = $coinsAmount / $rate;
        $stmt = $this->db->prepare("UPDATE users SET coins_balance = coins_balance - ?, real_balance = real_balance + ? WHERE id = ? AND coins_balance >= ?");
        return $stmt->execute([$coinsAmount, $real, $userId, $coinsAmount]);
    }

    public function updateAvatar($userId, $avatarPath) {
        $stmt = $this->db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        return $stmt->execute([$avatarPath, $userId]);
    }
}
?>
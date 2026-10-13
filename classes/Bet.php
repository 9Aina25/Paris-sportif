<?php
class Bet {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function placeBet($userId, $matchId, $betType, $amount, $predictedScore = null) {
        $stmt = $this->db->prepare("INSERT INTO bets (user_id, match_id, bet_type, amount, predicted_score) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$userId, $matchId, $betType, $amount, $predictedScore]);
    }

    public function getBetsByMatch($matchId) {
        $stmt = $this->db->prepare("SELECT * FROM bets WHERE match_id = ?");
        $stmt->execute([$matchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function settleBet($betId, $status, $payout) {
        $stmt = $this->db->prepare("UPDATE bets SET status=?, payout=? WHERE id=?");
        return $stmt->execute([$status, $payout, $betId]);
    }

    public function getUserBets($userId) {
        $stmt = $this->db->prepare("SELECT b.*, m.title FROM bets b JOIN matches m ON b.match_id = m.id WHERE b.user_id = ? ORDER BY b.id DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
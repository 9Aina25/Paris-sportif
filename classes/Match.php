<?php
class Match {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($title, $desc, $date, $type, $predictedScore = null, $createdBy = 1) {
        $stmt = $this->db->prepare("INSERT INTO matches (title, description, match_date, result_type, predicted_score, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$title, $desc, $date, $type, $predictedScore, $createdBy]);
    }

    public function getAllPending() {
        $stmt = $this->db->prepare("SELECT * FROM matches WHERE status = 'pending' ORDER BY match_date ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM matches ORDER BY match_date DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM matches WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM matches WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function resolveMatch($id, $actualResult, $actualScore = null) {
        // Mise à jour du match
        $stmt = $this->db->prepare("UPDATE matches SET status='resolved', actual_result=?, actual_score=? WHERE id=?");
        $stmt->execute([$actualResult, $actualScore, $id]);

        // Traiter les paris associés
        $betObj = new Bet($this->db);
        $bets = $betObj->getBetsByMatch($id);
        $match = $this->getById($id);

        foreach ($bets as $bet) {
            $won = false;
            $payout = 0;

            if ($match['result_type'] == 'classic') {
                if ($bet['bet_type'] == $actualResult) {
                    $won = true;
                    if ($actualResult == 'win') $payout = $bet['amount'] * 2;
                    elseif ($actualResult == 'neutral') $payout = $bet['amount'] * 1;
                    else $payout = 0;
                }
            } else { // score exact
                if ($bet['predicted_score'] == $actualScore) {
                    $won = true;
                    $payout = $bet['amount'] * 4;
                }
            }

            if ($won && $payout > 0) {
                $betObj->settleBet($bet['id'], 'won', $payout);
                $userObj = new User($this->db);
                $userObj->updateBalance($bet['user_id'], $payout);
            } else {
                $betObj->settleBet($bet['id'], 'lost', 0);
            }
        }
        return true;
    }
}
?>
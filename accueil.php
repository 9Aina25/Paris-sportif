<?php session_start();
if(!isset($_SESSION['user_id'])) header('Location: index.php');
require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Match.php';
require_once 'classes/Bet.php';
$db = Database::getInstance()->getConnection();
$user = new User($db);
$current = $user->getUserById($_SESSION['user_id']);
$matchObj = new Match($db);
$betObj = new Bet($db);
$matches = $matchObj->getAllPending();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_bet'])) {
    $matchId = $_POST['match_id'];
    $betType = $_POST['bet_type'];
    $amount = floatval($_POST['amount']);
    $predicted = $_POST['predicted_score'] ?? null;
    if ($amount <= 0 || $amount > $current['coins_balance']) $msg = "Montant invalide ou solde insuffisant";
    else {
        $betObj->placeBet($_SESSION['user_id'], $matchId, $betType, $amount, $predicted);
        $user->updateBalance($_SESSION['user_id'], -$amount);
        $msg = "Paris enregistré !";
        header("Location: accueil.php?msg=".urlencode($msg));
        exit;
    }
}
?>
<!DOCTYPE html>
<html><head><title>Paris - PariZone</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
<h2>⚽ Matchs ouverts aux paris</h2>
<?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
<?php if(count($matches)==0) echo "<p>Aucun match disponible pour le moment.</p>"; ?>
<?php foreach($matches as $m): ?>
<div class="bet-card">
<h3><?= htmlspecialchars($m['title']) ?></h3>
<p><?= htmlspecialchars($m['description']) ?> - <?= date('d/m/Y H:i', strtotime($m['match_date'])) ?></p>
<?php if($m['result_type']=='classic'): ?>
<form method="post">
<input type="hidden" name="match_id" value="<?= $m['id'] ?>">
<select name="bet_type" required>
<option value="win">🏆 Victoire (x2)</option>
<option value="loss">💀 Défaite (x0)</option>
<option value="neutral">🤝 Nul (x1)</option>
</select>
<input type="number" step="0.01" name="amount" placeholder="Montant (coins)" required>
<button type="submit" name="place_bet" class="btn btn-primary">Parier</button>
</form>
<?php else: ?>
<form method="post">
<input type="hidden" name="match_id" value="<?= $m['id'] ?>">
<label>Prédisez le score exact (ex: 2-1) :</label>
<input type="text" name="predicted_score" placeholder="0-0, 2-1..." required>
<input type="number" step="0.01" name="amount" placeholder="Montant (coins)" required>
<input type="hidden" name="bet_type" value="score">
<button type="submit" name="place_bet" class="btn btn-primary">Parier score (x4)</button>
</form>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</body></html>
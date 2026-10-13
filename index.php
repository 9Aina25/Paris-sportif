<?php
session_start();
if(!isset($_SESSION['user_id'])) header('Location: index.php');
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Match.php';
require_once 'classes/Bet.php';

$db = Database::getInstance()->getConnection();
$user = new User($db);
$matchObj = new Match($db);
$betObj = new Bet($db);
$current = $user->getUserById($_SESSION['user_id']);
$matches = $matchObj->getAllPending();
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_bet'])) {
    $matchId = $_POST['match_id'];
    $betType = $_POST['bet_type'];
    $amount = floatval($_POST['amount']);
    $predicted = isset($_POST['predicted_score']) ? $_POST['predicted_score'] : null;
    
    if ($amount <= 0 || $amount > $current['coins_balance']) {
        $msg = "Montant invalide ou solde coins insuffisant";
    } else {
        $betObj->placeBet($_SESSION['user_id'], $matchId, $betType, $amount, $predicted);
        $user->updateBalance($_SESSION['user_id'], -$amount);
        $msg = "Paris placé avec succès !";
        header("Location: accueil.php?msg=".urlencode($msg));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Paris</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:system-ui; }
        body { background:#0b1a2e; color:#eef4ff; }
        .header { background:#0a1424; padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; border-bottom:1px solid #2c3e66; }
        .logo { font-size:1.8rem; font-weight:bold; background:linear-gradient(135deg,#ffd966,#ffb347); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .nav a { color:#eef4ff; text-decoration:none; margin:0 0.8rem; }
        .user-badge { background:#1e2a3a; padding:0.3rem 1rem; border-radius:40px; }
        .container { max-width:1200px; margin:2rem auto; padding:0 2rem; }
        .bet-card { background:#0e1a28; border-radius:24px; padding:1.5rem; margin-bottom:1.5rem; border-left:6px solid #ffb347; }
        input, select { background:#1e2a3a; border:1px solid #3a4c6e; padding:0.6rem; border-radius:40px; color:white; width:100%; margin:0.5rem 0; }
        button { background:#ff8c42; border:none; padding:0.7rem 1.5rem; border-radius:40px; font-weight:bold; cursor:pointer; }
        .alert { background:#14532d; padding:0.7rem; border-radius:40px; margin-bottom:1rem; }
    </style>
</head>
<body>
<div class="header">
    <div class="logo">PariZone</div>
    <div class="nav">
        <a href="accueil.php">🏠 Paris</a>
        <a href="boutique.php">💰 Boutique</a>
        <a href="compte.php">👤 Compte</a>
        <a href="logout.php">🚪 Déconnexion</a>
    </div>
    <div class="user-badge">🪙 <?= number_format($current['coins_balance'],2) ?> coins</div>
</div>
<div class="container">
    <h2>⚽ Matchs ouverts aux paris</h2>
    <?php if($msg): ?><div class="alert"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if(empty($matches)): ?><p>Aucun match disponible pour le moment. Revenez plus tard.</p><?php endif; ?>
    <?php foreach($matches as $m): ?>
    <div class="bet-card">
        <h3><?= htmlspecialchars($m['title']) ?></h3>
        <p><?= htmlspecialchars($m['description']) ?> - <strong><?= date('d/m/Y H:i', strtotime($m['match_date'])) ?></strong></p>
        <?php if($m['result_type'] == 'classic'): ?>
        <form method="post">
            <input type="hidden" name="match_id" value="<?= $m['id'] ?>">
            <select name="bet_type" required>
                <option value="win">🏆 Victoire (gain x2)</option>
                <option value="loss">💀 Défaite (perte)</option>
                <option value="neutral">🤝 Nul (gain x1)</option>
            </select>
            <input type="number" step="0.1" name="amount" placeholder="Mise (coins)" required>
            <button type="submit" name="place_bet">Parier</button>
        </form>
        <?php else: ?>
        <form method="post">
            <input type="hidden" name="match_id" value="<?= $m['id'] ?>">
            <input type="text" name="predicted_score" placeholder="Score exact (ex: 2-1)" required>
            <input type="number" step="0.1" name="amount" placeholder="Mise (coins)" required>
            <input type="hidden" name="bet_type" value="score">
            <button type="submit" name="place_bet">Parier score exact (x4)</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
</body>
</html>
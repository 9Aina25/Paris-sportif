<?php session_start();
if(!isset($_SESSION['user_id'])) header('Location: index.php');
require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Bet.php';
$db = Database::getInstance()->getConnection();
$user = new User($db);
$current = $user->getUserById($_SESSION['user_id']);
$betObj = new Bet($db);
$history = $betObj->getUserBets($_SESSION['user_id']);
$msg = '';

if($_SERVER['REQUEST_METHOD']=='POST' && isset($_FILES['avatar'])){
    $target = "uploads/".time()."_".basename($_FILES['avatar']['name']);
    move_uploaded_file($_FILES['avatar']['tmp_name'], $target);
    $user->updateAvatar($_SESSION['user_id'], $target);
    $msg="Avatar mis à jour";
}
?>
<!DOCTYPE html>
<html><head><title>Mon Compte - PariZone</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
<div class="flex-between"><h2>👤 <?= htmlspecialchars($current['username']) ?></h2>
<img src="<?= $current['avatar'] ?>" width="60" style="border-radius:50%"></div>
<p>🪙 Coins : <?= number_format($current['coins_balance'],2) ?> | 💶 Solde réel : <?= number_format($current['real_balance'],2) ?> €</p>
<form method="post" enctype="multipart/form-data">
<label>Changer avatar :</label> <input type="file" name="avatar"><button class="btn">Mettre à jour</button>
</form>
<h3>Historique des paris</h3>
<table width="100%" border="0" cellpadding="8">
<tr><th>Match</th><th>Mise</th><th>Type</th><th>Statut</th><th>Gain</th></tr>
<?php foreach($history as $h): ?>
<tr><td><?= $h['title'] ?></td><td><?= $h['amount'] ?> coins</td><td><?= $h['bet_type'] ?></td>
<td><?= $h['status'] ?></td><td><?= $h['payout'] ?? 0 ?> coins</td></tr>
<?php endforeach; ?>
</table>
</div>
</body></html>
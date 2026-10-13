<?php session_start();
if(!isset($_SESSION['user_id'])) header('Location: index.php');
require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
$db = Database::getInstance()->getConnection();
$user = new User($db);
$current = $user->getUserById($_SESSION['user_id']);
$msg = '';

if($_SERVER['REQUEST_METHOD']=='POST'){
    if(isset($_POST['buy_coins'])){
        $amount = floatval($_POST['amount']);
        $method = $_POST['method'];
        if($amount>=1){
            // Simulation de paiement
            $user->addCoins($_SESSION['user_id'], $amount);
            $msg = "Achat de $amount coins via $method réussi !";
        } else $msg = "Montant invalide";
    } elseif(isset($_POST['exchange'])){
        $coins = floatval($_POST['coins_exchange']);
        if($user->exchangeCoinsToMoney($_SESSION['user_id'], $coins))
            $msg = "Échange effectué : $coins coins -> ". ($coins/10) ."€";
        else $msg = "Solde coins insuffisant";
    }
    header("Location: boutique.php?msg=".urlencode($msg));
    exit;
}
?>
<!DOCTYPE html>
<html><head><title>Boutique - PariZone</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="container grid-2">
<div class="card">
<h3>💸 Acheter des Coins</h3>
<p>Méthodes : Mvola / OrangeMoney / AirtelMoney (simulation)</p>
<form method="post">
<input type="number" step="10" name="amount" placeholder="Montant (coins)" required>
<select name="method">
<option>Mvola</option><option>OrangeMoney</option><option>AirtelMoney</option>
</select>
<button type="submit" name="buy_coins" class="btn btn-primary">Payer et créditer</button>
</form>
</div>
<div class="card">
<h3>🔄 Échanger Coins → Argent réel</h3>
<p>10 coins = 1€ (ajouté à votre solde réel)</p>
<form method="post">
<input type="number" step="10" name="coins_exchange" placeholder="Coins à échanger" required>
<button type="submit" name="exchange" class="btn btn-success">Échanger</button>
</form>
</div>
</div>
</body></html>
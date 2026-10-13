<?php session_start();
if(!isset($_SESSION['admin_auth']) && (!isset($_SESSION['role']) || $_SESSION['role']!='admin')) die("Accès interdit");
require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/Match.php';
require_once 'classes/User.php';
$db = Database::getInstance()->getConnection();
$matchObj = new Match($db);
$userObj = new User($db);

if($_SERVER['REQUEST_METHOD']=='POST'){
    if(isset($_POST['create'])){
        $matchObj->create($_POST['title'],$_POST['desc'],$_POST['date'],$_POST['type'],$_POST['predicted_score']??null);
    } elseif(isset($_POST['delete'])){
        $matchObj->delete($_POST['id']);
    } elseif(isset($_POST['resolve'])){
        $matchObj->resolveMatch($_POST['id'], $_POST['actual_result'], $_POST['actual_score']??null);
    }
}
$allMatches = $matchObj->getAll();
?>
<!DOCTYPE html>
<html><head><title>Admin Dashboard</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
<h2>📋 Panneau Admin - Paris</h2>
<form method="post"><div class="card">
<h3>➕ Créer un match</h3>
<input type="text" name="title" placeholder="Titre (ex: PSG - OM)" required>
<textarea name="desc" placeholder="Description"></textarea>
<input type="datetime-local" name="date" required>
<select name="type"><option value="classic">Classique (Win/Loss/Neutre)</option><option value="score">Prédiction de Score (x4)</option></select>
<input type="text" name="predicted_score" placeholder="Score prédit (ex: 2-1) optionnel">
<button type="submit" name="create" class="btn btn-primary">Créer</button>
</div></form>

<h3>📌 Matchs existants</h3>
<?php foreach($allMatches as $m): ?>
<div class="bet-card">
<form method="post" style="display:inline">
<input type="hidden" name="id" value="<?= $m['id'] ?>">
<strong><?= htmlspecialchars($m['title']) ?></strong> - <?= $m['status'] ?> <br>
<?php if($m['status']=='pending'): ?>
<select name="actual_result">
<option value="win">Gagné</option><option value="loss">Perdu</option><option value="neutral">Nul</option>
</select>
<input type="text" name="actual_score" placeholder="Score exact si type score">
<button type="submit" name="resolve">Résoudre</button>
<?php endif; ?>
<button type="submit" name="delete" onclick="return confirm('Supprimer ?')">🗑️ Supprimer</button>
</form>
</div>
<?php endforeach; ?>
</div></body></html>
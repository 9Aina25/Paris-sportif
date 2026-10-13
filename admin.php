<?php session_start();
if($_SERVER['REQUEST_METHOD']=='POST' && $_POST['admin_pass']=='admin123'){
    $_SESSION['admin_auth']=true;
    header('Location: admin_dashboard.php');
    exit;
}
?>
<form method="post"><input type="password" name="admin_pass"><button>Accéder admin</button></form>
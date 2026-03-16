<?php
session_start();
include('../database/config.php');

if(isset($_POST['upload_logo'])) {
    $target_dir = "../assets/img/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_name = "logo_entreprise_" . time() . "." . pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
        $conn->query("UPDATE site_settings SET logo_path='$file_name' WHERE id=1");
        $msg = "Logo mis à jour avec succès !";
    }
}

$res = $conn->query("SELECT logo_path FROM site_settings WHERE id=1");
$config = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres du Site</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f7f6; padding: 40px; }
        .card { background: white; max-width: 500px; margin: auto; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; }
        .logo-preview { max-width: 200px; margin: 20px 0; border: 1px solid #eee; padding: 10px; border-radius: 10px; }
        .btn { background: #3b5998; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Configuration du Logo</h2>
        <img src="../assets/img/<?= $config['logo_path'] ?>" class="logo-preview" alt="Logo actuel">
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="logo" required style="margin-bottom: 20px;">
            <br>
            <button type="submit" name="upload_logo" class="btn">Changer le logo</button>
        </form>
        <br>
        <a href="dashboard.php" style="text-decoration:none; color:#666;">Retour</a>
    </div>
</body>
</html>
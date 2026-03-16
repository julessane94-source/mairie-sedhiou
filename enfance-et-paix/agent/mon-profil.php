<?php
// 1. Connexion à la base de données
require_once __DIR__ . '/../database/config.php';

// Pour cet exemple, nous utilisons l'ID de l'admin par défaut (1) 
// En production, vous utiliserez $_SESSION['id_agent']
$id_agent = 1; 

$message_status = "";

// 2. Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $nouveau_mdp = $_POST['nouveau_mdp'];

    if (!empty($nouveau_mdp)) {
        // Mise à jour avec mot de passe
        $stmt = $conn->prepare("UPDATE agents SET nom=?, prenom=?, email=?, mot_de_passe=? WHERE id_agent=?");
        $stmt->bind_param("ssssi", $nom, $prenom, $email, $nouveau_mdp, $id_agent);
    } else {
        // Mise à jour sans changer le mot de passe
        $stmt = $conn->prepare("UPDATE agents SET nom=?, prenom=?, email=? WHERE id_agent=?");
        $stmt->bind_param("sssi", $nom, $prenom, $email, $id_agent);
    }

    if ($stmt->execute()) {
        $message_status = "<p style='color: green;'>Profil mis à jour avec succès !</p>";
    } else {
        $message_status = "<p style='color: red;'>Erreur lors de la mise à jour.</p>";
    }
}

// 3. Récupération des infos actuelles
$res = $conn->query("SELECT * FROM agents WHERE id_agent = $id_agent");
$agent = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - Espace Agent</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .profile-container { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { color: #3b5998; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-save { width: 100%; padding: 12px; background: #3b5998; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        .btn-save:hover { background: #2d4373; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #666; text-decoration: none; }
    </style>
</head>
<body>

<div class="profile-container">
    <h2>👤 Mon Profil Agent</h2>
    
    <?= $message_status ?>

    <form method="POST">
        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($agent['nom'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($agent['prenom'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($agent['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Nouveau mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" name="nouveau_mdp" placeholder="********">
        </div>
        
        <button type="submit" class="btn-save">Enregistrer les modifications</button>
    </form>

    <a href="dashboard.html" class="back-link">⬅ Retour au tableau de bord</a>
</div>

</body>
</html>
<?php
// 1. CONNEXION À LA BASE DE DONNÉES (TOUJOURS EN PREMIER)
require_once __DIR__ . '/../database/config.php';

// 2. RÉCUPÉRATION DU LOGO (Maintenant que $conn est défini)
$config_res = $conn->query("SELECT logo_path FROM site_settings WHERE id=1");
$site_logo = ($config_res && $config_res->num_rows > 0) ? $config_res->fetch_assoc()['logo_path'] : 'default_logo.png';

// On simule l'ID de l'agent connecté
$id_agent = 1; 
$status_msg = "";

// 3. GESTION DE L'UPLOAD (AJOUT D'UN DOCUMENT)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['nouveau_doc'])) {
    $titre = $_POST['titre'];
    $desc = $_POST['description'];
    
    $target_dir = "uploads_perso/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_name = time() . "_" . basename($_FILES["nouveau_doc"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["nouveau_doc"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO documents (titre, description, fichier, id_agent, date_upload) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssi", $titre, $desc, $file_name, $id_agent);
        
        if ($stmt->execute()) {
            $status_msg = "<p style='color: green; font-weight: bold;'>✔️ Document ajouté avec succès !</p>";
        }
    } else {
        $status_msg = "<p style='color: red; font-weight: bold;'>❌ Erreur lors du transfert du fichier.</p>";
    }
}

// 4. RÉCUPÉRATION DES DOCUMENTS DE CET AGENT
$sql = "SELECT * FROM documents WHERE id_agent = $id_agent ORDER BY date_upload DESC";
$resultat = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Documents - Espace Agent</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f4f4; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 900px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        .header { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #eee; }
        .header img { height: 60px; object-fit: contain; }
        .header h2 { color: #3b5998; margin: 0; }
        
        /* Formulaire */
        .upload-section { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #ddd; }
        .upload-section h3 { margin-top: 0; font-size: 1.1rem; color: #555; }
        .upload-section input, .upload-section textarea { width: 100%; margin-bottom: 12px; padding: 10px; border-radius: 5px; border: 1px solid #ccc; box-sizing: border-box; }
        .btn-add { background: #3b5998; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn-add:hover { background: #2d4373; }

        /* Tableau */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #3b5998; color: white; border-radius: 5px 5px 0 0; }
        .btn-dl { color: #3b5998; text-decoration: none; font-weight: bold; border: 1px solid #3b5998; padding: 5px 10px; border-radius: 4px; transition: 0.2s; }
        .btn-dl:hover { background: #3b5998; color: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <img src="../assets/img/<?= htmlspecialchars($site_logo) ?>" alt="Logo">
        <h2>📁 Mes Documents Personnels</h2>
    </div>

    <div style="margin-bottom: 20px;">
        <a href="dashboard.html" style="text-decoration: none; color: #666; font-weight: 500;">⬅ Retour au Dashboard</a>
    </div>

    <?= $status_msg ?>

    <div class="upload-section">
        <h3>Ajouter un nouveau document</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="titre" placeholder="Titre du document (ex: Contrat de travail)" required>
            <textarea name="description" placeholder="Description ou notes (optionnel)" rows="2"></textarea>
            <input type="file" name="nouveau_doc" required>
            <button type="submit" class="btn-add">Enregistrer le document</button>
        </form>
    </div>

    <h3>Liste de vos fichiers</h3>
    <table>
        <thead>
            <tr>
                <th>Titre & Description</th>
                <th>Date d'ajout</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultat && $resultat->num_rows > 0): ?>
                <?php while($doc = $resultat->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($doc['titre']) ?></strong><br>
                            <small style="color: #777;"><?= htmlspecialchars($doc['description']) ?></small>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($doc['date_upload'])) ?></td>
                        <td>
                            <a href="uploads_perso/<?= htmlspecialchars($doc['fichier']) ?>" class="btn-dl" download>
                                <i class="fas fa-download"></i> Télécharger
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align:center; padding: 30px; color: #999;">
                        Aucun document trouvé dans votre espace.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
<?php
// 1. CONNEXION À LA BASE DE DONNÉES (Indispensable en premier !)
require_once __DIR__ . '/../database/config.php';

// 2. RÉCUPÉRATION DU LOGO
$config_res = $conn->query("SELECT logo_path FROM site_settings WHERE id=1");
$site_logo = ($config_res && $config_res->num_rows > 0) ? $config_res->fetch_assoc()['logo_path'] : 'default_logo.png';

// --- 3. GESTION DU CLOUD (UPLOAD DE FICHIERS) ---
if (isset($_FILES['fichier_cloud'])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    $file_name = time() . "_" . basename($_FILES["fichier_cloud"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["fichier_cloud"]["tmp_name"], $target_file)) {
        $success_upload = "Fichier partagé !";
    }
}

// --- 4. GESTION DU CHAT (ENVOI MESSAGE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer_message'])) {
    $message = $_POST['message'];
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO messages (expediteur, message) VALUES ('Agent', ?)");
        $stmt->bind_param("s", $message);
        $stmt->execute();
        header("Location: espace-travail.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Collaboratif | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #3b5998;
            --secondary: #4a69bd;
            --bg: #f1f5f9;
            --text: #1e293b;
            --accent: #10b981;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; }

        /* Sidebar */
        .sidebar { width: 280px; background: white; height: 100vh; position: fixed; box-shadow: 2px 0 10px rgba(0,0,0,0.05); padding: 20px; box-sizing: border-box; }
        .sidebar h2 { color: var(--primary); font-size: 1.2rem; margin-bottom: 30px; display: flex; align-items: center; gap: 10px; }
        .logo-container { text-align: center; margin-bottom: 20px; }
        .menu-item { display: flex; align-items: center; gap: 12px; padding: 12px; color: #64748b; text-decoration: none; border-radius: 8px; transition: 0.3s; margin-bottom: 5px; }
        .menu-item:hover, .menu-item.active { background: #f1f5f9; color: var(--primary); font-weight: 600; }
        .badge { background: #ef4444; color: white; padding: 2px 8px; border-radius: 20px; font-size: 0.7rem; margin-left: auto; }

        /* Contenu Principal */
        .main-content { margin-left: 280px; padding: 40px; width: calc(100% - 280px); }
        .header-title { margin-bottom: 30px; }
        .header-title h1 { margin: 0; font-size: 1.8rem; }
        .header-title p { color: #64748b; margin: 5px 0 0; }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; }

        /* Modules */
        .module { background: white; border-radius: 16px; padding: 25px; box-shadow: var(--card-shadow); border: 1px solid rgba(0,0,0,0.05); }
        .module h3 { margin: 0 0 20px; font-size: 1.1rem; display: flex; align-items: center; gap: 10px; color: var(--primary); }

        /* Chat Style Messenger */
        .chat-box { height: 300px; overflow-y: auto; display: flex; flex-direction: column-reverse; padding: 10px; background: #f8fafc; border-radius: 12px; margin-bottom: 15px; }
        .msg { margin-bottom: 15px; max-width: 85%; }
        .msg-content { background: white; padding: 10px 15px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); font-size: 0.9rem; }
        .msg-info { font-size: 0.75rem; color: #94a3b8; margin-top: 5px; }
        .chat-form { display: flex; gap: 10px; }
        .chat-form input { flex: 1; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; }
        .btn-send { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; transition: 0.3s; }
        .btn-send:hover { background: var(--secondary); }

        /* Projets & Cloud */
        .proj-item { margin-bottom: 20px; }
        progress { width: 100%; height: 8px; border-radius: 10px; margin: 8px 0; }
        progress::-webkit-progress-bar { background-color: #f1f5f9; border-radius: 10px; }
        progress::-webkit-progress-value { background-color: var(--accent); border-radius: 10px; }

        .file-item { display: flex; align-items: center; justify-content: space-between; padding: 10px; background: #f8fafc; border-radius: 8px; margin-top: 10px; font-size: 0.85rem; }
        .btn-download { color: var(--primary); text-decoration: none; font-weight: 600; }

        .btn-upload { background: #f1f5f9; border: 2px dashed #cbd5e1; padding: 15px; border-radius: 12px; text-align: center; cursor: pointer; transition: 0.3s; display: block; width: 100%; box-sizing: border-box; }
        .btn-upload:hover { border-color: var(--primary); background: #eff6ff; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo-container">
        <img src="assets/img/<?= htmlspecialchars($site_logo) ?>" alt="Logo" style="height: 50px;">
    </div>
    <h2><i class="fas fa-briefcase"></i> Espace Agent</h2>
    <a href="dashboard.html" class="menu-item"><i class="fas fa-home"></i> Accueil</a>
    <a href="mes-documents.php" class="menu-item"><i class="fas fa-file-alt"></i> Mes Documents</a>
    <a href="#" class="menu-item active"><i class="fas fa-project-diagram"></i> Espace Travail</a>
    <a href="#" class="menu-item"><i class="fas fa-bell"></i> Notifications <span class="badge">3</span></a>
    <a href="../index.php" class="menu-item" style="margin-top: auto; color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
</div>

<div class="main-content">
    <div class="header-title">
        <h1>Outils Collaboratifs</h1>
        <p>Gérez vos projets et communiquez avec l'équipe en temps réel.</p>
    </div>

    <div class="grid">
        <div class="module">
            <h3><i class="fas fa-comments"></i> Chat d'équipe</h3>
            <div class="chat-box">
                <?php
                $res_msg = $conn->query("SELECT * FROM messages ORDER BY date_envoi DESC LIMIT 15");
                if ($res_msg) {
                    while($msg = $res_msg->fetch_assoc()): ?>
                        <div class="msg">
                            <div class="msg-content">
                                <strong><?= htmlspecialchars($msg['expediteur']) ?></strong>: <?= htmlspecialchars($msg['message']) ?>
                            </div>
                            <div class="msg-info"><?= date('H:i', strtotime($msg['date_envoi'])) ?></div>
                        </div>
                    <?php endwhile;
                } ?>
            </div>
            <form class="chat-form" method="POST">
                <input type="text" name="message" placeholder="Tapez votre message..." required>
                <button type="submit" name="envoyer_message" class="btn-send"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>

        <div class="module">
            <h3><i class="fas fa-tasks"></i> Projets en cours</h3>
            <?php
            $res_proj = $conn->query("SELECT * FROM projets");
            if ($res_proj) {
                while($proj = $res_proj->fetch_assoc()): ?>
                    <div class="proj-item">
                        <div style="display: flex; justify-content: space-between; font-size: 0.9rem;">
                            <span style="font-weight: 500;"><?= htmlspecialchars($proj['nom_projet']) ?></span>
                            <span style="color: var(--accent); font-weight: 700;"><?= $proj['progression'] ?>%</span>
                        </div>
                        <progress value="<?= $proj['progression'] ?>" max="100"></progress>
                        <div style="font-size: 0.75rem; color: #94a3b8;">Statut: <?= htmlspecialchars($proj['statut']) ?></div>
                    </div>
                <?php endwhile;
            } ?>
        </div>

        <div class="module">
            <h3><i class="fas fa-cloud-upload-alt"></i> Partage Cloud</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <label class="btn-upload">
                    <input type="file" name="fichier_cloud" style="display: none;" onchange="this.form.submit()">
                    <i class="fas fa-plus-circle" style="font-size: 1.5rem; color: var(--primary);"></i>
                    <p style="margin: 5px 0 0; font-size: 0.85rem;">Cliquez pour partager un fichier</p>
                </label>
            </form>
            
            <div style="margin-top: 20px;">
                <h4 style="font-size: 0.85rem; color: #64748b; text-transform: uppercase;">Fichiers récents</h4>
                <?php
                if (is_dir("uploads/")) {
                    $files = array_diff(scandir("uploads/"), array('.', '..'));
                    foreach (array_slice($files, -4) as $file): ?>
                        <div class="file-item">
                            <span><i class="far fa-file"></i> <?= htmlspecialchars(substr($file, 11)) ?></span>
                            <a href="uploads/<?= $file ?>" class="btn-download" download><i class="fas fa-download"></i></a>
                        </div>
                    <?php endforeach;
                } ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
<?php
// 1. Connexion à la base de données mairie_platform
$host = 'localhost';
$dbname = 'mairie_platform';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// 2. Récupération de l'ID de la demande
$id_demande = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 3. Traitement de l'assignation (RECTIFIÉ)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $id_agent = $_POST['user_id'];
    
    // RECTIFICATION : On ne met à jour QUE l'agent_id. 
    // On ne mentionne plus la colonne 'statut' pour ne pas l'écraser ou la vider.
    $sql = "UPDATE demandes SET agent_id = :agent_id WHERE id = :id_demande";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute(['agent_id' => $id_agent, 'id_demande' => $id_demande])) {
        echo "<script>alert('Assignation réussie !'); window.location.href='dashboard.php';</script>";
        exit;
    }
}

// 4. Récupération des agents uniquement
$query = "SELECT id, nom, prenom FROM users WHERE role = 'agent' ORDER BY nom ASC";
$agents = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigner un agent | Mairie</title>
    <style>
        :root {
            --primary: #2c3e50;
            --accent: #3498db;
            --light: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #e9ecef;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: white;
            width: 90%;
            max-width: 400px;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            text-align: center;
        }

        .badge {
            background: var(--accent);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.9em;
            margin-bottom: 20px;
            display: inline-block;
        }

        h2 { color: var(--primary); margin-bottom: 25px; }

        select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            margin-bottom: 10px;
            border: none;
            font-size: 16px;
        }

        .btn-confirm {
            background-color: #27ae60;
            color: white;
        }

        .btn-back {
            background-color: #95a5a6;
            color: white;
        }

        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>

<div class="card">
    <span class="badge">Demande #<?php echo $id_demande; ?></span>
    <h2>Assigner un agent</h2>

    <form method="POST">
        <select name="user_id" required>
            <option value="">-- Sélectionner l'agent --</option>
            <?php foreach ($agents as $agent): ?>
                <option value="<?php echo $agent['id']; ?>">
                    <?php echo htmlspecialchars(strtoupper($agent['nom']) . ' ' . $agent['prenom']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit" class="btn btn-confirm">Confirmer l'assignation</button>
        <a href="dashboard.php" class="btn btn-back">Retour</a>
    </form>
</div>

</body>
</html>
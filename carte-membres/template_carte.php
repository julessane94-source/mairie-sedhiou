<?php
// template_carte.php - Pour prévisualiser une carte individuelle
require_once 'config.php';

$membre_id = $_GET['id'] ?? 0;
if (!$membre_id) die("Membre non spécifié");

$stmt = $pdo->prepare("
    SELECT m.*, s.nom as structure_nom, s.logo, s.couleur_principale, s.couleur_secondaire 
    FROM membres m
    JOIN structures s ON m.structure_id = s.id
    WHERE m.id = ?
");
$stmt->execute([$membre_id]);
$membre = $stmt->fetch();

if (!$membre) die("Membre non trouvé");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carte de <?= htmlspecialchars($membre['prenom'] . ' ' . $membre['nom']) ?></title>
    <style>
        body {
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: Arial, sans-serif;
        }
        
        .carte {
            width: 85.6mm; /* ISO/IEC 7810 ID-1 */
            height: 54mm;
            background: linear-gradient(135deg, <?= $membre['couleur_principale'] ?>, <?= $membre['couleur_secondaire'] ?>);
            border-radius: 5mm;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
            color: white;
            padding: 5mm;
            box-sizing: border-box;
        }
        
        .carte::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }
        
        .logo {
            width: 20mm;
            height: 20mm;
            background: rgba(255,255,255,0.2);
            border-radius: 3mm;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10mm;
            float: left;
            margin-right: 3mm;
            overflow: hidden;
        }
        
        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .structure-nom {
            font-size: 5mm;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        
        .photo {
            width: 20mm;
            height: 20mm;
            background: rgba(255,255,255,0.2);
            border-radius: 3mm;
            position: absolute;
            bottom: 5mm;
            left: 5mm;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .infos {
            position: absolute;
            left: 30mm;
            right: 5mm;
            bottom: 5mm;
        }
        
        .nom {
            font-size: 5mm;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        
        .numero {
            font-size: 3.5mm;
            opacity: 0.9;
            margin-bottom: 1mm;
        }
        
        .validite {
            font-size: 2.5mm;
            position: absolute;
            bottom: 2mm;
            right: 5mm;
            text-align: right;
        }
        
        .qr {
            width: 15mm;
            height: 15mm;
            background: white;
            position: absolute;
            top: 5mm;
            right: 5mm;
            border-radius: 2mm;
            display: flex;
            align-items: center;
            justify-content: center;
            color: black;
            font-size: 2mm;
        }
        
        @media print {
            body {
                background: white;
                padding: 5mm;
            }
            .carte {
                box-shadow: none;
                break-inside: avoid;
                margin: 2mm;
            }
        }
    </style>
</head>
<body>
    <div class="carte">
        <div class="logo">
            <?php if ($membre['logo'] && file_exists(LOGO_PATH . '/' . $membre['logo'])): ?>
                <img src="uploads/logos/<?= $membre['logo'] ?>" alt="Logo">
            <?php else: ?>
                <span>🏢</span>
            <?php endif; ?>
        </div>
        
        <div class="structure-nom"><?= htmlspecialchars($membre['structure_nom']) ?></div>
        
        <div class="photo">
            <?php if ($membre['photo'] && file_exists(PHOTO_PATH . '/' . $membre['photo'])): ?>
                <img src="uploads/membres/<?= $membre['photo'] ?>" alt="Photo">
            <?php else: ?>
                <span>📷</span>
            <?php endif; ?>
        </div>
        
        <div class="infos">
            <div class="nom"><?= htmlspecialchars($membre['prenom'] . ' ' . $membre['nom']) ?></div>
            <div class="numero">N° <?= htmlspecialchars($membre['numero_membre']) ?></div>
            <?php if (!empty($membre['date_naissance'])): ?>
                <div class="numero">Né le <?= date('d/m/Y', strtotime($membre['date_naissance'])) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="validite">
            Valable jusqu'au<br>
            <strong><?= date('d/m/Y', strtotime($membre['date_expiration'])) ?></strong>
        </div>
        
        <div class="qr">
            QR Code
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print me-2"></i>Imprimer
        </button>
        <a href="generate.php?membre=<?= $membre_id ?>" class="btn btn-success">
            <i class="fas fa-download me-2"></i>Télécharger PDF
        </a>
    </div>
</body>
</html>
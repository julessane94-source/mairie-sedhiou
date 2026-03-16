<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use TCPDF;

$structure_id = $_GET['structure'] ?? 0;
if (!$structure_id) {
    header('Location: index.php');
    exit();
}

// Récupérer la structure
$stmt = $pdo->prepare("SELECT * FROM structures WHERE id = ?");
$stmt->execute([$structure_id]);
$structure = $stmt->fetch();

if (!$structure) {
    die("Structure non trouvée");
}

// Récupérer les membres
$stmt = $pdo->prepare("SELECT * FROM membres WHERE structure_id = ? AND statut = 'actif' ORDER BY nom, prenom");
$stmt->execute([$structure_id]);
$membres = $stmt->fetchAll();

if (empty($membres)) {
    header('Location: import.php?structure=' . $structure_id . '&error=Aucun membre à exporter');
    exit();
}

// Couleurs de la structure (en utilisant la fonction hex2rgb de config.php)
$couleur_principale = hex2rgb($structure['couleur_principale'] ?? '#3498db');
$couleur_secondaire = hex2rgb($structure['couleur_secondaire'] ?? '#2980b9');

// Créer le PDF
$pdf = new TCPDF('L', 'mm', array(86, 54), true, 'UTF-8', false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);

foreach ($membres as $membre) {
    $pdf->AddPage();
    
    $w = $pdf->getPageWidth();
    $h = $pdf->getPageHeight();
    
    // ===== 1. FOND AVEC DÉGRADÉ ÉLÉGANT =====
    for($i=0; $i<=$w; $i+=2) {
        $ratio = $i / $w;
        $r = $couleur_principale[0] * (1-$ratio) + $couleur_secondaire[0] * $ratio;
        $g = $couleur_principale[1] * (1-$ratio) + $couleur_secondaire[1] * $ratio;
        $b = $couleur_principale[2] * (1-$ratio) + $couleur_secondaire[2] * $ratio;
        $pdf->Rect($i, 0, 2, $h, 'F', array('all' => array('width' => 0, 'color' => array($r, $g, $b))));
    }
    
    // ===== 2. MOTIF DE FOND SUBTIL =====
    $pdf->SetAlpha(0.05);
    for($i=0; $i<$w; $i+=10) {
        for($j=0; $j<$h; $j+=10) {
            $pdf->Circle($i, $j, 2, 0, 360, 'F', array('all' => array('width' => 0, 'color' => array(255,255,255))));
        }
    }
    $pdf->SetAlpha(1);
    
    // ===== 3. BANDE BLANCHE MODERNE EN HAUT =====
    $pdf->SetAlpha(0.1);
    $pdf->Rect(0, 0, $w, 12, 'F', array('all' => array('width' => 0, 'color' => array(255,255,255))));
    $pdf->SetAlpha(1);
    
    // ===== 4. BANDE LATÉRALE GAUCHE =====
    $pdf->Rect(0, 0, 4, $h, 'F', array('all' => array('width' => 0, 'color' => array(255,255,255))));
    $pdf->SetAlpha(0.3);
    $pdf->Rect(2, 0, 2, $h, 'F', array('all' => array('width' => 0, 'color' => array(255,255,255))));
    $pdf->SetAlpha(1);
    
    // ===== 5. LOGO AVEC CADRE ÉLÉGANT =====
    $logo_path = LOGO_PATH . '/' . $structure['logo'];
    if ($structure['logo'] && file_exists($logo_path)) {
        // Cercle de fond pour le logo
        $pdf->SetAlpha(0.2);
        $pdf->Circle(18, 15, 12, 0, 360, 'F', array('all' => array('width' => 0, 'color' => array(255,255,255))));
        $pdf->SetAlpha(1);
        
        // Cercle extérieur
        $pdf->SetDrawColor(255, 255, 255);
        $pdf->SetLineWidth(0.3);
        $pdf->Circle(18, 15, 11, 0, 360, 'D');
        
        // Logo
        $pdf->Image($logo_path, 10, 7, 16, 16);
    } else {
        // Placeholder élégant si pas de logo
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetAlpha(0.2);
        $pdf->Circle(18, 15, 10, 0, 360, 'F');
        $pdf->SetAlpha(1);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY(10, 10);
        $pdf->Cell(16, 10, '🏢', 0, 1, 'C');
    }
    
    // ===== 6. NOM DE LA STRUCTURE =====
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetXY(30, 8);
    $pdf->Cell(50, 6, strtoupper($structure['nom']), 0, 1, 'R');
    
    // ===== 7. TITRE DE LA CARTE =====
    $pdf->SetFont('helvetica', '', 6);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetAlpha(0.7);
    $pdf->SetXY(30, 14);
    $pdf->Cell(50, 4, 'MEMBERSHIP CARD', 0, 1, 'R');
    $pdf->SetAlpha(1);
    
    // ===== 8. PHOTO AVEC CADRE MODERNE =====
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetAlpha(0.15);
    $pdf->RoundedRect(5, 22, 22, 24, 3, 'F');
    $pdf->SetAlpha(1);
    
    $pdf->SetDrawColor(255, 255, 255);
    $pdf->SetLineWidth(0.5);
    $pdf->RoundedRect(5, 22, 22, 24, 3, 'D');
    
    // Icône personne stylisée
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetAlpha(0.8);
    $pdf->Circle(16, 30, 4, 0, 360, 'F');
    $pdf->Rect(10, 34, 12, 6, 'F');
    $pdf->SetAlpha(1);
    
    // ===== 9. INFORMATIONS MEMBRE AVEC CADRE =====
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetAlpha(0.1);
    $pdf->RoundedRect(30, 22, 50, 24, 3, 'F');
    $pdf->SetAlpha(1);
    
    // Nom du membre
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetXY(32, 25);
    $nom_complet = $membre['prenom'] . ' ' . $membre['nom'];
    $pdf->Cell(46, 5, $nom_complet, 0, 1, 'L');
    
    // Numéro de membre
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetXY(32, 31);
    $pdf->Cell(46, 4, 'N° ' . $membre['numero_membre'], 0, 1, 'L');
    
    // Date de naissance
    if (!empty($membre['date_naissance'])) {
        $pdf->SetXY(32, 36);
        $pdf->Cell(46, 4, 'Né(e) le ' . date('d/m/Y', strtotime($membre['date_naissance'])), 0, 1, 'L');
    }
    
    // ===== 10. QR CODE MODERNE =====
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetAlpha(0.9);
    $pdf->RoundedRect(65, 30, 16, 16, 2, 'F');
    $pdf->SetAlpha(1);
    
    // QR Code simulé
    $pdf->SetFillColor(0, 0, 0);
    for($i=0; $i<5; $i++) {
        for($j=0; $j<5; $j++) {
            if(($i+$j) % 3 != 0) {
                $pdf->Rect(66 + $i*2.5, 31 + $j*2.5, 1.5, 1.5, 'F');
            }
        }
    }
    
    // ===== 11. DATE D'EXPIRATION STYLISÉE =====
    $pdf->SetFillColor($couleur_secondaire[0], $couleur_secondaire[1], $couleur_secondaire[2]);
    $pdf->Rect(0, 46, $w, 8, 'F');
    
    $pdf->SetAlpha(0.2);
    $pdf->Rect(0, 46, $w, 4, 'F', array('all' => array('width' => 0, 'color' => array(255,255,255))));
    $pdf->SetAlpha(1);
    
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetXY(0, 48);
    $pdf->Cell($w, 4, 'VALID UNTIL ' . date('d/m/Y', strtotime($membre['date_expiration'])), 0, 1, 'C');
    
    // ===== 12. NUMÉRO DE SÉRIE EN MICRO =====
    $pdf->SetFont('helvetica', '', 4);
    $pdf->SetAlpha(0.5);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetXY(2, 2);
    $pdf->Cell(20, 2, 'ID: ' . $membre['id'] . ' - ' . date('Ymd'), 0, 1, 'L');
    $pdf->SetAlpha(1);
}

if (ob_get_length()) ob_clean();
$pdf->Output('cartes_' . $structure['nom'] . '_' . date('Y-m-d') . '.pdf', 'D');
exit();
?>
<?php
require_once '../config.php';

// Vérifier les droits d'accès
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../login.php');
    exit();
}

// Inclusion des bibliothèques (si installées via Composer)
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Récupérer les paramètres
$type = $_GET['type'] ?? 'demandes';
$format = $_GET['format'] ?? 'excel';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$statut = $_GET['statut'] ?? '';
$type_demande = $_GET['type_demande'] ?? '';

// Construire la requête SQL
$sql = "
    SELECT 
        d.id,
        d.numero_demande,
        d.type_demande,
        d.statut,
        d.date_demande,
        d.date_traitement,
        d.commentaire,
        d.commentaire_reponse,
        c.numero_citoyen,
        u.nom as citoyen_nom,
        u.prenom as citoyen_prenom,
        u.email as citoyen_email,
        u.telephone as citoyen_telephone,
        ag.nom as agent_nom,
        ag.prenom as agent_prenom
    FROM demandes d
    JOIN citoyens c ON d.citoyen_id = c.id
    JOIN users u ON c.user_id = u.id
    LEFT JOIN users ag ON d.agent_id = ag.id
    WHERE 1=1
";

$params = [];

if (!empty($date_debut)) {
    $sql .= " AND DATE(d.date_demande) >= ?";
    $params[] = $date_debut;
}

if (!empty($date_fin)) {
    $sql .= " AND DATE(d.date_demande) <= ?";
    $params[] = $date_fin;
}

if (!empty($statut)) {
    $sql .= " AND d.statut = ?";
    $params[] = $statut;
}

if (!empty($type_demande)) {
    $sql .= " AND d.type_demande = ?";
    $params[] = $type_demande;
}

$sql .= " ORDER BY d.date_demande DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$demandes = $stmt->fetchAll();

// Compter pour le résumé
$total = count($demandes);
$traitees = count(array_filter($demandes, fn($d) => $d['statut'] == 'traite'));
$en_attente = count(array_filter($demandes, fn($d) => $d['statut'] == 'en_attente'));
$en_cours = count(array_filter($demandes, fn($d) => $d['statut'] == 'en_cours'));

// Nom du fichier
$filename = 'export_demandes_' . date('Y-m-d_H-i-s');

// === EXPORT EXCEL ===
if ($format == 'excel') {
    try {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Titre du document
        $sheet->setTitle('Demandes');
        
        // En-têtes
        $headers = [
            'A1' => 'ID',
            'B1' => 'N° Demande',
            'C1' => 'Type',
            'D1' => 'Statut',
            'E1' => 'Date demande',
            'F1' => 'Date traitement',
            'G1' => 'Citoyen',
            'H1' => 'N° Citoyen',
            'I1' => 'Email',
            'J1' => 'Téléphone',
            'K1' => 'Agent',
            'L1' => 'Commentaire',
            'M1' => 'Réponse'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        
        // Style des en-têtes
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        // Données
        $row = 2;
        foreach ($demandes as $demande) {
            $sheet->setCellValue('A' . $row, $demande['id']);
            $sheet->setCellValue('B' . $row, $demande['numero_demande']);
            $sheet->setCellValue('C' . $row, $demande['type_demande']);
            $sheet->setCellValue('D' . $row, $demande['statut']);
            $sheet->setCellValue('E' . $row, date('d/m/Y H:i', strtotime($demande['date_demande'])));
            $sheet->setCellValue('F' . $row, $demande['date_traitement'] ? date('d/m/Y H:i', strtotime($demande['date_traitement'])) : '-');
            $sheet->setCellValue('G' . $row, $demande['citoyen_prenom'] . ' ' . $demande['citoyen_nom']);
            $sheet->setCellValue('H' . $row, $demande['numero_citoyen']);
            $sheet->setCellValue('I' . $row, $demande['citoyen_email']);
            $sheet->setCellValue('J' . $row, $demande['citoyen_telephone']);
            $sheet->setCellValue('K' . $row, $demande['agent_prenom'] ? $demande['agent_prenom'] . ' ' . $demande['agent_nom'] : 'Non assigné');
            $sheet->setCellValue('L' . $row, $demande['commentaire']);
            $sheet->setCellValue('M' . $row, $demande['commentaire_reponse']);
            
            // Alternance des couleurs
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2']
                    ]
                ]);
            }
            
            $row++;
        }
        
        // Ajuster automatiquement la largeur des colonnes
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Ajouter une feuille de résumé
        $summarySheet = $spreadsheet->createSheet();
        $summarySheet->setTitle('Résumé');
        
        $summarySheet->setCellValue('A1', 'Rapport d\'export');
        $summarySheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        
        $summarySheet->setCellValue('A3', 'Date d\'export');
        $summarySheet->setCellValue('B3', date('d/m/Y H:i:s'));
        
        $summarySheet->setCellValue('A4', 'Période');
        $summarySheet->setCellValue('B4', ($date_debut ?: 'Début') . ' - ' . ($date_fin ?: 'Fin'));
        
        $summarySheet->setCellValue('A5', 'Statut filtré');
        $summarySheet->setCellValue('B5', $statut ?: 'Tous');
        
        $summarySheet->setCellValue('A7', 'Total demandes');
        $summarySheet->setCellValue('B7', $total);
        
        $summarySheet->setCellValue('A8', 'Traitées');
        $summarySheet->setCellValue('B8', $traitees);
        
        $summarySheet->setCellValue('A9', 'En attente');
        $summarySheet->setCellValue('B9', $en_attente);
        
        $summarySheet->setCellValue('A10', 'En cours');
        $summarySheet->setCellValue('B10', $en_cours);
        
        $summarySheet->getStyle('A7:A10')->getFont()->setBold(true);
        
        // Style du résumé
        $summarySheet->getStyle('A3:B10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        // Ajuster les colonnes
        $summarySheet->getColumnDimension('A')->setAutoSize(true);
        $summarySheet->getColumnDimension('B')->setAutoSize(true);
        
        // Envoi du fichier
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
        
    } catch (Exception $e) {
        die("Erreur lors de la génération du fichier Excel : " . $e->getMessage());
    }
}

// === EXPORT PDF ===
elseif ($format == 'pdf') {
    try {
        // Créer un nouveau document PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Informations du document
        $pdf->SetCreator('Mairie');
        $pdf->SetAuthor('Administration');
        $pdf->SetTitle('Export des demandes');
        $pdf->SetSubject('Liste des demandes');
        
        // Supprimer l'en-tête et le pied de page par défaut
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Ajouter une page
        $pdf->AddPage();
        
        // Titre
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->Cell(0, 20, 'Liste des demandes', 0, 1, 'C');
        
        // Date d'export
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 10, 'Export du ' . date('d/m/Y H:i:s'), 0, 1, 'R');
        
        // Filtres appliqués
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Filtres appliqués :', 0, 1);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 8, 'Période : ' . ($date_debut ?: 'Début') . ' au ' . ($date_fin ?: 'Fin'), 0, 1);
        $pdf->Cell(0, 8, 'Statut : ' . ($statut ?: 'Tous'), 0, 1);
        $pdf->Cell(0, 8, 'Type : ' . ($type_demande ?: 'Tous'), 0, 1);
        
        // Résumé
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Résumé :', 0, 1);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(60, 8, 'Total demandes : ' . $total, 0, 0);
        $pdf->Cell(60, 8, 'Traitées : ' . $traitees, 0, 0);
        $pdf->Cell(60, 8, 'En attente : ' . $en_attente, 0, 1);
        $pdf->Cell(60, 8, 'En cours : ' . $en_cours, 0, 0);
        
        $pdf->Ln(10);
        
        // Tableau des demandes
        $html = '<style>
            table { border-collapse: collapse; width: 100%; }
            th { background-color: #4472C4; color: white; font-weight: bold; padding: 8px; text-align: center; }
            td { padding: 6px; border: 1px solid #ddd; }
            tr:nth-child(even) { background-color: #f2f2f2; }
            .statut-en-attente { color: #856404; background-color: #fff3cd; }
            .statut-en-cours { color: #0c5460; background-color: #d1ecf1; }
            .statut-traite { color: #155724; background-color: #d4edda; }
            .statut-rejete { color: #721c24; background-color: #f8d7da; }
        </style>';
        
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>N° Demande</th>';
        $html .= '<th>Type</th>';
        $html .= '<th>Statut</th>';
        $html .= '<th>Date</th>';
        $html .= '<th>Citoyen</th>';
        $html .= '<th>Agent</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($demandes as $demande) {
            $statut_class = 'statut-' . str_replace('_', '-', $demande['statut']);
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($demande['numero_demande']) . '</td>';
            $html .= '<td>' . htmlspecialchars($demande['type_demande']) . '</td>';
            $html .= '<td class="' . $statut_class . '">' . htmlspecialchars($demande['statut']) . '</td>';
            $html .= '<td>' . date('d/m/Y', strtotime($demande['date_demande'])) . '</td>';
            $html .= '<td>' . htmlspecialchars($demande['citoyen_prenom'] . ' ' . $demande['citoyen_nom']) . '</td>';
            $html .= '<td>' . ($demande['agent_prenom'] ? htmlspecialchars($demande['agent_prenom'] . ' ' . $demande['agent_nom']) : 'Non assigné') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        
        // Ajouter le tableau au PDF
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Ajouter une page pour les détails si nécessaire
        if (count($demandes) < 20) { // Seulement si peu de demandes
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Détails des demandes', 0, 1, 'C');
            $pdf->Ln(5);
            
            foreach ($demandes as $demande) {
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->Cell(0, 8, 'Demande : ' . $demande['numero_demande'], 0, 1);
                $pdf->SetFont('helvetica', '', 11);
                $pdf->MultiCell(0, 6, 'Commentaire : ' . ($demande['commentaire'] ?: 'Aucun'), 0, 1);
                $pdf->MultiCell(0, 6, 'Réponse : ' . ($demande['commentaire_reponse'] ?: 'Aucune'), 0, 1);
                $pdf->Ln(5);
            }
        }
        
        // Envoi du PDF
        $pdf->Output($filename . '.pdf', 'D');
        exit();
        
    } catch (Exception $e) {
        die("Erreur lors de la génération du PDF : " . $e->getMessage());
    }
}

// === AUTRES FORMATS (CSV) ===
elseif ($format == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // BOM pour UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // En-têtes CSV
    fputcsv($output, [
        'ID',
        'N° Demande',
        'Type',
        'Statut',
        'Date demande',
        'Date traitement',
        'Citoyen',
        'N° Citoyen',
        'Email',
        'Téléphone',
        'Agent',
        'Commentaire',
        'Réponse'
    ], ';');
    
    // Données
    foreach ($demandes as $demande) {
        fputcsv($output, [
            $demande['id'],
            $demande['numero_demande'],
            $demande['type_demande'],
            $demande['statut'],
            date('d/m/Y H:i', strtotime($demande['date_demande'])),
            $demande['date_traitement'] ? date('d/m/Y H:i', strtotime($demande['date_traitement'])) : '',
            $demande['citoyen_prenom'] . ' ' . $demande['citoyen_nom'],
            $demande['numero_citoyen'],
            $demande['citoyen_email'],
            $demande['citoyen_telephone'],
            $demande['agent_prenom'] ? $demande['agent_prenom'] . ' ' . $demande['agent_nom'] : 'Non assigné',
            $demande['commentaire'],
            $demande['commentaire_reponse']
        ], ';');
    }
    
    fclose($output);
    exit();
}

// === PAGE D'EXPORT AVEC FILTRES ===
else {
    // Afficher la page de sélection d'export
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Export des données - Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            .sidebar {
                min-height: 100vh;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            .sidebar a {
                color: white;
                text-decoration: none;
                padding: 15px 20px;
                display: block;
            }
            .sidebar a:hover {
                background: rgba(255,255,255,0.1);
            }
            .main-content {
                padding: 20px;
                background: #f8f9fa;
            }
            .export-card {
                background: white;
                border-radius: 15px;
                padding: 30px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            }
            .format-btn {
                padding: 40px 20px;
                text-align: center;
                border: 2px solid #e9ecef;
                border-radius: 10px;
                transition: all 0.3s;
                cursor: pointer;
                text-decoration: none;
                color: #333;
                display: block;
            }
            .format-btn:hover {
                transform: translateY(-5px);
                border-color: #667eea;
                box-shadow: 0 5px 20px rgba(102,126,234,0.2);
            }
            .format-btn i {
                font-size: 3rem;
                margin-bottom: 15px;
            }
            .format-btn.excel i { color: #217346; }
            .format-btn.pdf i { color: #f40f02; }
            .format-btn.csv i { color: #ffa500; }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <?php include 'sidebar.php'; ?>
                
                <!-- Main Content -->
                <div class="col-md-10 main-content">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>Export des données</h3>
                        <a href="demandes.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                    </div>
                    
                    <div class="export-card">
                        <h4 class="mb-4"><i class="fas fa-download me-2 text-primary"></i>Exporter les demandes</h4>
                        
                        <form method="GET" action="export.php" class="row g-3">
                            <input type="hidden" name="type" value="demandes">
                            
                            <div class="col-md-4">
                                <label class="form-label">Date début</label>
                                <input type="date" class="form-control" name="date_debut" value="<?= date('Y-m-01') ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Date fin</label>
                                <input type="date" class="form-control" name="date_fin" value="<?= date('Y-m-d') ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Statut</label>
                                <select class="form-select" name="statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="en_attente">En attente</option>
                                    <option value="en_cours">En cours</option>
                                    <option value="traite">Traité</option>
                                    <option value="rejete">Rejeté</option>
                                </select>
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label">Type de demande</label>
                                <select class="form-select" name="type_demande">
                                    <option value="">Tous les types</option>
                                    <?php
                                    $types = $pdo->query("SELECT DISTINCT type_demande FROM demandes")->fetchAll();
                                    foreach ($types as $t) {
                                        echo '<option value="' . $t['type_demande'] . '">' . $t['type_demande'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <hr>
                                <h5 class="mb-3">Choisissez le format d'export</h5>
                            </div>
                            
                            <div class="col-md-4">
                                <button type="submit" name="format" value="excel" class="format-btn excel" style="background: none; border: 2px solid #e9ecef; width: 100%;">
                                    <i class="fas fa-file-excel"></i>
                                    <h5>Excel</h5>
                                    <small class="text-muted">Format .xlsx</small>
                                </button>
                            </div>
                            
                            <div class="col-md-4">
                                <button type="submit" name="format" value="pdf" class="format-btn pdf" style="background: none; border: 2px solid #e9ecef; width: 100%;">
                                    <i class="fas fa-file-pdf"></i>
                                    <h5>PDF</h5>
                                    <small class="text-muted">Format .pdf</small>
                                </button>
                            </div>
                            
                            <div class="col-md-4">
                                <button type="submit" name="format" value="csv" class="format-btn csv" style="background: none; border: 2px solid #e9ecef; width: 100%;">
                                    <i class="fas fa-file-csv"></i>
                                    <h5>CSV</h5>
                                    <small class="text-muted">Format .csv</small>
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-4 p-3 bg-light rounded">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            <small>L'export inclura uniquement les demandes correspondant aux filtres sélectionnés.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
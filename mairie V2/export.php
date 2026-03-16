<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$type = $_GET['type'] ?? 'demandes';
$format = $_GET['format'] ?? 'excel';

// Récupérer les données selon le rôle
switch($_SESSION['user_role']) {
    case 'admin':
        $query = "SELECT d.*, c.numero_citoyen, u.nom, u.prenom 
                  FROM demandes d
                  JOIN citoyens c ON d.citoyen_id = c.id
                  JOIN users u ON c.user_id = u.id
                  ORDER BY d.date_demande DESC";
        break;
    case 'agent':
        $query = "SELECT d.*, c.numero_citoyen, u.nom, u.prenom 
                  FROM demandes d
                  JOIN citoyens c ON d.citoyen_id = c.id
                  JOIN users u ON c.user_id = u.id
                  WHERE d.agent_id = " . $_SESSION['user_id'] . "
                  ORDER BY d.date_demande DESC";
        break;
    default:
        die('Accès non autorisé');
}

$stmt = $pdo->query($query);
$data = $stmt->fetchAll();

if ($format === 'excel') {
    // Export Excel (CSV)
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=export_demandes_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // En-têtes
    fputcsv($output, ['N° Demande', 'Citoyen', 'N° Citoyen', 'Type', 'Date', 'Statut', 'Agent ID', 'Commentaire']);
    
    // Données
    foreach ($data as $row) {
        fputcsv($output, [
            $row['numero_demande'],
            $row['prenom'] . ' ' . $row['nom'],
            $row['numero_citoyen'],
            $row['type_demande'],
            $row['date_demande'],
            $row['statut'],
            $row['agent_id'],
            $row['commentaire']
        ]);
    }
    
    fclose($output);
    
} elseif ($format === 'pdf') {
    // Export PDF (nécessite une bibliothèque comme dompdf ou TCPDF)
    // Exemple avec TCPDF
    require_once('../vendor/autoload.php');
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    $pdf->SetCreator('Mairie');
    $pdf->SetAuthor('Mairie');
    $pdf->SetTitle('Export des demandes');
    
    $pdf->AddPage();
    
    $html = '<h1>Liste des demandes</h1>';
    $html .= '<table border="1" cellpadding="5">';
    $html .= '<tr><th>N° Demande</th><th>Citoyen</th><th>Type</th><th>Date</th><th>Statut</th></tr>';
    
    foreach ($data as $row) {
        $html .= '<tr>';
        $html .= '<td>' . $row['numero_demande'] . '</td>';
        $html .= '<td>' . $row['prenom'] . ' ' . $row['nom'] . '</td>';
        $html .= '<td>' . $row['type_demande'] . '</td>';
        $html .= '<td>' . date('d/m/Y', strtotime($row['date_demande'])) . '</td>';
        $html .= '<td>' . $row['statut'] . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</table>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('export_demandes_' . date('Y-m-d') . '.pdf', 'D');
}
?>
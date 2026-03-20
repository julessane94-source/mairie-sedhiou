<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de paiement - {{ $payment->reference_recu }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            background: white;
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #1e40af;
        }
        
        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            margin-top: 15px;
            color: #1e40af;
        }
        
        .receipt-number {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 5px 10px;
            display: inline-block;
            border-radius: 3px;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e40af;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 11px;
        }
        
        .info-value {
            text-align: right;
            color: #333;
        }
        
        .amount-section {
            background: #f0f9ff;
            border-left: 4px solid #1e40af;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .amount-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
        }
        
        .amount-label {
            font-weight: bold;
        }
        
        .total-amount {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
            border-top: 2px solid #1e40af;
            border-bottom: 2px solid #1e40af;
            margin: 10px 0;
        }
        
        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status.paid {
            background: #dcfce7;
            color: #166534;
        }
        
        .status.pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status.cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .status.refunded {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .citizen-info {
            background: #f9fafb;
            padding: 12px;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        .citizen-info p {
            font-size: 12px;
            margin: 4px 0;
        }
        
        .demande-info {
            background: #f9fafb;
            padding: 12px;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        .demande-info p {
            font-size: 12px;
            margin: 4px 0;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #999;
            font-size: 10px;
        }
        
        .footer-text {
            margin: 5px 0;
        }
        
        .signature-space {
            margin-top: 40px;
            display: flex;
            justify-content: space-around;
            text-align: center;
        }
        
        .signature-line {
            width: 150px;
            border-top: 1px solid #333;
            padding-top: 10px;
            font-size: 11px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <div class="company-name">🏛️ MAIRI</div>
            <div class="receipt-title">REÇU DE PAIEMENT</div>
            <div class="receipt-number">#{{ $payment->reference_recu }}</div>
        </div>

        <!-- Informations du paiement -->
        <div class="section">
            <div class="section-title">Informations du paiement</div>
            
            <div class="info-row">
                <span class="info-label">Date de création:</span>
                <span class="info-value">{{ $payment->created_at->format('d/m/Y à H:i') }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Statut:</span>
                <span class="info-value">
                    <span class="status @if($payment->statut === 'paid') paid @elseif($payment->statut === 'pending') pending @elseif($payment->statut === 'cancelled') cancelled @else refunded @endif">
                        @if($payment->statut === 'paid') ✓ Payé
                        @elseif($payment->statut === 'pending') ⏳ En attente
                        @elseif($payment->statut === 'cancelled') ✗ Annulé
                        @else ↩️ Remboursé
                        @endif
                    </span>
                </span>
            </div>
            
            @if($payment->isPaid())
            <div class="info-row">
                <span class="info-label">Date de paiement:</span>
                <span class="info-value">{{ $payment->date_paiement->format('d/m/Y à H:i') }}</span>
            </div>
            @endif
            
            <div class="info-row">
                <span class="info-label">Méthode de paiement:</span>
                <span class="info-value">
                    @if($payment->methode_paiement === 'virement') Virement bancaire
                    @elseif($payment->methode_paiement === 'cheque') Chèque
                    @elseif($payment->methode_paiement === 'especes') Espèces
                    @elseif($payment->methode_paiement === 'carte') Carte bancaire
                    @else Paiement mobile
                    @endif
                </span>
            </div>
            
            @if($payment->numero_transaction)
            <div class="info-row">
                <span class="info-label">Numéro de transaction:</span>
                <span class="info-value">{{ $payment->numero_transaction }}</span>
            </div>
            @endif
        </div>

        <!-- Montant -->
        <div class="amount-section">
            <div class="total-amount">
                <span>MONTANT TOTAL</span>
                <span>{{ number_format($payment->montant, 2, ',', ' ') }} {{ $payment->devise }}</span>
            </div>
        </div>

        <!-- Informations du citoyen -->
        <div class="section">
            <div class="section-title">Informations du citoyen</div>
            <div class="citizen-info">
                <p><strong>Nom:</strong> {{ $citoyen->name }}</p>
                <p><strong>Email:</strong> {{ $citoyen->email }}</p>
                @if($citoyen->profil?->telephone)
                <p><strong>Téléphone:</strong> {{ $citoyen->profil->telephone }}</p>
                @endif
                @if($citoyen->profil?->adresse)
                <p><strong>Adresse:</strong> {{ $citoyen->profil->adresse }}</p>
                @endif
            </div>
        </div>

        <!-- Demande associée -->
        <div class="section">
            <div class="section-title">Demande associée</div>
            <div class="demande-info">
                <p><strong>Titre:</strong> {{ $demande->titre }}</p>
                <p><strong>Type:</strong> {{ $demande->type }}</p>
                <p><strong>Créée le:</strong> {{ $demande->created_at->format('d/m/Y à H:i') }}</p>
            </div>
        </div>

        @if($payment->description)
        <!-- Notes -->
        <div class="section">
            <div class="section-title">Notes</div>
            <p style="font-size: 12px; white-space: pre-wrap;">{{ $payment->description }}</p>
        </div>
        @endif

        <!-- Conditions -->
        <div class="section" style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #e5e7eb;">
            <div class="section-title">Conditions importantes</div>
            <p style="font-size: 11px; color: #666; line-height: 1.8;">
                • Ce reçu certifie que le paiement a été enregistré dans le système MAIRI<br>
                • Veuillez conserver ce reçu à titre de preuve de paiement<br>
                • Pour toute question ou réclamation, veuillez contacter l'administration<br>
                • Ce document est valide avec ou sans signature
            </p>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <div class="footer-text">
                Généré par le système MAIRI le {{ now()->format('d/m/Y à H:i:s') }}
            </div>
            <div class="footer-text">
                Plateforme de gestion des demandes citoyennes
            </div>
            <div class="footer-text">
                © 2026 - Tous droits réservés
            </div>
        </div>
    </div>
</body>
</html>

<?php

namespace App\Services;

use App\Models\Payment;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;
use App\Services\AuditService;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade as PDF;

class PaymentReceiptService
{
    /**
     * Génère un reçu de paiement en PDF
     */
    public function generateReceipt(Payment $payment): string
    {
        $payment->load('demande', 'citoyen');

        $data = [
            'payment' => $payment,
            'demande' => $payment->demande,
            'citoyen' => $payment->citoyen,
        ];

        // Générer le PDF
        $pdf = PDF::loadView('payments.receipt', $data);
        
        // Sauvegarder le fichier
        $filename = "receipt_{$payment->reference_recu}.pdf";
        $path = "receipts/{$filename}";
        
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Génère une référence unique pour le reçu
     */
    public function generateReceiptReference(): string
    {
        $date = now()->format('YmdHis');
        $random = strtoupper(substr(uniqid(), -6));
        return "REC-{$date}-{$random}";
    }

    /**
     * Obtient le lien de téléchargement du reçu
     */
    public function getReceiptUrl(Payment $payment): string
    {
        $filename = "receipt_{$payment->reference_recu}.pdf";
        return route('payments.receipt.download', ['payment' => $payment->id]);
    }

    /**
     * Crée un paiement avec reçu
     */
    public function createPayment(
        $demandeId,
        $citoyenId,
        $montant,
        $methodePaiement = 'virement',
        $description = null
    ): Payment {
        $payment = Payment::create([
            'demande_id' => $demandeId,
            'citoyen_id' => $citoyenId,
            'montant' => $montant,
            'methode_paiement' => $methodePaiement,
            'description' => $description,
            'reference_recu' => $this->generateReceiptReference(),
            'statut' => 'pending',
        ]);

        // Enregistre la création en audit log
        AuditService::logCreate($payment, request());

        return $payment;
    }

    /**
     * Marque le paiement comme complété et génère le reçu
     */
    public function markAsPaid(Payment $payment, $numeroTransaction = null): Payment
    {
        $oldValues = $payment->toArray();

        $payment->update([
            'statut' => 'paid',
            'date_paiement' => now(),
            'numero_transaction' => $numeroTransaction,
        ]);

        // Générer le reçu PDF
        $this->generateReceipt($payment);

        // Enregistre la mise à jour en audit log
        AuditService::logUpdate(
            $payment,
            $oldValues,
            $payment->fresh()->toArray(),
            request()
        );

        return $payment;
    }

    /**
     * Annule un paiement
     */
    public function cancel(Payment $payment, $raison = null): Payment
    {
        $oldValues = $payment->toArray();

        $payment->update([
            'statut' => 'cancelled',
            'description' => $raison,
        ]);

        // Enregistre l'annulation
        AuditService::logUpdate(
            $payment,
            $oldValues,
            $payment->fresh()->toArray(),
            request()
        );

        return $payment;
    }

    /**
     * Rembourse un paiement
     */
    public function refund(Payment $payment, $raison = null): Payment
    {
        $oldValues = $payment->toArray();

        $payment->update([
            'statut' => 'refunded',
            'description' => $raison,
        ]);

        // Enregistre le remboursement
        AuditService::logUpdate(
            $payment,
            $oldValues,
            $payment->fresh()->toArray(),
            request()
        );

        return $payment;
    }

    /**
     * Retourne les informations formattées du paiement pour le reçu
     */
    public function getFormattedPaymentInfo(Payment $payment): array
    {
        $status = PaymentStatus::tryFrom($payment->statut);
        $method = PaymentMethod::tryFrom($payment->methode_paiement);

        return [
            'reference' => $payment->reference_recu,
            'montant' => number_format($payment->montant, 2, ',', ' ') . ' ' . $payment->devise,
            'date_creation' => $payment->created_at->format('d/m/Y H:i'),
            'date_paiement' => $payment->date_paiement?->format('d/m/Y H:i') ?? '—',
            'statut' => $status?->label() ?? $payment->statut,
            'methode' => $method?->label() ?? $payment->methode_paiement,
        ];
    }
}

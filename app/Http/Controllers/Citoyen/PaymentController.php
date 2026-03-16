<?php

namespace App\Http\Controllers\Citoyen;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Payment;
use App\Services\PaymentReceiptService;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    protected PaymentReceiptService $receiptService;

    public function __construct(PaymentReceiptService $receiptService)
    {
        $this->middleware('auth');
        $this->middleware('role:citoyen');
        $this->receiptService = $receiptService;
    }

    /**
     * Affiche la liste des paiements du citoyen
     */
    public function index(): View
    {
        $payments = auth()->user()->payments()
            ->with('demande')
            ->latest()
            ->paginate(15);

        $statistiques = [
            'total_montant' => auth()->user()->payments()
                ->where('statut', 'paid')
                ->sum('montant'),
            'paiements_en_attente' => auth()->user()->payments()
                ->where('statut', 'pending')
                ->sum('montant'),
            'nombre_payes' => auth()->user()->payments()
                ->where('statut', 'paid')
                ->count(),
            'nombre_en_attente' => auth()->user()->payments()
                ->where('statut', 'pending')
                ->count(),
        ];

        return view('citoyen.payments.index', [
            'payments' => $payments,
            'statistiques' => $statistiques,
        ]);
    }

    /**
     * Crée un paiement pour une demande
     */
    public function create(Demande $demande): View
    {
        $this->authorize('view', $demande);

        // Vérifier s'il y a déjà un paiement valide
        $existingPayment = $demande->payments()
            ->whereIn('statut', ['paid', 'pending'])
            ->first();

        return view('citoyen.payments.create', [
            'demande' => $demande,
            'existingPayment' => $existingPayment,
        ]);
    }

    /**
     * Stocke un nouveau paiement
     */
    public function store(Request $request, Demande $demande): RedirectResponse
    {
        $this->authorize('view', $demande);

        $validated = $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'methode_paiement' => 'required|in:virement,cheque,especes,carte,mobile_money',
            'description' => 'nullable|string|max:500',
        ]);

        $payment = $this->receiptService->createPayment(
            demandeId: $demande->id,
            citoyenId: auth()->id(),
            montant: $validated['montant'],
            methodePaiement: $validated['methode_paiement'],
            description: $validated['description']
        );

        return redirect()->route('citoyen.payments.show', $payment)
            ->with('success', 'Paiement créé avec succès. Référence: ' . $payment->reference_recu);
    }

    /**
     * Affiche les détails d'un paiement
     */
    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load('demande', 'citoyen');

        return view('citoyen.payments.show', [
            'payment' => $payment,
            'formattedInfo' => $this->receiptService->getFormattedPaymentInfo($payment),
        ]);
    }

    /**
     * Marque le paiement comme effectué
     */
    public function markAsPaid(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('update', $payment);

        $validated = $request->validate([
            'numero_transaction' => 'nullable|string|max:100',
        ]);

        $this->receiptService->markAsPaid(
            $payment,
            $validated['numero_transaction'] ?? null
        );

        return redirect()->back()->with('success', 'Paiement marqué comme payé. Reçu généré.');
    }

    /**
     * Télécharge le reçu PDF
     */
    public function downloadReceipt(Payment $payment): Response
    {
        $this->authorize('view', $payment);

        // Vérifier que le reçu existe
        if (!$payment->isPaid()) {
            return response()->json([
                'message' => 'Impossible de télécharger le reçu pour un paiement non complété'
            ], 403);
        }

        $filename = "recu_paiement_{$payment->reference_recu}.pdf";
        $path = storage_path('app/public/receipts/receipt_' . $payment->reference_recu . '.pdf');

        if (!file_exists($path)) {
            // Régénérer le reçu s'il n'existe pas
            $this->receiptService->generateReceipt($payment);
        }

        return response()->download($path, $filename);
    }

    /**
     * Affiche l'aperçu du reçu PDF
     */
    public function previewReceipt(Payment $payment)
    {
        $this->authorize('view', $payment);

        if (!$payment->isPaid()) {
            return response()->json([
                'message' => 'Impossible de prévisualiser le reçu pour un paiement non complété'
            ], 403);
        }

        $payment->load('demande', 'citoyen');

        $data = [
            'payment' => $payment,
            'demande' => $payment->demande,
            'citoyen' => $payment->citoyen,
        ];

        $pdf = \PDF::loadView('payments.receipt', $data);
        return $pdf->stream("recu_paiement_{$payment->reference_recu}.pdf");
    }

    /**
     * Annule un paiement
     */
    public function cancel(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('update', $payment);

        if ($payment->isPaid()) {
            return redirect()->back()->with('error', 'Impossible d\'annuler un paiement déjà effectué');
        }

        $validated = $request->validate([
            'raison' => 'nullable|string|max:500',
        ]);

        $this->receiptService->cancel($payment, $validated['raison'] ?? null);

        return redirect()->route('citoyen.payments.index')
            ->with('success', 'Paiement annulé avec succès');
    }
}

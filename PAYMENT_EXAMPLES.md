# 💳 Exemples d'utilisation du système de paiement

## Exemples pratiques d'intégration

### 1. Créer un paiement pour une demande

```php
<?php
namespace App\Http\Controllers\Citoyen;

use App\Models\Demande;
use App\Models\Payment;
use App\Services\PaymentReceiptService;

class PaymentExampleController
{
    public function createPaymentForDemande(Demande $demande, PaymentReceiptService $service)
    {
        // Créer un paiement
        $payment = $service->createPayment(
            demandeId: $demande->id,
            citoyenId: auth()->id(),
            montant: 50000,
            methodePaiement: 'virement',
            description: 'Frais de traitement de la demande'
        );

        return response()->json([
            'success' => true,
            'payment' => $payment,
            'reference' => $payment->reference_recu
        ]);
    }
}
```

### 2. Marquer un paiement comme payé avec génération de reçu

```php
<?php
namespace App\Http\Controllers\Citoyen;

use App\Models\Payment;
use App\Services\PaymentReceiptService;

class PaymentExampleController
{
    public function markPaymentAsPaid(
        Payment $payment, 
        PaymentReceiptService $service
    ) {
        // Marquer comme payé (le reçu est généré automatiquement)
        $service->markAsPaid(
            payment: $payment,
            numeroTransaction: 'TRX20260316001122'
        );

        // La méthode retourne true en cas de succès
        return redirect()->back()
            ->with('success', 'Paiement confirmé et reçu généré');
    }
}
```

### 3. Télécharger un reçu PDF

```php
<?php
namespace App\Http\Controllers\Citoyen;

use App\Models\Payment;
use App\Services\PaymentReceiptService;
use Illuminate\Support\Facades\Storage;

class PaymentController
{
    public function downloadReceipt(
        Payment $payment,
        PaymentReceiptService $service
    ) {
        // Vérifier les permissions
        $this->authorize('view', $payment);

        // Télécharger le fichier PDF
        if (!Storage::disk('public')->exists($payment->chemin_recu)) {
            return abort(404, 'Reçu non trouvé');
        }

        return Storage::disk('public')
            ->download($payment->chemin_recu, "recu_{$payment->reference_recu}.pdf");
    }
}
```

### 4. Utiliser dans un formulaire Blade

```blade
<!-- resources/views/citoyen/demandes/show.blade.php -->

@if($demande->statut === 'acceptee')
    <div class="card">
        <div class="card-header bg-info">
            <h5>💳 Paiement requis</h5>
        </div>
        <div class="card-body">
            @if($demande->payments->isEmpty())
                <!-- Aucun paiement créé -->
                <a href="{{ route('citoyen.payment.create', $demande) }}" 
                   class="btn btn-primary">
                    Créer un paiement
                </a>
            @else
                <!-- Afficher les paiements existants -->
                @foreach($demande->payments as $payment)
                    <div class="alert alert-info">
                        <strong>Référence:</strong> {{ $payment->reference_recu }} <br>
                        <strong>Montant:</strong> {{ $payment->montant }} {{ $payment->devise }} <br>
                        <strong>Statut:</strong> 
                        <span class="badge badge-{{ $payment->statut === 'paid' ? 'success' : 'warning' }}">
                            {{ ucfirst($payment->statut) }}
                        </span>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endif
```

### 5. Afficher les statistiques de paiement

```php
<?php
namespace App\Http\Controllers\Citoyen;

use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class DashboardController
{
    public function index()
    {
        $citoyen = Auth::user();

        // Récupérer les statistiques
        $totalPayments = $citoyen->payments()->sum('montant');
        $paidPayments = $citoyen->payments()
            ->where('statut', 'paid')
            ->sum('montant');
        $pendingPayments = $citoyen->payments()
            ->where('statut', 'pending')
            ->count();

        return view('citoyen.dashboard', [
            'totalPayments' => $totalPayments,
            'paidPayments' => $paidPayments,
            'pendingPayments' => $pendingPayments,
            'recentPayments' => $citoyen->payments()
                ->latest()
                ->limit(5)
                ->get()
        ]);
    }
}
```

### 6. Créer un événement après paiement

```php
<?php
namespace App\Events;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Payment $payment)
    {
        //
    }
}

// Dans PaymentReceiptService
public function markAsPaid(Payment $payment, ?string $numeroTransaction = null): bool
{
    $payment->statut = 'paid';
    $payment->numero_transaction = $numeroTransaction;
    $payment->date_paiement = now();
    $payment->save();

    // Générer le reçu
    $payment->chemin_recu = $this->generateReceipt($payment);
    $payment->save();

    // Déclencher l'événement
    event(new PaymentCompleted($payment));

    return true;
}

// Listener pour envoyer un email
<?php
namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Mail\PaymentReceiptMail;
use Illuminate\Support\Facades\Mail;

class SendPaymentReceipt
{
    public function handle(PaymentCompleted $event)
    {
        Mail::to($event->payment->citoyen->email)
            ->send(new PaymentReceiptMail($event->payment));
    }
}
```

### 7. API REST - Récupérer les paiements

```php
<?php
namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentApiController
{
    public function index(Request $request)
    {
        // Récupérer tous les paiements du citoyen connecté
        $payments = auth()->user()
            ->payments()
            ->with('demande')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $payments,
            'meta' => [
                'total' => $payments->total(),
                'per_page' => $payments->perPage(),
                'current_page' => $payments->currentPage()
            ]
        ]);
    }

    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);

        return response()->json([
            'success' => true,
            'data' => $payment->load('demande', 'citoyen')
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'demande_id' => 'required|exists:demandes,id',
            'montant' => 'required|numeric|min:0.01',
            'methode_paiement' => 'required|in:virement,cheque,especes,carte,mobile_money',
            'description' => 'nullable|string|max:500'
        ]);

        $payment = Payment::create([
            ...$validated,
            'citoyen_id' => auth()->id(),
            'statut' => 'pending',
            'reference_recu' => Payment::generateUniqueReference()
        ]);

        return response()->json([
            'success' => true,
            'data' => $payment
        ], 201);
    }
}
```

### 8. Queryables et Scopes

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Payment extends Model
{
    // Scopes
    public function scopePaid($query)
    {
        return $query->where('statut', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('statut', 'pending');
    }

    public function scopeByDemande($query, $demandeId)
    {
        return $query->where('demande_id', $demandeId);
    }

    public function scopeByDate($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Utilisation
    $payment = Payment::paid()
        ->byDemande($demande->id)
        ->first();
}
```

### 9. Générateur de rapport de paiement

```php
<?php
namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentReportService
{
    public function getMonthlySummary(?string $userId = null)
    {
        $query = Payment::where('statut', 'paid');

        if ($userId) {
            $query->where('citoyen_id', $userId);
        }

        return $query
            ->groupByRaw('DATE_TRUNC(\'month\', date_paiement)')
            ->select(
                DB::raw('DATE_TRUNC(\'month\', date_paiement) as mois'),
                DB::raw('COUNT(*) as nombre'),
                DB::raw('SUM(montant) as total'),
                DB::raw('AVG(montant) as moyenne')
            )
            ->orderByDesc('mois')
            ->get();
    }

    public function getPaymentMethodStats()
    {
        return Payment::where('statut', 'paid')
            ->groupBy('methode_paiement')
            ->select(
                'methode_paiement',
                DB::raw('COUNT(*) as nombre'),
                DB::raw('SUM(montant) as total')
            )
            ->get();
    }
}
```

### 10. Tests unitaires

```php
<?php
namespace Tests\Unit;

use App\Models\Payment;
use App\Models\Demande;
use App\Models\User;
use App\Services\PaymentReceiptService;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    public function test_payment_reference_is_unique()
    {
        $payment1 = Payment::factory()->create();
        $payment2 = Payment::factory()->create();

        $this->assertNotEqual(
            $payment1->reference_recu,
            $payment2->reference_recu
        );
    }

    public function test_payment_belongs_to_demande()
    {
        $payment = Payment::factory()->create();
        $this->assertInstanceOf(Demande::class, $payment->demande);
    }

    public function test_payment_belongs_to_user()
    {
        $payment = Payment::factory()->create();
        $this->assertInstanceOf(User::class, $payment->citoyen);
    }

    public function test_generate_receipt_creates_pdf()
    {
        $payment = Payment::factory()->create(['statut' => 'pending']);
        $service = app(PaymentReceiptService::class);

        $service->markAsPaid($payment);

        $this->assertTrue(
            \Storage::disk('public')->exists($payment->chemin_recu)
        );
    }
}
```

## 🎯 Bonnes pratiques

1. **Toujours vérifier les permissions:**
   ```php
   $this->authorize('view', $payment);
   ```

2. **Valider les montants:**
   ```php
   $validated = $request->validate([
       'montant' => 'required|numeric|min:0.01|max:999999.99'
   ]);
   ```

3. **Utiliser les transactions DB pour plusieurs opérations:**
   ```php
   DB::transaction(function () {
       $payment = Payment::create($data);
       $service->markAsPaid($payment);
       event(new PaymentCompleted($payment));
   });
   ```

4. **Logger les opérations sensibles:**
   ```php
   \Log::info('Payment marked as paid', [
       'payment_id' => $payment->id,
       'user_id' => auth()->id(),
       'amount' => $payment->montant
   ]);
   ```

5. **Tester les scénarios d'erreur:**
   ```php
   $this->expectException(AuthorizationException::class);
   $this->authorize('view', $otherUserPayment);
   ```

# 🎨 Architecture - Diagrammes visuels

## 🏗️ Architecture du système

```
┌─────────────────────────────────────────────────────────────────┐
│                    MAIRI - PAYMENT SYSTEM                        │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────┐         ┌──────────────────┐
│  Citoyen         │         │  Admin/Agent     │
│  (Frontend)      │         │  (Monitoring)    │
└────────┬─────────┘         └────────┬─────────┘
         │                            │
         │ HTTP Requests              │ Supervise
         └─────────────┬──────────────┘
                       │
         ┌─────────────▼──────────────┐
         │   PaymentController        │
         │  (8 Endpoints)             │
         │  - index()                 │
         │  - create()                │
         │  - store()                 │
         │  - show()                  │
         │  - markAsPaid()            │
         │  - downloadReceipt()       │
         │  - previewReceipt()        │
         │  - cancel()                │
         └─────────────┬──────────────┘
                       │
         ┌─────────────▼──────────────┐
         │  PaymentReceiptService     │
         │  (Business Logic)          │
         │  - createPayment()         │
         │  - generateReceipt()       │
         │  - markAsPaid()            │
         │  - cancel()                │
         │  - refund()                │
         └─────────────┬──────────────┘
                       │
      ┌────────────────┼────────────────┐
      │                │                │
      ▼                ▼                ▼
   MySQL DB       Storage (PDF)    Policies
   (Payment)      (Physical Files) (Authorization)
```

---

## 📊 Flux de données

```
FLUX DE PAIEMENT COMPLET
═══════════════════════════

1. CRÉER LE PAIEMENT
   ┌──────────────────────────────────────────┐
   │ POST /demandes/{id}/paiement             │
   │ - montant                                │
   │ - methode_paiement                       │
   │ - description                            │
   └──────────┬───────────────────────────────┘
              │
              ▼
   ┌──────────────────────────────────────────┐
   │ PaymentController::store()               │
   │ - Validé montant & méthode               │
   │ - Crée l'enregistrement                  │
   │ - Génère référence unique                │
   └──────────┬───────────────────────────────┘
              │
              ▼
   ┌──────────────────────────────────────────┐
   │ Base de données                          │
   │ Nouveau payment: statut = "pending"      │
   │ reference_recu = "REC-20260316..."       │
   └──────────────────────────────────────────┘

2. MARQUER COMME PAYÉ

   ┌──────────────────────────────────────────┐
   │ POST /paiements/{id}/marquer-paye        │
   │ - numero_transaction (optionnel)         │
   └──────────┬───────────────────────────────┘
              │
              ▼
   ┌──────────────────────────────────────────┐
   │ PaymentController::markAsPaid()          │
   │ - Vérifie les permissions               │
   │ - Appel le service                       │
   └──────────┬───────────────────────────────┘
              │
              ▼
   ┌──────────────────────────────────────────┐
   │ PaymentReceiptService::markAsPaid()      │
   │ - Définit statut = "paid"                │
   │ - Enregistre la date de paiement         │
   │ - GÉNÈRE LE PDF REÇU                     │
   └──────────┬───────────────────────────────┘
              │
              ▼
   ┌──────────────────────────────────────────┐
   │ Blade Template: payments/receipt.blade   │
   │ - Données du paiement                    │
   │ - Infos citoyen & demande                │
   │ - Formatage professionnel                │
   └──────────┬───────────────────────────────┘
              │
              ▼
   ┌──────────────────────────────────────────┐
   │ DomPDF Engine                            │
   │ - Compile HTML → PDF                     │
   │ - Applique CSS pour mise en forme        │
   └──────────┬───────────────────────────────┘
              │
              ▼
   ┌──────────────────────────────────────────┐
   │ Storage: storage/app/public/receipts/    │
   │ receipt_REC-20260316103000-ABC123.pdf    │
   └──────────┬───────────────────────────────┘
              │
              ▼
   ┌──────────────────────────────────────────┐
   │ Base de données (mis à jour)             │
   │ payment:                                 │
   │ - statut = "paid"                        │
   │ - date_paiement = maintenant             │
   │ - chemin_recu = "receipts/receipt..."   │
   └──────────────────────────────────────────┘

3. TÉLÉCHARGER LE REÇU

   ┌──────────────────────────────────────────┐
   │ GET /paiements/{id}/recu/telechargement  │
   └──────────┬───────────────────────────────┘
              │
              ▼
   ┌──────────────────────────────────────────┐
   │ PaymentController::downloadReceipt()     │
   │ - Vérifie les permissions               │
   │ - Vérifie que le fichier existe         │
   └──────────┬───────────────────────────────┘
              │
              ▼
   ┌──────────────────────────────────────────┐
   │ Storage::download()                      │
   │ - Lit le fichier PDF                     │
   │ - Définit les headers HTTP              │
   │ - Envoie au navigateur                  │
   └──────────┬───────────────────────────────┘
              │
              ▼
   ┌──────────────────────────────────────────┐
   │ Navigateur                               │
   │ receipt_REC-20260316103000-ABC123.pdf    │
   │ Téléchargement ou affichage              │
   └──────────────────────────────────────────┘
```

---

## 🔄 Diagramme des statuts

```
CYCLE DE VIE D'UN PAIEMENT
══════════════════════════

                    ┌─────────────┐
                    │  CRÉATION   │
                    │ (pending)   │
                    └──────┬──────┘
                           │
                ┌──────────┴──────────┐
                │                     │
                ▼                     ▼
        ┌──────────────┐      ┌──────────────┐
        │ CONFIRMATION │      │  ANNULATION  │
        │   (paid)     │      │(cancelled)   │
        │  [PDF Gen]   │      │              │
        └──────┬───────┘      └──────────────┘
               │
               ▼
        ┌──────────────┐
        │ REMBOURSEMENT│
        │  (refunded)  │
        └──────────────┘

Transitions autorisées:
- pending → paid (via markAsPaid)
- pending → cancelled (via cancel)
- paid → refunded (via refund)

Terminal states: cancelled, refunded
(Pas de transition possible après)
```

---

## 📁 Structure des dossiers

```
MAIRI Project
│
├── app/
│   ├── Models/
│   │   ├── Payment.php                 ← Modèle principal
│   │   ├── User.php                    (updated: payments method)
│   │   └── Demande.php                 (updated: payments method)
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Citoyen/
│   │   │       └── PaymentController.php
│   │   │
│   │   └── Middleware/
│   │       └── CheckRole.php           (utilisé pour auth)
│   │
│   ├── Services/
│   │   └── PaymentReceiptService.php   ← Logique métier
│   │
│   └── Policies/
│       └── PaymentPolicy.php           ← Authorization
│
├── database/
│   └── migrations/
│       └── 2026_03_16_000005_create_payments_table.php
│
├── resources/
│   └── views/
│       ├── citoyen/
│       │   └── payments/
│       │       ├── index.blade.php     ← Liste des paiements
│       │       ├── create.blade.php    ← Créer paiement
│       │       └── show.blade.php      ← Détails paiement
│       │
│       └── payments/
│           └── receipt.blade.php       ← Template PDF
│
├── storage/
│   └── app/
│       └── public/
│           └── receipts/               ← PDFs générés
│               ├── receipt_REC-202603161...pdf
│               ├── receipt_REC-202603162...pdf
│               └── ...
│
├── routes/
│   └── web.php                         (8 endpoints ajoutés)
│
├── tests/
│   ├── Feature/
│   │   └── PaymentTest.php             (à ajouter)
│   └── Unit/
│       └── PaymentServiceTest.php      (à ajouter)
│
├── config/
│   └── dompdf.php                      ← Config PDF
│
├── PAYMENT_README.md                   ← Navigation docs
├── QUICKSTART_PAYMENT.md               ← Démarrage rapide
├── PAYMENTS.md                         ← Vue d'ensemble
├── PAYMENT_SETUP.md                    ← Configuration
├── PAYMENT_EXAMPLES.md                 ← Exemples code
└── PAYMENT_API.md                      ← Endpoints REST
```

---

## 🔌 Relation entre entités

```
MODÈLE DE DONNÉES
═════════════════

┌─────────────────┐
│     User        │
├─────────────────┤
│ id (PK)         │
│ nom             │
│ email           │
│ role            │
│ statut          │
│ ...             │
└────────┬────────┘
         │
         │ 1:N (citoyen -> payments)
         │
    ┌────▼──────────────┐
    │                   │
    ▼                   ▼
    
┌──────────────────────────┐      ┌──────────────────┐
│      Payment             │      │     Demande      │
├──────────────────────────┤      ├──────────────────┤
│ id (PK)                  │      │ id (PK)          │
│ demande_id (FK) ─────────┼──────┤ id               │
│ citoyen_id (FK) ─────┐   │      │ citoyen_id       │
│ montant              │   │      │ statut           │
│ devise               │   │      │ priorite         │
│ methode_paiement     │   │      │ ...              │
│ statut               │   │      └──────────────────┘
│ numero_transaction   │   │
│ date_paiement        │   │
│ reference_recu       │   │
│ chemin_recu          │   │
│ description          │   │
│ created_at           │   │
│ updated_at           │   │
└──────────────────────────┘
         │
         │ User citoyen (FK)
         │
         └──────────────────┘

Relationships:
- Payment belongsTo User (citoyen)
- Payment belongsTo Demande
- User hasMany Payments
- Demande hasMany Payments
```

---

## 🌐 Flux HTTP (Vue serveur)

```
CLIENT REQUEST
│
├─ URL: /citoyen/paiements/1/marquer-paye
├─ METHOD: POST
├─ HEADERS: Authorization, Content-Type
└─ BODY: { numero_transaction: '...' }
│
▼
Router (routes/web.php)
│
├─ Middleware: auth
├─ Middleware: CheckRole (citoyen)
│
▼
PaymentController::markAsPaid($payment)
│
├─ $this->authorize('update', $payment)  ← Vérifie PaymentPolicy
│
▼
PaymentReceiptService::markAsPaid()
│
├─ Update DB: statut = 'paid'
├─ Update DB: date_paiement = now()
├─ Call: $this->generateReceipt($payment)
│
▼
PDF Generation (DomPDF)
│
├─ Load: resources/views/payments/receipt.blade.php
├─ Compile: HTML + CSS → PDF
├─ Save: storage/app/public/receipts/receipt_....pdf
├─ Return: 'receipts/receipt_...pdf'
│
▼
Update DB: chemin_recu

▼
HTTP Response (200 OK)
│
└─ JSON: { success: true, payment: {...} }
```

---

## 🔐 Flux de sécurité

```
AUTHENTIFICATION & AUTORISATION
═════════════════════════════════

CLIENT
│
├─ Auth::check() ← Utilisateur connecté?
├─ Auth::user()->role === 'citoyen' ← Bon rôle?
│
▼
PaymentPolicy::view($user, $payment)
├─ $user->id === $payment->citoyen_id ← Propriétaire?
├─ Retourne: true ou AuthorizationException
│
▼
Si autorisé: Continue
Si non autorisé: 403 Forbidden
```

---

## 📈 Flux de données (JSON)

```
CREATE PAYMENT REQUEST
══════════════════════

POST /citoyen/demandes/5/paiement
Content-Type: application/json

{
  "montant": 50000,
  "methode_paiement": "virement",
  "devise": "XOF",
  "description": "Frais de traitement"
}

    ▼

VALIDATION (Laravel Request)
├─ montant: numeric, min=0.01, max=999999.99 ✓
├─ methode_paiement: in:virement,cheque,... ✓
├─ devise: optional, string ✓
└─ description: optional, max=500 ✓

    ▼

DATABASE INSERT
├─ demande_id = 5
├─ citoyen_id = auth()->id()
├─ montant = 50000
├─ methode_paiement = 'virement'
├─ devise = 'XOF'
├─ description = 'Frais de traitement'
├─ statut = 'pending'
├─ reference_recu = 'REC-20260316...'
├─ numero_transaction = null
├─ date_paiement = null
├─ chemin_recu = null
└─ created_at/updated_at = now()

    ▼

HTTP RESPONSE
{
  "success": true,
  "payment": {
    "id": 1,
    "demande_id": 5,
    "citoyen_id": 2,
    "montant": "50000.00",
    "devise": "XOF",
    "methode_paiement": "virement",
    "statut": "pending",
    "reference_recu": "REC-20260316103245-XYZ789",
    "created_at": "2026-03-16T10:32:45.000000Z"
  }
}
```

---

## ⚙️ Classe PaymentReceiptService

```
PaymentReceiptService
│
├── PUBLIC METHODS
│   ├─ createPayment(...)
│   │  └─ Crée et retourne un Payment
│   │
│   ├─ generateReceipt(Payment)
│   │  └─ Génère PDF, retourne le chemin
│   │
│   ├─ markAsPaid(Payment, ?string)
│   │  └─ Met à jour statut + génère PDF
│   │
│   ├─ cancel(Payment)
│   │  └─ Marque comme annulé
│   │
│   └─ refund(Payment)
│      └─ Marque comme remboursé
│
└── PRIVATE/HELPER METHODS
    ├─ generateReceiptReference()
    │  └─ Crée ref unique: REC-...
    │
    └─ getFormattedPaymentInfo(Payment)
       └─ Prépare les données pour le PDF
```

---

## 🧮 Performance (Estimation)

```
OPÉRATION              TEMPS      RESSOURCES
═════════════════════════════════════════════
Créer paiement        ~50ms      Faible
Marquer comme payé    ~200ms     Moyen (gen PDF)
Générer PDF           ~150ms     Moyen-Haut
Télécharger PDF       ~100ms     Réseau
Lister paiements      ~50ms      Faible

STOCKAGE
═════════════════
Taille PDF moyen      ~50KB
Par 1000 paiements    ~50MB
Limite recommandée    1000s
```

Ce système est optimisé pour:
- ✅ Petites à moyennes quantités
- ✅ Performance acceptable
- ✅ Scalabilité modérée

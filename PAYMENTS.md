# 💳 Système de Gestion des Paiements - Mairi

Gestion complète des paiements et génération automatique de reçus PDF pour les demandes citoyennes.

## 🎯 Fonctionnalités

### Pour les citoyens:
✅ Créer un paiement pour une demande  
✅ Enregistrer le montant et la méthode de paiement  
✅ Consulter l'historique de tous les paiements  
✅ Confirmer le paiement et générer automatiquement un reçu  
✅ Télécharger le reçu en PDF  
✅ Prévisualiser le reçu en PDF  
✅ Imprimer le reçu  
✅ Annuler un paiement en attente  

### Référence unique:
- Chaque paiement reçoit une référence unique: `REC-YYYYMMDDhhmmss-XXXXXX`
- Format: Reçu + Date/Heure + Aléatoire
- Utilisée pour l'identification et le suivi

## 📊 Structure de la base de données

### Table `payments`
```sql
- id (PK)
- demande_id (FK)
- citoyen_id (FK)
- montant (decimal)
- devise (string) - XOF, EUR, USD, etc.
- methode_paiement (enum) - virement, cheque, especes, carte, mobile_money
- statut (enum) - pending, paid, cancelled, refunded
- numero_transaction (string, nullable)
- date_paiement (datetime, nullable)
- reference_recu (string, unique)
- description (text, nullable)
- timestamps
```

## 🔄 Flux de paiement

```
1. Citoyen crée une demande
   ↓
2. Citoyen clique "Payer" sur la demande
   ↓
3. Crée un paiement (en attente)
   → Référence unique générée automatiquement
   ↓
4. Citoyen confirme le paiement
   → Date de paiement enregistrée
   → Reçu PDF généré automatiquement
   ↓
5. Citoyen télécharge/imprime le reçu
```

## 🗂️ Fichiers créés

### Modèles
- `app/Models/Payment.php` - Modèle payment

### Contrôleurs
- `app/Http/Controllers/Citoyen/PaymentController.php` - Gestion des paiements

### Services
- `app/Services/PaymentReceiptService.php` - Génération des reçus PDF

### Policies
- `app/Policies/PaymentPolicy.php` - Autorisation d'accès

### Vues
- `resources/views/citoyen/payments/index.blade.php` - Liste des paiements
- `resources/views/citoyen/payments/create.blade.php` - Création de paiement
- `resources/views/citoyen/payments/show.blade.php` - Détails du paiement
- `resources/views/payments/receipt.blade.php` - Template reçu PDF

### Migrations
- `database/migrations/2026_03_16_000005_create_payments_table.php`

### Routes
```
GET    /citoyen/paiements                           → Liste des paiements
GET    /citoyen/demandes/{demande}/paiement/creer   → Créer un paiement
POST   /citoyen/demandes/{demande}/paiement         → Stocker le paiement
GET    /citoyen/paiements/{payment}                 → Détails du paiement
POST   /citoyen/paiements/{payment}/marquer-paye    → Marquer comme payé
POST   /citoyen/paiements/{payment}/annuler         → Annuler le paiement
GET    /citoyen/paiements/{payment}/recu/telechargement → Télécharger PDF
GET    /citoyen/paiements/{payment}/recu/apercu     → Prévisualiser PDF
```

## 📝 Utilisation

### Créer un paiement
```php
$paymentService = app(PaymentReceiptService::class);

$payment = $paymentService->createPayment(
    demandeId: $demande->id,
    citoyenId: auth()->id(),
    montant: 50000,
    methodePaiement: 'virement',
    description: 'Paiement de certificat de résidence'
);
```

### Marquer comme payé
```php
$paymentService->markAsPaid(
    payment: $payment,
    numeroTransaction: 'TRX2026031600123'
);
// Le reçu PDF est automatiquement généré
```

### Annuler un paiement
```php
$paymentService->cancel(
    payment: $payment,
    raison: 'Demande annulée'
);
```

### Rembourser un paiement
```php
$paymentService->refund(
    payment: $payment,
    raison: 'Demande rejetée'
);
```

## 📋 Champs du reçu PDF

Le reçu contient:
- 🏢 En-tête MAIRI avec logo
- 📌 Numéro de référence unique
- 💰 Montant en chiffres et lettres
- 📅 Dates (création et paiement)
- 👤 Informations du citoyen
- 📄 Détails de la demande
- ✅ Statut du paiement
- 💳 Méthode de paiement
- 🔢 Numéro de transaction
- 📝 Notes additionnelles

## 🔒 Sécurité

### Policies d'autorisation:
- ✅ Citoyen ne voit que ses paiements
- ✅ Citoyen ne peut modifier que ses paiements en attente
- ✅ Admin seul peut supprimer
- ✅ Vérification automatique des droits

### Validation:
- Montant positif et validé
- Référence unique garantie
- Dates formatées et valides
- Méthodes de paiement limitées

## 📱 Méthodes de paiement

- 🏦 **Virement bancaire**
- 📄 **Chèque**
- 💵 **Espèces**
- 💳 **Carte bancaire**
- 📱 **Paiement mobile**

## 🎨 Statuts du paiement

| Statut | Couleur | Signification |
|--------|--------|---------------|
| `pending` | 🟡 Jaune | En attente de confirmation |
| `paid` | 🟢 Vert | Paiement effectué et reçu généré |
| `cancelled` | 🔴 Rouge | Paiement annulé |
| `refunded` | 🔵 Bleu | Paiement remboursé |

## 📦 Installation

### 1. Exécuter les migrations
```bash
php artisan migrate
```

### 2. Publier les assets (stockage)
```bash
php artisan storage:link
```

### 3. Tester
```bash
php artisan test
```

## 🚀 Next Steps

### Améliorations futures:
- [ ] Intégration avec des passerelles de paiement (Stripe, PayPal)
- [ ] Emails de confirmation automatiques
- [ ] Rappels de paiement
- [ ] Export de rapports financiers
- [ ] Dashboard financier pour l'admin
- [ ] Statistiques de paiement par type
- [ ] Notifications SMS
- [ ] Signatures numériques

## 📞 Support

Pour des questions ou des problèmes avec les paiements, consultez:
- `TESTING.md` pour les tests
- `GITHUB_ACTIONS_GUIDE.md` pour le CI/CD
- Code source dans `app/Services/PaymentReceiptService.php`

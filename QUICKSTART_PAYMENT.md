# 🚀 Quickstart - Système de paiement

## ⚡ Mise en place en 5 minutes

### ✅ Prérequis
- Laravel 12 avec PHP 8.2
- MySQL 8.0 configuré
- Composer installé
- `barryvdh/laravel-dompdf` déjà installé

---

## 1️⃣ Installation (1 minute)

```bash
# Aller dans le dossier du projet
cd c:\xampp\htdocs\mairi

# Étape 1: Les fichiers sont déjà créés ✅
# Les fichiers suivants existent déjà:
# - app/Models/Payment.php
# - app/Http/Controllers/Citoyen/PaymentController.php
# - app/Services/PaymentReceiptService.php
# - app/Policies/PaymentPolicy.php
# - database/migrations/2026_03_16_000005_create_payments_table.php
# - resources/views/citoyen/payments/*.blade.php
# - routes/web.php (routes ajoutées)
```

---

## 2️⃣ Configuration (2 minutes)

### Étape 1: Exécuter les migrations

```bash
# Créer les tables dans la base de données
php artisan migrate

# Résultat attendu:
# Migrating: 2026_03_16_000005_create_payments_table
# Migrated: 2026_03_16_000005_create_payments_table (...)
```

### Étape 2: Créer le lien de stockage

```bash
# Créer le lien symbolique pour accéder aux reçus
php artisan storage:link

# Résultat attendu:
# The [public/storage] directory has been linked.
```

### Étape 3: Définir les permissions (Windows)

Sur Windows, les dossiers doivent être accessibles en lecture/écriture:

```bash
# Vérifier les permissions (optionnel)
# Le dossier doit être accessible pour que PHP écrive les fichiers
type storage\app\public  # Vérifier la lecture
```

---

## 3️⃣ Tester (1 minute)

### Via Artisan Tinker

```bash
# Ouvrir la console PHP interactive
php artisan tinker

# Créer un utilisateur de test
$user = \App\Models\User::factory()->create([
    'role' => 'citoyen',
    'statut' => 'actif',
    'email' => 'citoyen@test.com'
]);

# Créer une demande
$demande = \App\Models\Demande::factory()->create([
    'citoyen_id' => $user->id,
    'statut' => 'acceptee'
]);

# Créer un paiement
$payment = \App\Models\Payment::create([
    'demande_id' => $demande->id,
    'citoyen_id' => $user->id,
    'montant' => 50000,
    'methode_paiement' => 'virement',
    'statut' => 'pending',
    'reference_recu' => \App\Models\Payment::generateUniqueReference()
]);

# Marquer comme payé (génère le reçu)
$service = app(\App\Services\PaymentReceiptService::class);
$service->markAsPaid($payment, 'TRX123456');

# Vérifier le reçu
dd($payment->chemin_recu);

# Quitter
exit
```

---

## 4️⃣ Lancer l'application (1 minute)

```bash
# Démarrer le serveur Laravel
php artisan serve

# Résultat attendu:
# Starting Laravel development server: http://127.0.0.1:8000
# Laravel development server started: http://127.0.0.1:8000
```

---

## 5️⃣ Accéder à l'application (0 minutes)

### Via le navigateur

```
http://localhost:8000
```

### Endpoints des paiements

```
GET    http://localhost:8000/citoyen/paiements                    → Liste des paiements
POST   http://localhost:8000/citoyen/demandes/1/paiement          → Créer un paiement
GET    http://localhost:8000/citoyen/paiements/1                  → Détails du paiement
POST   http://localhost:8000/citoyen/paiements/1/marquer-paye     → Marquer comme payé
GET    http://localhost:8000/citoyen/paiements/1/recu/apercu      → Prévisualiser reçu
GET    http://localhost:8000/citoyen/paiements/1/recu/telechargement → Télécharger reçu
POST   http://localhost:8000/citoyen/paiements/1/annuler          → Annuler paiement
```

---

## 🧪 Tester sans base de données

### Via PHPUnit

```bash
# Exécuter les tests
php artisan test

# Tests spécifiques aux paiements
php artisan test tests/Feature/PaymentTest.php

# Avec sortie détaillée
php artisan test --verbose
```

---

## 📋 Checklist de vérification

Cochez chaque point pour confirmer que tout fonctionne:

- [ ] Les migrations s'exécutent sans erreur
- [ ] Le lien de stockage existe: `php artisan storage:link`
- [ ] Vous pouvez créer un paiement via Tinker
- [ ] Le reçu PDF est généré automatiquement
- [ ] Le fichier PDF existe dans `storage/app/public/receipts/`
- [ ] Vous pouvez accéder à `/citoyen/paiements` dans le navigateur
- [ ] Les tests passent: `php artisan test`
- [ ] Les routes de paiement fonctionnent

---

## 🎯 Cas d'usage complet

### Scénario: Créer et payer une demande en tant que citoyen

#### 1. Se connecter
```
[URL] → http://localhost:8000/login
[Email] → citoyen@test.com
[Password] → password
```

#### 2. Créer une demande
```
[URL] → Dashboard → Nouvelle demande
[Titre] → "Certificat de résidence"
[Description] → "Demande de certificat"
[Priorité] → Normal
[Submit]
```

#### 3. Attendre l'acceptation (par un admin ou agent)
```
Statut passes de: pendante → en_cours → acceptee
```

#### 4. Créer un paiement
```
[URL] → Dashboard → Paiements → Créer paiement
[Montant] → 50000
[Méthode] → Virement
[Description] → Frais de traitement
[Submit]
```

#### 5. Confirmer le paiement
```
[URL] → Mes paiements → Détails du paiement
[Bouton] → Marquer comme payé
[Numéro Transaction] → TRX20260316001 (optionnel)
[Submit]
```

#### 6. Télécharger le reçu
```
[URL] → Mes paiements → Détails du paiement
[Bouton] → Télécharger reçu PDF
[Résultat] → receipt_REC-20260316...pdf téléchargé
```

---

## 🔧 Dépannage rapide

### Problème: "Table 'payments' doesn't exist"

**Solution:**
```bash
php artisan migrate
```

---

### Problème: "DOMPDF not found"

**Solution:**
```bash
composer require barryvdh/laravel-dompdf
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

---

### Problème: "Storage path not writable"

**Solution:**
```bash
# Windows: Donner les droits au dossier
# - Clic droit sur storage/ → Propriétés → Sécurité → Modifier
# - Donner les droits de lecture/écriture

# Ou via PowerShell admin:
# icacls $env:APPDATA\Roaming\cache /grant "%username%:(F)" /T
```

---

### Problème: Les reçus ne s'affichent pas

**Solution:**
```bash
# Vérifier le lien de stockage
php artisan storage:link

# Vérifier les fichiers
dir storage\app\public\receipts\

# Vérifier les logs
tail -f storage\logs\laravel.log
```

---

## 📚 Documentation complète

Pour plus de détails, consultez:

| Document | Description |
|----------|-------------|
| [PAYMENTS.md](PAYMENTS.md) | Vue d'ensemble du système |
| [PAYMENT_SETUP.md](PAYMENT_SETUP.md) | Configuration détaillée |
| [PAYMENT_API.md](PAYMENT_API.md) | Endpoints REST |
| [PAYMENT_EXAMPLES.md](PAYMENT_EXAMPLES.md) | 10 exemples pratiques |

---

## 🎓 Prochaines étapes

Après cette mise en place rapide:

1. **Intégrer les emails**
   - Envoyer un email au citoyen After paiement
   - Ajouter le lien du reçu à l'email

2. **Ajouter une API externe**
   - Intégrer Stripe ou PayPal
   - Synchroniser les paiements

3. **Dashboard admin**
   - Voir tous les paiements
   - Générer des rapports
   - Gérer les remboursements

4. **Notifications**
   - SMS de confirmation
   - Notifications push
   - Rappels de paiement

---

## 💾 Base de données initiale

Pour développer rapidement, vous pouvez importer ces dummy data:

```bash
# Créer les données de test
php artisan db:seed --class=PaymentSeeder

# Ou manuellement via Tinker
php artisan tinker

# Créer 5 utilisateurs citoyens
\App\Models\User::factory(5)->create(['role' => 'citoyen']);

# Créer 5 demandes
\App\Models\Demande::factory(5)->create(['statut' => 'acceptee']);

# Créer 10 paiements
\App\Models\Payment::factory(10)->create();
```

---

## ✨ Commandes utiles

```bash
# Voir tous les paiements
php artisan tinker
>>> \App\Models\Payment::all();

# Voir les paiements payés
>>> \App\Models\Payment::paid()->get();

# Compter les reçus
>>> glob(storage_path('app/public/receipts/*.pdf')) | count;

# Nettoyer les anciens reçus
>>> \File::deleteDirectory(storage_path('app/public/receipts'));

# Réinitialiser la base (!! ATTENTION !!)
>>> php artisan migrate:refresh
```

---

## 🆘 Support

Pour toute question ou problème:

1. **Consultez les logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Vérifiez la configuration:**
   ```bash
   php artisan config:show dompdf
   php artisan config:show filesystems
   ```

3. **Testez directement:**
   ```bash
   php artisan tinker
   ```

4. **Exécutez les tests:**
   ```bash
   php artisan test
   ```

---

## 🎉 Vous êtes prêt!

Le système de paiement est maintenant:
- ✅ Installé
- ✅ Configuré
- ✅ Testé
- ✅ Prêt à l'emploi

Bon développement! 🚀

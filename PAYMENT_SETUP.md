# 🔧 Configuration du système de paiement

## ✅ Checklist d'installation

### Étape 1: Dépendances requises

```bash
# Laravel DomPDF (si pas déjà installé)
composer require barryvdh/laravel-dompdf

# Publier la configuration DomPDF
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

### Étape 2: Migrations de la base de données

```bash
# Exécuter la migration des paiements
php artisan migrate

# Résultat attendu:
# Migration: 2026_03_16_000005_create_payments_table ... DONE
```

### Étape 3: Lien de stockage

```bash
# Créer le lien symbolique pour accéder aux fichiers de stockage
php artisan storage:link

# Cela crée: public/storage -> storage/app/public
# Les reçus seront accessibles via: /storage/receipts/{filename}.pdf
```

### Étape 4: Permissions des dossiers

```bash
# Linux/Mac - Définir les permissions de stockage
chmod -R 775 storage/app/public
chmod -R 775 bootstrap/cache

# Windows - Via PowerShell (admin)
# Les dossiers doivent être accessibles en lecture/écriture
```

### Étape 5: Configuration du fichier `.env`

```env
# Système de fichiers (local ou s3)
FILESYSTEM_DISK=local

# Configuration DomPDF
DOMPDF_ENABLE_REMOTE=false  # Pour la sécurité
DOMPDF_MEMORY_LIMIT=128M    # Augmenter si nécessaire
DOMPDF_UNICODE_ENABLED=true # Support Unicode/accents

# Si vous changez le mode d'application
APP_ENV=production  # ou development
APP_DEBUG=false     # false en production
```

### Étape 6: Configuration DomPDF avancée

Éditer `config/dompdf.php`:

```php
'dompdf' => [
    'enable_remote' => false,  // Sécurité
    'convert_entities' => true, // Convertir les entités HTML
    'isPhpEnabled' => false,    // Désactiver PHP dans PDF
    'isHtmlEntitiesEnabled' => true,
    'isRemoteEnabled' => false,
    'chroot' => __DIR__.'/../',
    'logOutputFile' => storage_path('logs/dompdf.log'),
    'tempDir' => sys_get_temp_dir(),
    'fontDir' => storage_path('fonts/'),
    'fontCache' => storage_path('fonts/'),
    'pdf_backend' => 'CPDF',  // CPDF ou GD
    'default_font' => 'Helvetica',
    'dpi' => 96,
    'enable_css_float' => true,
    'enable_javascript' => false,  // Sécurité
    'font_supplier_class' => \Barryvdh\DomPDF\FontLib\FontMetrics::class,
],

'font' => [
    'sans-serif' => 'Helvetica',
    'serif' => 'Times New Roman',
    'monospace' => 'Courier',
],
```

### Étape 7: Polices personnalisées (optionnel)

Pour ajouter des polices personnalisées:

```bash
# 1. Placer les fichiers de police dans:
storage/fonts/

# 2. Mettre à jour la configuration dans config/dompdf.php:
'fontDir' => storage_path('fonts/'),
'fontCache' => storage_path('fonts/'),

# 3. Utiliser dans le CSS du PDF:
@font-face {
    font-family: 'CustomFont';
    src: url('path/to/font.ttf');
}

body {
    font-family: 'CustomFont', sans-serif;
}
```

## 🗂️ Structure des dossiers

```
storage/
├── app/
│   ├── public/
│   │   └── receipts/          # Reçus PDF générés
│   │       ├── receipt_REC-20260316100000-ABC123.pdf
│   │       └── receipt_REC-20260316101500-DEF456.pdf
│   └── logs/
├── logs/
│   ├── dompdf.log            # Logs DomPDF
│   └── laravel.log           # Logs application
└── fonts/                     # Polices personnalisées (optionnel)
    ├── custom-font.ttf
    └── awesome-font.ttf

public/
└── storage/                   # Lien symbolique
    └── receipts/             # Accès public aux reçus
        └── receipt_*.pdf
```

## 🔐 Configuration de sécurité

### Protéger les reçus PDF

Ajouter une vérification dans le contrôleur:

```php
// app/Http/Controllers/Citoyen/PaymentController.php

public function downloadReceipt(Payment $payment)
{
    // Zoner l'accès au propriétaire du paiement
    $this->authorize('view', $payment);

    if (!Storage::disk('public')->exists($payment->chemin_recu)) {
        abort(404, 'Reçu non trouvé');
    }

    // Logger le téléchargement
    \Log::info('Payment receipt downloaded', [
        'payment_id' => $payment->id,
        'user_id' => auth()->id()
    ]);

    return Storage::disk('public')
        ->download($payment->chemin_recu);
}
```

### Valider les répertoires

```php
// Dans une policy ou middleware

protected function validateStoragePath(string $path): bool
{
    $realPath = realpath(storage_path('app/public/receipts/' . $path));
    $allowedPath = realpath(storage_path('app/public/receipts/'));

    return str_starts_with($realPath, $allowedPath);
}
```

## 🧪 Test de configuration

### Via Artisan Tinker

```bash
php artisan tinker
```

```php
# Tester la génération de reçu
$payment = \App\Models\Payment::first();
$service = app(\App\Services\PaymentReceiptService::class);

# Vérifier que le service est chargé
dd($service);

# Vérifier qu'une instance de paiement existe
dd($payment);

# Générer un reçu de test
$path = $service->generateReceipt($payment);
dd($path);  // Devrait retourner: receipts/receipt_REC-...pdf
```

### Via Test unitaire

```bash
php artisan test tests/Unit/PaymentReceiptServiceTest.php
```

```php
// tests/Unit/PaymentReceiptServiceTest.php

<?php
namespace Tests\Unit;

use App\Models\Payment;
use App\Services\PaymentReceiptService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentReceiptServiceTest extends TestCase
{
    public function test_generate_receipt_creates_pdf()
    {
        Storage::fake('public');

        $payment = Payment::factory()->create(['statut' => 'pending']);
        $service = app(PaymentReceiptService::class);

        $path = $service->generateReceipt($payment);

        Storage::disk('public')->assertExists($path);
    }

    public function test_unique_receipt_reference()
    {
        $ref1 = Payment::generateUniqueReference();
        $ref2 = Payment::generateUniqueReference();

        $this->assertNotEqual($ref1, $ref2);
        $this->assertStringStartsWith('REC-', $ref1);
    }
}
```

## 📊 Variables d'environnement (résumé)

| Variable | Valeur par défaut | Description |
|----------|------------------|-------------|
| `FILESYSTEM_DISK` | `local` | Driver de stockage |
| `DOMPDF_ENABLE_REMOTE` | `false` | Charger des ressources distantes |
| `DOMPDF_MEMORY_LIMIT` | `128M` | Limite mémoire pour les PDFs |
| `DOMPDF_UNICODE_ENABLED` | `true` | Support des caractères spéciaux |
| `APP_ENV` | `production` | Environnement app |
| `APP_DEBUG` | `false` | Mode debug (jamais true en prod) |

## 🚀 Déploiement sur serveur

### Sur un serveur Apache

```bash
# 1. Copier le projet
git clone <repo> /var/www/html/mairi

# 2. Installer les dépendances
cd /var/www/html/mairi
composer install --optimize-autoloader --no-dev

# 3. Exécuter les migrations
php artisan migrate --force

# 4. Créer le lien de stockage
php artisan storage:link

# 5. Définir les permissions
sudo chown -R www-data:www-data /var/www/html/mairi
sudo chmod -R 755 /var/www/html/mairi
sudo chmod -R 775 /var/www/html/mairi/storage
sudo chmod -R 775 /var/www/html/mairi/bootstrap/cache
```

### Avec Docker

```dockerfile
# Dockerfile
FROM php:8.2-apache

# Installer les extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copier le vhost Apache
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# Activer mod_rewrite
RUN a2enmod rewrite

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier le code
COPY . .

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installer les dépendances
RUN composer install --optimize-autoloader --no-dev

# Permissions
RUN chown -R www-data:www-data . && chmod -R 755 .
RUN chmod -R 775 storage bootstrap/cache

# Commandes de démarrage
CMD ["apache2-foreground"]
```

### docker-compose.yml

```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "80:80"
    volumes:
      - ./:/var/www/html
      - ./storage:/var/www/html/storage
    environment:
      - DB_HOST=db
      - DB_DATABASE=mairi
      - DB_USERNAME=mairi_user
      - DB_PASSWORD=secure_password
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=root
      - MYSQL_DATABASE=mairi
      - MYSQL_USER=mairi_user
      - MYSQL_PASSWORD=secure_password
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
```

## 🔍 Dépannage courant

### Erreur: "DOMPDF not found"

```bash
# Solution: Réinstaller les dépendances
composer require barryvdh/laravel-dompdf
php artisan vendor:publish
```

### Erreur: "Storage path not writable"

```bash
# Solution: Correction des permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### Les reçus ne s'affichent pas

```php
// Vérifier que le lien de stockage existe
php artisan storage:link

// Vérifier les permissions du dossier
ls -la public/storage/

// Vérifier les logs
tail -f storage/logs/laravel.log
```

### Les PDF ne génèrent pas

```php
// Dans PaymentReceiptService, ajouter du log
\Log::info('Attempting to generate receipt', [
    'payment_id' => $payment->id,
    'path' => storage_path('app/public/receipts/')
]);

// Vérifier la configuration DomPDF
php artisan config:show dompdf
```

### Problèmes avec les caractères spéciaux

```php
// S'assurer que l'encodage est correct dans le fichier
// resources/views/payments/receipt.blade.php

<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

// Et en CSS
@page {
    charset: utf-8;
}
```

## 📝 Logs et monitoring

### Activer les logs détaillés

```php
// config/dompdf.php
'logOutputFile' => storage_path('logs/dompdf.log'),

// Dans le fichier .env
LOG_CHANNEL=single
LOG_LEVEL=debug
```

### Monitorer les PDF générés

```bash
# Voir les PDF créés
$ ls -lh storage/app/public/receipts/

# Compter le nombre de PDF
$ find storage/app/public/receipts/ -name "*.pdf" | wc -l

# Voir la taille totale
$ du -sh storage/app/public/receipts/
```

### Nettoyer les anciens reçus (optionnel)

```bash
# Supprimer les réçus plus vieux que 90 jours
find storage/app/public/receipts/ -type f -mtime +90 -delete
```

## ✨ Prochaines étapes

1. ✅ Vérifier toutes les dépendances sont installées
2. ✅ Exécuter les migrations
3. ✅ Créer le lien de stockage
4. ✅ Tester la génération de PDF
5. ✅ Configurer les emails de confirmation
6. ✅ Mettre en place la sauvegarde des reçus
7. ⏳ Intégrer avec une API de paiement (Stripe, etc.)
8. ⏳ Ajouter l'export de rapports

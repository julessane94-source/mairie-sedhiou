# 📦 Guide d'installation complet - Système Admin MAIRI

## Étapes d'installation pas à pas

### ✅ Checklist prérequis

```
☑ PHP 8.2 ou supérieur
☑ MySQL 8.0 ou supérieur
☑ Composer installé
☑ Laravel 12 créé et prêt
☑ NPM/Node.js pour Tailwind
☑ Git configuré
```

---

## 📋 Étape 1: Préparation de l'environnement

### 1.1 Vérifier PHP et MySQL

```bash
# Vérifier PHP
php -v
# Expected: PHP 8.2.x CLI (cli)

# Vérifier MySQL
mysql --version
# Expected: mysql  Ver 8.0.x

# Démarrer services (si serveur local)
# Windows: Ouvrir XAMPP et démarrer Apache + MySQL
# Mac: brew services start mysql
# Linux: sudo service mysql start
```

### 1.2 Configurer .env Laravel

```bash
# Copier et configurer
cp .env.example .env

# Éditer fichier .env
APP_NAME="MAIRI"
APP_KEY=base64:... (généré après)
APP_DEBUG=true           # Changer à false en production
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1        # ou votre serveur MySQL
DB_PORT=3306
DB_DATABASE=mairi
DB_USERNAME=root         # ou votre user MySQL
DB_PASSWORD=             # Votre password (vide si XAMPP)

# Générer APP_KEY
php artisan key:generate
# Output: Application key set successfully.
```

### 1.3 Créer la base de données

```bash
# Via MySQL CLI
mysql -u root -p
CREATE DATABASE IF NOT EXISTS mairi;
exit;

# Ou via phpmyadmin
# 1. Ouvrir http://localhost/phpmyadmin
# 2. Créer database "mairi"
# 3. Vérifier permissions
```

---

## 🚀 Étape 2: Installation du système admin

### 2.1 Copier les fichiers

```bash
# Les fichiers suivants doivent être présents:

# Models:
cp app/Models/Attendance.php
cp app/Models/PlatformSettings.php

# Migrations:
cp database/migrations/2026_03_16_000006_create_attendances_table.php
cp database/migrations/2026_03_16_000007_create_platform_settings_table.php

# Controllers:
cp app/Http/Controllers/Admin/AgentController.php
cp app/Http/Controllers/Admin/AttendanceController.php
cp app/Http/Controllers/Admin/SettingsController.php
cp app/Http/Controllers/Admin/DashboardController.php

# Policies:
cp app/Policies/AgentPolicy.php

# Views:
cp -r resources/views/admin/

# Routes:
# Vérifier/mettre à jour routes/web.php
```

### 2.2 Exécuter les migrations

```bash
# Voir les migrations disponibles
php artisan migrate:status
# Expected:
# Migration Name ......................... Batch / Status
# 2025_01_01_000000_create_users_table ... yes
# 2026_03_16_000006_create_attendances_table PENDING
# 2026_03_16_000007_create_platform_settings_table PENDING

# Exécuter les migrations
php artisan migrate

# Output:
# Migrated: 2026_03_16_000006_create_attendances_table
# Migrated: 2026_03_16_000007_create_platform_settings_table

# Vérifier les tables créées
mysql -u root
USE mairi;
SHOW TABLES;
# Expected: attendances, platform_settings, users, etc.

# Vérifier structure
DESCRIBE attendances;
DESCRIBE platform_settings;
exit;
```

### 2.3 Vérifier les routes

```bash
# Lister toutes les routes
php artisan route:list | grep admin

# Expected output: 21+ routes
# GET|HEAD   /admin/dashboard
# GET|HEAD   /admin/agents
# POST       /admin/agents
# GET|HEAD   /admin/agents/{id}
# PUT        /admin/agents/{id}
# DELETE     /admin/agents/{id}
# ... etc
```

---

## 👤 Étape 3: Créer un utilisateur administrateur

### 3.1 Via artisan command

```bash
# Si vous avez un seeder pour admin
php artisan db:seed

# Ou manuellement
php artisan tinker
> $user = \App\Models\User::create([
    'name' => 'Admin MAIRI',
    'email' => 'admin@mairi.sn',
    'password' => Hash::make('password123'),
    'role' => 'admin'
  ]);
> exit
```

### 3.2 Via base de données directe

```bash
mysql -u root -p mairi

INSERT INTO users (name, email, password, role, created_at, updated_at) 
VALUES (
  'Admin',
  'admin@mairi.sn',
  '$2y$12$abcdefghijklmnopqrstuvwxyz...',  # Hash bcrypt générée
  'admin',
  NOW(),
  NOW()
);

exit;
```

---

## 🎨 Étape 4: Configuration des vues (Tailwind CSS)

### 4.1 Installer Tailwind (optionnel, si pas déjà fait)

```bash
# Installer dependencies
npm install

# Ou si vous utiliser yarn
yarn install

# Compiler Tailwind CSS
npm run dev

# Production build
npm run build
```

### 4.2 Vérifier les layouts

```bash
# Vérifier que app.blade.php inclut Tailwind:
cat resources/views/layouts/app.blade.php

# Doit contenir:
# @vite(['resources/css/app.css', 'resources/js/app.js'])
# Ou: <link rel="stylesheet" href="{{ asset('css/app.css') }}">
```

---

## 🖥️ Étape 5: Démarrer le serveur

### 5.1 Lancer le serveur Laravel

```bash
# Terminal 1: Lancer serveur PHP
php artisan serve

# Output:
# Laravel development server started on [http://127.0.0.1:8000]
# Quit the server with CONTROL+C.
```

### 5.2 Dans un autre terminal: Compiler assets (si nécessaire)

```bash
# Terminal 2 (optionnel, si Tailwind)
npm run dev

# Output:
# VITE v4.x.x  ready in xxx ms
# ➜  Local:   http://localhost:5173
# ➜  Press h + enter to show help
```

### 5.3 Accéder à l'application

```
Ouvrir navigateur:
URL: http://localhost:8000

Login:
email: admin@mairi.sn
password: password123

Puis aller à: http://localhost:8000/admin/dashboard
```

---

## ⚙️ Étape 6: Configuration initiale

### 6.1 Configurer les paramètres de l'app

```bash
# Méthode 1: Via l'interface
1. Aller à http://localhost:8000/admin/parametres
2. Cliquer sur "Application"
3. Remplir:
   - Nom app: "Mairie de [Ville]"
   - Email: votre email
   - Téléphone: votre numéro
   - Adresse: votre adresse

# Méthode 2: Via Tinker
php artisan tinker
> \App\Models\PlatformSettings::set('app_name', 'Mairie de Saint-Louis');
> \App\Models\PlatformSettings::set('max_demandes_par_agent', 15);
> \App\Models\PlatformSettings::set('devise_par_defaut', 'XOF');
> exit
```

### 6.2 Configurer la sécurité

```bash
php artisan tinker
> \App\Models\PlatformSettings::set('enable_2fa', false);       # À true en prod
> \App\Models\PlatformSettings::set('session_timeout', 60);     # Minutes
> \App\Models\PlatformSettings::set('login_max_attempts', 5);
> exit
```

### 6.3 Configurer les notifications

```bash
php artisan tinker
> \App\Models\PlatformSettings::set('notify_email_demande_new', true);
> \App\Models\PlatformSettings::set('notify_email_demande_assign', true);
> \App\Models\PlatformSettings::set('notify_email_payment', true);
> exit
```

---

## 📁 Étape 7: Configurer le storage

### 7.1 Créer les dossiers

```bash
# Créer les dossiers de stockage
mkdir -p storage/app/public/logos
mkdir -p storage/app/public/justificatifs
mkdir -p storage/app/public/exports

# Permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

### 7.2 Lier le storage public

```bash
# Lier storage pour les uploads
php artisan storage:link

# Output:
# The [public/storage] link has been connected to [storage/app/public].
```

### 7.3 Vérifier les uploads

```bash
# Tester upload (via admin interface):
1. Aller à /admin/parametres/application
2. Upload un logo
3. Vérifier dans storage/app/public/logos/
```

---

## 🧪 Étape 8: Tester le système

### 8.1 Tests basiques

```bash
# 1. Test dashboard
http://localhost:8000/admin/dashboard
# Devrait afficher statistiques, agents, etc.

# 2. Test agents
http://localhost:8000/admin/agents
# Devrait afficher liste vide ou agents

# 3. Test attendance
http://localhost:8000/admin/pointage
# Devrait afficher calendrier

# 4. Test settings
http://localhost:8000/admin/parametres
# Devrait afficher formulaires
```

### 8.2 Tests de création

```bash
# 1. Créer un agent
1. Aller /admin/agents/create
2. Remplir formulaire
3. Cliquer "Créer"
4. Vérifier redirection et présence dans liste

# 2. Marquer présence
1. Aller /admin/pointage
2. Cliquer sur cell date
3. Marquer présent/absent
4. Vérifier enregistrement
```

---

## 🔍 Étape 9: Dépannage courant

### Erreur: "Base de données n'existe pas"

```bash
# Solution:
# 1. Créer la DB
mysql -u root
CREATE DATABASE mairi;
exit;

# 2. Vérifier .env DB_DATABASE=mairi
# 3. Re-migrer
php artisan migrate
```

### Erreur: "Class not found"

```bash
# Solution:
# 1. Régénérer autoload
composer dump-autoload

# 2. Vérifier fichier existe
ls app/Models/Attendance.php

# 3. Vérifier namespace correct
# Le fichier doit commencer par:
# <?php namespace App\Models;
```

### Erreur: "Unauthorized to perform this action"

```bash
# Solution:
# 1. Vérifier que l'utilisateur a role='admin'
php artisan tinker
> $user = \App\Models\User::find(1);
> $user->update(['role' => 'admin']);
> exit;

# 2. Recharger page
```

### Erreur: "No matching routes"

```bash
# Solution:
# 1. Dégager le cache routes
php artisan route:cache --clear
php artisan config:clear

# 2. Vérifier web.php contient routes admin
grep -n "admin" routes/web.php

# 3. Regénérer routes si nécessaire
php artisan optimize
```

---

## 📈 Étape 10: Production (Optionnel)

### 10.1 Passer en mode production

```bash
# .env
APP_DEBUG=false       # Important!
APP_ENV=production

# Optimiser Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build assets
npm run build
```

### 10.2 Backup avant déploiement

```bash
# Backup database
mysqldump -u root -p mairi > backup_mairi_$(date +%Y%m%d).sql

# Backup fichiers
zip -r backup_mairi_$(date +%Y%m%d).zip /chemin/vers/mairi/
```

### 10.3 HTTPS et sécurité

```bash
# Générer certificat SSL (Let's Encrypt)
# Ou utiliser certificat existant

# Vérifier HTTPS dans .env
APP_URL=https://mairi.sn

# Forcer HTTPS dans middleware
# kernel.php: 'https' => \Illuminate\Http\Middleware\ForceHttps::class,
```

---

## ✅ Checklist finale

```
Installation:
☑ PHP + MySQL version correctes
☑ Laravel 12 créé
☑ .env configuré
☑ Database créée et migrée
☑ Utilisateur admin créé
☑ Storage configuré et lié

Vérification:
☑ All 21 routes disponibles
☑ Dashboard affiche les stats
☑ Formulaires agents fonctionnent
☑ Pointage enregistre données
☑ Paramètres sauvegardent les valeurs
☑ Logs s'affichent

Sécurité:
☑ Admin authentifié
☑ Non-admin refusé (/admin)
☑ CSRF tokens présents
☑ Passwords hashés
☑ Uploads sécurisés

Performance:
☑ Dashboard < 500ms
☑ List agents < 1s
☑ Database queries optimisés
☑ Assets minifiés
☑ Cache configuré

Documentation:
☑ Guide technique lu
☑ Guide utilisation compris
☑ Guide test exécuté
```

---

## 📞 Support et aide

Si vous rencontrez des problèmes:

1. **Vérifier les logs**: `storage/logs/laravel.log`
2. **Consulter la documentation**: `ADMIN_SYSTEM_FINAL.md`
3. **Lancer les tests**: `ADMIN_TESTING_GUIDE.md`
4. **Vérifier configure**: `.env` et `database.php`

---

## 🎉 Installation complète!

Vous êtes maintenant prêt à utiliser le système administratif MAIRI!

```
✨ Système Admin MAIRI v1.0 - Production Ready ✨

Accéder à: http://localhost:8000/admin/dashboard
Compte:    admin@mairi.sn / password123

Bon développement! 🚀
```

---

**Date**: Mars 2026  
**Version**: 1.0  
**Status**: Installation Checklist Complète


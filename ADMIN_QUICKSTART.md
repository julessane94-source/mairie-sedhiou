# 🚀 Guide d'installation et d'utilisation - Système Admin MAIRI

## Mode d'emploi complet

### 1️⃣ Étapes d'installation

#### Prérequis
- PHP 8.2+
- Laravel 12
- MySQL 8.0+
- Composer
- Node.js (pour Tailwind/npm)

#### Installation rapide
```bash
# 1. Exécuter les migrations
php artisan migrate

# 2. Lancer le serveur
php artisan serve

# 3. Accéder à l'admin
http://localhost:8000/admin/dashboard
# (Vous devez avoir role=admin)
```

---

## 📱 Interface administrateur - Liste complète

### Dashboard
**URL**: `/admin/dashboard`
**Fonctionnalités**:
- 📊 4 cartes avec statistiques globales
- 🚨 5 règles de diagnostic automatiques
- 📈 Tableau top agents par performance
- 💯 Métriques de performance
- 📋 Activités récentes (citoyens, paiements, demandes)
- 🎯 Boutons d'actions rapides

### Gestion des Agents
**Base URL**: `/admin/agents`

| URL | Méthode | Fonction |
|-----|---------|----------|
| `/admin/agents` | GET | Liste tous les agents |
| `/admin/agents/create` | GET | Formulaire création agent |
| `/admin/agents` | POST | Créer un agent |
| `/admin/agents/{id}` | GET | Voir détails agent + stats |
| `/admin/agents/{id}/edit` | GET | Formulaire modification |
| `/admin/agents/{id}` | PUT | Mettre à jour agent |
| `/admin/agents/{id}` | DELETE | Supprimer agent |
| `/admin/agents/{id}/demandes` | GET | Demandes assignées |
| `/admin/agents/{id}/statut` | PATCH | Changer statut (actif/inactif/congé/suspendu) |

**Formulaire agent inclut**:
- ✅ Nom complet, email, téléphone
- ✅ Spécialité, adresse
- ✅ Date d'embauche
- ✅ Salaire, statut
- ✅ Department, supérieur hiérarchique

### Présence/Pointage
**Base URL**: `/admin/pointage`

| URL | Méthode | Fonction |
|-----|---------|----------|
| `/admin/pointage` | GET | Calendrier mensuel présences |
| `/admin/pointage/{agent}` | GET | Détails présence agent |
| `/admin/pointage/{agent}/presence` | POST | Marquer présence manuelle |
| `/admin/pointage/{agent}/checkin` | POST | Check-in automatique |
| `/admin/pointage/{agent}/checkout` | POST | Check-out automatique |
| `/admin/pointage/{id}/justifier` | POST | Justifier absence |
| `/admin/pointage/rapport` | GET | Rapport détaillé + export |

**Statuts présence disponibles**:
- 🟢 **present** - Présent
- 🔴 **absent** - Absent
- 🔵 **congé** - Congé/vacances
- 🟡 **retard** - Arrivée tardive
- ⚪ **repos** - Jour de repos

**Rapport inclut**:
- 📊 Statistiques par agent
- 📈 Graphiques tendance (Chart.js)
- 💾 Export CSV
- 📄 Export PDF

### Paramètres et Configuration
**Base URL**: `/admin/parametres`

#### 1. Application `/admin/parametres/application`
```
✅ Nom app
✅ Logo (upload)
✅ Email contact
✅ Téléphone
✅ Adresse
```

#### 2. Opérations `/admin/parametres/operations`
```
✅ Max demandes/agent
✅ Délai réponse (jours)
✅ Devise (XOF/USD/EUR)
✅ Taux change USD
✅ Horaires travail (début/fin)
✅ Heures travail/jour
✅ Jour repos hebdo
```

#### 3. Sécurité `/admin/parametres/securite`
```
✅ 2FA (actif/inactif)
✅ HTTPS obligatoire
✅ Session timeout (minutes)
✅ Sessions concurrentes
✅ Tentatives login max
✅ Durée blocage (minutes)
✅ Renouvellement password (jours)
```

#### 4. Notifications `/admin/parametres/notifications`
```
📧 Email (5 types)
📱 SMS (3 types + prestataire)
🔔 In-app (3 types)
⏰ Heures actif (début/fin)
🚫 Désactiver week-ends
```

#### 5. Journaux `/admin/parametres/logs`
```
📋 Affichage table logs
🔍 Recherche/filtres
📊 Statistiques résumé
💾 Export logs
🗑️ Effacer tous logs
```

### Demandes (Requests)
**Base URL**: `/admin/demandes`

| URL | Méthode | Fonction |
|-----|---------|----------|
| `/admin/demandes` | GET | Liste demandes + filtres |
| `/admin/demandes/{id}` | GET | Voir détails demande |
| `/admin/demandes/{id}/edit` | GET | Modifier demande |
| `/admin/demandes/{id}` | PUT | Enregistrer modifications |
| `/admin/demandes/{id}` | DELETE | Supprimer demande |

**Filtres disponibles**:
- Statut (en attente, en cours, acceptée, rejetée)
- Priorité (basse, moyenne, haute)
- Agent assigné
- Recherche texte

### Paiements
**URL**: `/admin/payments/{id}`
- 💳 Détails paiement
- 📊 Montant et statut
- 📄 Demande associée
- 🔄 Actions (confirmer/rejeter)

---

## 🔐 Contrôle d'accès par Policy

### AgentPolicy (app/Policies/AgentPolicy.php)

```php
// Qui peut voir tous les agents?
-> Admin seulement

// Qui peut voir un agent?
-> Admin ou l'agent lui-même

// Qui peut créer un agent?
-> Admin seulement

// Qui peut modifier un agent?
-> Admin ou l'agent lui-même (son profil)

// Qui peut supprimer un agent?
-> Admin seulement

// Qui peut assigner une demande?
-> Admin seulement

// Qui peut voir les demandes d'un agent?
-> Admin ou l'agent lui-même

// Qui peut voir la présence d'un agent?
-> Admin ou l'agent lui-même

// Qui peut marquer la présence?
-> Admin ou l'agent lui-même
```

---

## 📊 Paramètres importants à configurer

### Priorité 1 (Immédiatement)
```php
// app_name - Nom de votre mairie
\App\Models\PlatformSettings::set('app_name', 'Mairie de Saint-Louis');

// app_email - Email de contact
\App\Models\PlatformSettings::set('app_email', 'contact@mairie-sl.sn');

// devise_par_defaut - Devise
\App\Models\PlatformSettings::set('devise_par_defaut', 'XOF');
```

### Priorité 2 (Avant utilisation)
```php
// max_demandes_par_agent - Charge agent
\App\Models\PlatformSettings::set('max_demandes_par_agent', 15);

// heure_arrivee - Moment pointage
\App\Models\PlatformSettings::set('heure_arrivee', '08:00');

// heure_depart - Fin journée
\App\Models\PlatformSettings::set('heure_depart', '17:00');
```

### Priorité 3 (Recommandé)
```php
// enable_2fa - Sécurité additionnelle
\App\Models\PlatformSettings::set('enable_2fa', true);

// session_timeout - Déconnexion inactivité
\App\Models\PlatformSettings::set('session_timeout', 60);

// notify_email_demande_new - Notifications
\App\Models\PlatformSettings::set('notify_email_demande_new', true);
```

---

## 🛠️ Commandes artisan utiles

```bash
# Vérifier toutes les routes admin
php artisan route:list | grep admin

# Mettre à jour les paramètres via Tinker
php artisan tinker
> \App\Models\PlatformSettings::set('app_name', 'MAIRI');

# Voir les utilisateurs admin
php artisan tinker
> APP\Models\User::where('role', 'admin')->get();

# Migrer les bases en-arrière
php artisan migrate:rollback

# Regénérer cache
php artisan cache:clear
php artisan config:clear
```

---

## 📥 Upload et fichiers

### Fichiers supportés
- **Logo**: PNG, JPG, GIF, WebP (max 2MB)
- **Justificatifs**: PDF, DOC, DOCX, JPG (max 5MB)

### Chemins de stockage
```
storage/app/public/logos/
storage/app/public/justificatifs/
storage/app/public/exports/
```

### Commande setup storage
```bash
php artisan storage:link
```

---

## 🐛 Troubleshooting

### Erreur: "Unauthorized to perform this action"
**Cause**: L'utilisateur n'a pas le rôle "admin"
**Solution**:
```php
php artisan tinker
> $user = \App\Models\User::find(1);
> $user->assignRole('admin'); // ou \App\Models\User::assignRole('admin')
```

### Erreur: "Call to undefined method"
**Cause**: Migration n'a pas été lancée
**Solution**:
```bash
php artisan migrate
```

### Erreur: "No matching routes"
**Cause**: Routes cachées ou middleware mal configuré
**Solution**:
```bash
php artisan route:cache --clear
```

### Performance lente
**Cause**: Trop de logs accumulés
**Solution**: Aller à `/admin/parametres/logs` et cliquer "Effacer les logs"

---

## 📈 Optimisations recommandées

### À court terme (1 semaine)
- [ ] Faire un backup configuration
- [ ] Tester avec 100+ demandes
- [ ] Valider export PDF
- [ ] Tester 2FA sur mobiles

### À moyen terme (1 mois)
- [ ] Ajouter Chart.js au dashboard
- [ ] Implémenter cache Redis
- [ ] Ajouter notifications SMS
- [ ] Créer tests automatisés

### À long terme (3 mois)
- [ ] Intégrer paiements mobiles
- [ ] Ajouter API externe
- [ ] Implémenter multi-langue
- [ ] Migrer vers PostgreSQL

---

## 📞 Support

**Pour des questions sur**:
- 🏗️ **Architecture**: Voir `ADMIN_SYSTEM_FINAL.md`
- 📝 **Code**: Voir les docstrings dans les controllers
- 🔌 **Intégration**: Voir `routes/web.php`
- 🎨 **UI/UX**: Voir `resources/views/admin/`

---

## ✅ Checklist pré-production

```
☑ Toutes les migrations exécutées
☑ Au moins 1 administrateur créé
☑ Paramètres essentiels configurés
☑ Logo/images uploadées
☑ Backup base de données fait
☑ Storage folders créés et liés
☑ .env configuré correctement
☑ Tests de load faits
☑ Documentation équipe validée
☑ Planning de maintenance établi
```

---

**Dernière mise à jour**: Mars 2026  
**Version**: 1.0 Production  
**Auteur**: MAIRI Admin Team

Bon développement! 🚀


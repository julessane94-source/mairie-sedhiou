# 🧪 Guide de test - Système Admin MAIRI

## Structure de test complète

### 1. Test des migrations

```bash
# Vérifier les tables créées
php artisan migrate:status

# Voir les tables
mysql> SHOW TABLES;
mysql> DESCRIBE attendances;
mysql> DESCRIBE platform_settings;
```

**Attendance table structure**:
```sql
id, agent_id, date, statut, check_in, check_out, heures_travaillees, 
justifiee, motif_absence, piece_justificative, created_at, updated_at
```

**Platform_settings table structure**:
```sql
id, key, value, type, created_at, updated_at
```

---

### 2. Test des modèles

#### Test Attendance
```php
php artisan tinker

// Créer une présence
$att = \App\Models\Attendance::create([
    'agent_id' => 1,
    'date' => now(),
    'statut' => 'present',
    'check_in' => '08:15',
    'check_out' => '17:30',
    'heures_travaillees' => 9.25
]);

// Requêtes scopes
$att->forAgent(1)->forDate(now())->first();
$att->forMonth(2026, 3)->present()->count(); // Présences ce mois

// Tests calculs
$att->isPresent(); // true
$att->isJustified(); // false
$att->calculateWorkingHours(); // 9.25
```

#### Test PlatformSettings
```php
php artisan tinker

// Set values
\App\Models\PlatformSettings::set('app_name', 'Test MAIRI');
\App\Models\PlatformSettings::set('max_demandes_par_agent', 20, 'integer');
\App\Models\PlatformSettings::set('enable_2fa', true, 'boolean');
\App\Models\PlatformSettings::set('taux_change_usd', 605.5, 'decimal');

// Get values
\App\Models\PlatformSettings::get('app_name'); // 'Test MAIRI'
\App\Models\PlatformSettings::get('enable_2fa'); // true (boolean)
\App\Models\PlatformSettings::get('taux_change_usd'); // 605.5 (float)
```

---

### 3. Test des routes

```bash
# Afficher toutes les routes admin
php artisan route:list | grep admin

# Test avec curl
curl http://localhost:8000/admin/dashboard -H "Authorization: Bearer {TOKEN}"

# Ou via Laravel Tinker (simulé)
php artisan tinker
> Route::dispatch(Request::create('/admin/dashboard', 'GET'));
```

**Routes à tester**:
```
✅ GET /admin/dashboard
✅ GET /admin/agents
✅ GET /admin/agents/create
✅ POST /admin/agents
✅ GET /admin/agents/{id}
✅ GET /admin/agents/{id}/edit
✅ PUT /admin/agents/{id}
✅ DELETE /admin/agents/{id}
✅ GET /admin/pointage
✅ GET /admin/pointage/{agent}
✅ POST /admin/pointage/{agent}/presence
✅ GET /admin/parametres
✅ GET /admin/parametres/application
✅ POST /admin/parametres/application
... etc
```

---

### 4. Test des controllers

#### Test AgentController
```php
// Dans feature test ou Tinker
$user = factory(\App\Models\User::class)->states('admin')->create();

$agent = [
    'name' => 'Jean Dupont',
    'email' => 'jean@exemple.com',
    'telephone' => '+221701234567',
    'statut' => 'actif',
    'specialite' => 'Finances',
];

// Test store
$response = actingAs($user)->post('/admin/agents', $agent);
$response->assertRedirect('/admin/agents');

// Test update
$stored = \App\Models\User::where('email', 'jean@exemple.com')->first();
$response = actingAs($user)->put("/admin/agents/{$stored->id}", [
    ...array_merge($agent, ['statut' => 'inactif'])
]);
$response->assertRedirect();
```

#### Test AttendanceController
```php
$user = factory(\App\Models\User::class)->states('admin')->create();

// Test marquer présence
$response = actingAs($user)->post("/admin/pointage/{$agent->id}/presence", [
    'date' => now()->format('Y-m-d'),
    'statut' => 'present',
]);
$response->assertStatus(200);

// Vérifier enregistrement
$att = \App\Models\Attendance::where([
    'agent_id' => $agent->id,
    'date' => now()->format('Y-m-d'),
])->first();
$this->assertEquals('present', $att->statut);
```

#### Test SettingsController
```php
$user = factory(\App\Models\User::class)->states('admin')->create();

// Test update setting
$response = actingAs($user)->patch('/admin/parametres/app_name', [
    'value' => 'Mairie Nouvelle'
]);

// Vérifier
$this->assertEquals('Mairie Nouvelle', 
    \App\Models\PlatformSettings::get('app_name'));
```

---

### 5. Test des policies

```php
$admin = factory(\App\Models\User::class)->states('admin')->create();
$agent = factory(\App\Models\User::class)->create();

// Can view any agents?
$this->assertTrue($admin->can('viewAny', \App\Models\User::class));
$this->assertFalse($agent->can('viewAny', \App\Models\User::class));

// Can view specific agent?
$this->assertTrue($admin->can('view', $agent));
$this->assertTrue($agent->can('view', $agent)); // Lui-même
$this->assertFalse($other->can('view', $agent)); // Autre agent

// Can create agent?
$this->assertTrue($admin->can('create', \App\Models\User::class));
$this->assertFalse($agent->can('create', \App\Models\User::class));

// Can change status?
$this->assertTrue($admin->can('changeStatus', $agent));
$this->assertFalse($agent->can('changeStatus', $agent));
```

---

### 6. Test des vues (blade)

#### Vérifier que les vues sont présentes
```bash
ls resources/views/admin/
# dashboard.blade.php
# agents/
# attendance/
# settings/
# demandes/
# payments/
```

#### Test rendu blade
```php
// Dans test feature
$response = actingAs($admin)->get('/admin/dashboard');
$response->assertViewIs('admin.dashboard');
$response->assertViewHas('stats');
$response->assertViewHas('diagnostics');

// Test contenu
$response->assertSeeText('Demandes');
$response->assertSeeText('Agents');
```

---

### 7. Test de performance

```php
// Charger 1000 agents
factory(\App\Models\User::class, 1000)
    ->create()
    ->each(fn($u) => $u->assignRole('agent'));

// Mesurer temps de chargement
$start = microtime(true);
$agents = \App\Models\User::whereHas('roles', 
    fn($q) => $q->where('name', 'agent')
)->get();
$end = microtime(true);

echo "Temps: " . ($end - $start) * 1000 . "ms";
// Devrait être < 100ms avec indexes
```

**Ajouter indexes**:
```php
// Dans migration attendances
$table->index('agent_id');
$table->index('date');
$table->index(['agent_id', 'date']);

// Dans migration users
$table->index('role');
```

---

### 8. Test sécurité

#### Test authentification
```php
// Sans login
$response = $this->get('/admin/dashboard');
$response->assertRedirect('login'); // ou 403

// Avec user non-admin
$user = factory(\App\Models\User::class)->create();
$response = actingAs($user)->get('/admin/dashboard');
$response->assertStatus(403); // Unauthorized
```

#### Test CSRF
```php
// POST sans token CSRF doit échouer
$response = $this->post('/admin/agents', $data);
$response->assertStatus(419); // Token mismatch

// Avec token CSRF valide
$response = actingAs($admin)->post('/admin/agents', $data);
$response->assertStatus(302); // Success
```

#### Test SQL injection
```php
// Test malveillant
$response = actingAs($admin)->get('/admin/demandes?search=<script>alert("XSS")</script>');
$response->assertDontSeeText('<script>'); // Doit être échappé

// Test injection SQL
$response = actingAs($admin)->get("/admin/agents/1' OR '1'='1");
$response->assertStatus(404); // Ou 403
```

---

### 9. Test des exports

```php
// Test export PDF (si implémenté)
$response = actingAs($admin)->get('/admin/pointage/rapport?export=pdf');
$response->assertHeader('Content-Type', 'application/pdf');
$response->assertStatus(200);

// Test export CSV
$response = actingAs($admin)->get('/admin/pointage/rapport?export=csv');
$response->assertHeader('Content-Type', 'text/csv');
$response->assertStatus(200);
```

---

### 10. Checklist de test complet

```
Models:
☐ Attendance créé avec toutes les colonnes
☐ PlatformSettings créé et fonctionnel
☐ Relations User ↔ Attendance ✓
☐ Relations User ↔ Demande ✓

Migrations:
☐ Tables créées sans erreurs
☐ Indexes ajoutés pour performance
☐ Contraintes FK configurées
☐ Seeding test data possible

Controllers:
☐ Tous les CRUD fonctionnels
☐ Validations en place
☐ Messages flash affichés
☐ Redirections correctes

Routes:
☐ Toutes les routes disponibles
☐ Middleware role:admin appliqué
☐ Paramètres {id} capturés
☐ Noms de routes utilisables

Policies:
☐ Admin accès complet
☐ Agent accès personnel limité
☐ Visiteur accès refusé
☐ Audit logs générés

Views:
☐ Dashboard affiche les stats
☐ Formulaires valident
☐ Tableaux paginés
☐ Responsive design mobile

Sécurité:
☐ CSRF protection active
☐ XSS prevention en place
☐ SQL injection protégé
☐ HTTPS enforcé (prod)
☐ Rate limiting actif

Performance:
☐ Chargement < 500ms (index)
☐ Chargement < 1s (show)
☐ Queries optimisées (no N+1)
☐ Cache Redis configuré
```

---

### 11. Commandes test utiles

```bash
# Exécuter les tests
php artisan test

# Tests spécifiques
php artisan test tests/Feature/Admin/AgentControllerTest.php

# Tests avec coverage
php artisan test --coverage

# Tests en parallèle
php artisan test --parallel

# Tests avec output détaillé
php artisan test --verbose
```

---

### 12. Métriques de santé

**Indiquer un bon système**:
- ✅ 95%+ couverture de code testée
- ✅ < 1s temps de réponse moyen
- ✅ 0 erreurs non gérées
- ✅ < 5% faux positifs diagnostics
- ✅ 100% uptime en test
- ✅ Zéro vulnérabilités connues

---

## Exemples de cas d'usage à tester

### Cas 1: Assigner une demande
```
1. Admin va à /admin/agents
2. Sélectionne un agent
3. Clique "Assigner demande"
4. Choisit une demande
5. Vérifier permission + log
Expected: Demande assignée, notification envoyée
```

### Cas 2: Marquer absences
```
1. Admin va à /admin/pointage
2. Sélectionne mois/année
3. Clique sur cell absente
4. Marque "absent"
5. Ajoute motif
Expected: Absence enregistrée avec motif
```

### Cas 3: Changer paramètres
```
1. Admin va à /admin/parametres/operations
2. Change max demandes de 10 à 15
3. Clique "Enregistrer"
4. Actualise page
Expected: Nouvelle valeur persistée et affichée
```

---

**Note**: Ces tests doivent être exécutés avant chaque déploiement en production.

Bon testing! 🧪


# 🚀 Guide de démarrage - Tests GitHub Actions

## Description

Un workflow GitHub Actions a été configuré pour tester automatiquement votre projet Laravel à chaque push et pull request.

## ✨ Ce qui a été créé

### 1. Workflow GitHub Actions
📄 **`.github/workflows/tests.yml`**

Contient:
- Configuration PHP 8.2
- Service MySQL pour les tests
- Exécution de toutes les migrations
- Tests automatiques en parallèle
- Vérification du style de code (Pint)

### 2. Configuration environnement de test
📄 **`.env.testing`**

Configuration dédiée aux tests avec:
- Base de données MySQL: `mairi_test`
- Cache et session en mémoire
- Autres services en mode test

### 3. Tests d'exemple
✅ **Tests Feature:**
- `tests/Feature/AuthenticationTest.php` - Authentification
- `tests/Feature/CitoyenDemandeTest.php` - Fonctionnalités citoyens
- `tests/Feature/AgentDemandeTest.php` - Fonctionnalités agents
- `tests/Feature/AdminTest.php` - Fonctionnalités admin

✅ **Tests Unit:**
- `tests/Unit/UserModelTest.php` - Modèle User

### 4. Documentation
📖 **`TESTING.md`**

Guide complet pour:
- Exécuter les tests localement
- Écrire de nouveaux tests
- Comprendre le workflow
- Bonnes pratiques

## 🏃 Démarrage rapide

### Avant de committer sur GitHub

1. **Exécuter les tests localement (optionnel)**
```bash
php artisan test
```

2. **Pousser le code**
```bash
git add .
git commit -m "Add automated tests with GitHub Actions"
git push origin main
```

3. **Consultez les résultats**
   - Allez sur GitHub
   - Onglet "Actions"
   - Cliquez sur le dernier workflow

## 🔄 Le workflow en action

À chaque **`git push`** ou **Pull Request**, le workflow:

```
1️⃣ Setup PHP 8.2
   ↓
2️⃣ Installer les dépendances
   ↓
3️⃣ Démarrer MySQL
   ↓
4️⃣ Exécuter les migrations
   ↓
5️⃣ Lancer les tests
   ↓
6️⃣ Vérifier le style de code
   ↓
✅ Succès ou ❌ Erreur
```

## 📋 Fichiers créés

```
.github/
  └── workflows/
      └── tests.yml          ← Workflow GitHub Actions
.env.testing               ← Config environnement test
TESTING.md                 ← Documentation complète
tests/
  ├── Feature/
  │   ├── AuthenticationTest.php
  │   ├── CitoyenDemandeTest.php
  │   ├── AgentDemandeTest.php
  │   └── AdminTest.php
  └── Unit/
      └── UserModelTest.php
```

## 🧪 Exemples de tests

### Tester un formulaire de connexion
```php
public function test_login_with_valid_credentials(): void
{
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123')
    ]);

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $this->assertAuthenticated();
}
```

### Tester une action d'agent
```php
public function test_agent_can_assign_demande(): void
{
    $agent = User::factory()->create(['role' => 'agent']);
    $demande = Demande::factory()->create();

    $response = $this->actingAs($agent)
        ->post(route('agent.demandes.assigner', $demande));

    $this->assertDatabaseHas('demandes', [
        'id' => $demande->id,
        'agent_assigne_id' => $agent->id,
    ]);
}
```

## 🛠️ Personnalisation du workflow

Les configuration peut être modifiée dans `.github/workflows/tests.yml`:

```yaml
on:
  push:
    branches: [ main, develop ]  ← Branches à tester
  pull_request:
    branches: [ main, develop ]
```

## 💡 Conseils

1. **Exécutez les tests avant de pusher:**
   ```bash
   php artisan test
   ```

2. **Ajoutez des tests pour chaque nouvelle fonctionnalité**

3. **Consultez les logs du workflow si échec:**
   - GitHub Actions → Workflow → Cliquez sur le job échoué

4. **Pour déboguer localement:**
   ```bash
   php artisan test --verbose
   ```

## 📝 Prochaines étapes

1. ✅ Vérifier que le workflow fonctionne
2. ✅ Ajouter plus de tests pour vos fonctionnalités
3. ✅ Configurer les branches protégées pour forcer les tests
4. ✅ Intégrer avec Codecov pour la couverture de code

## ✅ Status Badge (optionnel)

Ajoutez ceci à votre `README.md` pour afficher le statut des tests:

```markdown
[![Tests](https://github.com/YOUR_USERNAME/mairi/workflows/Tests%20Laravel/badge.svg)](https://github.com/YOUR_USERNAME/mairi/actions)
```

---

**Questions?** Consultez [TESTING.md](./TESTING.md) pour un guide complet! 🚀

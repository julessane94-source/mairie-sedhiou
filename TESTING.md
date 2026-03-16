# 🧪 Tests - Mairi

Guide complet pour tester votre application Laravel Mairi.

## Installation

### Dépendances requises

Les outils de test sont déjà installés avec Laravel:
- **PHPUnit** - Framework de test unitaire
- **Laravel Testing Utilities** - Helpers pour les tests
- **Faker** - Générer des données de test

## Exécution des tests

### Tous les tests
```bash
php artisan test
```

### Tests en parallèle (plus rapide)
```bash
php artisan test --parallel
```

### Tests spécifiques

**Tests Feature uniquement:**
```bash
php artisan test tests/Feature
```

**Tests Unit uniquement:**
```bash
php artisan test tests/Unit
```

**Un seul fichier de test:**
```bash
php artisan test tests/Feature/AuthenticationTest.php
```

**Une seule méthode de test:**
```bash
php artisan test tests/Feature/AuthenticationTest.php --filter=test_login_page_is_accessible
```

### Avec couverture de code
```bash
php artisan test --coverage
```

## Structure des tests

### 📂 Tests Feature (`tests/Feature/`)
Tests d'intégration qui testent les fonctionnalités complètes:

- **AuthenticationTest.php** - Connexion, inscription, déconnexion
- **CitoyenDemandeTest.php** - Gestion des demandes par les citoyens
- **AgentDemandeTest.php** - Gestion des demandes par les agents
- **AdminTest.php** - Fonctionnalités admin

### 📂 Tests Unit (`tests/Unit/`)
Tests unitaires pour les modèles, méthodes individuelles:

- **UserModelTest.php** - Tests des méthodes du modèle User

## Écrire de nouveaux tests

### Créer un fichier de test
```bash
# Test Feature
php artisan make:test DemandeWorkflowTest

# Test Unit
php artisan make:test UserRoleTest --unit
```

### Exemple de test Feature
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class ExampleTest extends TestCase
{
    /**
     * Test example
     */
    public function test_example(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * Test avec authentification
     */
    public function test_authenticated_user(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/citoyen/dashboard');
        $response->assertStatus(200);
    }
}
```

### Exemple de test Unit
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_example(): void
    {
        $this->assertTrue(true);
    }
}
```

## GitHub Actions (CI/CD)

Le workflow GitHub Actions automatise les tests à chaque push.

### Fichier: `.github/workflows/tests.yml`

Le workflow exécute:
1. ✅ Tests Feature avec MySQL
2. ✅ Tests Unit
3. ✅ Vérification du style de code (Pint)
4. ✅ Run en parallèle pour plus de vitesse

### Configuration environnement de test

Fichier: `.env.testing`

```env
APP_ENV=testing
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=mairi_test
DB_USERNAME=root
DB_PASSWORD=root
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
```

### Vérifier les résultats

1. Allez sur votre repo GitHub
2. Allez dans l'onglet **Actions**
3. Consultez les logs du workflow

## Assertions utiles

### Réponses HTTP
```php
$response->assertStatus(200);
$response->assertStatus(404);
$response->assertRedirect('/dashboard');
$response->assertViewIs('demandes.index');
```

### Base de données
```php
$this->assertDatabaseHas('users', ['email' => 'test@example.com']);
$this->assertDatabaseMissing('users', ['email' => 'deleted@example.com']);
```

### Authentification
```php
$this->actingAs($user)->get('/dashboard');
$this->assertAuthenticated();
$this->assertGuest();
```

### Vue et données
```php
$response->assertViewHas('demandes');
$response->assertViewHasAll(['demandes', 'user']);
```

## Bonnes pratiques

✅ **À faire:**
- Tester les cas happy path et unhappy path
- Utiliser RefreshDatabase pour isoler les tests
- Organiser les tests par fonctionnalité
- Nommer les tests clairement (test_user_can_...)

❌ **À éviter:**
- Tests trop longs ou complexes
- Dépendances entre tests
- Tests sans assertions
- Requêtes réelles vers l'API externe

## Dépannage

### "Access denied for user 'root'@'localhost'"
→ Vérifier la configuration `.env.testing`

### Tests échouent localement mais passent sur GitHub
→ Les environnements peuvent être différents
→ Vérifier les variables d'environnement

### Tests très lents
→ Utiliser `--parallel`
→ Réduire les tests inutiles

## Ressources

- [Documentation Laravel Testing](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://docs.phpunit.de/)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)

# 📡 API Endpoints - Système de paiement

## 📌 Base URL

```
http://localhost:8000  (développement)
https://mairi.example.com  (production)
```

## 🔐 Authentification

Tous les endpoints nécessitent une authentification. Utilisez:

```
Authorization: Bearer {token}
```

ou la session Laravel standard pour les routes web.

## 💳 Endpoints des paiements

---

### 1️⃣ Lister tous les paiements d'un citoyen

**GET** `/citoyen/paiements`

#### Paramètres de query
```
?page=1              # Numéro de page (pagination)
?sort=created_at     # Champ de tri (created_at, montant, statut)
?order=desc          # Ordre: asc ou desc
?statut=paid         # Filtrer par statut: pending, paid, cancelled, refunded
?from=2026-01-01     # Filtrer par date de début
?to=2026-12-31       # Filtrer par date de fin
```

#### Exemple de requête
```bash
curl -X GET "http://localhost:8000/citoyen/paiements?page=1&statut=paid" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Réponse (200 OK)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "demande_id": 5,
      "citoyen_id": 2,
      "montant": "50000.00",
      "devise": "XOF",
      "methode_paiement": "virement",
      "statut": "paid",
      "numero_transaction": "TRX20260316001",
      "date_paiement": "2026-03-16T10:30:00.000000Z",
      "reference_recu": "REC-20260316103000-ABC123",
      "description": "Frais de traitement",
      "chemin_recu": "receipts/receipt_REC-20260316103000-ABC123.pdf",
      "created_at": "2026-03-15T08:00:00.000000Z",
      "updated_at": "2026-03-16T10:30:00.000000Z",
      "demande": { /* données demande */ },
      "citoyen": { /* données citoyen */ }
    }
  ],
  "pagination": {
    "total": 15,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1,
    "from": 1,
    "to": 15
  }
}
```

---

### 2️⃣ Afficher les détails d'un paiement

**GET** `/citoyen/paiements/{payment_id}`

#### Paramètres
```
payment_id: ID du paiement
```

#### Exemple de requête
```bash
curl -X GET "http://localhost:8000/citoyen/paiements/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Réponse (200 OK)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "demande_id": 5,
    "citoyen_id": 2,
    "montant": "50000.00",
    "devise": "XOF",
    "methode_paiement": "virement",
    "statut": "paid",
    "numero_transaction": "TRX20260316001",
    "date_paiement": "2026-03-16T10:30:00.000000Z",
    "reference_recu": "REC-20260316103000-ABC123",
    "description": "Frais de traitement",
    "chemin_recu": "receipts/receipt_REC-20260316103000-ABC123.pdf",
    "created_at": "2026-03-15T08:00:00.000000Z",
    "updated_at": "2026-03-16T10:30:00.000000Z",
    "demande": {
      "id": 5,
      "titre": "Certificat de résidence",
      "description": "...",
      "statut": "acceptee",
      "priorite": "normale",
      "url": "/citoyen/demandes/5"
    },
    "citoyen": {
      "id": 2,
      "nom": "John Doe",
      "email": "john@example.com",
      "telephone": "+221 76 123 45 67"
    }
  }
}
```

#### Erreurs possibles
```json
// 404 Not Found - Paiement non trouvé
{
  "success": false,
  "message": "Payment not found"
}

// 403 Forbidden - Accès refusé
{
  "success": false,
  "message": "This action is unauthorized"
}
```

---

### 3️⃣ Créer un paiement pour une demande

**POST** `/citoyen/demandes/{demande_id}/paiement`

#### Paramètres du body
```json
{
  "montant": 50000,                           // Requis: nombre positif
  "methode_paiement": "virement",            // Requis: virement|cheque|especes|carte|mobile_money
  "devise": "XOF",                           // Optionnel: XOF, EUR, USD (défaut: XOF)
  "description": "Paiement de frais"         // Optionnel: texte max 500 caractères
}
```

#### Validation
```
montant:
  - Required
  - Numeric
  - Min: 0.01
  - Max: 999999.99

methode_paiement:
  - Required
  - In: virement, cheque, especes, carte, mobile_money

description:
  - Optional
  - String
  - Max: 500
```

#### Exemple de requête
```bash
curl -X POST "http://localhost:8000/citoyen/demandes/5/paiement" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "montant": 50000,
    "methode_paiement": "virement",
    "description": "Frais de traitement de demande"
  }'
```

#### Réponse (201 Created)
```json
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
    "reference_recu": "REC-20260316153245-XYZ789",
    "description": "Frais de traitement de demande",
    "created_at": "2026-03-16T15:32:45.000000Z",
    "updated_at": "2026-03-16T15:32:45.000000Z"
  },
  "message": "Payment created successfully"
}
```

#### Erreurs possibles
```json
// 422 Unprocessable Entity - Validation échouée
{
  "success": false,
  "message": "The given data was invalid",
  "errors": {
    "montant": ["The montant field is required"],
    "methode_paiement": ["The methode_paiement must be one of: virement, cheque, especes, carte, mobile_money"]
  }
}
```

---

### 4️⃣ Marquer un paiement comme payé

**POST** `/citoyen/paiements/{payment_id}/marquer-paye`

#### Paramètres du body
```json
{
  "numero_transaction": "TRX20260316001122"  // Optionnel: numéro de transaction
}
```

#### Exemple de requête
```bash
curl -X POST "http://localhost:8000/citoyen/paiements/1/marquer-paye" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "numero_transaction": "TRX20260316001122"
  }'
```

#### Réponse (200 OK)
```json
{
  "success": true,
  "payment": {
    "id": 1,
    "statut": "paid",
    "date_paiement": "2026-03-16T15:35:00.000000Z",
    "numero_transaction": "TRX20260316001122",
    "chemin_recu": "receipts/receipt_REC-20260316153245-XYZ789.pdf",
    "reference_recu": "REC-20260316153245-XYZ789"
  },
  "message": "Payment marked as paid. Receipt generated."
}
```

---

### 5️⃣ Télécharger le reçu PDF

**GET** `/citoyen/paiements/{payment_id}/recu/telechargement`

#### Exemple de requête
```bash
curl -X GET "http://localhost:8000/citoyen/paiements/1/recu/telechargement" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output receipt.pdf
```

#### Réponse
- Fichier PDF (Content-Type: application/pdf)
- Nom du fichier: `receipt_REC-20260316103000-ABC123.pdf`
- Headers:
  ```
  Content-Type: application/pdf
  Content-Disposition: attachment; filename="receipt_REC-20260316103000-ABC123.pdf"
  Content-Length: 45234
  ```

---

### 6️⃣ Prévisualiser le reçu PDF

**GET** `/citoyen/paiements/{payment_id}/recu/apercu`

Affiche le PDF directement dans le navigateur (sans télécharger).

#### Exemple de requête
```bash
curl -X GET "http://localhost:8000/citoyen/paiements/1/recu/apercu" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Réponse
- Fichier PDF streamed (Content-Type: application/pdf)
- Affichage (inline) dans le navigateur

---

### 7️⃣ Annuler un paiement

**POST** `/citoyen/paiements/{payment_id}/annuler`

#### Paramètres du body
```json
{
  "raison": "Demande annulée"  // Optionnel: raison de l'annulation
}
```

#### Validation
```
raison:
  - Optional
  - String
  - Max: 500
```

#### Exemple de requête
```bash
curl -X POST "http://localhost:8000/citoyen/paiements/1/annuler" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "raison": "Demande retirée"
  }'
```

#### Réponse (200 OK)
```json
{
  "success": true,
  "payment": {
    "id": 1,
    "statut": "cancelled",
    "montant": "50000.00"
  },
  "message": "Payment cancelled successfully"
}
```

#### Erreurs possibles
```json
// 400 Bad Request - Paiement ne peut pas être annulé
{
  "success": false,
  "message": "Only pending payments can be cancelled"
}
```

---

## 📊 Filtres et tri

### Exemples de filtrage

```bash
# Tous les paiements payés
GET /citoyen/paiements?statut=paid

# Paiements en attente, triés par date
GET /citoyen/paiements?statut=pending&sort=created_at&order=asc

# Paiements entre deux dates
GET /citoyen/paiements?from=2026-01-01&to=2026-12-31

# Combinaison de filtres
GET /citoyen/paiements?statut=paid&from=2026-03-01&sort=montant&order=desc
```

### Champs de tri disponibles
- `created_at` - Date de création
- `updated_at` - Date de modification
- `montant` - Montant du paiement
- `statut` - Statut du paiement
- `reference_recu` - Référence du reçu

---

## 🔄 Codes de statut HTTP

| Code | Signification | Quand |
|------|---------------|-------|
| `200` | OK | Requête réussie |
| `201` | Created | Ressource créée |
| `400` | Bad Request | Paramètres invalides |
| `401` | Unauthorized | Non authentifié |
| `403` | Forbidden | Non autorisé |
| `404` | Not Found | Ressource non trouvée |
| `422` | Unprocessable Entity | Validation échouée |
| `500` | Internal Error | Erreur serveur |

---

## 📌 Statuts des paiements

| Statut | Description | Transition possible vers |
|--------|-------------|------------------------|
| `pending` | En attente de paiement | `paid`, `cancelled` |
| `paid` | Paiement effectué | `refunded` |
| `cancelled` | Paiement annulé | - |
| `refunded` | Paiement remboursé | - |

---

## 💾 Modèle de données complet

```json
{
  "Payment": {
    "id": "integer [PK]",
    "demande_id": "integer [FK]",
    "citoyen_id": "integer [FK]",
    "montant": "decimal(10,2)",
    "devise": "enum(XOF, EUR, USD)",
    "methode_paiement": "enum(virement, cheque, especes, carte, mobile_money)",
    "statut": "enum(pending, paid, cancelled, refunded)",
    "numero_transaction": "string|null",
    "date_paiement": "timestamp|null",
    "reference_recu": "string [UNIQUE]",
    "chemin_recu": "string|null",
    "description": "text|null",
    "created_at": "timestamp",
    "updated_at": "timestamp"
  }
}
```

---

## 🧪 Collections Postman

### Importer dans Postman

1. Créer une nouvelle collection `MAIRI Paiements`
2. Ajouter les 7 endpoints ci-dessus
3. Configurer les variables: `{{base_url}}`, `{{token}}`
4. Importer les exemples de request/response

### Fichier postman_collection.json

```json
{
  "info": {
    "name": "MAIRI Payment System API",
    "description": "Endpoints pour le système de paiement",
    "version": "1.0"
  },
  "item": [
    {
      "name": "List Payments",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/citoyen/paiements",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ]
      }
    },
    {
      "name": "Get Payment",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/citoyen/paiements/1",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ]
      }
    }
  ]
}
```

---

## 🔗 Webhooks (futur)

Les webhooks suivants peuvent être implémentés:

```
- payment.created
- payment.marked_as_paid
- payment.cancelled
- payment.refunded
- receipt.generated
- receipt.downloaded
```

---

## 📞 Support

Pour des questions ou des problèmes avec l'API:
- Consultez les logs: `storage/logs/laravel.log`
- Vérifiez les erreurs de validation
- Assurez-vous que le token est valide
- Vérifiez les permissions d'accès

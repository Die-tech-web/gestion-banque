<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Personnalisation de l'URL de Base avec "die.niang"

Cette section explique comment l'URL de base de l'API et de la documentation Swagger a été personnalisée pour inclure le nom "die.niang".

### 1. Définition du Nom Dynamique (`die.niang`)

Le nom dynamique "die.niang" est défini dans le fichier de configuration de l'API et peut être surchargé par une variable d'environnement :

*   **`config/api.php`**: Ce fichier contient la configuration par défaut pour le nom de l'API.
    ```php
    // config/api.php
    return [
        'name' => env('API_DYNAMIC_NAME', 'die.niang'),
    ];
    ```
    Par défaut, si `API_DYNAMIC_NAME` n'est pas défini dans le fichier `.env`, le nom `die.niang` sera utilisé.

*   **`.env`**: Vous pouvez surcharger la valeur par défaut en définissant la variable `API_DYNAMIC_NAME` dans votre fichier `.env`.
    ```
    # .env
    API_DYNAMIC_NAME=die.niang
    ```
    Assurez-vous que cette variable est définie sans préfixe `/v1/` pour éviter les duplications dans les routes.

### 2. Configuration des Endpoints API

La personnalisation de l'URL de base pour les endpoints API est gérée dans les fichiers suivants :

*   **`app/Providers/RouteServiceProvider.php`**: Ce service provider est responsable du chargement des fichiers de routes et de l'application des préfixes globaux.
    ```php
    // app/Providers/RouteServiceProvider.php
    Route::middleware('api')
        ->prefix('api/v1/' . config('api.name')) // Applique le préfixe global
        ->group(base_path('routes/api.php'));
    ```
    Ici, toutes les routes définies dans `routes/api.php` sont automatiquement préfixées par `api/v1/` suivi de la valeur de `config('api.name')` (qui est `die.niang`).

*   **`routes/api.php`**: Ce fichier contient les définitions spécifiques de vos routes API.
    ```php
    // routes/api.php
    // Ancien code avec préfixe redondant :
    // Route::prefix('/v1/' . config('api.name'))->group(function () { ... });

    // Code corrigé (sans le préfixe redondant) :
    Route::apiResource('comptes', CompteController::class)->only(['index']);
    Route::get('/comptes/non-archives', [CompteController::class, 'getNonArchivedComptes']);
    // ... autres routes
    ```
    **Correction effectuée :** Le préfixe `Route::prefix('/v1/' . config('api.name'))` a été supprimé de ce fichier car il entraînait une duplication du segment `v1/die.niang` dans l'URL finale (par exemple, `api/v1/die.niang/v1/die.niang/comptes`). Le préfixe global défini dans `RouteServiceProvider` est suffisant.

**Exemples d'endpoints API (avec `die.niang` comme nom dynamique) :**

*   **Obtenir tous les comptes :** `http://127.0.0.1:8000/api/v1/die.niang/comptes`
*   **Obtenir les comptes non archivés :** `http://127.0.0.1:8000/api/v1/die.niang/comptes/non-archives`
*   **Obtenir les comptes archivés :** `http://127.0.0.1:8000/api/v1/die.niang/comptes/archives`
*   **Route de test :** `http://127.0.0.1:8000/api/v1/die.niang/test`
*   **Supprimer un compte (Soft Delete) :** `DELETE http://127.0.0.1:8000/api/v1/die.niang/comptes/{id}` (Remplacez `{id}` par l'ID réel du compte)

### 3. Configuration de la Documentation Swagger

La documentation Swagger est également configurée pour utiliser ce nom dynamique :

*   **`.env`**: Les variables d'environnement suivantes sont utilisées pour configurer le chemin de base et l'hôte de Swagger.
    ```
    # .env
    L5_SWAGGER_BASE_PATH=/api/v1/die.niang
    L5_SWAGGER_CONST_HOST=http://127.0.0.1:8000
    ```
    `L5_SWAGGER_BASE_PATH` définit le chemin de base que Swagger utilisera pour construire les URLs de vos API. `L5_SWAGGER_CONST_HOST` définit l'hôte de base.

*   **`config/l5-swagger.php`**: Ce fichier de configuration contient les paramètres détaillés de L5 Swagger.
    ```php
    // config/l5-swagger.php
    'documentations' => [
        'default' => [
            'routes' => [
                'api' => 'die.niang/api/documentation', // Route pour accéder à l'interface Swagger
            ],
            // ...
        ],
    ],
    'defaults' => [
        'paths' => [
            'base' => env('L5_SWAGGER_BASE_PATH', '/api/v1/die.niang'), // Chemin de base utilisé par Swagger
        ],
        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://127.0.0.1:8000'),
        ],
    ],
    ```
    La route pour accéder à l'interface Swagger est définie pour inclure `die.niang`. Le `base` path et le `CONST_HOST` sont configurés pour utiliser les valeurs du `.env` ou des valeurs par défaut qui incluent le nom dynamique.

**Exemple d'accès à la documentation Swagger :**

*   **Documentation Swagger :** `http://127.0.0.1:8000/die.niang/api/documentation`

En suivant ces configurations, l'application Laravel et sa documentation Swagger utilisent un nom dynamique (`die.niang` par défaut) dans leurs URLs, offrant une flexibilité pour la personnalisation.

## Test de la Fonctionnalité de Déblocage Automatique des Comptes

Cette section explique comment tester la nouvelle fonctionnalité de déblocage automatique des comptes expirés à l'aide de Postman.

### Prérequis
- Avoir un compte existant dans la base de données
- Utiliser Postman ou un outil similaire pour les requêtes HTTP

### Étapes de Test

#### 1. Bloquer un Compte avec une Durée Courte
Pour tester le déblocage automatique, bloquez un compte avec une durée très courte (par exemple 1 minute).

**Requête POST :**
```
URL: http://127.0.0.1:8000/api/v1/die.niang/comptes/{id}/bloquer
Method: POST
Headers:
  Content-Type: application/json
  Accept: application/json
Body (raw JSON):
{
  "motif": "Test de déblocage automatique",
  "duree": 1,
  "unite": "jour"
}
```

**Exemple concret :**
```
URL: http://127.0.0.1:8000/api/v1/die.niang/comptes/1/bloquer
Method: POST
Headers:
  Content-Type: application/json
  Accept: application/json
Body:
{
  "motif": "Activé suspecte detectée",
  "duree": 1,
  "unite": "jour"
}
```

**Réponse attendue :**
```json
{
  "success": true,
  "message": "Compte bloqué avec succès",
  "data": {
    "id": 1,
    "statut": "bloque",
    "motifBlocage": "Test automatique",
    "dateBlocage": "2025-10-26T06:20:00.000000Z",
    "dateDeblocagePrevue": "2025-10-27T06:20:00.000000Z"
  }
}
```

#### 2. Vérifier l'État du Compte
Récupérez la liste des comptes pour voir que le compte est bien bloqué.

**Requête GET :**
```
URL: http://127.0.0.1:8000/api/v1/die.niang/comptes
Method: GET
Headers:
  Accept: application/json
```

**Réponse attendue :** Le compte avec l'ID spécifié devrait avoir `statut: "bloque"`.

#### 3. Simuler l'Expiration (Optionnel)
Pour tester immédiatement, vous pouvez modifier manuellement la `dateDeblocagePrevue` dans la base de données pour qu'elle soit dans le passé, ou attendre que la durée configurée s'écoule.

#### 4. Vérifier le Déblocage Automatique
Après l'expiration de la durée, faites une nouvelle requête pour récupérer les comptes. Le système vérifiera automatiquement et débloquera les comptes expirés.

**Requête GET (même que l'étape 2) :**
```
URL: http://127.0.0.1:8000/api/v1/die.niang/comptes
Method: GET
Headers:
  Accept: application/json
```

**Réponse attendue après expiration :** Le compte devrait maintenant avoir `statut: "actif"` et les champs de blocage remis à `null`.

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "numeroCompte": "CM123456789",
      "statut": "actif",
      "motifBlocage": null,
      "dateBlocage": null,
      "dateDeblocagePrevue": null,
      // ... autres champs
    }
  ],
  // ... pagination et autres métadonnées
}
```

### Points Importants
- Le déblocage automatique se produit lors des opérations suivantes :
  - Récupération de la liste des comptes (`GET /api/v1/die.niang/comptes`)
  - Récupération des comptes non archivés (`GET /api/v1/die.niang/comptes/non-archives`)
  - Tentative de blocage d'un compte déjà bloqué
  - Tentative de déblocage d'un compte
- Le système ne modifie que les comptes dont la `dateDeblocagePrevue` est dépassée
- Toutes les fonctionnalités existantes de blocage/déblocage manuel restent intactes
- Les comptes débloqués automatiquement passent au statut "actif" avec remise à zéro des champs de blocage

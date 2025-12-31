# Documentation des Seeders - MODULE A2

## Vue d'ensemble

Les seeders du MODULE A2 ont été créés pour peupler la base de données avec des données de test complètes pour les années académiques et les périodes.

## Seeders créés

### 1. SchoolSeeder (mis à jour)
**Fichier :** `database/seeders/SchoolSeeder.php`

**Fonction :** Crée 3 écoles de test avec différents systèmes académiques
- École Enma School (EES) - Système trimestre
- Collège Moderne d'Abidjan (CMA) - Système semestre  
- Groupe Scolaire Les Palmiers (GSP) - Système trimestre

**Caractéristiques :**
- Vérification d'existence avant création
- Configuration des paramètres par défaut
- Support de différents systèmes académiques

### 2. AcademicYearSeeder (mis à jour)
**Fichier :** `database/seeders/AcademicYearSeeder.php`

**Fonction :** Crée 4 années académiques par école
- 2023-2024 (inactive)
- 2024-2025 (inactive)
- 2025-2026 (active)
- 2026-2027 (inactive)

**Caractéristiques :**
- Liaison avec les écoles via `school_id`
- Création automatique des périodes
- Support multi-écoles

### 3. GradePeriodSeeder (mis à jour)
**Fichier :** `database/seeders/GradePeriodSeeder.php`

**Fonction :** Vérification et création des périodes académiques
- Trimestres pour les écoles avec système trimestre
- Semestres pour les écoles avec système semestre

**Caractéristiques :**
- Vérification d'existence avant création
- Respect du système académique de l'école
- Types et ordre définis pour chaque période

### 4. AdminUsersSeeder (nouveau)
**Fichier :** `database/seeders/AdminUsersSeeder.php`

**Fonction :** Crée les utilisateurs administrateurs de test
- 1 Super Administrateur (accès toutes écoles)
- 1 Admin par école (accès école spécifique)
- 1 Directeur par école (accès école spécifique)

**Comptes créés :**
```
superadmin@enmaschool.com - Super Admin
admin.ees@enmaschool.com - Admin École Enma
admin.cma@enmaschool.com - Admin Collège Moderne
directeur.ees@enmaschool.com - Directeur École Enma  
directeur.cma@enmaschool.com - Directeur Collège Moderne
```

### 5. AcademicTestDataSeeder (nouveau)
**Fichier :** `database/seeders/AcademicTestDataSeeder.php`

**Fonction :** Ajoute des données de test supplémentaires
- Années académiques futures (2027-2028)
- Années académiques archivées (2022-2023)
- Configuration des périodes actives

### 6. Module02DemoSeeder (nouveau)
**Fichier :** `database/seeders/Module02DemoSeeder.php`

**Fonction :** Seeder principal de démonstration
- Orchestration de tous les autres seeders
- Affichage d'un résumé des données créées
- Génération du rapport final avec comptes de test

## Utilisation

### Lancer tous les seeders
```bash
php artisan db:seed --class=Module02DemoSeeder
```

### Lancer un seeder spécifique
```bash
php artisan db:seed --class=AcademicYearSeeder
php artisan db:seed --class=AdminUsersSeeder
```

### Recréer la base complètement
```bash
php artisan migrate:fresh
php artisan db:seed --class=Module02DemoSeeder
```

## Données créées

### Écoles
- 2 écoles avec systèmes différents (trimestre/semestre)
- Paramètres et configurations par défaut
- Settings spécifiques par école

### Années académiques
- 6 années par école (de 2022-2023 à 2027-2028)
- Une seule année active par école (2025-2026)
- Périodes créées automatiquement

### Périodes académiques
- Trimestres : 3 périodes par année
- Semestres : 2 périodes par année  
- Types et ordres définis
- Une seule période active par année

### Utilisateurs
- 7 comptes administrateurs de test
- Rôles et permissions configurés
- Profils staff associés

## Comptes de test

**Mot de passe pour tous :** `password123`

| Email | Rôle | Accès |
|-------|------|-------|
| superadmin@enmaschool.com | Super Admin | Toutes les écoles |
| admin.ees@enmaschool.com | Admin | École Enma School |
| admin.cma@enmaschool.com | Admin | Collège Moderne |
| directeur.ees@enmaschool.com | Directeur | École Enma School |
| directeur.cma@enmaschool.com | Directeur | Collège Moderne |

## Interface d'administration

L'interface d'administration est accessible via :
- **URL :** `/admin/academic-years`
- **Authentification :** Utiliser l'un des comptes ci-dessus
- **Fonctionnalités :** CRUD complet pour années et périodes

## Sécurité

- Middleware `admin.access` pour la protection des routes
- Contrôle d'accès basé sur les rôles (RBAC)
- Validation des données dans les formulaires
- Protection CSRF sur tous les formulaires

## Notes techniques

- Seeders compatibles avec les données existantes
- Vérification d'existence avant création
- Support du système multi-écoles
- Gestion automatique des périodes selon le système académique
- Structure de base de données respectée
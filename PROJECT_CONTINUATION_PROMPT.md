# 🎓 ENMA School v1 - Prompt de Continuation du Module Universitaire

## 📋 CONTEXTE GÉNÉRAL DU PROJET

### Informations Système
- **Framework** : Laravel 11
- **Frontend** : Blade + Tailwind CSS + Alpine.js
- **Base de données** : MySQL
- **Architecture** : MVC avec Eloquent ORM
- **Authentification** : Laravel Breeze + Spatie Roles & Permissions
- **Date de dernière modification** : 31 décembre 2025

### Description du Projet
ENMA School v1 est un système de gestion scolaire complet supportant deux types d'établissements :
- **Pré-universitaire** : (primaire, secondaire, technique) avec structure Cycles → Niveaux → Classes → Matières
- **Universitaire** : Structure UFR → Départements → Programmes → Semestres → Unités d'Enseignement (système ECTS)

## 🎯 ÉTAT ACTUEL DU DÉVELOPPEMENT

### ✅ Modules Complètement Implémentés

#### 1. Infrastructure de Base
- [x] Modèles de base : User, School, SchoolSetting
- [x] Système d'authentification avec rôles (admin, teacher, student, etc.)
- [x] Gestion des types d'établissements flexibles
- [x] Configuration des paramètres scolaires

#### 2. Module Universitaire (RÉCEMMENT COMPLÉTÉ)
- [x] **Modèles universitaires** :
  - `UFR` (Unité de Formation et de Recherche)
  - `Department` (Département)
  - `Program` (Programme d'études)
  - `Semester` (Semestre)
  - `CourseUnit` (Unité d'Enseignement)

- [x] **Base de données** :
  - Migrations pour toutes les tables universitaires
  - Relations hiérarchiques complètes
  - Contraintes de clés étrangères
  - Système ECTS intégré

- [x] **Contrôleur UniversityController** :
  - Middleware de protection universitaire
  - Méthodes CRUD pour UFR
  - Gestion du contexte universitaire
  - Calcul de statistiques dynamiques

- [x] **Interface UFR** :
  - Vue dashboard universitaire : `resources/views/university/dashboard.blade.php`
  - Liste des UFR : `resources/views/university/ufrs/index.blade.php`
  - Création UFR : `resources/views/university/ufrs/create.blade.php`
  - Détail UFR : `resources/views/university/ufrs/show.blade.php`

- [x] **Navigation intégrée** :
  - Menu sidebar adaptatif selon le type d'établissement
  - Section "Module Universitaire" visible uniquement pour les universités

### 🏗️ Infrastructure Technique Détaillée

#### Routes Universitaires (routes/web.php)
```php
// Routes universitaires protégées par middleware
Route::middleware(['auth', 'university'])->prefix('university')->name('university.')->group(function () {
    Route::get('/dashboard', [UniversityController::class, 'dashboard'])->name('dashboard');
    
    // UFR Management
    Route::get('/ufrs', [UniversityController::class, 'ufrs'])->name('ufrs.index');
    Route::get('/ufrs/create', [UniversityController::class, 'createUFR'])->name('ufrs.create');
    Route::post('/ufrs', [UniversityController::class, 'storeUFR'])->name('ufrs.store');
    Route::get('/ufrs/{ufr}', [UniversityController::class, 'showUFR'])->name('ufrs.show');
});
```

#### Relations Modèles Clés
```php
// School.php
public function ufrs() { return $this->hasMany(UFR::class); }
public function isUniversity() { return $this->type === 'university'; }

// UFR.php
public function school() { return $this->belongsTo(School::class); }
public function departments() { return $this->hasMany(Department::class); }

// Department.php
public function ufr() { return $this->belongsTo(UFR::class); }
public function programs() { return $this->hasMany(Program::class); }
```

#### Configuration Base de Données
- **École active** : "Université des Sciences et Technologies" (type: university)
- **Utilisateur admin** : admin@enmaschool.com / password123
- **Rôles configurés** : admin, teacher, student, staff, etc.

## 🚀 PROCHAINES ÉTAPES À IMPLÉMENTER

### 1. Complétion Interface UFR (PRIORITÉ IMMÉDIATE)
```php
// Vues manquantes à créer :
- resources/views/university/ufrs/edit.blade.php (Édition UFR)
- Implémentation méthodes editUFR() et updateUFR() dans UniversityController
- Ajout gestion suppression UFR avec confirmations
```

### 2. Module Départements (ÉTAPE SUIVANTE)
```php
// À implémenter :
- Vues : index, create, show, edit pour départements
- Méthodes contrôleur pour CRUD départements
- Relations UFR ↔ Département dans les vues
- Statistiques départements (nombre de programmes, étudiants)
```

### 3. Module Programmes d'Études
```php
// À implémenter :
- Gestion niveaux académiques (L1-L3, M1-M2, D1-D3)
- Configuration ECTS par programme
- Durée programme (semestres/années)
- Prérequis entre programmes
```

### 4. Module Semestres et UE
```php
// À implémenter :
- Planification semestres académiques
- Répartition UE par semestre
- Calcul charge horaire (CM, TD, TP)
- Système d'évaluation par UE
```

## 🔧 SPÉCIFICATIONS TECHNIQUES DÉTAILLÉES

### Architecture Requise
- **Pattern MVC strict** : Modèles dans app/Models/, Contrôleurs dans app/Http/Controllers/
- **Validation** : Form Request classes pour validation complexe
- **Middleware personnalisé** : Protection routes universitaires
- **Traits** : HasSchoolContext pour injection contexte école

### Standards de Code
- **Nommage** : camelCase pour méthodes, snake_case pour DB
- **Vues Blade** : Héritage layout dashboard, composants réutilisables
- **Styles** : Tailwind CSS avec support mode sombre
- **JavaScript** : Alpine.js pour interactivité légère

### Base de Données
```sql
-- Structure tables principales
schools (id, name, type, academic_system, ...)
u_f_r_s (id, school_id, name, code, dean_name, ...)
departments (id, ufr_id, name, code, head_name, ...)
programs (id, department_id, name, level, ects_credits, ...)
semesters (id, program_id, number, ects_credits, ...)
course_units (id, semester_id, name, ects_credits, type, ...)
```

## 📊 FONCTIONNALITÉS BUSINESS À COMPLÉTER

### 1. Gestion Académique Avancée
- [ ] Import/Export données UFR (Excel/CSV)
- [ ] Génération rapports statistiques
- [ ] Historique modifications structures académiques
- [ ] Validation cohérence ECTS totaux

### 2. Interface Utilisateur
- [ ] Recherche/Filtrage avancé UFR/Départements
- [ ] Vue calendrier planning académique
- [ ] Dashboard graphiques (Chart.js)
- [ ] Notifications changements structures

### 3. Intégrations
- [ ] API REST pour données universitaires
- [ ] Export PDF structures académiques
- [ ] Synchronisation avec systèmes externes
- [ ] Backup automatique configurations

## 🎯 MISSION SPÉCIFIQUE

**Objectif immédiat** : Compléter l'interface de gestion des UFR puis étendre aux départements et programmes.

**Instructions pour l'assistant** :
1. Analyser le code existant dans le projet
2. Identifier les patterns utilisés (architecture, nommage, styles)
3. Continuer l'implémentation en respectant la cohérence
4. Proposer des améliorations techniques et fonctionnelles
5. Suggérer les prochaines étapes logiques de développement

## 🔍 POINTS D'ATTENTION CRITIQUES

### Erreurs à Éviter
- ❌ Ne pas casser les relations existantes entre modèles
- ❌ Respecter la validation des contraintes ECTS
- ❌ Maintenir la cohérence UI/UX avec l'existant
- ❌ Vérifier la protection middleware sur routes sensibles

### Bonnes Pratiques à Maintenir
- ✅ Utiliser les helpers school() et auth() existants
- ✅ Respecter la structure de navigation sidebar
- ✅ Implémenter validation côté serveur robuste
- ✅ Ajouter messages de succès/erreur utilisateur

## 💡 DEMANDES SPÉCIFIQUES À L'ASSISTANT

Quand tu reprends ce projet, peux-tu :

1. **Analyser le code existant** et identifier les patterns/conventions utilisées
2. **Compléter les fonctionnalités manquantes** en respectant l'architecture existante
3. **Proposer des améliorations** techniques ou fonctionnelles pertinentes
4. **Suggérer les prochaines étapes** logiques de développement
5. **Identifier les optimisations** possibles (performance, sécurité, UX)
6. **Recommander des fonctionnalités avancées** pour enrichir le module universitaire

## 📁 FICHIERS CLÉS DU PROJET

### Modèles
- `app/Models/School.php` - Modèle école principal
- `app/Models/UFR.php` - Modèle UFR universitaire
- `app/Models/Department.php` - Modèle département
- `app/Models/Program.php` - Modèle programme d'études

### Contrôleurs
- `app/Http/Controllers/UniversityController.php` - Contrôleur principal universitaire

### Vues
- `resources/views/university/` - Dossier vues universitaires
- `resources/views/components/layout/sidebar.blade.php` - Navigation principale
- `resources/views/layouts/dashboard.blade.php` - Layout principal

### Base de données
- `database/migrations/` - Migrations universitaires
- `database/seeders/` - Seeders pour données test

### Configuration
- `routes/web.php` - Routes universitaires
- `app/Http/Middleware/` - Middlewares de protection

---

**Note importante** : Ce projet utilise un système de types d'établissements flexibles. L'interface universitaire ne doit être accessible que si `school()->isUniversity()` retourne true. Le système pré-universitaire continue de fonctionner en parallèle.

**État serveur** : Laravel dev server démarrable avec `php artisan serve` sur http://127.0.0.1:8000

**Connexion test** : admin@enmaschool.com / password123
# PROCHAINE ÉTAPE TECHNIQUE PRIORITAIRE - EnmaSchool

**Date d'analyse** : 6 janvier 2026  
**Objectif** : Consolidation du socle technique pour un ERP éducatif national

---

## I. DIAGNOSTIC TECHNIQUE ACTUEL DU PROJET

### ✅ **Points forts identifiés**
- **Architecture modulaire** : Domaines bien séparés (Academic, Evaluation, Enrollment, etc.)
- **Spatie Permission** : Système de rôles/permissions robuste déjà intégré
- **Modèles polymorphiques** : Support préuniv/universitaire via `School.type`
- **Structure académique** : Relations cohérentes entre cycles, niveaux, classes
- **Middleware spécialisés** : `PreUniversityMiddleware`, `UniversityMiddleware`

### 🔧 **Architecture technique solide**
```php
// Relations bien définies
School -> hasMany(Students, Teachers, Classes, AcademicYears)
Student -> belongsTo(User, School) + hasMany(Enrollments)
Teacher -> belongsTo(User, School) + hasMany(Assignments)
SchoolClass -> belongsTo(School, Level, AcademicYear)
```

---

## II. PROBLÈMES BLOQUANTS IDENTIFIÉS

### 🚨 **CRITIQUE 1 : Gestion du contexte école défaillante**
```php
// Problème actuel dans School.php
public static function getActiveSchool() {
    return static::active()->first(); // Récupère LA PREMIÈRE école active
}
```
**Impact** : Impossible de gérer plusieurs écoles, contexte utilisateur absent

### 🚨 **CRITIQUE 2 : Utilisateurs non liés aux écoles**
```php
// Table users actuelle : PAS de school_id
protected $fillable = [
    'name', 'email', 'password', 'phone', 'address'
    // MANQUE : 'school_id' ou relation contextuelle
];
```

### 🚨 **CRITIQUE 3 : Contexte école dispersé**
- `HasSchoolContext` trait : logique cachée
- `HasUniversityContext` trait : duplication
- `EnsureSchoolExists` middleware : logique basique
- Helpers `school()` : approche simpliste

### 🚨 **CRITIQUE 4 : Permissions non contextualisées**
```php
// Permissions actuelles sans contexte école
$user->hasPermission('manage_students'); // Pour QUELLE école ?
```

---

## III. CORE COMMUN À CONSOLIDER EN PRIORITÉ

### 🎯 **PRIORITÉ ABSOLUE : SYSTÈME DE CONTEXTE ÉCOLE UNIFIÉ**

Le contexte école est le **socle technique fondamental** qui conditionne :
- Multi-établissements (préparation V2)
- Multi-rôles contextuels
- Multi-dashboards par type d'école
- Permissions granulaires
- Isolation des données

---

## IV. MODÈLES ET TABLES À AUDITER / ÉTENDRE

### 📋 **Tables à modifier (migrations)**
1. **`users`** : Ajouter `school_id` (nullable pour transition)
2. **Créer `user_school_contexts`** : Table pivot pour contextes multiples (prép V2)

### 🔧 **Modèles à refactoriser**
1. **User.php** : Ajouter relation avec School
2. **School.php** : Refactoriser `getActiveSchool()` → `getSchoolForUser()`
3. **Traits existants** : Centraliser en un seul `HasSchoolContext`

---

## V. REFACTORING NÉCESSAIRE

### 🔄 **Centralisation du contexte école**
- Fusionner `HasSchoolContext` + `HasUniversityContext` 
- Créer middleware central `SchoolContextMiddleware`
- Unifier la logique dans un `SchoolContextService`

---

## VI. FONCTIONNALITÉS À NE PAS IMPLÉMENTER MAINTENANT

❌ **Nouvelles fonctionnalités métier** (bulletins, emplois du temps, etc.)
❌ **Interface de configuration avancée** 
❌ **Multi-établissements complet** (prévu V2)
❌ **API externes**
❌ **Optimisations de performance** (cache, queues)

---

## VII. PROCHAINE ÉTAPE TECHNIQUE CONCRÈTE

### 🎯 **ÉTAPE UNIQUE : CONSOLIDATION DU SYSTÈME DE CONTEXTE ÉCOLE**

**Objectif** : Créer un système de contexte école robuste, unifié et évolutif qui servira de socle pour toutes les fonctionnalités futures.

---

## VIII. PLAN D'IMPLÉMENTATION DÉTAILLÉ

### **Phase 1 : Préparation des données (30 min)**

1. **Migration User-School**
```php
// database/migrations/add_school_context_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('school_id')->nullable()
          ->constrained()->onDelete('set null');
    $table->index('school_id');
});
```

2. **Seeder de transition**
```php
// Associer users existants à l'école active
$activeSchool = School::active()->first();
User::whereNull('school_id')->update(['school_id' => $activeSchool->id]);
```

### **Phase 2 : Service central (45 min)**

3. **Créer SchoolContextService**
```php
// app/Services/SchoolContextService.php
class SchoolContextService
{
    public function getSchoolForUser(User $user): ?School
    public function setUserSchoolContext(User $user, School $school): void
    public function getCurrentSchoolContext(): ?School
    public function validateSchoolAccess(User $user, School $school): bool
}
```

### **Phase 3 : Middleware central (30 min)**

4. **SchoolContextMiddleware**
```php
// app/Http/Middleware/SchoolContextMiddleware.php
// Remplace EnsureSchoolExists + logique contexte
public function handle(Request $request, Closure $next): Response
{
    $user = auth()->user();
    $school = $this->contextService->getSchoolForUser($user);
    
    // Injection contexte dans request
    $request->merge(['school_context' => $school]);
    app()->instance('current_school', $school);
    
    return $next($request);
}
```

### **Phase 4 : Refactoring modèles (45 min)**

5. **User.php - Relation école**
```php
// Dans User.php
public function school(): BelongsTo {
    return $this->belongsTo(School::class);
}

public function hasSchoolAccess(School $school): bool {
    return $this->school_id === $school->id;
}
```

6. **School.php - Méthodes contextuelles**
```php
// Remplacer getActiveSchool()
public static function getForUser(User $user): ?School {
    return $user->school;
}

public static function getCurrentContext(): ?School {
    return app('current_school');
}
```

### **Phase 5 : Trait unifié (30 min)**

7. **HasSchoolContext unifié**
```php
// app/Traits/HasSchoolContext.php
trait HasSchoolContext
{
    protected function getCurrentSchool(): School
    protected function ensureSchoolAccess(): void
    protected function getSchoolContext(): array
    protected function ensureUniversityMode(): void
    protected function ensurePreUniversityMode(): void
}
```

### **Phase 6 : Tests et validation (30 min)**

8. **Tests unitaires**
```php
// tests/Unit/SchoolContextTest.php
test('user can access their school context')
test('school context is injected in middleware')
test('unauthorized school access is blocked')
```

### **Ordre d'exécution**
1. Migration + Seeder (données)
2. SchoolContextService (logique métier)
3. SchoolContextMiddleware (injection contexte)
4. Refactoring User + School (relations)
5. HasSchoolContext unifié (interface)
6. Tests + validation

### **Fichiers concernés**
- `database/migrations/` : 1 nouvelle migration
- `app/Services/SchoolContextService.php` : nouveau
- `app/Http/Middleware/SchoolContextMiddleware.php` : nouveau
- `app/Models/User.php` : modification relation
- `app/Models/School.php` : refactoring méthodes
- `app/Traits/HasSchoolContext.php` : unification
- `app/Http/Kernel.php` : enregistrement middleware
- `tests/Unit/SchoolContextTest.php` : nouveau

### **Bonnes pratiques Laravel**
- Service Provider pour l'injection de dépendances
- Middleware ordering approprié (auth avant school context)
- Caching du contexte école dans l'instance app()
- Validation avec Form Requests
- Tests Feature + Unit séparés

---

## IX. RÉSULTAT ATTENDU UNE FOIS CETTE ÉTAPE TERMINÉE

### 🎯 **Socle technique solide**
✅ **Contexte école unifié** pour toutes les fonctionnalités
✅ **Base préparée** pour multi-établissements V2  
✅ **Permissions contextualisées** possibles
✅ **Architecture évolutive** pour les dashboards
✅ **Code maintenable** sans duplication de logique

### 🚀 **Déblocage fonctionnel**
✅ Les contrôleurs peuvent facilement accéder au contexte école
✅ Les permissions peuvent être étendues avec contexte
✅ Les vues peuvent s'adapter au type d'école
✅ Les services métiers ont un contexte fiable

### 📊 **Métriques de succès**
- ⚡ Temps d'accès contexte école < 5ms
- 🔒 100% des accès école validés
- 🧪 Couverture tests > 90%
- 📝 Code DRY (suppression doublons traits)

---

## X. VALIDATION TECHNIQUE

### **Critères d'acceptation**
1. ✅ Migration exécutée sans erreur
2. ✅ Service de contexte fonctionnel
3. ✅ Middleware injecte correctement le contexte
4. ✅ Relations User-School opérationnelles
5. ✅ Trait unifié remplace les anciens
6. ✅ Tests passent à 100%

### **Points de contrôle**
- **Données** : Tous les users ont un school_id
- **Service** : Contexte école accessible partout
- **Middleware** : Injection automatique du contexte
- **Modèles** : Relations cohérentes et optimisées
- **Code** : Suppression de la duplication
- **Tests** : Couverture complète des cas d'usage

---

**Cette étape technique est FONDAMENTALE** car elle conditionne toute l'évolution future du projet vers un SaaS éducatif national robuste et évolutif.

---

*Document généré le 6 janvier 2026 - EnmaSchool Technical Architecture*
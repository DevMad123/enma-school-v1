# Système de Resynchronisation Différée UE-ECUE

## Vue d'ensemble

Le système de resynchronisation différée permet de gérer automatiquement les erreurs de synchronisation entre les Unités d'Enseignement (UE) et leurs Éléments Constitutifs (ECUE) via un système de Jobs en arrière-plan.

## Composants

### 1. Job ResyncCourseUnitJob

**Fichier**: `app/Jobs/ResyncCourseUnitJob.php`

**Fonctionnalités**:
- Resynchronisation automatique des UE avec leurs ECUE
- Système de retry avec délais exponentiels (30s, 60s, 120s)
- Validation des données post-synchronisation
- Logging détaillé des opérations

**Utilisation**:
```php
// Dispatch immédiat
ResyncCourseUnitJob::dispatch($courseUnitId, 'reason');

// Dispatch avec délai
ResyncCourseUnitJob::dispatch($courseUnitId, 'reason')
    ->delay(now()->addMinutes(2));
```

### 2. Observer CourseUnitElementObserver

**Fichier**: `app/Observers/CourseUnitElementObserver.php`

**Nouvelle fonctionnalité**: Méthode `handleSyncError()`
- Détecte les erreurs de synchronisation
- Programme automatiquement un Job de resynchronisation pour certains types d'erreurs
- Gère spécifiquement les QueryException et Deadlock

**Types d'erreurs gérées**:
- `QueryException`: Resync avec délai de 2 minutes
- `Deadlock`: Resync avec délai de 30 secondes
- Autres exceptions: Pas de resync automatique

### 3. Migration et Modèle

**Nouvelle colonne**: `last_job_sync` dans `course_units`
- Timestamp de la dernière resynchronisation par Job
- Permet le suivi et l'audit des resynchronisations

## Configuration Queue

Le système utilise la configuration `database` par défaut pour les queues.

**Prérequis**:
- Table `jobs` migrée
- Configuration queue active dans `.env`:
```env
QUEUE_CONNECTION=database
```

## Démarrage du Worker

Pour traiter les jobs en arrière-plan :

```bash
# Worker simple
php artisan queue:work

# Worker avec options recommandées
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

## Monitoring

### Logs
Les opérations sont loggées dans `storage/logs/laravel.log` avec les tags :
- `Resynchronisation différée`
- `ResyncCourseUnitJob`

### Commandes utiles

```bash
# Voir les jobs en queue
php artisan queue:monitor

# Voir les jobs failed
php artisan queue:failed

# Nettoyer la queue
php artisan queue:clear

# Retry failed jobs
php artisan queue:retry all
```

## Tests

### Scripts de test inclus

1. **`test_resync_job.php`**: Test unitaire du Job
2. **`test_observer_job_integration.php`**: Test intégration Observer -> Job  
3. **`test_worker_jobs.php`**: Test exécution des Jobs

### Exécution des tests

```bash
php test_resync_job.php
php test_observer_job_integration.php  
php test_worker_jobs.php
```

## Exemples d'utilisation

### Déclencher une resync manuelle
```php
$courseUnit = CourseUnit::find(1);
ResyncCourseUnitJob::dispatch($courseUnit->id, 'manual_trigger');
```

### Forcer une resync immédiate
```php
$courseUnit = CourseUnit::find(1);
ResyncCourseUnitJob::dispatchSync($courseUnit->id, 'immediate_sync');
```

### Programmer une resync avec délai personnalisé
```php
ResyncCourseUnitJob::dispatch($courseUnit->id, 'custom_delay')
    ->delay(now()->addHour());
```

## Surveillance et Métriques

### Vérifications recommandées

1. **Nombre de jobs failed**: Doit rester bas
2. **Temps d'exécution moyen**: < 5 secondes par job
3. **Fréquence des erreurs de sync**: Monitoring via logs
4. **Cohérence des données**: Validation post-sync automatique

### Alertes suggérées

- Job failed > 10 en 1 heure
- Queue size > 100 jobs
- Erreurs de sync récurrentes sur même UE

## Bonnes Pratiques

1. **Toujours utiliser des délais** pour éviter la surcharge DB
2. **Monitorer les performances** du worker
3. **Vérifier régulièrement** les failed jobs
4. **Maintenir** les logs pour audit
5. **Tester** les modifications avec les scripts fournis

## Limitations

- Maximum 3 tentatives par job
- Timeout de 5 secondes par transaction de resync
- Worker doit être actif pour traitement automatique
- Dépendant de la configuration database pour les queues

## Dépannage

### Job ne s'exécute pas
1. Vérifier que le worker est démarré
2. Vérifier la configuration queue
3. Vérifier les permissions sur la table jobs

### Erreurs de timeout
1. Augmenter le timeout de transaction
2. Optimiser les requêtes de synchronisation
3. Réduire la charge sur la DB

### Jobs failed récurrents
1. Analyser les logs d'erreur
2. Vérifier l'intégrité des données
3. Considérer l'ajustement des délais de retry
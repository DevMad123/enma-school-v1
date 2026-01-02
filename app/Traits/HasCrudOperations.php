<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Trait pour les opérations CRUD standardisées
 * 
 * Ce trait fournit des méthodes réutilisables pour :
 * - Validation de données
 * - Création d'entités avec transaction
 * - Mise à jour sécurisée
 * - Suppression avec vérifications
 * - Logging automatique des opérations
 * 
 * @trait HasCrudOperations
 * @package App\Traits
 * @author N'golo Madou OUATTARA  
 * @version 1.0
 * @since 2026-01-02
 */
trait HasCrudOperations
{
    /**
     * Créer une entité avec validation et transaction
     * 
     * @param string $modelClass Classe du modèle à créer
     * @param array $data Données validées
     * @param string $entityName Nom de l'entité pour le logging
     * @param array $additionalData Données supplémentaires à ajouter
     * @return Model Instance du modèle créé
     * 
     * @throws \Exception Si la création échoue
     */
    protected function createEntity(
        string $modelClass, 
        array $data, 
        string $entityName,
        array $additionalData = []
    ): Model {
        try {
            DB::beginTransaction();
            
            // Fusion des données
            $createData = array_merge($data, $additionalData);
            
            // Création de l'entité
            /** @var Model $entity */
            $entity = $modelClass::create($createData);
            
            // Logging de création
            $this->logCrudOperation('create', $entityName, $entity, $data);
            
            DB::commit();
            return $entity;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Erreur lors de la création {$entityName}", [
                'data' => $data,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            throw new \Exception("Erreur lors de la création de {$entityName}: " . $e->getMessage());
        }
    }
    
    /**
     * Mettre à jour une entité avec validation et transaction
     * 
     * @param Model $entity Entité à mettre à jour
     * @param array $data Nouvelles données validées
     * @param string $entityName Nom de l'entité pour le logging
     * @return bool True si la mise à jour a réussi
     * 
     * @throws \Exception Si la mise à jour échoue
     */
    protected function updateEntity(Model $entity, array $data, string $entityName): bool
    {
        try {
            DB::beginTransaction();
            
            $originalData = $entity->toArray();
            
            // Mise à jour
            $updated = $entity->update($data);
            
            if ($updated) {
                // Logging avec comparaison des changements
                $changes = array_diff_assoc($entity->fresh()->toArray(), $originalData);
                $this->logCrudOperation('update', $entityName, $entity, $changes);
            }
            
            DB::commit();
            return $updated;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Erreur lors de la mise à jour {$entityName}", [
                'entity_id' => $entity->id ?? null,
                'data' => $data,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            throw new \Exception("Erreur lors de la mise à jour de {$entityName}: " . $e->getMessage());
        }
    }
    
    /**
     * Supprimer une entité avec vérifications et transaction
     * 
     * @param Model $entity Entité à supprimer
     * @param string $entityName Nom de l'entité pour le logging
     * @param callable|null $validator Fonction de validation personnalisée
     * @return bool True si la suppression a réussi
     * 
     * @throws \App\Exceptions\BusinessRuleException Si la suppression n'est pas autorisée
     * @throws \Exception Si la suppression échoue
     */
    protected function deleteEntity(
        Model $entity, 
        string $entityName, 
        ?callable $validator = null
    ): bool {
        try {
            DB::beginTransaction();
            
            // Validation personnalisée si fournie
            if ($validator && !$validator($entity)) {
                throw new \Exception("Validation de suppression échouée pour {$entityName}");
            }
            
            // Sauvegarder les données pour le logging
            $entityData = $entity->toArray();
            $entityId = $entity->id;
            
            // Suppression
            $deleted = $entity->delete();
            
            if ($deleted) {
                $this->logCrudOperation('delete', $entityName, null, $entityData);
            }
            
            DB::commit();
            return $deleted;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Erreur lors de la suppression {$entityName}", [
                'entity_id' => $entity->id ?? null,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Valider des données avec des règles personnalisées
     * 
     * @param array $data Données à valider
     * @param array $rules Règles de validation
     * @param array $messages Messages d'erreur personnalisés
     * @return array Données validées
     * 
     * @throws \Illuminate\Validation\ValidationException Si la validation échoue
     */
    protected function validateData(array $data, array $rules, array $messages = []): array
    {
        $validator = Validator::make($data, $rules, $messages);
        
        if ($validator->fails()) {
            Log::warning('Validation échouée', [
                'errors' => $validator->errors()->toArray(),
                'data' => collect($data)->except(['password', 'password_confirmation'])->toArray(),
                'user_id' => auth()->id()
            ]);
            
            throw new \Illuminate\Validation\ValidationException($validator);
        }
        
        return $validator->validated();
    }
    
    /**
     * Pagination standardisée avec filtres
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query Builder de requête
     * @param Request $request Requête HTTP avec filtres
     * @param int $perPage Nombre d'éléments par page
     * @param array $allowedFilters Filtres autorisés
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Résultats paginés
     */
    protected function paginateWithFilters(
        $query, 
        Request $request, 
        int $perPage = 15, 
        array $allowedFilters = []
    ) {
        // Application des filtres
        foreach ($allowedFilters as $filter => $column) {
            if ($request->filled($filter)) {
                $value = $request->get($filter);
                
                if (is_array($column)) {
                    // Filtre personnalisé avec callback
                    $callback = $column['callback'] ?? null;
                    if ($callback && is_callable($callback)) {
                        $query = $callback($query, $value);
                    }
                } else {
                    // Filtre simple sur colonne
                    if (str_contains($column, '.')) {
                        // Relation
                        $parts = explode('.', $column);
                        $relation = $parts[0];
                        $field = $parts[1];
                        $query->whereHas($relation, function ($q) use ($field, $value) {
                            $q->where($field, $value);
                        });
                    } else {
                        // Colonne directe
                        $query->where($column, $value);
                    }
                }
            }
        }
        
        // Recherche textuelle si fournie
        if ($request->filled('search') && isset($allowedFilters['search'])) {
            $searchFields = $allowedFilters['search']['fields'] ?? [];
            $search = $request->get('search');
            
            $query->where(function ($q) use ($searchFields, $search) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }
        
        return $query->paginate($perPage);
    }
    
    /**
     * Logger une opération CRUD
     * 
     * @param string $operation Type d'opération (create, update, delete)
     * @param string $entityName Nom de l'entité
     * @param Model|null $entity Instance de l'entité (null pour delete)
     * @param array $data Données de l'opération
     */
    private function logCrudOperation(
        string $operation, 
        string $entityName, 
        ?Model $entity, 
        array $data
    ): void {
        try {
            // Utiliser le modèle ActivityLog directement si le package activity est installé
            if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                \Spatie\Activitylog\Models\Activity::create([
                    'log_name' => 'crud',
                    'description' => ucfirst($operation) . " {$entityName}",
                    'subject_type' => $entity ? get_class($entity) : null,
                    'subject_id' => $entity ? $entity->id : null,
                    'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                    'causer_id' => auth()->id(),
                    'properties' => [
                        'operation' => $operation,
                        'entity_name' => $entityName,
                        'data' => $data,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent()
                    ]
                ]);
            } else {
                // Fallback vers les logs Laravel standards
                Log::info("CRUD {$operation}: {$entityName}", [
                    'operation' => $operation,
                    'entity_name' => $entityName,
                    'entity_id' => $entity ? $entity->id : null,
                    'user_id' => auth()->id(),
                    'data' => $data
                ]);
            }
            
        } catch (\Exception $e) {
            // Ne pas faire échouer l'opération principale si le logging échoue
            Log::error('Erreur lors du logging CRUD', [
                'operation' => $operation,
                'entity_name' => $entityName,
                'error' => $e->getMessage()
            ]);
        }
    }
}
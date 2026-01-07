<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EducationalSettingAudit extends Model
{
    use HasFactory;

    protected $table = 'edu_settings_audit';

    protected $fillable = [
        'setting_id',
        'action',
        'old_value',
        'new_value',
        'changed_by',
        'changed_at',
        'reason',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'changed_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Relations
     */
    public function setting(): BelongsTo
    {
        return $this->belongsTo(EducationalSetting::class, 'setting_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scopes
     */
    public function scopeForSetting($query, int $settingId)
    {
        return $query->where('setting_id', $settingId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('changed_by', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('changed_at', '>=', now()->subDays($days));
    }

    /**
     * Helper methods
     */
    public function getChangesAttribute(): array
    {
        if ($this->action === 'create') {
            return [
                'type' => 'creation',
                'new_data' => $this->new_value,
            ];
        }

        if ($this->action === 'delete') {
            return [
                'type' => 'suppression',
                'old_data' => $this->old_value,
            ];
        }

        return [
            'type' => 'modification',
            'old_data' => $this->old_value,
            'new_data' => $this->new_value,
            'differences' => $this->calculateDifferences(),
        ];
    }

    public function getFormattedChangesAttribute(): string
    {
        $changes = $this->changes;
        
        switch ($changes['type']) {
            case 'creation':
                return 'Création avec la valeur : ' . json_encode($changes['new_data']);
                
            case 'suppression':
                return 'Suppression de la valeur : ' . json_encode($changes['old_data']);
                
            case 'modification':
                $diffs = [];
                foreach ($changes['differences'] as $key => $change) {
                    $diffs[] = "{$key}: {$change['old']} → {$change['new']}";
                }
                return 'Modifications : ' . implode(', ', $diffs);
        }

        return 'Modification inconnue';
    }

    private function calculateDifferences(): array
    {
        $differences = [];
        $oldValue = (array) $this->old_value;
        $newValue = (array) $this->new_value;

        // Clés modifiées ou ajoutées
        foreach ($newValue as $key => $value) {
            if (!isset($oldValue[$key]) || $oldValue[$key] !== $value) {
                $differences[$key] = [
                    'old' => $oldValue[$key] ?? null,
                    'new' => $value,
                ];
            }
        }

        // Clés supprimées
        foreach ($oldValue as $key => $value) {
            if (!isset($newValue[$key])) {
                $differences[$key] = [
                    'old' => $value,
                    'new' => null,
                ];
            }
        }

        return $differences;
    }
}
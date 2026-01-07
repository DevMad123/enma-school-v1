<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EducationalSetting extends Model
{
    use HasFactory;

    protected $table = 'school_edu_settings';

    protected $fillable = [
        'school_id',
        'school_type',
        'edu_level',
        'category',
        'key',
        'value',
        'changed_by',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Relations
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(EducationalSettingAudit::class, 'setting_id');
    }

    /**
     * Scopes
     */
    public function scopeForSchool($query, ?int $schoolId)
    {
        if ($schoolId) {
            return $query->where('school_id', $schoolId);
        }
        return $query->whereNull('school_id');
    }

    public function scopeForSchoolType($query, string $schoolType)
    {
        return $query->where('school_type', $schoolType);
    }

    public function scopeForEducationalLevel($query, ?string $level)
    {
        if ($level) {
            return $query->where('educational_level', $level);
        }
        return $query->whereNull('educational_level');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('setting_category', $category);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Helper methods
     */
    public function getFullKeyAttribute(): string
    {
        return $this->setting_category . '.' . $this->setting_key;
    }

    public function isGlobal(): bool
    {
        return $this->school_id === null;
    }

    public function getValue(mixed $default = null): mixed
    {
        return $this->is_active ? $this->setting_value : $default;
    }

    /**
     * Boot method pour l'audit automatique
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($setting) {
            $setting->createAudit('create', null, $setting->setting_value);
        });

        static::updated(function ($setting) {
            if ($setting->isDirty('setting_value')) {
                $setting->createAudit('update', $setting->getOriginal('setting_value'), $setting->setting_value);
            }
        });

        static::deleted(function ($setting) {
            $setting->createAudit('delete', $setting->setting_value, null);
        });
    }

    /**
     * Créer un enregistrement d'audit
     */
    private function createAudit(string $action, mixed $oldValue, mixed $newValue): void
    {
        EducationalSettingAudit::create([
            'setting_id' => $this->id,
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
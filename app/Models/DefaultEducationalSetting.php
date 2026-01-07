<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DefaultEducationalSetting extends Model
{
    use HasFactory;

    protected $table = 'default_edu_settings';

    protected $fillable = [
        'school_type',
        'educational_level',
        'setting_category',
        'setting_key',
        'setting_value',
        'description',
        'is_required',
        'validation_rules',
    ];

    protected $casts = [
        'setting_value' => 'array',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
    ];

    /**
     * Scopes
     */
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

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Helper methods
     */
    public function getFullKeyAttribute(): string
    {
        return $this->setting_category . '.' . $this->setting_key;
    }

    public function validateValue(mixed $value): array
    {
        $errors = [];
        
        if (!$this->validation_rules) {
            return $errors;
        }

        foreach ($this->validation_rules as $rule => $ruleValue) {
            switch ($rule) {
                case 'required':
                    if ($ruleValue && ($value === null || $value === '')) {
                        $errors[] = "Le paramètre {$this->setting_key} est obligatoire";
                    }
                    break;
                    
                case 'min':
                    if (is_numeric($value) && $value < $ruleValue) {
                        $errors[] = "Le paramètre {$this->setting_key} doit être supérieur à {$ruleValue}";
                    }
                    break;
                    
                case 'max':
                    if (is_numeric($value) && $value > $ruleValue) {
                        $errors[] = "Le paramètre {$this->setting_key} doit être inférieur à {$ruleValue}";
                    }
                    break;
                    
                case 'in':
                    if (!in_array($value, $ruleValue)) {
                        $errors[] = "Le paramètre {$this->setting_key} doit être l'une des valeurs : " . implode(', ', $ruleValue);
                    }
                    break;
                    
                case 'regex':
                    if (is_string($value) && !preg_match($ruleValue, $value)) {
                        $errors[] = "Le paramètre {$this->setting_key} ne respecte pas le format requis";
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * Créer un setting personnalisé basé sur ce template
     */
    public function createCustomSetting(int $schoolId, mixed $customValue = null): EducationalSetting
    {
        return EducationalSetting::create([
            'school_id' => $schoolId,
            'school_type' => $this->school_type,
            'educational_level' => $this->educational_level,
            'setting_category' => $this->setting_category,
            'setting_key' => $this->setting_key,
            'setting_value' => $customValue ?? $this->setting_value,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);
    }
}
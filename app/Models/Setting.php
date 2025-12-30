<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Récupérer une valeur de paramètre
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            // Si c'est un tableau JSON, le retourner décodé
            if (is_array($setting->value)) {
                return $setting->value;
            }

            // Sinon retourner la valeur simple
            return $setting->value;
        });
    }

    /**
     * Définir une valeur de paramètre
     */
    public static function set(string $key, $value): void
    {
        $type = is_array($value) ? 'array' : (is_bool($value) ? 'boolean' : 'string');
        
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
            ]
        );

        Cache::forget("setting.{$key}");
    }

    /**
     * Supprimer un paramètre
     */
    public static function forget(string $key): void
    {
        static::where('key', $key)->delete();
        Cache::forget("setting.{$key}");
    }

    /**
     * Récupérer tous les paramètres d'un préfixe
     */
    public static function getByPrefix(string $prefix): array
    {
        return static::where('key', 'like', $prefix . '%')
            ->pluck('value', 'key')
            ->toArray();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'key',
        'value',
    ];

    /**
     * Relation avec l'école
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }
}

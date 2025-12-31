<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'entity',
        'entity_id',
        'action',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes pour faciliter les requêtes
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
    }

    public function scopeByEntity($query, $entity)
    {
        return $query->where('entity', $entity);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeForTeachers($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->whereHas('teacher');
        });
    }

    public function scopeForStudents($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->whereHas('student');
        });
    }

    /**
     * Méthodes statiques pour créer des logs d'activité facilement
     */
    public static function logActivity(User $user, string $entity, $entityId, string $action, array $properties = [])
    {
        return static::create([
            'user_id' => $user->id,
            'entity' => $entity,
            'entity_id' => $entityId,
            'action' => $action,
            'properties' => $properties,
        ]);
    }

    public static function logCourseActivity(User $user, $courseId, string $action, array $properties = [])
    {
        return static::logActivity($user, 'course', $courseId, $action, $properties);
    }

    public static function logAssignmentActivity(User $user, $assignmentId, string $action, array $properties = [])
    {
        return static::logActivity($user, 'assignment', $assignmentId, $action, $properties);
    }

    public static function logStudentActivity(User $user, $studentId, string $action, array $properties = [])
    {
        return static::logActivity($user, 'student', $studentId, $action, $properties);
    }

    public static function logPaymentActivity(User $user, $paymentId, string $action, array $properties = [])
    {
        return static::logActivity($user, 'payment', $paymentId, $action, $properties);
    }
}
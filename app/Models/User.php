<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'date_of_birth',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relations vers les profils métier
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function parentProfile()
    {
        return $this->hasOne(ParentProfile::class);
    }

    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    /**
     * Vérifier le type de profil utilisateur
     */
    public function isStudent(): bool
    {
        return $this->student !== null;
    }

    public function isTeacher(): bool
    {
        return $this->teacher !== null;
    }

    public function isParent(): bool
    {
        return $this->parentProfile !== null;
    }

    public function isStaff(): bool
    {
        return $this->staff !== null;
    }

    /**
     * Obtenir le profil actif de l'utilisateur
     */
    public function getActiveProfile()
    {
        if ($this->isStudent()) return $this->student;
        if ($this->isTeacher()) return $this->teacher;
        if ($this->isParent()) return $this->parentProfile;
        if ($this->isStaff()) return $this->staff;
        return null;
    }

    /**
     * Relations pour les logs
     */
    public function userLogs()
    {
        return $this->hasMany(UserLog::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Méthodes utilitaires pour les logs
     */
    public function logActivity(string $entity, $entityId, string $action, array $properties = [])
    {
        return ActivityLog::logActivity($this, $entity, $entityId, $action, $properties);
    }

    public function logAction(string $action, string $description = null, array $metadata = [])
    {
        return UserLog::logAction($this, $action, $description, $metadata);
    }
}

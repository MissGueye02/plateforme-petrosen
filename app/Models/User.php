<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['nom', 'prenom', 'mail', 'motdepasse', 'role_id', 'created_by'])]
#[Hidden(['motdepasse', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'utilisateurs';
    protected $primaryKey = 'id';

    public function getAuthPassword()
    {
        return $this->motdepasse;
    }

    protected function casts(): array
    {
        return [
            'motdepasse' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function createur()
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    public function utilisateursCrees()
    {
        return $this->hasMany(self::class, 'created_by');
    }

    public function roleCode(): ?string
    {
        if (! $this->role) {
            return null;
        }

        $knownCodes = ['administrateur', 'ingenieur_terrain', 'chef_projet', 'partenaire', 'auditeur_itie'];
        if (in_array($this->role->code, $knownCodes, true)) {
            return $this->role->code;
        }

        $label = mb_strtolower(trim($this->role->libelle));
        $label = str_replace(['é', 'è', 'ê', 'ë'], 'e', $label);
        return match ($label) {
            'administrateur' => 'administrateur',
            'ingenieur terrain' => 'ingenieur_terrain',
            'chef de projet' => 'chef_projet',
            'partenaire' => 'partenaire',
            'auditeur itie' => 'auditeur_itie',
            default => null,
        };
    }

    public function hasRole(string $roleCode): bool
    {
        $code = $this->roleCode();
        return $code === $roleCode || $this->role?->libelle === $roleCode;
    }

    public function hasPermission(string|array $permission): bool
    {
        $role = $this->role;
        if (! $role) {
            return false;
        }

        $permissions = $role->permissions()->pluck('name')->toArray();

        if (is_array($permission)) {
            foreach ($permission as $p) {
                if (in_array($p, $permissions, true)) {
                    return true;
                }
            }
            return false;
        }

        return in_array($permission, $permissions, true);
    }
}

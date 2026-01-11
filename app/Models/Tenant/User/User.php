<?php

namespace App\Models\Tenant\User;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasUniqueUuid;
use App\Models\Tenant\Team\Team;
use Illuminate\Notifications\Notifiable;
use App\Models\Tenant\Team\Member\TeamMember;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasUniqueUuid;

    protected $guarded = ['uuid'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'image',
        'company_name',
        'is_mfa_enabled',
        'is_email_authentication_enabled',
        'mfa_secret_code',
        'mfa_authentication_image',
        'is_login_verified',
        'is_active',
        'force_password_change',
        'last_logged_in',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret_code',
        'mfa_authentication_image',
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
            'last_logged_in' => 'datetime',
            'is_mfa_enabled' => 'boolean',
            'is_email_authentication_enabled' => 'boolean',
            'is_login_verified' => 'boolean',
            'is_active' => 'boolean',
            'force_password_change' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function getFullNameAttribute(): ?string
    {
        return $this->display_name;
    }

    public function getDisplayNameAttribute(): ?string
    {
        $fullName = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));

        return $fullName ?: ($this->company_name ?: null);
    }

    /**
     * Relationships and helpers for team membership / roles
     */
    public function teamMemberships()
    {
        return $this->hasMany(TeamMember::class, 'user_id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members')
                    ->withPivot('role_id')->withTimestamps();
    }

    public function hasRoleInTeam($team, $roleNameOrId): bool
    {
        $teamId = is_object($team) ? ($team->id ?? null) : $team;
        if (empty($teamId)) {
            return false;
        }

        $member = $this->teamMemberships()->where('team_id', $teamId)->first();
        if (! $member) {
            return false;
        }

        if (is_numeric($roleNameOrId)) {
            return (int) $member->role_id === (int) $roleNameOrId;
        }

        return optional($member->role)->name === $roleNameOrId;
    }

    public function assignRoleToTeam($team, $role)
    {
        $teamId = is_object($team) ? ($team->id ?? null) : $team;
        $roleId = is_object($role) ? ($role->id ?? $role) : $role;

        if (empty($teamId) || empty($roleId)) {
            return null;
        }

        return TeamMember::updateOrCreate(
            ['team_id' => $teamId, 'user_id' => $this->id],
            ['role_id' => $roleId]
        );
    }
}

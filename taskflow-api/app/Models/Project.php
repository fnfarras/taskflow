<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'owner_id',
    ];

    // -------------------------------------------------------------------------
    // Relasi
    // -------------------------------------------------------------------------

    /**
     * Project dimiliki oleh satu User sebagai owner.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Project memiliki banyak anggota (User) melalui tabel pivot project_members.
     * Pivot menyertakan kolom 'role'.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Project memiliki banyak record ProjectMember (untuk akses langsung ke pivot).
     */
    public function projectMembers(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    /**
     * Project memiliki banyak Task.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}

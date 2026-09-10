<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'assignee_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status'   => 'string',
            'priority' => 'string',
            'due_date' => 'date',
        ];
    }

    // -------------------------------------------------------------------------
    // Relasi
    // -------------------------------------------------------------------------

    /**
     * Task dimiliki oleh satu Project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Task di-assign ke satu User (nullable).
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Task memiliki banyak TaskComment.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    /** @use HasFactory<\Database\Factories\PollFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'podium_size',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'podium_size' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    /** @return HasMany<PollItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PollItem::class);
    }

    /** @return HasMany<Vote, $this> */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /** Votações que aceitam votos: ativas e não expiradas. */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', 'active')->where('expires_at', '>', now());
    }

    /** Votações finalizadas: inativas ou expiradas. */
    public function scopeFinished(Builder $query): void
    {
        $query->where(function (Builder $q) {
            $q->where('status', 'inactive')->orWhere('expires_at', '<=', now());
        });
    }

    /** Se a votação aceita votos agora. */
    public function isOpen(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }

    /** Pontos que uma posição do pódio vale nesta votação. */
    public function pointsForPosition(int $position): int
    {
        return 2 * ($this->podium_size - $position) + 1;
    }
}

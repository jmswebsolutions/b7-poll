<?php

namespace App\Models;

use App\Enums\PollStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model representando uma votação.
 */
class Poll extends Model
{
    /** @use HasFactory<\Database\Factories\PollFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'podium_size',
        'expires_at',
    ];

    protected $attributes = [
        'podium_size' => 3,
        'status' => PollStatus::ACTIVE->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'podium_size' => 'integer',
            'expires_at' => 'datetime',
            'status' => PollStatus::class,
        ];
    }

    /**
     * Relacionamento com os itens da votação.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<PollItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PollItem::class);
    }

    /**
     * Relacionamento com os votos da votação.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Scope para votações que aceitam votos: ativas e não expiradas.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Poll>  $query
     * @return void
     */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', PollStatus::ACTIVE)->where('expires_at', '>', now());
    }

    /**
     * Scope para votações finalizadas: inativas ou expiradas.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Poll>  $query
     * @return void
     */
    public function scopeFinished(Builder $query): void
    {
        $query->where(function (Builder $q) {
            $q->where('status', PollStatus::INACTIVE)->orWhere('expires_at', '<=', now());
        });
    }

    /**
     * Verifica se a votação aceita votos agora.
     *
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->status === PollStatus::ACTIVE && $this->expires_at->isFuture();
    }

    /**
     * Calcula os pontos que uma posição do pódio vale nesta votação.
     *
     * @param  int  $position
     * @return int
     */
    public function pointsForPosition(int $position): int
    {
        $multiplier = 2;
        $base = 1;

        return $multiplier * ($this->podium_size - $position) + $base;
    }
}

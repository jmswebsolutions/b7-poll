<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PollItem extends Model
{
    /** @use HasFactory<\Database\Factories\PollItemFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'poll_id',
        'name',
        'description',
    ];

    /** @return BelongsTo<Poll, $this> */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    /** @return HasMany<VoteItem, $this> */
    public function voteItems(): HasMany
    {
        return $this->hasMany(VoteItem::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoteItem extends Model
{
    /** @use HasFactory<\Database\Factories\VoteItemFactory> */
    use HasFactory;

    protected $fillable = [
        'vote_id',
        'poll_item_id',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    /** @return BelongsTo<Vote, $this> */
    public function vote(): BelongsTo
    {
        return $this->belongsTo(Vote::class);
    }

    /** @return BelongsTo<PollItem, $this> */
    public function pollItem(): BelongsTo
    {
        return $this->belongsTo(PollItem::class);
    }
}

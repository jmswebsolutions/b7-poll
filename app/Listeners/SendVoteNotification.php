<?php

namespace App\Listeners;

use App\Events\VoteRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendVoteNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(VoteRegistered $event): void
    {
        Log::info('Voto registrado', [
            'user_id' => $event->user->id,
            'poll_id' => $event->poll->id,
            'vote_id' => $event->vote->id,
        ]);

        // Aqui você pode adicionar lógica de notificação:
        // - Email de confirmação
        // - Notificação no sistema
        // - Webhook para integrações
    }
}

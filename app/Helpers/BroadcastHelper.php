<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Throwable;

class BroadcastHelper
{
    /**
     * Safely broadcast an event. If broadcasting fails due to missing socket,
     * network error, or Pusher/Reverb server exception, catch it and log a warning
     * so that the primary database transaction is not aborted.
     *
     * @param mixed $event
     * @param bool $toOthers
     * @return void
     */
    public static function safeBroadcast($event, bool $toOthers = false): void
    {
        try {
            $pendingBroadcast = broadcast($event);
            if ($toOthers) {
                $pendingBroadcast->toOthers();
            }
        } catch (Throwable $e) {
            Log::warning('Broadcast failed safely: ' . $e->getMessage(), [
                'event' => get_class($event),
                'exception' => $e,
            ]);
        }
    }
}

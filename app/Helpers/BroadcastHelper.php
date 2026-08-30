<?php

namespace App\Helpers;

use Throwable;
use Illuminate\Support\Facades\Log;

class BroadcastHelper
{
    /**
     * Safely dispatch a broadcast event with toOthers() option.
     * Prevents PusherException (e.g., Invalid socket ID undefined) from crashing the request.
     *
     * @param mixed $event
     * @param bool $toOthers
     * @return void
     */
    public static function safeBroadcast($event, bool $toOthers = true): void
    {
        try {
            $pendingBroadcast = broadcast($event);
            if ($toOthers) {
                $pendingBroadcast->toOthers();
            }
        } catch (Throwable $e) {
            Log::warning('Broadcast failed safely: ' . $e->getMessage());
        }
    }
}

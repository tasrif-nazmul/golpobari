<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Hook for future notifications (email / SMS / WhatsApp). No listeners in the MVP.
 */
class OrderSubmitted
{
    use Dispatchable;

    public function __construct(public readonly \Illuminate\Database\Eloquent\Model $subject) {}
}

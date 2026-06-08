<?php

namespace Webrek\StateMachine\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * Fired before the new state is written to the model.
 */
class StateTransitioning
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public readonly Model $model,
        public readonly string $attribute,
        public readonly string $from,
        public readonly string $to,
        public readonly string $transition,
        public readonly array $context = [],
    ) {}
}

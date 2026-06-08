<?php

use Webrek\StateMachine\Models\StateTransition;

return [

    /*
    |--------------------------------------------------------------------------
    | Transition history
    |--------------------------------------------------------------------------
    |
    | When enabled, every applied transition is recorded as a row in the history
    | table — a built-in audit trail of how each model reached its current state.
    | Publish and run the migration first:
    |
    |     php artisan vendor:publish --tag=state-machine-migrations
    |
    */

    'history' => [
        'enabled' => env('STATE_MACHINE_HISTORY', false),
        'table' => 'state_transitions',
        'model' => StateTransition::class,
    ],

];

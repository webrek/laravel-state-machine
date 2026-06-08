<?php

namespace Webrek\StateMachine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $subject_type
 * @property int|string $subject_id
 * @property string $field
 * @property string|null $from_state
 * @property string $to_state
 * @property string $transition
 * @property array<string, mixed>|null $context
 */
class StateTransition extends Model
{
    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'context' => 'array',
    ];

    public function getTable(): string
    {
        return config('state-machine.history.table', 'state_transitions');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}

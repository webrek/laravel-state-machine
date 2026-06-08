<?php

namespace Webrek\StateMachine\Concerns;

use Webrek\StateMachine\Exceptions\UnknownStateMachineException;
use Webrek\StateMachine\StateMachine;
use Webrek\StateMachine\StateMachineHandler;

/**
 * Add to an Eloquent model to bind one or more attributes to state machine
 * definitions. Implement {@see stateMachines()} to map attribute => definition.
 */
trait HasStateMachines
{
    /**
     * Seed the initial state of each machine when the model is created.
     */
    public static function bootHasStateMachines(): void
    {
        static::creating(function ($model): void {
            foreach ($model->stateMachines() as $attribute => $definition) {
                if (blank($model->getAttribute($attribute))) {
                    $model->setAttribute($attribute, $model->resolveStateMachine($definition)->initialState());
                }
            }
        });
    }

    /**
     * Map of attribute name => state machine definition class.
     *
     * @return array<string, class-string<StateMachine>>
     */
    public function stateMachines(): array
    {
        return [];
    }

    public function stateMachine(?string $attribute = null): StateMachineHandler
    {
        $machines = $this->stateMachines();
        $attribute ??= array_key_first($machines);

        if ($attribute === null || ! isset($machines[$attribute])) {
            throw UnknownStateMachineException::for((string) $attribute, static::class);
        }

        return new StateMachineHandler($this, $attribute, $this->resolveStateMachine($machines[$attribute]));
    }

    /**
     * @param  class-string<StateMachine>  $definition
     */
    protected function resolveStateMachine(string $definition): StateMachine
    {
        return new $definition;
    }
}

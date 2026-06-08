<?php

namespace Webrek\StateMachine;

use Webrek\StateMachine\Exceptions\InvalidStateException;

/**
 * Base class for a state machine definition. Extend it and declare the states,
 * the transitions between them, and the initial state.
 */
abstract class StateMachine
{
    /**
     * The complete list of valid states.
     *
     * @return list<string>
     */
    abstract public function states(): array;

    /**
     * The transitions, keyed by the name used to apply them.
     *
     * @return array<string, Transition>
     */
    abstract public function transitions(): array;

    /**
     * The state a model starts in.
     */
    abstract public function initialState(): string;

    /**
     * Ensure every transition and the initial state reference declared states.
     *
     * @param  array<string, Transition>  $transitions
     */
    public function validate(array $transitions): void
    {
        $states = $this->states();

        if (! in_array($this->initialState(), $states, true)) {
            throw InvalidStateException::initial($this->initialState(), static::class);
        }

        foreach ($transitions as $name => $transition) {
            foreach ($transition->sources() as $source) {
                if (! in_array($source, $states, true)) {
                    throw InvalidStateException::unknown($source, $name, static::class);
                }
            }

            if (! in_array($transition->target(), $states, true)) {
                throw InvalidStateException::unknown($transition->target(), $name, static::class);
            }
        }
    }
}

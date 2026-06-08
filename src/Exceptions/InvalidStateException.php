<?php

namespace Webrek\StateMachine\Exceptions;

use LogicException;

class InvalidStateException extends LogicException
{
    public static function initial(string $state, string $definition): self
    {
        return new self(sprintf('Initial state "%s" is not declared in %s::states().', $state, $definition));
    }

    public static function unknown(string $state, string $transition, string $definition): self
    {
        return new self(sprintf(
            'Transition "%s" references state "%s", which is not declared in %s::states().',
            $transition,
            $state,
            $definition,
        ));
    }
}

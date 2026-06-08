<?php

namespace Webrek\StateMachine\Exceptions;

use InvalidArgumentException;

class UnknownTransitionException extends InvalidArgumentException
{
    public static function for(string $transition, string $definition): self
    {
        return new self(sprintf('Transition "%s" is not defined in %s.', $transition, $definition));
    }
}

<?php

namespace Webrek\StateMachine\Exceptions;

use InvalidArgumentException;

class UnknownStateMachineException extends InvalidArgumentException
{
    public static function for(string $attribute, string $model): self
    {
        return new self(sprintf('No state machine is registered for "%s" on %s.', $attribute, $model));
    }
}

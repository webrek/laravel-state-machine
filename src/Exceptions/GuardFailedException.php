<?php

namespace Webrek\StateMachine\Exceptions;

use RuntimeException;

class GuardFailedException extends RuntimeException
{
    public static function for(string $transition): self
    {
        return new self(sprintf('The guard for transition "%s" did not pass.', $transition));
    }
}

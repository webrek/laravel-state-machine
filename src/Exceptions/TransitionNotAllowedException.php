<?php

namespace Webrek\StateMachine\Exceptions;

use RuntimeException;

class TransitionNotAllowedException extends RuntimeException
{
    public static function from(string $transition, string $state): self
    {
        return new self(sprintf('Transition "%s" cannot be applied from state "%s".', $transition, $state));
    }
}

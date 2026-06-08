<?php

namespace Webrek\StateMachine\Console;

use Illuminate\Console\Command;
use Webrek\StateMachine\StateMachine;

class DiagramCommand extends Command
{
    protected $signature = 'state-machine:diagram {definition : The fully-qualified StateMachine class}';

    protected $description = 'Print a Mermaid state diagram for a state machine definition';

    public function handle(): int
    {
        /** @var class-string $class */
        $class = $this->argument('definition');

        if (! class_exists($class) || ! is_subclass_of($class, StateMachine::class)) {
            $this->error("[{$class}] is not a " . StateMachine::class . '.');

            return self::FAILURE;
        }

        $this->line((new $class)->toMermaid());

        return self::SUCCESS;
    }
}

<?php

namespace Webrek\StateMachine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webrek\StateMachine\Exceptions\InvalidStateException;
use Webrek\StateMachine\StateMachine;
use Webrek\StateMachine\Tests\Support\OrderState;
use Webrek\StateMachine\Transition;

class StateMachineDefinitionTest extends TestCase
{
    public function test_a_valid_definition_passes_validation(): void
    {
        $definition = new OrderState;

        $definition->validate($definition->transitions());

        $this->assertSame('pending', $definition->initialState());
        $this->assertArrayHasKey('pay', $definition->transitions());
    }

    public function test_it_rejects_a_transition_to_an_unknown_state(): void
    {
        $definition = new class extends StateMachine
        {
            public function states(): array
            {
                return ['draft', 'published'];
            }

            public function transitions(): array
            {
                return ['publish' => Transition::from('draft')->to('archived')];
            }

            public function initialState(): string
            {
                return 'draft';
            }
        };

        $this->expectException(InvalidStateException::class);

        $definition->validate($definition->transitions());
    }

    public function test_it_rejects_an_unknown_initial_state(): void
    {
        $definition = new class extends StateMachine
        {
            public function states(): array
            {
                return ['draft'];
            }

            public function transitions(): array
            {
                return [];
            }

            public function initialState(): string
            {
                return 'somewhere';
            }
        };

        $this->expectException(InvalidStateException::class);

        $definition->validate($definition->transitions());
    }
}

<?php

namespace Webrek\StateMachine\Tests\Feature;

use stdClass;
use Webrek\StateMachine\Exceptions\UnknownStateMachineException;
use Webrek\StateMachine\Tests\Support\Order;
use Webrek\StateMachine\Tests\TestCase;

class MutationCoverageTest extends TestCase
{
    public function test_requesting_an_unknown_state_machine_throws(): void
    {
        $order = Order::create();

        $this->expectException(UnknownStateMachineException::class);

        $order->stateMachine('does-not-exist');
    }

    public function test_diagram_command_rejects_a_class_that_is_not_a_state_machine(): void
    {
        $this->artisan('state-machine:diagram', ['definition' => stdClass::class])
            ->expectsOutputToContain('stdClass')
            ->assertFailed();
    }
}

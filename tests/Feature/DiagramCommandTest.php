<?php

namespace Webrek\StateMachine\Tests\Feature;

use Webrek\StateMachine\Tests\Support\Order;
use Webrek\StateMachine\Tests\Support\OrderState;
use Webrek\StateMachine\Tests\TestCase;

class DiagramCommandTest extends TestCase
{
    public function test_it_prints_a_diagram_for_a_definition(): void
    {
        $this->artisan('state-machine:diagram', ['definition' => OrderState::class])
            ->expectsOutputToContain('stateDiagram-v2')
            ->assertSuccessful();
    }

    public function test_it_fails_for_a_non_definition(): void
    {
        $this->artisan('state-machine:diagram', ['definition' => 'Not\\A\\Class'])
            ->assertFailed();
    }

    public function test_the_handler_exposes_the_diagram(): void
    {
        $order = Order::create();

        $this->assertStringContainsString('stateDiagram-v2', $order->stateMachine()->toMermaid());
    }
}

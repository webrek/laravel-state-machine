<?php

namespace Webrek\StateMachine\Tests\Feature;

use Webrek\StateMachine\Exceptions\GuardFailedException;
use Webrek\StateMachine\Exceptions\TransitionNotAllowedException;
use Webrek\StateMachine\Exceptions\UnknownTransitionException;
use Webrek\StateMachine\Tests\Support\Order;
use Webrek\StateMachine\Tests\TestCase;

class StateMachineHandlerTest extends TestCase
{
    public function test_it_seeds_the_initial_state_on_create(): void
    {
        $order = Order::create();

        $this->assertSame('pending', $order->status);
        $this->assertSame('pending', $order->stateMachine()->state());
        $this->assertTrue($order->stateMachine()->is('pending'));
    }

    public function test_it_does_not_override_an_explicit_state(): void
    {
        $order = Order::create(['status' => 'paid']);

        $this->assertSame('paid', $order->stateMachine()->state());
    }

    public function test_it_reports_allowed_transitions(): void
    {
        $order = Order::create();

        $this->assertSame(['pay', 'cancel'], $order->stateMachine()->allowed());
        $this->assertTrue($order->stateMachine()->can('pay'));
        $this->assertFalse($order->stateMachine()->can('ship'));
        $this->assertTrue($order->stateMachine()->canTransitionTo('paid'));
        $this->assertFalse($order->stateMachine()->canTransitionTo('delivered'));
    }

    public function test_it_applies_a_transition(): void
    {
        $order = Order::create();

        $returned = $order->stateMachine()->apply('pay');

        $this->assertSame('paid', $order->status);
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame($order, $returned);
    }

    public function test_a_guard_blocks_a_transition(): void
    {
        $order = Order::create(['status' => 'paid']);

        $this->assertFalse($order->stateMachine()->can('ship'));

        $order->address = '123 Main St';

        $this->assertTrue($order->stateMachine()->can('ship'));
        $order->stateMachine()->apply('ship');
        $this->assertSame('shipped', $order->status);
    }

    public function test_applying_a_blocked_guard_throws(): void
    {
        $order = Order::create(['status' => 'paid']);

        $this->expectException(GuardFailedException::class);

        $order->stateMachine()->apply('ship');
    }

    public function test_it_rejects_a_transition_from_the_wrong_state(): void
    {
        $order = Order::create();

        $this->expectException(TransitionNotAllowedException::class);

        $order->stateMachine()->apply('deliver');
    }

    public function test_it_rejects_an_unknown_transition(): void
    {
        $order = Order::create();

        $this->expectException(UnknownTransitionException::class);

        $order->stateMachine()->apply('teleport');
    }

    public function test_it_supports_multiple_source_states(): void
    {
        $fromPending = Order::create();
        $fromPaid = Order::create(['status' => 'paid']);

        $fromPending->stateMachine()->apply('cancel');
        $fromPaid->stateMachine()->apply('cancel');

        $this->assertSame('cancelled', $fromPending->status);
        $this->assertSame('cancelled', $fromPaid->status);
    }
}

<?php

namespace Webrek\StateMachine\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Webrek\StateMachine\Events\StateTransitioned;
use Webrek\StateMachine\Events\StateTransitioning;
use Webrek\StateMachine\Tests\Support\Order;
use Webrek\StateMachine\Tests\TestCase;

class EventsTest extends TestCase
{
    public function test_it_fires_transition_events(): void
    {
        $order = Order::create();

        Event::fake([StateTransitioning::class, StateTransitioned::class]);

        $order->stateMachine()->apply('pay', ['actor' => 'tester']);

        Event::assertDispatched(StateTransitioning::class, fn (StateTransitioning $e): bool => $e->from === 'pending'
            && $e->to === 'paid'
            && $e->transition === 'pay'
            && $e->attribute === 'status');

        Event::assertDispatched(StateTransitioned::class, fn (StateTransitioned $e): bool => $e->from === 'pending'
            && $e->to === 'paid'
            && $e->context === ['actor' => 'tester']);
    }
}

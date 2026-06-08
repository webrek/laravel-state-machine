<?php

namespace Webrek\StateMachine\Tests\Feature;

use Webrek\StateMachine\Tests\Support\Order;
use Webrek\StateMachine\Tests\TestCase;

class HistoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createStateTransitionsTable();
    }

    public function test_it_records_history_when_enabled(): void
    {
        config(['state-machine.history.enabled' => true]);

        $order = Order::create();
        $order->stateMachine()->apply('pay');
        $order->address = '123 Main St';
        $order->stateMachine()->apply('ship', ['carrier' => 'DHL']);

        $history = $order->stateMachine()->history();

        $this->assertCount(2, $history);
        $this->assertSame('pending', $history[0]->from_state);
        $this->assertSame('paid', $history[0]->to_state);
        $this->assertSame('pay', $history[0]->transition);
        $this->assertSame('shipped', $history[1]->to_state);
        $this->assertSame(['carrier' => 'DHL'], $history[1]->context);
    }

    public function test_it_records_nothing_when_disabled(): void
    {
        config(['state-machine.history.enabled' => false]);

        $order = Order::create();
        $order->stateMachine()->apply('pay');

        $this->assertCount(0, $order->stateMachine()->history());
    }
}

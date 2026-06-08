<?php

namespace Webrek\StateMachine\Tests\Feature;

use Closure;
use RuntimeException;
use Webrek\StateMachine\StateMachine;
use Webrek\StateMachine\StateMachineHandler;
use Webrek\StateMachine\Tests\Support\Order;
use Webrek\StateMachine\Tests\TestCase;
use Webrek\StateMachine\Transition;

class EffectTest extends TestCase
{
    private function handlerFor(Order $order, Closure $effect): StateMachineHandler
    {
        $definition = new class($effect) extends StateMachine
        {
            public function __construct(private readonly Closure $effect) {}

            public function states(): array
            {
                return ['pending', 'done'];
            }

            public function transitions(): array
            {
                return ['finish' => Transition::from('pending')->to('done')->using($this->effect)];
            }

            public function initialState(): string
            {
                return 'pending';
            }
        };

        return new StateMachineHandler($order, 'status', $definition);
    }

    public function test_the_effect_runs_inside_the_transition(): void
    {
        $order = Order::create();

        $this->handlerFor($order, function (Order $model): void {
            $model->address = 'set-by-effect';
            $model->save();
        })->apply('finish');

        $this->assertSame('done', $order->status);
        $this->assertSame('set-by-effect', Order::findOrFail($order->id)->address);
    }

    public function test_a_failing_effect_rolls_back_the_whole_transition(): void
    {
        $order = Order::create();

        $handler = $this->handlerFor($order, function (Order $model): void {
            $model->address = 'partial-write';
            $model->save();

            throw new RuntimeException('boom');
        });

        try {
            $handler->apply('finish');
            $this->fail('Expected the effect to throw.');
        } catch (RuntimeException $e) {
            $this->assertSame('boom', $e->getMessage());
        }

        $fresh = Order::findOrFail($order->id);

        $this->assertSame('pending', $fresh->status, 'The state change must roll back.');
        $this->assertNull($fresh->address, "The effect's writes must roll back.");
        $this->assertSame('pending', $order->status, 'The in-memory state must be reverted.');
    }
}

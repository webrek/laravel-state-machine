<?php

namespace Webrek\StateMachine\Tests\Support;

use Webrek\StateMachine\StateMachine;
use Webrek\StateMachine\Transition;

class OrderState extends StateMachine
{
    public function states(): array
    {
        return ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
    }

    public function transitions(): array
    {
        return [
            'pay' => Transition::from('pending')->to('paid'),
            'ship' => Transition::from('paid')->to('shipped')
                ->guard(fn (Order $order): bool => filled($order->address)),
            'deliver' => Transition::from('shipped')->to('delivered'),
            'cancel' => Transition::from(['pending', 'paid'])->to('cancelled'),
        ];
    }

    public function initialState(): string
    {
        return 'pending';
    }
}

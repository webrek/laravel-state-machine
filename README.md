# Laravel State Machine

[![Latest Version on Packagist](https://img.shields.io/packagist/v/webrek/laravel-state-machine.svg?style=flat-square)](https://packagist.org/packages/webrek/laravel-state-machine)
[![Total Downloads](https://img.shields.io/packagist/dt/webrek/laravel-state-machine.svg?style=flat-square)](https://packagist.org/packages/webrek/laravel-state-machine)
[![Tests](https://img.shields.io/github/actions/workflow/status/webrek/laravel-state-machine/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/webrek/laravel-state-machine/actions/workflows/tests.yml)
[![PHP Version](https://img.shields.io/packagist/php-v/webrek/laravel-state-machine.svg?style=flat-square)](https://php.net)
[![License](https://img.shields.io/packagist/l/webrek/laravel-state-machine.svg?style=flat-square)](LICENSE)

Declarative state machines for Eloquent models. Define the states a model can be
in and the transitions between them, then let the package enforce that only valid
transitions happen — with guards, events and an optional audit trail.

## Quickstart

```bash
composer require webrek/laravel-state-machine
```

Define a machine:

```php
use Webrek\StateMachine\StateMachine;
use Webrek\StateMachine\Transition;

class OrderStatus extends StateMachine
{
    public function states(): array
    {
        return ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
    }

    public function transitions(): array
    {
        return [
            'pay'     => Transition::from('pending')->to('paid'),
            'ship'    => Transition::from('paid')->to('shipped')
                            ->guard(fn ($order) => filled($order->address)),
            'deliver' => Transition::from('shipped')->to('delivered'),
            'cancel'  => Transition::from(['pending', 'paid'])->to('cancelled'),
        ];
    }

    public function initialState(): string
    {
        return 'pending';
    }
}
```

Bind it to a model attribute:

```php
use Illuminate\Database\Eloquent\Model;
use Webrek\StateMachine\Concerns\HasStateMachines;

class Order extends Model
{
    use HasStateMachines;

    public function stateMachines(): array
    {
        return ['status' => OrderStatus::class];
    }
}
```

Use it:

```php
$order = Order::create();          // status seeded to "pending"

$order->stateMachine()->can('pay');        // true
$order->stateMachine()->allowed();         // ['pay', 'cancel']
$order->stateMachine()->apply('pay');      // status is now "paid", persisted

$order->stateMachine()->apply('deliver');  // throws TransitionNotAllowedException
```

## Why a state machine instead of `if` statements

The status of an order, a subscription, a support ticket or a KYC application is
rarely a free-form string — it's a set of named states with strict rules about
which one can follow which. Encoding those rules as scattered
`if ($order->status === 'paid')` checks means the rules live in a dozen places
and nothing stops an invalid jump like `pending → delivered`.

A state machine puts the rules in one declaration and enforces them:

- **Invalid transitions can't happen.** Applying a transition from the wrong
  state throws instead of silently corrupting your data.
- **Guards gate transitions on business rules.** "You can't ship without an
  address" becomes a guard, not a code review comment.
- **Every change emits an event.** Hook side effects (send the receipt, notify
  the warehouse) onto `StateTransitioned` instead of hunting for every setter.
- **You get a free audit trail.** Optional history records who moved what, from
  where, to where, and when.

## The handler API

`$model->stateMachine($attribute)` returns a handler. With a single machine the
attribute is optional.

```php
$sm = $order->stateMachine('status');

$sm->state();                  // 'paid'
$sm->is('paid');               // true
$sm->can('ship');              // bool — allowed from here AND guard passes
$sm->allowed();                // ['ship', ... ] transition names available now
$sm->canTransitionTo('shipped'); // bool
$sm->apply('ship', ['carrier' => 'DHL']); // returns the model
$sm->history();                // Collection of recorded transitions
```

The context array passed to `apply()` reaches guards and the dispatched events,
and is stored with the history row.

## Guards

A guard is a closure receiving the model and the context array. The transition is
only allowed when it returns `true`.

```php
'refund' => Transition::from('paid')->to('refunded')
    ->guard(fn ($order, array $context) => $context['approved_by'] ?? false),
```

`can()` returns `false` when a guard blocks; `apply()` throws
`GuardFailedException`.

## Events

Two events fire around every transition:

- `Webrek\StateMachine\Events\StateTransitioning` — before the new state is saved.
- `Webrek\StateMachine\Events\StateTransitioned` — after it is saved.

Both carry the model, attribute, `from`, `to`, transition name and context.

```php
Event::listen(StateTransitioned::class, function ($event) {
    if ($event->transition === 'ship') {
        Notification::send($event->model->customer, new OrderShipped($event->model));
    }
});
```

## Transition history

History is opt-in. Publish and run the migration, then enable it:

```bash
php artisan vendor:publish --tag=state-machine-migrations
php artisan migrate
```

```env
STATE_MACHINE_HISTORY=true
```

Every applied transition is then recorded, and `->history()` returns the trail,
oldest first:

```php
$order->stateMachine()->history()->each(function ($row) {
    echo "{$row->from_state} → {$row->to_state} via {$row->transition}";
});
```

Each row stores the subject (morph), the field, `from_state`, `to_state`, the
transition name, the JSON context and timestamps.

## Multiple machines per model

A model can drive several attributes at once:

```php
public function stateMachines(): array
{
    return [
        'status'         => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
    ];
}

$order->stateMachine('payment_status')->apply('authorize');
```

## Requirements

| Component | Version |
| --------- | ------- |
| PHP | 8.2+ |
| Laravel | 12.x |

## Testing

```bash
composer install
composer test
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## Security

Please review the [security policy](SECURITY.md) before reporting a
vulnerability.

## License

The MIT License (MIT). See [LICENSE](LICENSE).

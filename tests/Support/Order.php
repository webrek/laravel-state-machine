<?php

namespace Webrek\StateMachine\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Webrek\StateMachine\Concerns\HasStateMachines;

/**
 * @property string $status
 * @property string|null $address
 */
class Order extends Model
{
    use HasStateMachines;

    protected $guarded = [];

    public $timestamps = false;

    public function stateMachines(): array
    {
        return ['status' => OrderState::class];
    }
}

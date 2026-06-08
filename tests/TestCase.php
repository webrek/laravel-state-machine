<?php

namespace Webrek\StateMachine\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Webrek\StateMachine\StateMachineServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('status')->nullable();
            $table->string('address')->nullable();
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            StateMachineServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function createStateTransitionsTable(): void
    {
        Schema::create('state_transitions', function (Blueprint $table): void {
            $table->id();
            $table->morphs('subject');
            $table->string('field');
            $table->string('from_state')->nullable();
            $table->string('to_state');
            $table->string('transition');
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }
}

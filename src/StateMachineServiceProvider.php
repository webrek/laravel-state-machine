<?php

namespace Webrek\StateMachine;

use Illuminate\Support\ServiceProvider;
use Webrek\StateMachine\Console\DiagramCommand;

class StateMachineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/state-machine.php', 'state-machine');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/state-machine.php' => $this->app->configPath('state-machine.php'),
            ], 'state-machine-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/create_state_transitions_table.php.stub' => $this->migrationPath(),
            ], 'state-machine-migrations');

            $this->commands([DiagramCommand::class]);
        }
    }

    private function migrationPath(): string
    {
        return $this->app->databasePath('migrations/' . date('Y_m_d_His') . '_create_state_transitions_table.php');
    }
}

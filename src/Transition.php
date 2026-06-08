<?php

namespace Webrek\StateMachine;

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * A single declarative transition: which states it may start from, the state it
 * moves to, and an optional guard that must pass for it to be allowed.
 */
final class Transition
{
    /** @var list<string> */
    private array $sources;

    private string $target;

    private ?Closure $guard = null;

    private ?Closure $effect = null;

    /**
     * @param  string|array<string>  $from
     */
    private function __construct(string|array $from)
    {
        $this->sources = array_values((array) $from);
    }

    /**
     * @param  string|array<string>  $from
     */
    public static function from(string|array $from): self
    {
        return new self($from);
    }

    public function to(string $state): self
    {
        $this->target = $state;

        return $this;
    }

    /**
     * Restrict the transition to when the guard returns true. The guard receives
     * the model and the context array passed to apply().
     *
     * @param  Closure(Model, array<string, mixed>): bool  $guard
     */
    public function guard(Closure $guard): self
    {
        $this->guard = $guard;

        return $this;
    }

    /**
     * A side effect run as part of the transition, inside the same database
     * transaction as the state change — so if it throws, the whole transition
     * (state, history and the effect's own writes) rolls back. The closure
     * receives the model and the context array passed to apply().
     *
     * @param  Closure(Model, array<string, mixed>): void  $effect
     */
    public function using(Closure $effect): self
    {
        $this->effect = $effect;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function sources(): array
    {
        return $this->sources;
    }

    public function target(): string
    {
        return $this->target;
    }

    public function allowsFrom(string $state): bool
    {
        return in_array($state, $this->sources, true);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function guardPasses(Model $model, array $context): bool
    {
        return $this->guard === null || ($this->guard)($model, $context) === true;
    }

    public function hasGuard(): bool
    {
        return $this->guard !== null;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function runEffect(Model $model, array $context): void
    {
        if ($this->effect !== null) {
            ($this->effect)($model, $context);
        }
    }

    public function hasEffect(): bool
    {
        return $this->effect !== null;
    }
}

<?php

namespace Webrek\StateMachine;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Throwable;
use Webrek\StateMachine\Events\StateTransitioned;
use Webrek\StateMachine\Events\StateTransitioning;
use Webrek\StateMachine\Exceptions\GuardFailedException;
use Webrek\StateMachine\Exceptions\TransitionNotAllowedException;
use Webrek\StateMachine\Exceptions\UnknownTransitionException;
use Webrek\StateMachine\Models\StateTransition;

/**
 * The runtime bound to a specific model attribute. Returned by
 * {@see Concerns\HasStateMachines::stateMachine()}.
 */
class StateMachineHandler
{
    /** @var array<string, Transition> */
    private array $transitions;

    public function __construct(
        private readonly Model $model,
        private readonly string $attribute,
        private readonly StateMachine $definition,
    ) {
        $this->transitions = $definition->transitions();
        $definition->validate($this->transitions);
    }

    public function state(): string
    {
        return (string) $this->model->getAttribute($this->attribute);
    }

    public function is(string $state): bool
    {
        return $this->state() === $state;
    }

    /**
     * @return array<string, Transition>
     */
    public function transitions(): array
    {
        return $this->transitions;
    }

    public function can(string $transition): bool
    {
        $definition = $this->transitions[$transition] ?? null;

        return $definition !== null
            && $definition->allowsFrom($this->state())
            && $definition->guardPasses($this->model, []);
    }

    /**
     * The names of every transition that can be applied right now.
     *
     * @return list<string>
     */
    public function allowed(): array
    {
        return array_values(array_filter(
            array_keys($this->transitions),
            fn (string $name): bool => $this->can($name),
        ));
    }

    /**
     * Render this machine's definition as a Mermaid state diagram.
     */
    public function toMermaid(): string
    {
        return $this->definition->toMermaid();
    }

    public function canTransitionTo(string $state): bool
    {
        foreach ($this->allowed() as $name) {
            if ($this->transitions[$name]->target() === $state) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply a transition: validate it, fire events, persist the new state and
     * record history.
     *
     * @param  array<string, mixed>  $context
     */
    public function apply(string $transition, array $context = []): Model
    {
        $definition = $this->transitions[$transition]
            ?? throw UnknownTransitionException::for($transition, $this->definition::class);

        $from = $this->state();

        if (! $definition->allowsFrom($from)) {
            throw TransitionNotAllowedException::from($transition, $from);
        }

        if (! $definition->guardPasses($this->model, $context)) {
            throw GuardFailedException::for($transition);
        }

        $to = $definition->target();

        event(new StateTransitioning($this->model, $this->attribute, $from, $to, $transition, $context));

        try {
            $this->model->getConnection()->transaction(function () use ($definition, $from, $to, $transition, $context): void {
                $this->model->setAttribute($this->attribute, $to);
                $this->model->save();

                $definition->runEffect($this->model, $context);

                $this->record($from, $to, $transition, $context);
            });
        } catch (Throwable $e) {
            // Roll the in-memory state back to match the rolled-back database.
            $this->model->setAttribute($this->attribute, $from);

            throw $e;
        }

        event(new StateTransitioned($this->model, $this->attribute, $from, $to, $transition, $context));

        return $this->model;
    }

    /**
     * The recorded transition history for this attribute, oldest first.
     *
     * @return Collection<int, StateTransition>
     */
    public function history(): Collection
    {
        /** @var class-string<StateTransition> $model */
        $model = config('state-machine.history.model', StateTransition::class);

        return $model::query()
            ->where('subject_type', $this->model->getMorphClass())
            ->where('subject_id', $this->model->getKey())
            ->where('field', $this->attribute)
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function record(string $from, string $to, string $transition, array $context): void
    {
        if (! config('state-machine.history.enabled', false)) {
            return;
        }

        /** @var class-string<StateTransition> $model */
        $model = config('state-machine.history.model', StateTransition::class);

        $model::query()->create([
            'subject_type' => $this->model->getMorphClass(),
            'subject_id' => $this->model->getKey(),
            'field' => $this->attribute,
            'from_state' => $from,
            'to_state' => $to,
            'transition' => $transition,
            'context' => $context === [] ? null : $context,
        ]);
    }
}

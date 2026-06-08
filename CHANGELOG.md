# Changelog

All notable changes to `webrek/laravel-state-machine` are documented here. The
format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and the
project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-06-07

### Added

- Transition effects via `Transition::using($closure)` that run inside the same
  database transaction as the state change and history record — if the effect
  throws, the whole transition rolls back (and the in-memory state is reverted).
- `StateMachine::toMermaid()` / `StateMachineHandler::toMermaid()` to render the
  machine as a Mermaid `stateDiagram-v2`.
- `state-machine:diagram` artisan command to print a definition's diagram.

### Changed

- `apply()` now wraps the state change, effect and history in a single database
  transaction.

## [1.0.0] - 2026-06-07

### Added

- Declarative `StateMachine` definitions: states, transitions and an initial
  state, validated on use.
- `Transition` builder with multiple source states, a target, and an optional
  guard closure.
- `HasStateMachines` trait that seeds the initial state on create and exposes a
  `stateMachine()` handler with `state()`, `is()`, `can()`, `allowed()`,
  `canTransitionTo()` and `apply()`.
- `StateTransitioning` and `StateTransitioned` events.
- Optional transition history with a publishable migration and `history()` query.
- Dedicated exceptions for unknown transitions, disallowed transitions, failed
  guards and invalid definitions.

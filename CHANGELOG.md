# Changelog

All notable changes to `webrek/laravel-state-machine` are documented here. The
format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and the
project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

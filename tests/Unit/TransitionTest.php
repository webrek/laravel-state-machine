<?php

namespace Webrek\StateMachine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webrek\StateMachine\Transition;

class TransitionTest extends TestCase
{
    public function test_sources_and_target(): void
    {
        $transition = Transition::from(['a', 'b'])->to('c');

        $this->assertSame(['a', 'b'], $transition->sources());
        $this->assertSame('c', $transition->target());
        $this->assertTrue($transition->allowsFrom('a'));
        $this->assertFalse($transition->allowsFrom('z'));
    }

    public function test_guard_flag(): void
    {
        $this->assertFalse(Transition::from('a')->to('b')->hasGuard());
        $this->assertTrue(Transition::from('a')->to('b')->guard(fn (): bool => true)->hasGuard());
    }

    public function test_effect_flag(): void
    {
        $this->assertFalse(Transition::from('a')->to('b')->hasEffect());
        $this->assertTrue(Transition::from('a')->to('b')->using(fn (): null => null)->hasEffect());
    }
}

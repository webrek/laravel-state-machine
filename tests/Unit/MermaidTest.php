<?php

namespace Webrek\StateMachine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webrek\StateMachine\Tests\Support\OrderState;

class MermaidTest extends TestCase
{
    public function test_it_renders_a_state_diagram(): void
    {
        $mermaid = (new OrderState)->toMermaid();

        $this->assertStringContainsString('stateDiagram-v2', $mermaid);
        $this->assertStringContainsString('[*] --> pending', $mermaid);
        $this->assertStringContainsString('pending --> paid: pay', $mermaid);
        $this->assertStringContainsString('paid --> shipped: ship', $mermaid);
        $this->assertStringContainsString('pending --> cancelled: cancel', $mermaid);
        $this->assertStringContainsString('paid --> cancelled: cancel', $mermaid);
    }
}

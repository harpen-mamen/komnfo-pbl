<?php

namespace Tests\Unit;

use App\Filament\Pages\AIAssistant;
use PHPUnit\Framework\TestCase;

class AIAssistantPageTest extends TestCase
{
    public function test_page_uses_the_expected_navigation_metadata(): void
    {
        $reflection = new \ReflectionClass(AIAssistant::class);

        $this->assertSame('AI Assistant', $reflection->getStaticPropertyValue('navigationLabel'));
        $this->assertSame('heroicon-o-sparkles', $reflection->getStaticPropertyValue('navigationIcon'));
        $this->assertSame('AI Assistant', $reflection->getStaticPropertyValue('title'));
    }
}

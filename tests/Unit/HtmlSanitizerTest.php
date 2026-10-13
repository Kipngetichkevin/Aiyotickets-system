<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public static function attacks(): array
    {
        return [
            'img onerror'        => ['<img src=x onerror=alert(1)>'],
            'script'             => ['<script>alert(1)</script>hi'],
            'javascript href'    => ['<a href="javascript:alert(1)">x</a>'],
            'obfuscated scheme'  => ['<a href="java&#x09;script:alert(1)">x</a>'],
            'svg onload'         => ['<svg onload=alert(1)><circle/></svg>'],
            'iframe'             => ['<iframe src="https://evil.example"></iframe>'],
            'style and onclick'  => ['<p style="background:url(javascript:alert(1))" onclick="x()">t</p>'],
            'data html image'    => ['<img src="data:text/html;base64,PHNjcmlwdD4=">'],
            'protocol-relative'  => ['<a href="//evil.example">x</a>'],
            'noscript breakout'  => ['<noscript><p title="</noscript><img src=x onerror=alert(1)>"></noscript>'],
        ];
    }

    #[DataProvider('attacks')]
    public function test_removes_script_vectors(string $html): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/<script|onerror|onload|onclick|javascript:|<iframe|<svg|data:text|style=|\/\/evil/i',
            HtmlSanitizer::clean($html)
        );
    }

    public function test_keeps_quill_formatting(): void
    {
        $html = '<h2>Lineup</h2><p class="ql-align-center"><strong>B</strong> <em>i</em></p>'
            . '<ol><li data-list="bullet">One</li></ol><p><a href="https://aiyotickets.com" target="_blank">site</a></p>';

        $clean = HtmlSanitizer::clean($html);

        $this->assertStringContainsString('<h2>Lineup</h2>', $clean);
        $this->assertStringContainsString('class="ql-align-center"', $clean);
        $this->assertStringContainsString('<li data-list="bullet">One</li>', $clean);
        $this->assertStringContainsString('href="https://aiyotickets.com"', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer nofollow"', $clean);
    }
}

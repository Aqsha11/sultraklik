<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public static function dangerousPayloads(): array
    {
        return [
            'script tag' => ['<script>alert(1)</script><p>aman</p>', ['<script', 'alert(1)']],
            'inline event handler' => ['<p onclick="steal()">x</p>', ['onclick', 'steal()']],
            'image error handler' => ['<img src="/a.jpg" onerror="alert(1)">', ['onerror']],
            'javascript url' => ['<a href="javascript:alert(1)">x</a>', ['javascript:']],
            'encoded javascript url' => ['<a href="&#106;avascript:alert(1)">x</a>', ['avascript:alert']],
            'iframe' => ['<iframe src="https://evil.test"></iframe><p>teks</p>', ['<iframe', 'evil.test']],
            'svg payload' => ['<svg onload="alert(1)"></svg>', ['<svg', 'onload']],
            'inline style' => ['<p style="position:fixed;top:0">x</p>', ['style=']],
            'form' => ['<form action="/x"><input name="a"></form>', ['<form', '<input']],
            'object embed' => ['<object data="x.swf"></object><embed src="y.swf">', ['<object', '<embed']],
            'meta refresh' => ['<meta http-equiv="refresh" content="0;url=https://evil.test">', ['<meta', 'evil.test']],
            'css expression' => ['<div style="width:expression(alert(1))">x</div>', ['style=']],
            'base tag' => ['<base href="https://evil.test/">', ['<base']],
        ];
    }

    #[DataProvider('dangerousPayloads')]
    public function test_dangerous_markup_is_removed(string $html, array $forbidden): void
    {
        $clean = HtmlSanitizer::clean($html);

        foreach ($forbidden as $needle) {
            $this->assertStringNotContainsStringIgnoringCase($needle, $clean);
        }
    }

    public function test_editor_markup_is_preserved(): void
    {
        $html = '<h2>Kronologi</h2><p>SULTRAKLIK, <strong>Kendari</strong> — <em>laporan</em>.</p>'
            .'<blockquote><p>"Kami berharap publik tenang."</p></blockquote>'
            .'<ul><li>Poin satu</li><li>Poin dua</li></ul>'
            .'<table><tbody><tr><td colspan="2">Data</td></tr></tbody></table>'
            .'<img src="/storage/featured/foto.jpg" alt="Foto" width="800">'
            .'<a href="https://sultraklik.com/berita" target="_blank">Sumber</a>';

        $clean = HtmlSanitizer::clean($html);

        $this->assertStringContainsString('<h2>Kronologi</h2>', $clean);
        $this->assertStringContainsString('<strong>Kendari</strong>', $clean);
        $this->assertStringContainsString('<blockquote>', $clean);
        $this->assertStringContainsString('<li>Poin satu</li>', $clean);
        $this->assertStringContainsString('colspan="2"', $clean);
        $this->assertStringContainsString('src="/storage/featured/foto.jpg"', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer nofollow"', $clean);
    }

    public function test_unicode_and_entities_survive(): void
    {
        $clean = HtmlSanitizer::clean('<p>Sultra &amp; Indonesia — “kutipan”</p>');

        $this->assertStringContainsString('&amp;', $clean);
        $this->assertStringContainsString('Sultra', $clean);
    }

    public function test_unknown_tags_are_unwrapped_but_text_kept(): void
    {
        $this->assertStringContainsString('teks', HtmlSanitizer::clean('<unknown-tag>teks</unknown-tag>'));
    }

    public function test_empty_input(): void
    {
        $this->assertSame('', HtmlSanitizer::clean(null));
        $this->assertSame('', HtmlSanitizer::clean('   '));
    }
}

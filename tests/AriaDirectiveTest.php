<?php

declare(strict_types=1);

use Illuminate\Contracts\View\ViewCompilationException;
use Illuminate\Support\Facades\Blade;
use NickSdot\BladeHtmlAttributes\Tests\TestCase;

final class AriaDirectiveTest extends TestCase
{
    public function testAriaDirective(): void
    {
        $default = "@aria('foo', \$bar)";

        $this->assertSame('aria-foo="test"', $this->render($default, [ 'bar' => 'test' ]));

        $this->assertSame('aria-foo="0"', $this->render($default, [ 'bar' => 0 ]));
        $this->assertSame('aria-foo="1"', $this->render($default, [ 'bar' => 1 ]));
        $this->assertSame('aria-foo="8"', $this->render($default, [ 'bar' => 8 ]));
        $this->assertSame('', $this->render($default, [ 'bar' => '' ])); // aria never has empty strings
        $this->assertSame('', $this->render($default, [ 'bar' => '   ' ])); // aria never has whitespace-only strings
        $this->assertSame('', $this->render($default, [ 'bar' => null ]));
        $this->assertSame('aria-foo="true"', $this->render($default, [ 'bar' => true ]));
        $this->assertSame('aria-foo="false"', $this->render($default, [ 'bar' => false ]));

        $this->assertSame(
            'aria-foo="&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;"',
            $this->render($default, [ 'bar' => "<script>alert('xss')</script>" ])
        );
    }

    public function testAriaDirectiveInHtml(): void
    {
        /** @noinspection HtmlUnknownAttribute */
        $renderable = '<button @aria("label", $label) @aria("hidden", $hidden)>Click</button>';

        $this->assertSame(
            '<button aria-label="Click me" aria-hidden="true">Click</button>',
            $this->render($renderable, ['label' => 'Click me', 'hidden' => true])
        );

        $this->assertSame(
            '<button aria-label="Click me" >Click</button>',
            $this->render($renderable, ['label' => 'Click me', 'hidden' => ''])
        );

        $this->assertSame(
            '<button  >Click</button>',
            $this->render($renderable, ['label' => null, 'hidden' => null])
        );
    }

    public function testAriaDirectiveParameterCount(): void
    {
        $this->expectException(ViewCompilationException::class);
        $this->expectExceptionMessage('The @aria directive requires exactly 2 parameters.');

        Blade::compileString("@aria('foo')");
    }

    /** @param array<string, bool|int|string|null> $data */
    protected function render(string $directive, array $data = []): string|false
    {
        $compiled = Blade::compileString($directive);

        \extract($data);
        \ob_start();
        eval('?>' . $compiled);

        return \ob_get_clean();
    }
}

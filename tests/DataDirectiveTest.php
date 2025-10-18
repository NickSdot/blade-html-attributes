<?php

declare(strict_types=1);

namespace NickSdot\BladeHtmlAttributes\Tests;

use Illuminate\Contracts\View\ViewCompilationException;
use Illuminate\Support\Facades\Blade;

use function extract;
use function ob_get_clean;
use function ob_start;

final class DataDirectiveTest extends TestCase
{
    public function testDataDirective(): void
    {
        $default = "@data('foo', \$bar)";
        $forceValue = "@data('foo=', \$bar)";

        $this->assertSame('data-foo="test"', $this->render($default, [ 'bar' => 'test' ]));
        $this->assertSame('data-foo="test"', $this->render($forceValue, [ 'bar' => 'test' ]));

        $this->assertSame('data-foo="0"', $this->render($default, [ 'bar' => 0 ]));
        $this->assertSame('data-foo="0"', $this->render($forceValue, [ 'bar' => '0' ]));

        $this->assertSame('data-foo="1"', $this->render($default, [ 'bar' => 1 ]));
        $this->assertSame('data-foo="1"', $this->render($forceValue, [ 'bar' => '1' ]));

        $this->assertSame('', $this->render($default, [ 'bar' => '' ]));
        $this->assertSame('data-foo=""', $this->render($forceValue, [ 'bar' => '' ]));

        $this->assertSame('', $this->render($default, [ 'bar' => '   ' ]));
        $this->assertSame('data-foo="   "', $this->render($forceValue, [ 'bar' => '   ' ]));

        $this->assertSame('', $this->render($default, [ 'bar' => null ]));
        $this->assertSame('', $this->render($forceValue, [ 'bar' => null ]));

        $this->assertSame('data-foo', $this->render($default, [ 'bar' => true ]));
        $this->assertSame('data-foo="true"', $this->render($forceValue, [ 'bar' => true ]));

        $this->assertSame('', $this->render($default, [ 'bar' => false ]));
        $this->assertSame('data-foo="false"', $this->render($forceValue, [ 'bar' => false ]));

        $this->assertSame(
            'data-foo="&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;"',
            $this->render($default, [ 'bar' => "<script>alert('xss')</script>" ])
        );
    }

    public function testDataDirectiveInHtml(): void
    {
        /** @noinspection HtmlUnknownAttribute */
        $renderable = '<div @data("id", $id) @data("value", $value)>Content</div>';

        $this->assertSame(
            '<div data-id="123" data-value="test">Content</div>',
            $this->render($renderable, [ 'id' => 123, 'value' => 'test' ])
        );

        $this->assertSame(
            '<div  data-value="test">Content</div>',
            $this->render($renderable, [ 'id' => '', 'value' => 'test' ])
        );

        $this->assertSame(
            '<div  >Content</div>',
            $this->render($renderable, [ 'id' => null, 'value' => null ])
        );
    }

    public function testDataDirectiveParameterCount(): void
    {
        $this->expectException(ViewCompilationException::class);
        $this->expectExceptionMessage('The @data directive requires exactly 2 parameters.');

        Blade::compileString("@data('foo')");
    }

    public function testDataDirectiveUnsupportedNegation(): void
    {
        $this->expectException(ViewCompilationException::class);
        $this->expectExceptionMessage('The @data directive does not support negation.');

        Blade::compileString("@data('!foo', true)");
    }

    /** @param array<string, bool|int|string|null> $data */
    protected function render(string $directive, array $data = []): string|false
    {
        $compiled = Blade::compileString($directive);

        extract($data);
        ob_start();
        eval('?>' . $compiled);

        return ob_get_clean();
    }
}

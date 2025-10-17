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
        $negated = "@data('!foo', \$bar)";

        $this->assertSame('data-foo="test"', $this->render($default, [ 'bar' => 'test' ]));
        $this->assertSame('data-foo="test"', $this->render($negated, [ 'bar' => 'test' ]));

        $this->assertSame('data-foo="0"', $this->render($default, [ 'bar' => 0 ]));
        $this->assertSame('data-foo="0"', $this->render($negated, [ 'bar' => '0' ]));

        $this->assertSame('data-foo="1"', $this->render($default, [ 'bar' => 1 ]));
        $this->assertSame('data-foo="1"', $this->render($negated, [ 'bar' => '1' ]));

        $this->assertSame('', $this->render($default, [ 'bar' => '' ]));
        $this->assertSame('data-foo=""', $this->render($negated, [ 'bar' => '' ]));

        $this->assertSame('', $this->render($default, [ 'bar' => '   ' ]));
        $this->assertSame('data-foo="   "', $this->render($negated, [ 'bar' => '   ' ]));

        $this->assertSame('', $this->render($default, [ 'bar' => null ]));
        $this->assertSame('', $this->render($negated, [ 'bar' => null ]));

        $this->assertSame('data-foo="true"', $this->render($default, [ 'bar' => true ]));
        $this->assertSame('data-foo', $this->render($negated, [ 'bar' => true ]));

        $this->assertSame('data-foo="false"', $this->render($default, [ 'bar' => false ]));
        $this->assertSame('', $this->render($negated, [ 'bar' => false ]));

        $this->assertSame(
            'data-foo="&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;"',
            $this->render($default, [ 'bar' => "<script>alert('xss')</script>" ])
        );
    }

    public function testDataDirectiveInHtml(): void
    {
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

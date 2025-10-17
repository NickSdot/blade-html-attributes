<?php

declare(strict_types=1);

namespace NickSdot\BladeHtmlAttributes\Tests;

use Illuminate\Contracts\View\ViewCompilationException;
use Illuminate\Support\Facades\Blade;

use function extract;
use function ob_get_clean;
use function ob_start;

final class EnumDirectiveTest extends TestCase
{
    public function testEnumDirective(): void
    {
        $default = "@enum('foo', \$bar)";
        $forceValue = "@enum('foo=', \$bar)";

        $this->assertSame('foo="test"', $this->render($default, [ 'bar' => 'test' ]));
        $this->assertSame('foo="test"', $this->render($forceValue, [ 'bar' => 'test' ]));

        $this->assertSame('foo="0"', $this->render($default, [ 'bar' => 0 ]));
        $this->assertSame('foo="0"', $this->render($forceValue, [ 'bar' => '0' ]));

        $this->assertSame('foo="1"', $this->render($default, [ 'bar' => 1 ]));
        $this->assertSame('foo="1"', $this->render($forceValue, [ 'bar' => '1' ]));

        $this->assertSame('', $this->render($default, [ 'bar' => '' ]));
        $this->assertSame('foo=""', $this->render($forceValue, [ 'bar' => '' ]));

        $this->assertSame('', $this->render($default, [ 'bar' => '   ' ]));
        $this->assertSame('foo="   "', $this->render($forceValue, [ 'bar' => '   ' ]));

        $this->assertSame('', $this->render($default, [ 'bar' => null ]));
        $this->assertSame('', $this->render($forceValue, [ 'bar' => null ]));

        $this->assertSame('foo', $this->render($default, [ 'bar' => true ]));
        $this->assertSame('foo="true"', $this->render($forceValue, [ 'bar' => true ]));

        $this->assertSame('', $this->render($default, [ 'bar' => false ]));
        $this->assertSame('foo="false"', $this->render($forceValue, [ 'bar' => false ]));

        $this->assertSame(
            'foo="&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;"',
            $this->render($default, [ 'bar' => "<script>alert('xss')</script>" ])
        );
    }

    public function testEnumDirectiveInHtml(): void
    {
        $renderable = '<div @enum("title", $title) @enum("data-id", $id)>Content</div>';

        $this->assertSame(
            '<div title="test" data-id="123">Content</div>',
            $this->render($renderable, ['title' => 'test', 'id' => 123])
        );

        $this->assertSame(
            '<div  data-id="123">Content</div>',
            $this->render($renderable, ['title' => '', 'id' => 123])
        );

        $this->assertSame(
            '<div  >Content</div>',
            $this->render($renderable, ['title' => null, 'id' => null])
        );
    }

    public function testEnumDirectiveParameterCount(): void
    {
        $this->expectException(ViewCompilationException::class);
        $this->expectExceptionMessage('The @enum directive requires exactly 2 parameters.');

        Blade::compileString("@enum('foo')");
    }

    public function testEnumDirectiveUnsupportedNegation(): void
    {
        $this->expectException(ViewCompilationException::class);
        $this->expectExceptionMessage('The @enum directive does not support negation.');

        Blade::compileString("@enum('!foo', true)");
    }

    /**
     * @param string $directive
     * @param array<string, bool|int|string|null> $data
     *
     * @return string|false
     */
    protected function render(string $directive, array $data = []): string|false
    {
        $compiled = Blade::compileString($directive);

        extract($data);
        ob_start();
        eval('?>' . $compiled);

        return ob_get_clean();
    }
}

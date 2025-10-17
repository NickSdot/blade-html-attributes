<?php

declare(strict_types=1);

namespace NickSdot\BladeHtmlAttributes\Tests;

use Illuminate\Contracts\View\ViewCompilationException;
use Illuminate\Support\Facades\Blade;

use function extract;
use function ob_get_clean;
use function ob_start;

final class NeatDirectiveTest extends TestCase
{
    public function testNeatWithSimpleVariable(): void
    {
        $string = '<a @neat(\'foo\', $foo)>Link</a>';
        $expected = '<a <?php if(\'\' !== $foo && null !== $foo && \'\' !== trim(is_bool($foo) ? ($foo ? \'true\' : \'false\') : $foo)) { echo \'foo\' . \'="\' . e(is_bool($foo) ? ($foo ? \'true\' : \'false\') : $foo) . \'"\'; } ?>>Link</a>';

        $this->assertEquals($expected, Blade::compileString($string));
    }

    public function testNeatWithObjectProperty(): void
    {
        $string = '<a @neat(\'foo\', $link->foo)>Link</a>';
        $expected = '<a <?php if(\'\' !== $link->foo && null !== $link->foo && \'\' !== trim(is_bool($link->foo) ? ($link->foo ? \'true\' : \'false\') : $link->foo)) { echo \'foo\' . \'="\' . e(is_bool($link->foo) ? ($link->foo ? \'true\' : \'false\') : $link->foo) . \'"\'; } ?>>Link</a>';

        $this->assertEquals($expected, Blade::compileString($string));
    }

    public function testNeatWithArrayAccess(): void
    {
        $string = '<a @neat(\'foo\', $data[\'foo\'])>Link</a>';
        $expected = '<a <?php if(\'\' !== $data[\'foo\'] && null !== $data[\'foo\'] && \'\' !== trim(is_bool($data[\'foo\']) ? ($data[\'foo\'] ? \'true\' : \'false\') : $data[\'foo\'])) { echo \'foo\' . \'="\' . e(is_bool($data[\'foo\']) ? ($data[\'foo\'] ? \'true\' : \'false\') : $data[\'foo\']) . \'"\'; } ?>>Link</a>';

        $this->assertEquals($expected, Blade::compileString($string));
    }

    public function testNeatWithMultipleAttributes(): void
    {
        $string = '<img @neat(\'alt\', $alt) @neat(\'data-src\', $src)/>';
        $expected = '<img <?php if(\'\' !== $alt && null !== $alt && \'\' !== trim(is_bool($alt) ? ($alt ? \'true\' : \'false\') : $alt)) { echo \'alt\' . \'="\' . e(is_bool($alt) ? ($alt ? \'true\' : \'false\') : $alt) . \'"\'; } ?> <?php if(\'\' !== $src && null !== $src && \'\' !== trim(is_bool($src) ? ($src ? \'true\' : \'false\') : $src)) { echo \'data-src\' . \'="\' . e(is_bool($src) ? ($src ? \'true\' : \'false\') : $src) . \'"\'; } ?>/>';

        $this->assertEquals($expected, Blade::compileString($string));
    }

    public function testNeatWithSpacesAroundParameters(): void
    {
        $string = '<a @neat( \'foo\' ,   $foo )>Link</a>';
        $expected = '<a <?php if(\'\' !== $foo && null !== $foo && \'\' !== trim(is_bool($foo) ? ($foo ? \'true\' : \'false\') : $foo)) { echo \'foo\' . \'="\' . e(is_bool($foo) ? ($foo ? \'true\' : \'false\') : $foo) . \'"\'; } ?>>Link</a>';

        $this->assertEquals($expected, Blade::compileString($string));
    }

    public function testNeatWithNonEmptyString(): void
    {
        $this->assertSame(
            "<?php if('' !== \$foo && null !== \$foo && '' !== trim(is_bool(\$foo) ? (\$foo ? 'true' : 'false') : \$foo)) { echo 'foo' . '=\"' . e(is_bool(\$foo) ? (\$foo ? 'true' : 'false') : \$foo) . '\"'; } ?>",
            Blade::compileString("@neat('foo', \$foo)")
        );
    }

    public function testNeatDirective(): void
    {
        $directive = "@neat('foo', \$bar)";

        $this->assertSame('foo="You can just do things"', $this->render($directive, [ 'bar' => 'You can just do things' ]));

        $this->assertSame('', $this->render($directive, [ 'bar' => null ]));

        $this->assertSame('foo="true"', $this->render($directive, [ 'bar' => true ]));
        $this->assertSame('foo="false"', $this->render($directive, [ 'bar' => false ]));

        $this->assertSame('foo="0"', $this->render($directive, [ 'bar' => 0 ]));
        $this->assertSame('foo="0"', $this->render($directive, [ 'bar' => '0' ]));

        $this->assertSame('foo="1"', $this->render($directive, [ 'bar' => 1 ]));
        $this->assertSame('foo="1"', $this->render($directive, [ 'bar' => '1' ]));

        $this->assertSame('foo="8"', $this->render($directive, [ 'bar' => 8 ]));
        $this->assertSame('foo="8"', $this->render($directive, [ 'bar' => '8' ]));

        $this->assertSame('', $this->render($directive, [ 'bar' => '' ]));
        $this->assertSame('', $this->render($directive, [ 'bar' => '   ' ]));

        $this->assertSame(
            'foo="&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;"',
            $this->render($directive, [ 'bar' => "<script>alert('xss')</script>" ])
        );
    }

    public function testNeatDirectiveInHtml(): void
    {
        $string = '<a href="#" @neat(\'foo\', $foo)>Link</a>';
        $compiled = Blade::compileString($string);

        $this->assertSame(
            '<a href="#" foo="click">Link</a>',
            $this->render($compiled, [ 'foo' => 'click' ])
        );

        $this->assertSame(
            '<a href="#" >Link</a>',
            $this->render($compiled, [ 'foo' => '' ])
        );
    }

    public function testNeatDirectiveParameterCount(): void
    {
        $this->expectException(ViewCompilationException::class);
        $this->expectExceptionMessage('The @neat directive requires exactly 2 parameters.');

        Blade::compileString("@neat('foo')");
    }

    public function testNeatDirectiveUnsupportedNegation(): void
    {
        $this->expectException(ViewCompilationException::class);
        $this->expectExceptionMessage('The @neat directive does not support negation.');

        Blade::compileString("@neat('!foo', true)");
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

<?php

declare(strict_types=1);

namespace NickSdot\BladeHtmlAttributes\Tests;

use Illuminate\Contracts\View\ViewCompilationException;
use Illuminate\Support\Facades\Blade;

use function extract;
use function ob_get_clean;
use function ob_start;

final class FlagDirectiveTest extends TestCase
{
    public function testFlagDirective(): void
    {
        $renderable = "@flag('disabled', \$disabled)";

        $this->assertSame('disabled', $this->render($renderable, [ 'disabled' => 9 ]));
        $this->assertSame('disabled', $this->render($renderable, [ 'disabled' => '1' ]));
        $this->assertSame('disabled', $this->render($renderable, [ 'disabled' => true ]));
        $this->assertSame('disabled', $this->render($renderable, [ 'disabled' => 'bar' ]));

        $this->assertSame('', $this->render($renderable, [ 'disabled' => false ]));
        $this->assertSame('', $this->render($renderable, [ 'disabled' => null ]));
        $this->assertSame('', $this->render($renderable, [ 'disabled' => 0 ]));
        $this->assertSame('', $this->render($renderable, [ 'disabled' => '0' ]));
        $this->assertSame('', $this->render($renderable, [ 'disabled' => '' ]));
        $this->assertSame('', $this->render($renderable, [ 'disabled' => '   ' ]));

        $this->assertSame(
            'disabled',
            $this->render($renderable, [ 'disabled' => "<script>alert('xss')</script>" ])
        );
    }

    public function testFlagDirectiveInHtml(): void
    {
        $renderable = '<input type="checkbox" @flag("checked", $checked) @flag("data-blah", true) />';

        $this->assertSame(
            '<input type="checkbox" checked data-blah />',
            $this->render($renderable, [ 'checked' => true ])
        );

        $this->assertSame(
            '<input type="checkbox"  data-blah />',
            $this->render($renderable, [ 'checked' => false ])
        );
    }

    public function testFlagDirectiveParameterCount(): void
    {
        $this->expectException(ViewCompilationException::class);
        $this->expectExceptionMessage('The @flag directive requires exactly 2 parameters.');

        Blade::compileString("@flag('disabled')");
    }

    public function testFlagDirectiveUnsupportedNegation(): void
    {
        $this->expectException(ViewCompilationException::class);
        $this->expectExceptionMessage('The @flag directive does not support negation.');

        Blade::compileString("@flag('!foo', true)");
    }

    /**
     * @param string $renderable
     * @param array<string, bool|int|string|null> $data
     *
     * @return string|false
     */
    protected function render(string $renderable, array $data = []): string|false
    {
        $compiled = Blade::compileString($renderable);

        extract($data);
        ob_start();
        eval('?>' . $compiled);

        return ob_get_clean();
    }
}

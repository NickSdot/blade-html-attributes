<?php

declare(strict_types=1);

namespace NickSdot\BladeHtmlAttributes;

use Illuminate\Contracts\View\ViewCompilationException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

use function array_map;
use function count;
use function explode;
use function is_bool;
use function mb_substr;
use function mb_trim;
use function str_ends_with;
use function str_starts_with;

final class BladeHtmlAttributesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Blade::directive('flag', function (string $expression)
        {
            return $this->compileFlag($expression);
        });

        Blade::directive('attr', function (string $expression)
        {
            return $this->compileAttr($expression);
        });

        Blade::directive('data', function (string $expression)
        {
            return $this->compileData($expression);
        });

        Blade::directive('aria', function (string $expression)
        {
            return $this->compileAria($expression);
        });
    }

    /** @throws \Illuminate\Contracts\View\ViewCompilationException */
    protected function compileFlag(string $expression): string
    {
        $parts = explode(',', $expression, 2);

        if (2 !== count($parts)) {
            throw new ViewCompilationException('The @flag directive requires exactly 2 parameters.');
        }

        [ $attribute, $data ] = array_map('trim', $parts);

        if (str_ends_with($attribute, "='") || str_ends_with($attribute, '="')) {
            throw new ViewCompilationException('The @flag directive does not support forced values.');
        }

        return "<?php echo \\NickSdot\\BladeHtmlAttributes\\BladeHtmlAttributesServiceProvider::renderFlag($attribute, $data); ?>";
    }

    /** @throws \Illuminate\Contracts\View\ViewCompilationException */
    protected function compileAttr(string $expression): string
    {
        $parts = explode(',', $expression, 2);

        if (2 !== count($parts)) {
            throw new ViewCompilationException('The @attr directive requires exactly 2 parameters.');
        }

        [ $attribute, $data ] = array_map('trim', $parts);

        $forceValue = str_ends_with($attribute, "='") || str_ends_with($attribute, '="');

        if ($forceValue) {
            return "<?php echo \\NickSdot\\BladeHtmlAttributes\\BladeHtmlAttributesServiceProvider::renderAttrForced($attribute, $data); ?>";
        }

        return "<?php echo \\NickSdot\\BladeHtmlAttributes\\BladeHtmlAttributesServiceProvider::renderAttr($attribute, $data); ?>";
    }


    /** @throws \Illuminate\Contracts\View\ViewCompilationException */
    protected function compileData(string $expression): string
    {
        $parts = explode(',', $expression, 2);

        if (2 !== count($parts)) {
            throw new ViewCompilationException('The @data directive requires exactly 2 parameters.');
        }

        [ $attribute, $data ] = array_map('trim', $parts);

        $forceValue = str_ends_with($attribute, "='") || str_ends_with($attribute, '="');

        if ($forceValue) {
            return "<?php echo \\NickSdot\\BladeHtmlAttributes\\BladeHtmlAttributesServiceProvider::renderDataForced($attribute, $data); ?>";
        }

        return "<?php echo \\NickSdot\\BladeHtmlAttributes\\BladeHtmlAttributesServiceProvider::renderData($attribute, $data); ?>";
    }


    /** @throws \Illuminate\Contracts\View\ViewCompilationException */
    protected function compileAria(string $expression): string
    {
        $parts = explode(',', $expression, 2);

        if (2 !== count($parts)) {
            throw new ViewCompilationException('The @aria directive requires exactly 2 parameters.');
        }

        [ $attribute, $data ] = array_map('trim', $parts);

        return "<?php echo \\NickSdot\\BladeHtmlAttributes\\BladeHtmlAttributesServiceProvider::renderAria($attribute, $data); ?>";
    }

    /** @api */
    public static function renderFlag(string $attribute, string|int|float|bool|null $data): string
    {
        if (null === $data || '' === $data || '0' === (string) $data || false === $data || '' === mb_trim((string) $data)) {
            return '';
        }

        return $attribute;
    }

    /** @api */
    public static function renderAttrForced(string $attribute, string|int|float|bool|null $data): string
    {
        if (null === $data) {
            return '';
        }

        $value = is_bool($data) ? ($data ? 'true' : 'false') : (string) $data;

        return $attribute . '"' . e($value) . '"';
    }

    /** @api */
    public static function renderAttr(string $attribute, string|int|float|bool|null $data): string
    {
        return self::renderCommon($data, $attribute);
    }

    /** @api */
    public static function renderDataForced(string $attribute, string|int|float|bool|null $data): string
    {
        if (null === $data) {
            return '';
        }

        $value = is_bool($data) ? ($data ? 'true' : 'false') : (string) $data;

        return 'data-' . $attribute . '"' . e($value) . '"';
    }

    /** @api */
    public static function renderData(string $attribute, string|int|float|bool|null $data): string
    {
        return self::renderCommon($data, 'data-' . $attribute);
    }

    /** @api */
    public static function renderAria(string $attribute, string|int|float|bool|null $data): string
    {
        if (null === $data) {
            return '';
        }

        $attribute = 'aria-' . $attribute;

        if (is_bool($data)) {
            return $attribute . '="' . ($data ? 'true' : 'false') . '"';
        }

        $stringData = (string) $data;

        if ('' === $stringData || '' === mb_trim($stringData)) {
            return '';
        }

        return $attribute . '="' . e($stringData) . '"';
    }

    private static function renderCommon(float|bool|int|string|null $data, string $attribute): string
    {
        if (null === $data || false === $data) {
            return '';
        }

        if (true === $data) {
            return $attribute;
        }

        $stringData = (string) $data;

        if ('' === $stringData || '' === mb_trim($stringData)) {
            return '';
        }

        return $attribute . '="' . e($stringData) . '"';
    }
}

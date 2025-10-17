<?php

declare(strict_types=1);

namespace NickSdot\BladeHtmlAttributes;

use Illuminate\Contracts\View\ViewCompilationException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

use function array_map;
use function count;
use function explode;
use function mb_substr;
use function str_ends_with;
use function str_starts_with;

final class BladeHtmlAttributesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Blade::directive('bool', function (string $expression)
        {
            return $this->compileBool($expression);
        });

        Blade::directive('enum', function (string $expression)
        {
            return $this->compileEnum($expression);
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
    protected function compileBool(string $expression): string
    {
        $parts = explode(',', $expression, 2);

        if (2 !== count($parts)) {
            throw new ViewCompilationException('The @bool directive requires exactly 2 parameters.');
        }

        [ $attribute, $data ] = array_map('trim', $parts);

        if (str_starts_with($attribute, "'!") || str_starts_with($attribute, '"!')) {
            throw new ViewCompilationException('The @bool directive does not support negation.');
        }

        if (str_ends_with($attribute, "='") || str_ends_with($attribute, '="')) {
            throw new ViewCompilationException('The @bool directive does not support forced values.');
        }

        return "<?php if(null !== $data && '' !== $data && '0' !== (string) $data && false !== $data && '' !== trim((string) $data)) echo $attribute; ?>";
    }

    /** @throws \Illuminate\Contracts\View\ViewCompilationException */
    protected function compileEnum(string $expression): string
    {
        $parts = explode(',', $expression, 2);

        if (2 !== count($parts)) {
            throw new ViewCompilationException('The @enum directive requires exactly 2 parameters.');
        }

        [ $attribute, $data ] = array_map('trim', $parts);

        if (str_starts_with($attribute, "'!") || str_starts_with($attribute, '"!')) {
            throw new ViewCompilationException('The @enum directive does not support negation.');
        }

        $forceValue = str_ends_with($attribute, "='") || str_ends_with($attribute, '="');

        if ($forceValue) {
            $attribute = mb_substr($attribute, 0, -2) . mb_substr($attribute, -1); // remove = operator
            return "<?php if(null !== $data) echo $attribute . '=\"' . e(is_bool($data) ? ($data ? 'true' : 'false') : $data) . '\"'; ?>";
        }

        return "<?php if(null !== $data && false !== $data && (is_bool($data) || ('' !== $data && '' !== trim((string) $data)))) echo (true === $data ? $attribute : $attribute . '=\"' . e(is_bool($data) ? 'false' : $data) . '\"'); ?>";
    }

    /** @throws \Illuminate\Contracts\View\ViewCompilationException */
    protected function compileData(string $expression): string
    {
        $parts = explode(',', $expression, 2);

        if (2 !== count($parts)) {
            throw new ViewCompilationException('The @data directive requires exactly 2 parameters.');
        }

        [ $attribute, $data ] = array_map('trim', $parts);

        if (str_starts_with($attribute, "'!") || str_starts_with($attribute, '"!')) {
            throw new ViewCompilationException('The @data directive does not support negation.');
        }

        $forceValue = str_ends_with($attribute, "='") || str_ends_with($attribute, '="');

        if ($forceValue) {
            $attribute = mb_substr($attribute, 0, 1) . 'data-' . mb_substr($attribute, 1, -2) . mb_substr($attribute, -1); // remove = operator, add `data-` prefix
            return "<?php if(null !== $data) echo $attribute . '=\"' . e(is_bool($data) ? ($data ? 'true' : 'false') : $data) . '\"'; ?>";
        }

        $attribute = mb_substr($attribute, 0, 1) . 'data-' . mb_substr($attribute, 1); // add `data-` prefix
        return "<?php if(null !== $data && false !== $data && (is_bool($data) || ('' !== $data && '' !== trim((string) $data)))) echo (true === $data ? $attribute : $attribute . '=\"' . e(is_bool($data) ? 'false' : $data) . '\"'); ?>";
    }

    /** @throws \Illuminate\Contracts\View\ViewCompilationException */
    protected function compileAria(string $expression): string
    {
        $parts = explode(',', $expression, 2);

        if (2 !== count($parts)) {
            throw new ViewCompilationException('The @aria directive requires exactly 2 parameters.');
        }

        [ $attribute, $data ] = array_map('trim', $parts);

        $negated = str_starts_with($attribute, "'!") || str_starts_with($attribute, '"!');

        if ($negated) {
            $attribute = mb_substr($attribute, 0, 1) . 'aria-' . mb_substr($attribute, 2); // remove ! operator, add `aria-` prefix
            return "<?php if(null !== $data && false !== $data && ('' !== $data && '' !== trim((string) $data))) echo $attribute . '=\"' . e(is_bool($data) ? 'true' : $data) . '\"'; ?>";
        }

        $attribute = mb_substr($attribute, 0, 1) . 'aria-' . mb_substr($attribute, 1); // add `aria-` prefix
        return "<?php if(null !== $data && (is_bool($data) || ('' !== $data && '' !== trim((string) $data)))) echo $attribute . '=\"' . e(is_bool($data) ? ($data ? 'true' : 'false') : $data) . '\"'; ?>";
    }
}

# Blade HTML Attributes

A Laravel package that provides Blade directives for conditionally rendering HTML attributes.

## Installation

```bash
composer require nicksdot/blade-html-attributes
```

The package will auto-register the service provider.

## Available Directives

### Behaviour Matrix

**Note:** Rows in **bold** show special operator (`=` & `!` behaviour.

| Value                 | `@attr`           | `@data`                | `@aria`            | `@flag`     |
|-----------------------|-------------------|------------------------|--------------------|-------------|
| `('foo', "bar")`      | `foo="bar"`       | `data-foo="bar"`       | `aria-foo="bar"`   | `foo`       |
| `('foo', "1")`        | `foo="1"`         | `data-foo="1"`         | `aria-foo="1"`     | `foo`       |
| `('foo', 1)`          | `foo="1"`         | `data-foo="1"`         | `aria-foo="1"`     | `foo`       |
| `('foo', true)`       | `foo`             | `data-foo`             | `aria-foo="true"`  | `foo`       |
| **`('foo=', true)`**  | **`foo="true"`**  | **`data-foo="true"`**  | `aria-foo="true"`  | `foo`       |
| `('foo', false)`      | _(nothing)_       | _(nothing)_            | `aria-foo="false"` | _(nothing)_ |
| **`('foo=', false)`** | **`foo="false"`** | **`data-foo="false"`** | `aria-foo="false"` | _(nothing)_ |
| **`('!foo', false)`** | _(throws)_        | _(throws)_             | **_(nothing)_**    | _(throws)_  |
| `('foo', "0")`        | `foo="0"`         | `data-foo="0"`         | `aria-foo="0"`     | _(nothing)_ |
| `('foo', 0)`          | `foo="0"`         | `data-foo="0"`         | `aria-foo="0"`     | _(nothing)_ |
| `('foo', '')`         | _(nothing)_       | _(nothing)_            | _(nothing)_        | _(nothing)_ |
| **`('foo=', '')`**    | **`foo=""`**      | **`data-foo=""`**      | _(nothing)_        | _(nothing)_ |
| `('foo', '   ')`      | _(nothing)_       | _(nothing)_            | _(nothing)_        | _(nothing)_ |
| **`('foo=', '   ')`** | **`foo="   "`**   | **`data-foo="   "`**   | _(nothing)_        | _(nothing)_ |
| `('foo', null)`       | _(nothing)_       | _(nothing)_            | _(nothing)_        | _(nothing)_ |

**Gotchas:**
- `@attr` and `@data` allow the  `=` suffix (e.g., `@attr('foo=', $value)`) to force values (always render with `="value"`, even for booleans and empty strings)
- `@aria` allows the `!` prefix (e.g., `@aria('!foo', $value)`) to negate false values for removing attribute entirely.

### Descriptions

- **`@attr`**: By default, `true` renders as a boolean flag (attribute name only), and `false`/empty/whitespace-only/null render nothing. With the force-value operator (`=` suffix like `'foo='`), always renders with values including `"true"`, `"false"`, and empty strings.

- **`@data`**: Same as `@attr` but automatically prefixes attribute names with `data-`.

- **`@aria`**: By default, renders all values including `"true"` and `"false"` (never as boolean flags). Never renders empty or whitespace-only strings. With the negation operator (`!` prefix like `'!
foo'`), `false` the attribute is completely removed instead of rendering as `"false"`.

- **`@flag`**: Outputs just the attribute name without a value (boolean flag), for truthy values only. Follows HTML spec for boolean attributes like `disabled`, `checked`, `required` or `data-foo`.

## Examples

### `@flag` Directive

```blade
{{-- Before --}}
<button @if($isDisabled) disabled @endif>Submit</button>

{{-- After --}}
<button @flag('disabled', $isDisabled)>Submit</button>

{{-- Multiple flag attributes --}}
<input type="checkbox" @flag('checked', $isChecked) @flag('required', $isRequired) />
```

### `@attr` Directive

```blade
{{-- Before / After --}}
<a href="{{ $link->route }}" @if($link->title) title="{{ $link->title }} @endif" @if($link->rel) rel="{{ $link->rel }} @endif"></a>
<a href="{{ $link->route }}" @maybe('title', $link->title) @maybe('rel', $link->rel)></a>

{{-- Before / After --}}
<input @if($value !== null) value="{{ $value }}" @endif />
<input @attr('value=', $value) />

{{-- Before / After --}}
<select @if($size) size="{{ $size }}" @endif></select>
<select @attr('size', $size)></select>

{{-- Force rendering booleans as string value --}}
<div @attr('contenteditable=', true)>
    Edit me (renders as `contenteditable="true"`)
</div>
```

### `@data` Directive

```blade
{{-- Before / After --}}
<div @if($id) data-id="{{ $id }}" @endif @if($value) data-value="{{ $value }}" @endif></div>
<div @data('id', $id) @data('value', $value)></div>

{{-- Before / After --}}
<button @if($toggle) data-toggle @endif></button>
<button @data('toggle', $toggle)></button>

{{-- Force rendering booleans as string value --}}
<button @data('toggle=', $toggle)>
    Click (renders as `data-toggle="true"`)
</button>
```

### `@aria` Directive

```blade
{{-- Before / After --}}
<button @if($label) aria-label="{{ $label }}" @endif @if($hidden) aria-hidden="{{ $hidden }}" @endif></button>
<button @aria('label', $label) @aria('hidden', $hidden)></button>

{{-- Before / After --}}
<div @if($label && $label !== '') aria-label="{{ $label }}" @endif></div>
<div @aria('label', $label)></div>

{{-- Before / After --}}
<div @if($hidden) aria-hidden="true" @endif></div>
<div @aria('!hidden', $hidden)></div>
```

## Requirements

- PHP 8.1+
- Laravel 10.0+

## License

This package is open-sourced software licensed under the [MIT licence](LICENSE.md).

## Credits

- [Nick](https://github.com/nicksdot)

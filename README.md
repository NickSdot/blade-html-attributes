# Blade HTML Attributes

A Laravel package that provides Blade directives for conditionally rendering HTML attributes.

## Installation

```bash
composer require nicksdot/blade-html-attributes
```

The package will auto-register the service provider.

## Available Directives

### Directive Behaviour Matrix

| Value                 | `@flag`     | `@attr`           | `@data`                | `@aria`            |
|-----------------------|-------------|-------------------|------------------------|--------------------|
| `('foo', "bar")`      | `foo`       | `foo="bar"`       | `data-foo="bar"`       | `aria-foo="bar"`   |
| `('foo', "1")`        | `foo`       | `foo="1"`         | `data-foo="1"`         | `aria-foo="1"`     |
| `('foo', 1)`          | `foo`       | `foo="1"`         | `data-foo="1"`         | `aria-foo="1"`     |
| `('foo', true)`       | `foo`       | `foo`             | `data-foo`             | `aria-foo="true"`  |
| **`('foo=', true)`**  | `foo`       | **`foo="true"`**  | **`data-foo="true"`**  | `aria-foo="true"`  |
| `('foo', false)`      | _(nothing)_ | _(nothing)_       | _(nothing)_            | `aria-foo="false"` |
| **`('foo=', false)`** | _(nothing)_ | **`foo="false"`** | **`data-foo="false"`** | `aria-foo="false"` |
| **`('!foo', false)`** | _(throws)_  | _(throws)_        | _(throws)_             | **_(nothing)_**    |
| `('foo', "0")`        | _(nothing)_ | `foo="0"`         | `data-foo="0"`         | `aria-foo="0"`     |
| `('foo', 0)`          | _(nothing)_ | `foo="0"`         | `data-foo="0"`         | `aria-foo="0"`     |
| `('foo', '')`         | _(nothing)_ | _(nothing)_       | _(nothing)_            | _(nothing)_        |
| **`('foo=', '')`**    | _(nothing)_ | **`foo=""`**      | **`data-foo=""`**      | _(nothing)_        |
| `('foo', '   ')`      | _(nothing)_ | _(nothing)_       | _(nothing)_            | _(nothing)_        |
| **`('foo=', '   ')`** | _(nothing)_ | **`foo="   "`**   | **`data-foo="   "`**   | _(nothing)_        |
| `('foo', null)`       | _(nothing)_ | _(nothing)_       | _(nothing)_            | _(nothing)_        |

**Note:** Rows in **bold** show special operator behaviour:

- `@attr` and `@data` allow the  `=` suffix (e.g., `@attr('foo=', $value)`) to force values (always render with `="value"`, even for booleans and empty strings)
- `@aria` allows the `!` prefix (e.g., `@aria('!foo', $value)`) to negate false values (removes attribute entirely when false)

### Directive Descriptions

- **`@flag`**: Outputs just the attribute name without a value (boolean flag), for truthy values only. Follows HTML spec for boolean attributes like `disabled`, `checked`, `required` or `data-foo`.

- **`@attr`**: By default, `true` renders as a boolean flag (attribute name only), and `false`/empty/whitespace-only/null render nothing. With the force-value operator (`=` suffix like `'foo='`), always renders
  with values including `"true"`, `"false"`, and empty strings.

- **`@data`**: Same as `@attr` but automatically prefixes attribute names with `data-`.

- **`@aria`**: Renders ARIA attributes with values. By default, renders all values including `"true"` and `"false"` (never as boolean flags). Never renders empty strings or whitespace. With the negation operator (`!` prefix like `'!foo'`),
  `false` the attribute is completely removed instead of rendering as `"false"`.

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
{{-- Before --}}
<select @if($size) size="{{ $size }}" @endif>
    <option>Small</option>
</select>

{{-- After --}}
<select @attr('size', $size)>
    <option>Small</option>
</select>

{{-- Before --}}
<input @if($value !== null) value="{{ $value }}" @endif />

{{-- After --}}
<input @attr('value=', $value) />

{{-- Before --}}
<div @if($editable) contenteditable @endif>
    Edit me
</div>

{{-- After --}}
<div @attr('contenteditable', $editable)>
    Edit me
</div>

{{-- Force rendering flag as string value --}}
<div @attr('contenteditable=', $editable)>
    Edit me (renders as `contenteditable="true"`)
</div>
```

### `@data` Directive

```blade
{{-- Before --}}
<div @if($id) data-id="{{ $id }}" @endif @if($value) data-value="{{ $value }}" @endif>
    Content
</div>

{{-- After --}}
<div @data('id', $id) @data('value', $value)>
    Content
</div>

{{-- Before --}}
<button @if($toggle) data-toggle @endif>
    Click
</button>

{{-- After --}}
<button @data('toggle', $toggle)>
    Click (renders as `data-toggle`)
</button>

{{-- Force rendering flag as string value --}}
<button @data('toggle=', $toggle)>
    Click (renders as `data-toggle="true"`)
</button>
```

### `@aria` Directive

```blade
{{-- Before --}}
<button @if($label) aria-label="{{ $label }}" @endif @if($hidden) aria-hidden="{{ $hidden }}" @endif>
    Click
</button>

{{-- After --}}
<button @aria('label', $label) @aria('hidden', $hidden)>
    Click
</button>

{{-- Before (never renders empty - skips empty string) --}}
<div @if($label && $label !== '') aria-label="{{ $label }}" @endif>
    Content
</div>

{{-- After --}}
<div @aria('label', $label)>
    Content
</div>

{{-- Before (negated true always has value) --}}
<div @if($hidden) aria-hidden="true" @endif>
    Content
</div>

{{-- After --}}
<div @aria('!hidden', $hidden)>
    Content
</div>
```

## Requirements

- PHP 8.1+
- Laravel 10.0+

## License

This package is open-sourced software licensed under the [MIT licence](LICENSE.md).

## Credits

- [Nick](https://github.com/nicksdot)

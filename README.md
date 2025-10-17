# Blade HTML Attributes

A Laravel package that provides Blade directives for conditionally rendering HTML attributes.

## Installation

```bash
composer require nicksdot/blade-html-attributes
```

The package will auto-register the service provider.

## Available Directives

### Behavior Matrix

| Value                 | `@neat`       | `@bool`     | `@enum`           | `@data`                | `@aria`            |
|-----------------------|---------------|-------------|-------------------|------------------------|--------------------|
| `('foo', "bar")`      | `foo="bar"`   | `foo`       | `foo="bar"`       | `data-foo="bar"`       | `aria-foo="bar"`   |
| `('foo', "1")`        | `foo="1"`     | `foo`       | `foo="1"`         | `data-foo="1"`         | `aria-foo="1"`     |
| `('foo', 1)`          | `foo="1"`     | `foo`       | `foo="1"`         | `data-foo="1"`         | `aria-foo="1"`     |
| `('foo', true)`       | `foo="true"`  | `foo`       | `foo`             | `data-foo`             | `aria-foo="true"`  |
| **`('foo=', true)`**  | `foo="true"`  | `foo`       | **`foo="true"`**  | **`data-foo="true"`**  | `aria-foo="true"`  |
| `('foo', false)`      | `foo="false"` | _(nothing)_ | _(nothing)_       | _(nothing)_            | `aria-foo="false"` |
| **`('foo=', false)`** | `foo="false"` | _(nothing)_ | **`foo="false"`** | **`data-foo="false"`** | `aria-foo="false"` |
| **`('!foo', false)`** | _(throws)_    | _(throws)_  | _(throws)_        | _(throws)_             | **_(nothing)_**    |
| `('foo', "0")`        | `foo="0"`     | _(nothing)_ | `foo="0"`         | `data-foo="0"`         | `aria-foo="0"`     |
| `('foo', 0)`          | `foo="0"`     | _(nothing)_ | `foo="0"`         | `data-foo="0"`         | `aria-foo="0"`     |
| `('foo', '')`         | _(nothing)_   | _(nothing)_ | _(nothing)_       | _(nothing)_            | _(nothing)_        |
| **`('foo=', '')`**    | _(nothing)_   | _(nothing)_ | **`foo=""`**      | **`data-foo=""`**      | _(nothing)_        |
| `('foo', '   ')`      | _(nothing)_   | _(nothing)_ | _(nothing)_       | _(nothing)_            | _(nothing)_        |
| **`('foo=', '   ')`** | _(nothing)_   | _(nothing)_ | **`foo="   "`**   | **`data-foo="   "`**   | _(nothing)_        |
| `('foo', null)`       | _(nothing)_   | _(nothing)_ | _(nothing)_       | _(nothing)_            | _(nothing)_        |

**Note:** Rows in **bold** show special operator behavior:

- `@enum` and `@data` use `=` suffix (e.g., `@enum('foo=', $value)`) to force values (always render with `="value"`, even for booleans and empty strings)
- `@aria` uses `!` prefix (e.g., `@aria('!foo', $value)`) to negate false values (removes attribute entirely when false)
- Values in bold indicate behavior changes with the operator - unchanged values repeat the normal behavior

### Directive Descriptions

- **`@neat`**: Renders attributes with values, skipping empty strings, whitespace-only strings, and null. Boolean values are converted to strings (`"true"` / `"false"`).

- **`@bool`**: Outputs just the attribute name without a value (boolean flag), for truthy values only. Follows HTML spec for attributes like `disabled`, `checked`, `required`.

- **`@enum`**: Renders attributes conditionally. By default, `true` renders as a boolean flag (just the attribute name), and `false`/empty/whitespace/null render nothing. With force-value operator (`=` suffix like `'foo='`), always renders
  with values including `"true"`, `"false"`, and empty strings.

- **`@data`**: Same as `@enum` but automatically prefixes attribute names with `data-`.

- **`@aria`**: Renders ARIA attributes with values. By default, renders all values including `"true"` and `"false"` (never as boolean flags). Never renders empty strings or whitespace. With negation operator (`!` prefix like `'!foo'`),
  `false` values are completely removed instead of rendering as `"false"`.

## Examples

### `@neat` Directive

```blade
{{-- Before --}}
<a @if($title) title="{{ $title }}" @endif>Link</a>

{{-- After --}}
<a @neat('title', $title)>Link</a>

{{-- Before --}}
<div @if($poll) wire:poll="{{ $poll }}" @endif>
    Content
</div>

{{-- After --}}
<div @neat('wire:poll', $poll)>
    Content
</div>
```

### `@bool` Directive

```blade
{{-- Before --}}
<button @if($isDisabled) disabled @endif>Submit</button>

{{-- After --}}
<button @bool('disabled', $isDisabled)>Submit</button>

{{-- Multiple boolean attributes --}}
<input type="checkbox" @bool('checked', $isChecked) @bool('required', $isRequired) />
```

### `@enum` Directive

```blade
{{-- Before --}}
<select @if($size) size="{{ $size }}" @endif>
    <option>Small</option>
</select>

{{-- After --}}
<select @enum('size', $size)>
    <option>Small</option>
</select>

{{-- Before --}}
<input @if($value !== null) value="{{ $value }}" @endif />

{{-- After --}}
<input @enum('value=', $value) />

{{-- Before --}}
<div @if($editable) contenteditable @endif>
    Edit me
</div>

{{-- After --}}
<div @enum('contenteditable', $editable)>
    Edit me
</div>

{{-- Force rendering boolean as string value --}}
<div @enum('contenteditable=', $editable)>
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

{{-- Force rendering boolean as string value --}}
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

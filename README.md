# kynx/laminas-form-shape

[![Continuous Integration](https://github.com/kynx/laminas-form-shape/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/kynx/laminas-form-shape/actions/workflows/continuous-integration.yml)

Generate [Psalm] types for [Laminas forms]

**This is a work in progress**. Until we hit a `1.x` release, the examples below are more to illustrate what _can_ be
done, not how it _will_ work once stable.

## Installation

Install this package as a development dependency using [Composer]:

```commandline
composer require --dev kynx/laminas-form-shape
```

## Usage

```commandline
vendor/bin/laminas form:psalm-type src/Forms/MyForm.php
```

...outputs an [array shape] something like:

```text
array{
    name:     non-empty-string,
    age:      numeric-string,
    gender?:  null|string,
    can_code: '0'|'1',
}
```

To see a full list of options:

```commandline
vendor/bin/laminas form:psalm-type --help
```

## Why?

Version [3.17.0] of Laminas Form introduced a Psalm template for describing the array returned by `Form::getData()`.
This is a great improvement, and should winkle out potential bugs for anyone using Psalm. However generating the array
shape for a form is a chore, and very easy to get wrong. Do you actually know the Psalm type produced by every
combination of `required`, `allow_empty` and `continue_if_empty`? And what about all the possible filters and
validators attached to the `InputFilter`?

This command introspects your form's input filter and generates the most specific array shape possible.

## Beware

If your code changes forms at run time - adding and removing elements, changing `required` properties or populating
`<select>` options - this tool won't know. The array shape it generates can serve as a good starting point, but will
need manual tweaks.

This tool aims to cover all the filters or validators installed when you `composer require laminas/laminas-form`. If it
encounters one it doesn't know about, it silently ignores it. See the [Customisation] section for pointers on handling
these.

### `Cannot get type for 'foo'`

It is possible to build an input filter with a combination of filters and validators that can never produce a result.
For instance, a `Boolean` filter with the `casting` option set to `true` will ony ever output a `bool` type. If you
follow that with a `Barcode` validator, the element can never validate. When the command encounters a situation like that
it will report a "Cannot get type" error.

If you see this error when parsing an existing form that has been functioning fine for years, you've hit a bug. Please
raise an issue with a _small_ example form that reproduces the error. Or, better yet, create a PR with a failing
test :smiley:

## Configuration

All configuration is stored under the `laminas-form-shape` configuration key. The examples below assume you are using
PHP configuration files that return arrays something like:

```php
return [
    'laminas-form-shape' => [
        // custom config here
    ],
];
```

### Output formatting

There are three top level settings to control how the array shapes are formatted:

1. `indent` - string to use for indentation when pretty-printing array shapes. Default to four spaces.
2. `max-string-length` - maximum number of characters to include when outputting literal strings. Defaults to whatever
   psalm is configured with (typically 1000 characters).
3. `literal-limit` - maximum number of literals to output. If your `AllowList` filters or `InArray` validators contain
   large number of items you may want to limit this. Defaults to 100.

Changing the last two will make your array shapes less exact, but more readable.

```php
return [
    'laminas-form-shape' => [
        'indent'             => "\t", // use tab for indenting
        'max-string-length'  => 50,   // don't output long literal strings
        'literal-limit'      => 20,   // don't output too many literals
    ],
];
```

### Filters

Each filter defined in your form's `InputFilter` will be processed by a number of [visitors]. Each visitor takes the
previous list of types (typically starting with `null|string`) and adds or removes types depending on what the filter
actually does.

Most filter visitors require no configuration, and for those that do we provide sensible defaults. But feel free to
tweak the following:

#### AllowList

The [AllowList] filter enables you to configure a list of terms that are "allowed" by the filter, or `null` if none
match. We turn this into a literal list like `'first'|'second'|1|2|null`.

If there are no terms in the list we pass on whatever type the previous visitor output. This is so code that dynamically
populates the list (for example, from a database query) does not barf.

You can change both behaviours via configuration:

```php
return [
    'laminas-form-shape' => [
        'filter'             => [
            'allow-list' => [
                'allow-empty-list' => false, // empty lists will produce "Cannot get type" error
            ],
        ],
    ],
];
```

#### Callback

[Callback filters] are hard to handle. In future I will write a visitor to fuzz them and see what they do,
but even that will have edge cases. I don't use them much: currently they are ignored. To protect the sanity of us tool
writers - and to keep your code testable and reusable - I suggest converting them to concrete classes and creating
visitors to describe the types they can produce.

### Validators

Each validator defined in your form's `InputFilter` will be processed by a number of [visitors]. The final list of types
produced by all the filters is fed to the first, then the output of that is fed to the next, and so on. Validators
typically narrow the final type. For instance, the visitor for a `Digits` validator will turn `string` types into
`numeric-string`[^1].

Most filter visitors require no configuration, and for those that do we provide sensible defaults. But feel free to
tweak the following:

#### Callback

[Callback validators] are hard to handle. In future I will write a visitor to fuzz them and see what they do, but even
that will have edge cases. I don't use them much: currently they are ignored. To protect the sanity of us tool writers -
and to keep your code testable and reusable - I suggest converting them to concrete classes and creating
visitors to describe the types they can produce.

#### File

The various [File] validators accept an array from the `$_FILES` super-global or an `UploadedFileInterface`. We have a
single visitor for handling them all.

If you've got a custom file validator that accepts the same, add it to the list of validators the visitor handles:

```php
use MyApp\Validator\MyCustomFileValidator;

return [
    'laminas-form-shape' => [
        'validator' => [
            'file' => [
                'validators' => [
                    MyCustomFileValidator::class,
                ],
            ],
        ],
    ],
];
```

#### InArray

Like the `AllowList` filter, the [InArray] validator accepts a list - or `haystack` - of values and verifies the input
is one of them. The visitor can return a literal type (`'first'|'second'|1|2`). By default it ignores an empty haystack.

You can change the defaults:

```php
return [
    'laminas-form-shape' => [
        'validator' => [
            'in-array' => [
                'allow-empty-haystack' => false, // empty haystacks will produce "Cannot get type" error
            ],
        ],
    ],
];
```

#### Regex

The [Regex] validator rejects input that doesn't match its regular expression. If I were a genius and had time on my
hands, I might be able to write a regex parser that could work out the type from the expression. But I'm not and I
don't.

Instead we provide a list of known regular expressions used by standard form elements in the configuration. If you've
got your own regular expressions you'll want to add them to the list.

The configuration is keyed by the regular expression string, and contains a list of [Psalm types] that are used to
narrow the type union. For instance, the `Number` element will validate a `float`, `int` or `numeric-string`. It's
configuration looks like:

```php
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TString;

return [
    'laminas-form-shape' => [
        'validator' => [
            'regex' => [
                '(^-?\d*(\.\d+)?$)' => [TFloat::class, TInt::class, TNumericString::class],
            ],
        ],
    ],
];
```

#### Strings

There are a large number of validators that do clever things to verify the format of strings, from [Barcode] to [Uuid].
But all they tell us about the type is to change `string` to `non-empty-string`.

If you have a custom validator that does the same, add it to the list:

```php
use MyApp\Validator\MyCustomStringValidator;

return [
    'laminas-form-shape' => [
        'validator' => [
            'non-empty-string' => [
                'validators' => [
                    MyCustomStringValidator::class,
                ],
            ],
        ],
    ],
];
```

## Custom filters and validators

To come, once things settle down...

[^1]: The final type decided on by all the filters and validators isn't the end of it. Depending on how the `Input`
  itself is configured, the type may be broadened. For instance, if you `allow_empty`, a `non-empty-string` will be
  broadened back to just `string`.

[Psalm]: https://psalm.dev
[Laminas forms]: https://docs.laminas.dev/laminas-form/
[Composer]: https://getcomposer.org
[array shape]: https://psalm.dev/docs/annotating_code/type_syntax/array_types/#array-shapes
[3.17.0]: https://github.com/laminas/laminas-form/releases/tag/3.17.0
[Customisation]: #custom-filters-and-validators
[visitors]: https://en.wikipedia.org/wiki/Visitor_pattern
[AllowList]: https://docs.laminas.dev/laminas-filter/v2/standard-filters/#allowlist
[Callback filters]: https://docs.laminas.dev/laminas-filter/v2/standard-filters/#callback
[Callback validators]: https://docs.laminas.dev/laminas-validator/validators/callback/
[File]: https://docs.laminas.dev/laminas-filter/v2/file/
[InArray]: https://docs.laminas.dev/laminas-validator/validators/in-array/
[Regex]: https://docs.laminas.dev/laminas-validator/validators/regex/
[Barcode]: https://docs.laminas.dev/laminas-validator/validators/barcode/
[Uuid]: https://docs.laminas.dev/laminas-validator/validators/uuid/
[Psalm types]: https://psalm.dev/docs/running_psalm/plugins/plugins_type_system/#creating-type-object-instances-directly

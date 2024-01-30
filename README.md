# kynx/laminas-form-shape

[![Continuous Integration](https://github.com/kynx/laminas-form-shape/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/kynx/laminas-form-shape/actions/workflows/continuous-integration.yml)

Generate [Psalm] types for [Laminas forms]

**This is a work in progress**. There's a ton of code and tests, but no functioning command. Yet. Stay tuned!

## Installation

```commandline
composer require --dev kynx/laminas-form-shape
```

## Usage

```commandline
vendor/bin/laminas form:shape src/Forms/MyForm.php
```

...outputs an [array shape] something like:

```text
array{
    name:       non-empty-string,
    age:        null|numeric-string,
    gender?:    null|string,
    is_redhead: '0'|'1',
}
```

Future versions will include options for automatically adding a `@psalm-type` and `@psalm-template-extends` annotation
to the form, saving you the cut-and-paste.

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
need manual tweaks. In a perfect world you will have created forms with all possible elements present, then remove the
ones you don't need. But the world is far from perfect.

It aims to cover all the filters or validators installed when you `composer require laminas/laminas-form`. If it
encounters one it doesn't know about, it silently ignores it. See the [Customisation] section for pointers on handling
these.

Callback filters and validators are hard to handle. In future I will write a visitor to fuzz them and see what they do,
but even that will be far from perfect. I don't use them much: currently they are ignored. To protect the sanity of
us tool writers - and to keep your code testable and reusable - I suggest converting them to concrete instances.

## Configuration

TBC

## Custom filters and validators

TBC

[Psalm]: https://psalm.dev
[Laminas forms]: https://docs.laminas.dev/laminas-form/
[array shape]: https://psalm.dev/docs/annotating_code/type_syntax/array_types/#array-shapes
[3.17.0]: https://github.com/laminas/laminas-form/releases/tag/3.17.0
[Customisation]: #custom-filters-and-validators

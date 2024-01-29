# kynx/laminas-form-cli

Command line tools for [Laminas forms]

**This is a work in progress**. There's a ton of code and tests, but no functioning command. Yet. Stay tuned!

## Installation

```commandline
composer require --dev kynx/laminas-form-cli
```

## `form:shape`

Generate a [Psalm array shape] for a form.

Version [3.17.0] of Laminas Form introduced a Psalm template for describing the array returned by `Form::getData()`. 
This is a great improvement, and should winkle out potential bugs for anyone using Psalm. However generating the array 
shape for a form is a chore, and very easy to get wrong. Do you actually know the Psalm type produced by every 
combination of `required`, `allow_empty` and `continue_if_empty`? And what about all the possible filters and 
validators attached to the `InputFilter`?

This command introspects the input filter and generates the array shape for you.

### Usage

```commandline
vendor/bin/laminas form:shape src/Forms/MyForm.php
```

...outputs something like:

```
array{
    name:       non-empty-string,
    age:        numeric-string,
    gender?:    null|string,
    is_redhead: '0'|'1',
}
```

Future versions will include options for automatically adding a `@psalm-type` and `@psalm-template-extends` annotation
to the form, saving you the cut-and-paste. 

[Laminas forms]: https://docs.laminas.dev/laminas-form/
[Psalm array shape]: https://psalm.dev/docs/annotating_code/type_syntax/array_types/#array-shapes
[3.17.0]: https://github.com/laminas/laminas-form/releases/tag/3.17.0
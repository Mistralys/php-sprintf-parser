# Sprintf format placeholders parser

PHP-based parser class that can be used to find all format 
placeholders of the [sprintf][] function in a string, and 
get information on them.



## Requirements

- PHP >= 7.4

## Installation

Simply require the package via composer:

```
composer require php-sprintf-parser
```

Or add it to your `composer.json` manually:

```json
{
  "require":{
    "mistralys/php-sprintf-parser": "^1.0"
  }
}
```

## Usage

### Parse texts with several placeholders

Finding all formatting placeholders in a given string
can be done with the `parseString()` function.

```php
use function Mistralys\SprintfParser\Functions\parseString;

$parser = parseString('The price of product %1$s has been set to %2$.2d EUR.');

$placeholders = $parser->getPlaceholders();
```

Each placeholder instance can then be used to access
all relevant information on the placeholder's configuration,
like its number (if any), precision, width, etc.

### Parse individual formatting strings

Single placeholder format strings can also be parsed
to retrieve information on the placeholder directly.

```php
use function Mistralys\SprintfParser\Functions\parseFormat;

$placeholder = parseFormat('%1$.2d');
```

### Access placeholder information

A placeholder gives access to all individual components of
a format string. As the official PHP documentation for [sprintf][]
states, the format prototype looks like this:

```
%[argnum$][flags][width][.precision]specifier
```

The placeholder class allows easy access to each of these
components. The following is a valid format for example,
which uses all possible options.

```php
use function Mistralys\SprintfParser\Functions\parseFormat;

$placeholder = parseFormat("%1$+-0'*4.3d");

$placeholder->getSpecifier(); // s
$placeholder->getNumber(); // 1
$placeholder->getWidth(); // 4
$placeholder->getPrecision(); // 3
$placeholder->getPaddingChar(); // *
$placeholder->hasPlusMinusPrefix(); // true
$placeholder->hasLeftJustification(); // true
$placeholder->hasOnlyZeroLeftPadding(); // true 
```


[sprintf]:https://www.php.net/manual/en/function.sprintf.php

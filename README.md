# Repr

[![Build Status](https://secure.travis-ci.org/IcecaveStudios/repr.png)](http://travis-ci.org/IcecaveStudios/repr)

**Repr** provides a way to generate informational string representations of any value, based loosely on Python's [reprlib](http://docs.python.org/release/3.1.5/library/reprlib.html) library.

## Installation

**Repr** requires PHP 5.3.

### With [Composer](http://getcomposer.org/)

* Add 'icecave/repr' to the project's composer.json dependencies
* Run `php composer.phar install`

### Bare installation

* Clone from GitHub: `git clone git://github.com/IcecaveStudios/repr.git`
* Use a [PSR-0](https://github.com/php-fig/fig-standards//IcecaveStudios/repr/blob/master/accepted/PSR-0.md)
  compatible autoloader (namespace 'Icecave\Repr' in the 'lib' directory)

## Example

Use the ```Repr::repr()``` method to obtain a string representation for any type.

```php
<?php
use Icecave\Repr\Repr;

echo Repr::repr(array(1, 2, 3));
```

The output from the example above is:

```
[1, 2, 3]
```

### Arrays

Arrays are represented using PHP 5.4 style short array notation. By default a maximum of 3 elements are shown along with a count of any additional elements.
Nested arrays are represented up to 3 levels deep by default, with any arrays nested deeper than this showing only the element count.

### Numeric Values

Numbers are represented naturally, floating point values will always display a decimal point even if representing a whole number.

### Strings

Strings are represented enclosed in double quotes up to a default maximum length of 50 characters. Any control characters are shown as escape sequences.

### Objects

Objects are represented as a class name and SPL object hash enclosed in angle brackets. If the object has a __toString method the result of this is shown after the class name according to the rules of string representations specified above.

If an object implements the RepresentableInterface interface, the result of its stringRepresentation() method is used instead.

### Resources

Resources are represented as a resource type and ID enclosed in angle brackets. Stream resources will also display the stream mode.

### Other Types

All other types are represented by the result of [var_export()](http://php.net/manual/en/function.var-export.php) in lowercase.

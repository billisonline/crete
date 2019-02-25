# Crete

Enums for PHP

## Getting Started

### Prerequisites

* PHP 7.1+

### Installing

(`composer require` command coming soon once I can get this into Packagist)

### Running the tests

```
phpunit
```

## Basic usage

### Creating, instantiating, and comparing enums

An enum is a class extending `Enum`, with a set of unique integer constants. The value of each constant *must* be a unique integer.

```php
use \BYanelli\Crete\Enum;

class Animal extends \BYanelli\Crete\Enum
{
    const Zebra = 1;
    const Giraffe = 2;
    const Meerkat = 3;
}
```

The class is instantiated with one of these constant values. The equivalent `Enum::make()` method is also provided for convenience.

```php
$zebra = new Animal(Animal::Zebra);

$zebra = Animal::make(Animal::Zebra);
```

Enums can be compared to each other using `$enum->is()`.

```php
function print_distinctive_feature(Animal $animal)
{
    if ($animal->is(Animal::Zebra)) {
        echo 'stripes';
    } elseif ($animal->is(Animal::Giraffe)) {
        echo 'long neck';
    } elseif ($animal->is(Animal::Meerkat)) {
        echo 'standing posture';
    } else {
        throw new \Exception('unknown animal');
    }
}
```

### Strict enum comparisons

The above `is` method may return false positives when comparing an enum with a value from another enum class. For example, if the following enum class also exists:

```php
class Mineral extends Enum
{
    const Feldspar = 1;
    const Quartz = 2;
    const Mica = 3;
}
```

...the following false-positive comparison can be made:

```php
$zebra->is(Mineral::Feldspar); // true
```

This problem can be avoided by instantiating the `Mineral` instead of using the constant as a shortcut to test equality.

```php
$feldspar = Mineral::make(Mineral::Feldspar);

$zebra->is($feldspar); // false
```

To enforce strict comparisons, use `->isEnum()` instead of `->is()`.

```php
$zebra->isEnum(Mineral::Feldspar); // throws TypeError

$zebra->isEnum($feldspar); // false
```


## Collections

### Creating collections

The `Enum::all()` method creates an `EnumCollection` (which extends the base [Illuminate](https://github.com/illuminate/support) collection) of all enums for the given class. Typical collection methods such as `map`, `filter`, and `each` may be used. For example:

```php
Animal::all()->each(function (Animal $animal) {
    echo "{$animal->getName()}: {$animal->getId()}\n";
});
```

...prints the following:

```php
Zebra: 1
Giraffe: 2
Meerkat: 3
```

The `Enum::collect()` method creates a collection of specific enums on the class. For example, to get all animals except the meerkat, we can do the following:

```php
$animalsExceptMeerkat = Animal::collect(Animal::Zebra, Animal::Giraffe);
```

### Collection methods shortcut

Collections methods like `each`, `map`, and `filter` can be accessed through static methods on the `Enum` class. For example, the snippet above echoing each animal is equivalent to:

```php
Animal::each(function (Animal $animal) {
    echo "{$animal->getName()}: {$animal->getId()}\n";
});
```

### Checking whether a collection contains an enum

The `EnumCollection` class extends the base class' `contains` method, adding some logic to compare the enums in the collection to an enum id or name that is passed in. Example:

```php
$animalsExceptMeerkat->contains(Animal::Zebra); // true

$animalsExceptMeerkat->contains(Animal::Meerkat); // false
```

### Strict "contains" checking

Analogous to the `isEnum()` method above (see the "Strict enum comparisons" section), the `containsStrict()` method prevents false positives when checking for an enum in a collection.

```php
$animalsExceptMeerkat->contains(Mineral::Feldspar); // true

$animalsExceptMeerkat->containsStrict(Mineral::Feldspar); // false
```

## Authors

* [Bill Yanelli](https://billyanelli.com)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

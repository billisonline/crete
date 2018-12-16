<?php

namespace BYanelli\Numerate\Tests;

use BYanelli\Numerate\Tests\Library\Animal;
use BYanelli\Numerate\Tests\Library\Mineral;
use BYanelli\Numerate\Tests\Library\Vegetable;
use PHPUnit\Framework\TestCase;

class EnumCollectionTest extends TestCase
{
    public function testCollectAllEnumValues()
    {
        $animals = Animal::all();

        $this->assertCount(3, $animals);
    }

    public function testCollectSpecificEnumValues()
    {
        $someAnimals = Animal::collect(Animal::Zebra, Animal::Meerkat);

        $this->assertCount(2, $someAnimals);
    }

    public function testCheckEnumCollectionContainsValue()
    {
        $animals = Animal::collect(Animal::Zebra, Animal::Meerkat);
        $zebra = Animal::make(Animal::Zebra);
        $feldspar = Mineral::make(Mineral::Feldspar);

        $this->assertTrue($animals->contains(Animal::Zebra));
        $this->assertTrue($animals->contains('Zebra'));
        $this->assertTrue($animals->contains($zebra));

        $this->assertFalse($animals->contains($feldspar));

        $this->assertTrue($animals->contains(Mineral::Feldspar)); // Same ids -- inherent limitation of Enums

        $this->assertFalse($animals->contains(function (Animal $animal) {
            return $animal->getName() == 'Giraffe';
        }));

        $this->assertTrue($animals->contains('id', Animal::Meerkat));

        $this->assertTrue(Animal::contains('Zebra'));
    }

    public function testCheckEnumCollectionStrictlyContains()
    {
        $zebra = Animal::make(Animal::Zebra);

        $this->assertFalse(Animal::containsStrict(Animal::Zebra));
        $this->assertFalse(Animal::containsStrict('Zebra'));
        $this->assertTrue(Animal::containsStrict($zebra));

        $this->assertFalse(Animal::containsStrict(Mineral::Feldspar));

        $this->assertTrue(Animal::containsStrict('id', Mineral::Feldspar));
    }

    public function testDiffEnumCollection()
    {
        $animals = Animal::all()->diff(Animal::collect(Animal::Zebra, Animal::Giraffe));

        $this->assertFalse($animals->contains(Animal::Zebra));
        $this->assertCount(1, $animals);

        // Need to test with multiple combinations to make sure array_udiff works
        $animals = Animal::all()->diff(Animal::collect(Animal::Meerkat, Animal::Giraffe));

        $this->assertTrue($animals->contains(Animal::Zebra));
        $this->assertCount(1, $animals);

        $animals = Animal::all()->diff(Mineral::collect(Mineral::Feldspar, Mineral::Mica));

        $this->assertCount(3, $animals);

        // We can diff against a combination of enums and integers
        $animals = Animal::all()->push(Vegetable::Carrot)->diff([Animal::Meerkat, Vegetable::Carrot]);

        $this->assertCount(2, $animals);

        // We can diff against a single enum
        $animals = Animal::all()->diff(Mineral::make(Mineral::Feldspar));

        $this->assertCount(3, $animals);
    }
}
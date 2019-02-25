<?php

namespace BYanelli\Crete\Tests;

use BYanelli\Crete\EnumCollection;
use BYanelli\Crete\Tests\Library\Animal;
use BYanelli\Crete\Tests\Library\InvalidAnimal;
use BYanelli\Crete\Tests\Library\InvalidMineral;
use BYanelli\Crete\Tests\Library\InvalidVegetable;
use BYanelli\Crete\Tests\Library\Mineral;
use BYanelli\Crete\Tests\Library\Vegetable;
use BYanelli\Crete\Tests\Library\ZooAnimal;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    public function testInstantiateWithMake()
    {
        $zebraId = Animal::Zebra;
        $zebraName = 'Zebra';
        $localZebra = new ZooAnimal('Local Zoo', Animal::Zebra);

        $zebraFromId = Animal::make($zebraId);
        $zebraFromName = Animal::make($zebraName);
        $zebraFromEnum = Animal::make($zebraFromId);
        $zebraFromEnumable = Animal::make($localZebra);

        // Instantiating from name and id are equivalent
        $this->assertEquals($zebraName, $zebraFromId->getName());
        $this->assertEquals($zebraId, $zebraFromName->getId());

        // Id is preserved when insantiating from Enum and Enumable
        $this->assertEquals($zebraId, $zebraFromEnum->getId());
        $this->assertEquals($zebraId, $zebraFromEnumable->getId());
    }

    public function testInstantiateWithConstructor()
    {
        $zebraId = Animal::Zebra;
        $zebraName = 'Zebra';
        $localZebra = new ZooAnimal('Local Zoo', Animal::Zebra);

        $zebraFromId = new Animal($zebraId);
        $zebraFromName = new Animal($zebraName);
        $zebraFromEnum = new Animal($zebraFromId);
        $zebraFromEnumable = new Animal($localZebra);

        // Instantiating from name and id are equivalent
        $this->assertEquals($zebraName, $zebraFromId->getName());
        $this->assertEquals($zebraId, $zebraFromName->getId());

        // Id is preserved when insantiating from Enum and Enumable
        $this->assertEquals($zebraId, $zebraFromEnum->getId());
        $this->assertEquals($zebraId, $zebraFromEnumable->getId());
    }

    public function testCannotInstantiateWithNonIntegerOrStringValue()
    {
        $this->expectException(\Exception::class);

        Animal::make([]);
    }

    public function testCannotInstantiateWithInvalidInteger()
    {
        $this->expectException(\Exception::class);

        Animal::make(Vegetable::Carrot);
    }

    public function testCannotInstantiateWithInvalidString()
    {
        $this->expectException(\Exception::class);

        Animal::make('Broccoli');
    }

    public function testCannotInstantiateWithInstanceOfAnotherEnum()
    {
        $this->expectException(\Exception::class);

        Animal::make(Mineral::make(Mineral::Feldspar));
    }

    public function testCompareEnums()
    {
        $zebra = Animal::make(Animal::Zebra);
        $zooZebra = new ZooAnimal('Local Zoo', Animal::Zebra);
        $giraffe = Animal::make(Animal::Giraffe);
        $feldspar = Mineral::make(Mineral::Feldspar);

        $this->assertTrue($zebra->is($zooZebra));
        $this->assertFalse($zebra->is($giraffe));
        $this->assertFalse($zebra->is($feldspar));

        $this->assertTrue($zebra->is('Zebra'));
        $this->assertFalse($zebra->is('Giraffe'));
        $this->assertFalse($zebra->is('Feldspar'));

        $this->assertTrue($zebra->is(Animal::Zebra));
        $this->assertFalse($zebra->is(Animal::Giraffe));
        $this->assertTrue($zebra->is(Mineral::Feldspar)); // Same ids -- inherent limitation of Enums
    }

    public function testCheckEnumInList()
    {
        $zebra = Animal::make(Animal::Zebra);
        $zooZebra = new ZooAnimal('Local Zoo', Animal::Zebra);
        $giraffe = Animal::make(Animal::Giraffe);
        $carrot = Vegetable::make(Vegetable::Carrot);
        $feldspar = Mineral::make(Mineral::Feldspar);

        $enumList = [$zooZebra, $giraffe];

        $this->assertTrue($zebra->inList($enumList));
        $this->assertTrue($giraffe->inList($enumList));
        $this->assertFalse($carrot->inList($enumList));
        $this->assertFalse($feldspar->inList($enumList));

        $stringList = ['Zebra', 'Giraffe'];

        $this->assertTrue($zebra->inList($stringList));
        $this->assertTrue($giraffe->inList($stringList));
        $this->assertFalse($carrot->inList($stringList));
        $this->assertFalse($feldspar->inList($stringList));

        $integerList = [Animal::Zebra, Animal::Giraffe];

        $this->assertTrue($zebra->inList($integerList));
        $this->assertTrue($giraffe->inList($integerList));
        $this->assertFalse($carrot->inList($integerList));
        $this->assertTrue($feldspar->inList($integerList)); // Same ids -- inherent limitation of Enums
    }

    public function testCannotMakeEnumWithNonIntegerIds()
    {
        $this->expectException(\Exception::class);

        InvalidAnimal::make(InvalidAnimal::Zebra);
    }

    public function testCannotMakeEnumWithDuplicateIds()
    {
        $this->expectException(\Exception::class);

        InvalidVegetable::make(InvalidVegetable::Carrot);
    }

    public function testCannotMakeBlankEnum()
    {
        $this->expectException(\Exception::class);

        InvalidMineral::make(9999);
    }

    public function testCastEnumToString()
    {
        $zebra = Animal::make(Animal::Zebra);

        $this->assertEquals('Zebra', strval($zebra));
    }

    public function testCannotSetEnumArrayKey()
    {
        $this->expectException(\Exception::class);

        $zebra = Animal::make(Animal::Zebra);

        $zebra['stripes'] = true;
    }

    public function testCannotUnsetEnumArrayKey()
    {
        $this->expectException(\Exception::class);

        $zebra = Animal::make(Animal::Zebra);

        unset($zebra['stripes']);
    }
}
<?php

namespace BYanelli\Crete\Tests\Library;

use BYanelli\Crete\Enum;
use BYanelli\Crete\Enumable;

class ZooAnimal implements Enumable
{
    /**
     * @var string
     */
    private $zooName;
    /**
     * @var int
     */
    private $animalId;

    public function __construct(string $zooName, int $animalId)
    {
        $this->zooName = $zooName;
        $this->animalId = $animalId;
    }

    public function toEnum(): Enum
    {
        return Animal::make($this->animalId);
    }
}
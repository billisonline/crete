<?php

namespace BYanelli\Numerate;

use Illuminate\Support\Collection;
use stdClass;

class EnumCollection extends Collection
{
    /**
     * @var Enum|string
     */
    private $enumClass;

    public static function make($items=[])
    {
        throw new \Exception('not supported');
    }

    public function __construct($items, string $enumClass)
    {
        $this->validateEnumClass($enumClass);

        $this->enumClass = $enumClass;

        foreach ($this->getArrayableItems($items) as $item) {
            $this->items[] = $this->makeEnum($item);
        }
    }

    private function validateEnumClass(string $enumClass)
    {
        if (!is_subclass_of($enumClass, Enum::class)) {
            throw new \Exception;
        }
    }

    private function makeEnum($val): Enum
    {
        /** @var Enum $enumClass */
        $enumClass = $this->enumClass;

        return $enumClass::make($val);
    }

    private function canMakeEnum($val): bool
    {
        /** @var Enum $enumClass */
        $enumClass = $this->enumClass;

        return $enumClass::canMake($val);
    }

    public function contains($key, $operator = null, $value = null)
    {
        if (func_num_args() === 1) {
            if ($this->canMakeEnum($key)) {
                return $this->makeEnum($key)->inList($this->items);
            }

            if ($this->useAsCallable($key)) {
                $placeholder = new stdClass;

                return $this->first($key, $placeholder) !== $placeholder;
            }

            return in_array($key, $this->items);
        }

        return $this->contains($this->operatorForWhere(...func_get_args()));
    }
}
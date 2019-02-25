<?php

namespace BYanelli\Crete;

use Illuminate\Support\Collection;
use stdClass;

class EnumCollection extends Collection
{
    protected function isEnum($val): bool
    {
        return is_object($val) && ($val instanceof Enum);
    }

    protected function areEnumsEquivalent($a, $b): bool
    {
        /** @var Enum|mixed $a */
        /** @var Enum|mixed $b */
        return (
            ($this->isEnum($a) && $a->is($b))
            || ($this->isEnum($b) && $b->is($a))
        );
    }

    protected function areEnumsIdentical($a, $b): bool
    {
        /** @var Enum|mixed $a */
        return (
            $this->isEnum($a)
            && $this->isEnum($b)
            && $a->is($b)
        );
    }

    protected function compareEnums(Enum $a, Enum $b): int
    {
        if (get_class($a) === get_class($b)) {
            $a = $a->getId();
            $b = $b->getId();
        } else {
            $a = crc32(get_class($a));
            $b = crc32(get_class($b));
        }

        if ($a == $b) {
            return 0;
        } elseif ($a > $b) {
            return 1;
        } else {
            return -1;
        }
    }

    protected function getNonEnumItems(): array
    {
        return array_filter($this->items, function ($item) {
            return !(is_object($item) && ($item instanceof Enum));
        });
    }

    public function contains($key, $operator = null, $value = null)
    {
        if (func_num_args() === 1) {
            if ($this->useAsCallable($key)) {
                $placeholder = new stdClass;

                return $this->first($key, $placeholder) !== $placeholder;
            }

            if ($this->containsEnum($key)) {
                return true;
            }

            return in_array($key, $this->getNonEnumItems());
        }

        return $this->contains($this->operatorForWhere(...func_get_args()));
    }

    public function containsStrict($key, $value = null)
    {
        if (func_num_args() === 2) {
            return $this->contains(function ($item) use ($key, $value) {
                return data_get($item, $key) === $value;
            });
        }

        if ($this->useAsCallable($key)) {
            return ! is_null($this->first($key));
        }

        if ($this->containsEnumStrict($key)) {
            return true;
        }

        return in_array($key, $this->getNonEnumItems(), true);
    }

    public function containsEnum($other, bool $strict=false): bool
    {
        foreach ($this->items as $item) {
            if (
                $strict
                    ? $this->areEnumsIdentical($item, $other)
                    : $this->areEnumsEquivalent($item, $other)
            ) {
                return true;
            }
        }

        return false;
    }

    public function containsEnumStrict($other): bool
    {
        return $this->containsEnum($other, true);
    }

    protected function enumCompareCallback(): callable
    {
        /**
         * @param Enum|mixed $a
         * @param Enum|mixed $b
         * @return int
         */
        return function ($a, $b): int {
            if ($this->isEnum($a) && $this->isEnum($b)) {
                return $this->compareEnums($a, $b);
            } else {
                if ($this->isEnum($a)) {
                    $a = $a->getId();
                }

                if ($this->isEnum($b)) {
                    $b = $b->getId();
                }

                if ($a == $b) {
                    return 0;
                } elseif ($a > $b) {
                    return 1;
                } else {
                    return -1;
                }
            }
        };
    }

    public function diff($items)
    {
        return $this->diffUsing($items, $this->enumCompareCallback());
    }

    protected function getArrayableItems($items)
    {
        if ($items instanceof Enum) {
            return [$items]; // Don't cast an enum to an array even though it implements Arrayable
        } else {
            return parent::getArrayableItems($items);
        }
    }
}
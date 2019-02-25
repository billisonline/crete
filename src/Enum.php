<?php

namespace BYanelli\Crete;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @method static avg($callback = null)
 * @method static average($callback = null)
 * @method static median($key = null)
 * @method static mode($key = null)
 * @method static contains($key, $operator = null, $value = null)
 * @method static containsStrict($key, $value = null)
 * @method static crossJoin(...$lists)
 * @method static dd(...$args)
 * @method static filter($callback = null)
 */
abstract class Enum implements Arrayable, \ArrayAccess
{
    protected static $__constants = [];

    /** @var string */
    private $name;

    /** @var int */
    private $id;

    private static function safeConvertToString($id): string
    {
        try {
            return strval($id);
        } catch (\Throwable $t) {
            return '(non-string value)';
        }
    }

    /**
     * @param array $constants
     * @throws \Exception
     */
    private static function validateConstants(array $constants)
    {
        $idsSeen = [];

        foreach ($constants as $name => $id) {
            if (!is_integer($id)) {
                throw new \Exception('Class constant id must be an integer: '.static::safeConvertToString($id));
            }

            if (in_array($id, $idsSeen)) {
                throw new \Exception('Class constant id must be unique: '.static::safeConvertToString($idsSeen));
            }

            $idsSeen[] = $id;
        }

        if (empty($idsSeen)) {
            throw new \Exception('Class must have at least one constant');
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    private static function getConstants(): array
    {
        // Memoize this function to avoid running reflection multiple times
        if (!is_null($constants = static::$__constants[static::class] ?? null)) {
            return $constants;
        }

        $reflectionClass = new \ReflectionClass(static::class);

        $constants = $reflectionClass->getConstants();

        static::validateConstants($constants);

        return (static::$__constants[static::class] = $constants);
    }

    /**
     * @param string|int|Enum|Enumable $val
     * @throws \Exception
     */
    public function __construct($val)
    {
        $constants = static::getConstants();

        if (is_string($val)) {
            $this->name = $val;
            $this->id = $this->getIdFromConstantsList($constants, $val);
        } elseif (is_integer($val)) {
            $this->name = $this->getNameFromConstantsList($constants, $val);
            $this->id = $val;
        } elseif ($val instanceof Enum) {
            static::validateOther($val);
            $this->setIdsFromOther($val);
        } elseif ($val instanceof Enumable) {
            $other = $val->toEnum();

            static::validateOther($other);
            $this->setIdsFromOther($other);
        } else {
            throw new \Exception;
        }
    }

    /**
     * @param string|int|Enum|Enumable $val
     * @return Enum
     * @throws \Exception
     */
    public static function make($val): Enum
    {
        if ($val instanceof Enum) {
            static::validateOther($val);
            return $val;
        } elseif ($val instanceof Enumable) {
            $other = $val->toEnum();

            static::validateOther($other);
            return $other;
        } else {
            return new static($val);
        }
    }

    protected static function newCollection(iterable $items): EnumCollection
    {
        return new EnumCollection($items);
    }

    /**
     * @param $list
     * @return static[]|EnumCollection
     * @throws \Exception
     */
    public static function collect($list): EnumCollection
    {
        $list = (
            (is_iterable($list))
                ? $list
                : func_get_args()
        );

        $enums = [];

        foreach ($list as $item) {
            $enums[] = static::make($item);
        }

        return static::newCollection($enums);
    }

    /**
     * @return static[]|EnumCollection
     * @throws \Exception
     */
    public static function all(): EnumCollection
    {
        return static::collect(array_values(static::getConstants()));
    }

    /**
     * @param array $constants
     * @param int $id
     * @return string
     * @throws \Exception
     */
    private function getNameFromConstantsList(array $constants, int $id): string
    {
        foreach ($constants as $name => $v) {
            if ($v === $id) {
                return $name;
            }
        }

        throw new \Exception("Invalid id: {$id}");
    }

    private function getIdFromConstantsList(array $constants, string $name): int
    {
        if ($id = ($constants[$name] ?? null)) {
            return $id;
        } else {
            throw new \Exception("Invalid name: {$name}");
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function isEnum(Enum $other): bool
    {
        return (
            (get_class($other) === get_class($this))
            && ($other->getId() === $this->getId())
        );
    }

    public function is($other): bool
    {
        return (
            (
                is_object($other)
                && ($other instanceof Enum)
                && $this->isEnum($other)
            )
            || (
                is_object($other)
                && ($other instanceof Enumable)
                && $this->isEnum($other->toEnum())
            )
            || (
                is_integer($other)
                && ($other === $this->getId())
            )
            || (
                is_string($other)
                && ($other === $this->getName())
            )
        );
    }

    public function inList(iterable $list): bool
    {
        foreach ($list as $item) {
            if ($this->is($item)) {
                return true;
            }
        }

        return false;
    }

    private static function validateOther(Enum $other)
    {
        if (get_class($other) !== static::class) {
            throw new \Exception('Cannot instantiate '.class_basename(static::class).' with an instance of '.class_basename($other));
        }
    }

    private function setIdsFromOther(Enum $other)
    {
        $this->name = $other->getName();
        $this->id = $other->getId();
    }

    public function toArray()
    {
        return [
            'id'    => $this->getId(),
            'name'  => $this->getName(),
        ];
    }

    public function offsetExists($offset)
    {
        return isset($this->toArray()[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->toArray()[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \Exception('Enums are read only');
    }

    public function offsetUnset($offset)
    {
        throw new \Exception('Enums are read only');
    }

    public static function __callStatic($name, $arguments)
    {
        return static::all()->{$name}(...$arguments);
    }
}
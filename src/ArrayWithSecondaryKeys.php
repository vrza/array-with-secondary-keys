<?php

namespace CardinalCollections\ArrayWithSecondaryKeys;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;

use CardinalCollections\HigherOrderMethods;
use CardinalCollections\IterableUtils;
use CardinalCollections\Iterators\IteratorFactory;

class ArrayWithSecondaryKeys implements ArrayAccess, Countable, Iterator
{
    use HigherOrderMethods;

    // primary map
    private $p = [];
    // secondary indexes
    private $s = [];
    // iterator
    private $iterator;

    public function __construct(iterable $map = [], $iteratorClass = null)
    {
        $this->iterator = IteratorFactory::create($iteratorClass);
        foreach ($map as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    // ArrayAccess interface
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->append($value);
            return;
        }

        if (!ArrUtils::isValidArrayKey($offset)) {
            $type = gettype($offset);
            throw new InvalidArgumentException("Offset cannot be $type, allowed types are string or integer");
        }

        $prevSecondaryValues = $this->getAllSecondaryIndexValues($offset);
        $this->p[$offset] = $value;
        $this->iterator->addIfAbsent($offset);
        $this->updateAllSecondaryIndexValues($offset, $prevSecondaryValues);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->p[$offset]);
    }

    public function offsetUnset($offset): void
    {
        if (is_null($offset)) {
            return;
        }
        if (!ArrUtils::isValidArrayKey($offset)) {
            $type = gettype($offset);
            throw new InvalidArgumentException("Offset cannot be $type, allowed types are string or integer");
        }

        $prevSecondaryValues = $this->getAllSecondaryIndexValues($offset);
        unset($this->p[$offset]);
        $this->iterator->remove($offset);
        $this->updateAllSecondaryIndexValues($offset, $prevSecondaryValues);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (is_null($offset)) {
            return null;
        }
        if (!ArrUtils::isValidArrayKey($offset)) {
            $type = gettype($offset);
            throw new InvalidArgumentException("Offset cannot be $type, allowed types are string or integer");
        }

        return $this->p[$offset] ?? null;
    }

    // Iterator interface
    public function rewind(): void
    {
        $this->iterator->rewind();
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        $key = $this->iterator->key();
        return $key === null ? null : $this->p[$key];
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->iterator->key();
    }

    public function next(): void
    {
        $this->iterator->next();
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    // Countable interface
    public function count(): int
    {
        return count($this->p);
    }

    public function dump(): void
    {
        var_dump($this->p);
        var_dump($this->s);
    }

    public function isEmpty(): bool
    {
        return empty($this->p);
    }

    public function append($document): ArrayWithSecondaryKeys
    {
        $this->p[] = $document;
        $primaryKey = IterableUtils::lastKey($this->p);
        $this->iterator->addIfAbsent($primaryKey);
        $this->addNewDocumentToAllIndexes($primaryKey, $document);
        return $this;
    }

    public function put($key, $document): ArrayWithSecondaryKeys
    {
        if (!ArrUtils::isValidArrayKey($key)) {
            $type = gettype($key);
            throw new InvalidArgumentException("Array key cannot be $type, allowed types are string or integer");
        }

        $primaryKey = is_string($key) ? explode('.', $key)[0] : $key;

        $prevSecondaryValues = $this->getAllSecondaryIndexValues($primaryKey);

        if (is_string($key)) {
            ArrUtils::set($this->p, $key, $document);
        } else {
            $this->p[$key] = $document;
        }

        $this->iterator->addIfAbsent($primaryKey);
        $this->updateAllSecondaryIndexValues($primaryKey, $prevSecondaryValues);

        return $this;
    }

    public function add($key, $document): ArrayWithSecondaryKeys
    {
        return $this->put($key, $document);
    }

    public function get($key, $default = null)
    {
        return ArrUtils::get($this->p, $key, $default);
    }

    public function has($keys): bool
    {
        if (
            is_array($keys) ||
            (is_string($keys) && strpos($keys, '.') !== false)
        ) {
            return ArrUtils::has($this->p, $keys);
        } else {
            return $this->offsetExists($keys);
        }
    }

    public function remove($key): ArrayWithSecondaryKeys
    {
        if (!ArrUtils::isValidArrayKey($key)) {
            $type = gettype($key);
            throw new InvalidArgumentException("Array key cannot be $type, allowed types are string or integer");
        }

        if (!$this->has($key)) {
            return $this;
        }

        $primaryKey = is_string($key) ? explode('.', $key)[0] : $key;

        $prevSecondaryValues = $this->getAllSecondaryIndexValues($primaryKey);

        if ($key === $primaryKey) {
            $this->offsetUnset($key);
        } else {
            ArrUtils::forget($this->p, $key);
        }

        $this->updateAllSecondaryIndexValues($primaryKey, $prevSecondaryValues);

        return $this;
    }

    public function containsPrimaryKey($primaryKey): bool
    {
        return array_key_exists($primaryKey, $this->p);
    }

    public function containsSecondaryKey($index, $secondaryKey): bool
    {
        if (!array_key_exists($index, $this->s)) {
            throw new NoSuchIndexException("Index $index not present");
        }
        return array_key_exists($secondaryKey, $this->s[$index]);
    }

    public function getPrimaryKeyByIndex($index, $secondaryKey)
    {
        if (!array_key_exists($index, $this->s)) {
            throw new NoSuchIndexException("Index $index not present");
        }
        $primaryKey = ArrUtils::get($this->s[$index], $secondaryKey);
        return ArrUtils::isValidArrayKey($primaryKey) ? $primaryKey : null;
    }

    public function getByIndex($index, $secondaryKey, $default = null)
    {
        $primaryKey = $this->getPrimaryKeyByIndex($index, $secondaryKey);
        return ArrUtils::isValidArrayKey($primaryKey)
            ? ArrUtils::get($this->p, $primaryKey, $default)
            : $default;
    }

    public function updateByIndex($index, $secondaryKey, $document): bool
    {
        $primaryKey = $this->getPrimaryKeyByIndex($index, $secondaryKey);
        if (ArrUtils::isValidArrayKey($primaryKey)) {
            $this->offsetSet($primaryKey, $document);
            return true;
        } else {
            return false;
        }
    }

    public function removeByIndex($index, $secondaryKey): bool
    {
        $primaryKey = $this->getPrimaryKeyByIndex($index, $secondaryKey);
        if (ArrUtils::isValidArrayKey($primaryKey)) {
            $this->offsetUnset($primaryKey);
            return true;
        } else {
            return false;
        }
    }

    public function updateSecondaryKey($index, $existingValue, $newValue)
    {
        $primaryKey = $this->getPrimaryKeyByIndex($index, $existingValue);
        if (ArrUtils::isValidArrayKey($primaryKey)) {
            $document = $this->p[$primaryKey];
            ArrUtils::set($document, $index, $newValue);
            $this->p[$primaryKey] = $document;
            $this->updateSecondaryIndexValue($index, $primaryKey, $existingValue, $newValue);
        }
        return $primaryKey;
    }

    public function putIfAbsent($key, $document)
    {
        if (!ArrUtils::isValidArrayKey($key)) {
            $type = gettype($key);
            throw new InvalidArgumentException("Array key cannot be $type, allowed types are string or integer");
        }

        $existing = ArrUtils::get($this->p, $key);
        if ($existing === null) {
            $this->put($key, $document);
            return null;
        } else {
            return $this->get($key, $document);
        }
    }

    public function asArray(): array
    {
        return $this->p;
    }

    public function primaryKeys(): array
    {
        return array_keys($this->p);
    }

    public function secondaryKeys($index): array
    {
        if (!array_key_exists($index, $this->s)) {
            throw new NoSuchIndexException("Index $index not present");
        }
        return array_keys($this->s[$index]);
    }

    public function createIndex($index): void
    {
        if (array_key_exists($index, $this->s)) {
            return;
        }
        $this->s[$index] = [];
        foreach ($this->p as $primaryKey => $document) {
            $this->addNewDocumentToIndex($index, $primaryKey, $document);
        }
    }

    private function addNewDocumentToIndex($index, $primaryKey, $document): void
    {
        $secondaryKey = ArrUtils::get($document, $index);
        if (ArrUtils::isValidArrayKey($secondaryKey)) {
            $this->s[$index][$secondaryKey] = $primaryKey;
        }
    }

    private function addNewDocumentToAllIndexes($primaryKey, $document): void
    {
        foreach (array_keys($this->s) as $index) {
            $this->addNewDocumentToIndex($index, $primaryKey, $document);
        }
    }

    private function getAllSecondaryIndexValues($primaryKey): array
    {
        $document = $this->p[$primaryKey] ?? null;
        $prevSecondaryValues = [];
        foreach (array_keys($this->s) as $index) {
            $prevSecondaryValues[$index] = ArrUtils::get($document, $index);
        }
        return $prevSecondaryValues;
    }

    private function updateAllSecondaryIndexValues($primaryKey, $prevSecondaryValues): void
    {
        $document = $this->offsetExists($primaryKey) ? $this->p[$primaryKey] : null;
        foreach (array_keys($this->s) as $index) {
            $prevValue = $prevSecondaryValues[$index];
            $newValue = ArrUtils::get($document, $index);
            $this->updateSecondaryIndexValue($index, $primaryKey, $prevValue, $newValue);
        }
    }

    private function updateSecondaryIndexValue($index, $primaryKey, $prevValue, $newValue): void
    {
        if ($prevValue != $newValue) {
            if (is_null($newValue)) {
                unset($this->s[$index][$prevValue]);
            } else {
                $this->s[$index][$newValue] = $primaryKey;
            }
        }
    }

}

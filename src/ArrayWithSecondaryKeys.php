<?php

namespace VladimirVrzic\ArrayWithSecondaryKeys;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;

class ArrayWithSecondaryKeys implements ArrayAccess, Countable, Iterator
{
    // primary map
    private $p;
    // secondary indexes
    private $s = [];

    public function __construct(array $array = [])
    {
        $this->p = $array;
    }

    // ArrayAccess interface
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            return $this->append($value);
        } else {
            if (!ArrUtils::isValidArrayKey($offset)) {
                $type = gettype($offset);
                throw new InvalidArgumentException("Offset cannot be $type, allowed types are string or integer");
            }

            $prevSecondaryValues = $this->getAllSecondaryIndexValues($offset);
            $this->p[$offset] = $value;
            $this->updateAllSecondaryIndexValues($offset, $prevSecondaryValues);
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->p[$offset]);
    }

    public function offsetUnset($offset)
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
        $this->updateAllSecondaryIndexValues($offset, $prevSecondaryValues);
    }

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
    public function rewind() {
        return reset($this->p);
    }

    public function current() {
        return current($this->p);
    }

    public function key() {
        return key($this->p);
    }

    public function next() {
        return next($this->p);
    }

    public function valid(): bool {
        return key($this->p) !== null;
    }

    // Countable interface
    public function count(): int
    {
        return count($this->p);
    }

    public function dump()
    {
        var_dump($this->p);
        var_dump($this->s);
    }

    public function isEmpty(): bool
    {
        return empty($this->p);
    }

    public function append($document)
    {
        $this->p[] = $document;
        // $primaryKey = array_key_last($this->p); // PHP >= 7.3
        $primaryKey = array_keys($this->p)[count($this->p) - 1];
        $this->addNewDocumentToAllIndexes($primaryKey, $document);
    }

    public function put($key, $document)
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

        $this->updateAllSecondaryIndexValues($primaryKey, $prevSecondaryValues);
    }

    public function get($key, $default = null)
    {
        return ArrUtils::get($this->p, $key, $default);
    }

    public function has($keys): bool
    {
        return ArrUtils::has($this->p, $keys);
    }

    public function remove($key)
    {
        if (!ArrUtils::isValidArrayKey($key)) {
            $type = gettype($key);
            throw new InvalidArgumentException("Array key cannot be $type, allowed types are string or integer");
        }

        if (!$this->has($key)) {
            return;
        }

        $primaryKey = is_string($key) ? explode('.', $key)[0] : $key;

        $prevSecondaryValues = $this->getAllSecondaryIndexValues($primaryKey);

        if (is_string($key)) {
            ArrUtils::forget($this->p, $key);
        } else {
            unset($this->p[$key]);
        }

        $this->updateAllSecondaryIndexValues($primaryKey, $prevSecondaryValues);
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
            $this->p[$primaryKey] = $document;
            return true;
        } else {
            return false;
        }
    }

    public function removeByIndex($index, $secondaryKey): bool
    {
        $primaryKey = $this->getPrimaryKeyByIndex($index, $secondaryKey);
        if (ArrUtils::isValidArrayKey($primaryKey)) {
            unset($this->p[$primaryKey]);
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
        if (is_null($existing)) {
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

    public function createIndex($index)
    {
        if (array_key_exists($index, $this->s)) {
            return;
        }
        $this->s[$index] = [];
        foreach ($this->p as $primaryKey => $document) {
            $this->addNewDocumentToIndex($index, $primaryKey, $document);
        }
    }

    private function addNewDocumentToIndex($index, $primaryKey, $document)
    {
        $secondaryKey = ArrUtils::get($document, $index);
        if (ArrUtils::isValidArrayKey($secondaryKey)) {
            $this->s[$index][$secondaryKey] = $primaryKey;
        }
    }

    private function addNewDocumentToAllIndexes($primaryKey, $document)
    {
        foreach (array_keys($this->s) as $index) {
            $this->addNewDocumentToIndex($index, $primaryKey, $document);
        }
    }

    private function getAllSecondaryIndexValues($primaryKey): array {
        $document = $this->p[$primaryKey] ?? null;
        $prevSecondaryValues = [];
        foreach (array_keys($this->s) as $index) {
            $prevSecondaryValues[$index] = ArrUtils::get($document, $index);
        }
        return $prevSecondaryValues;
    }

    private function updateAllSecondaryIndexValues($primaryKey, $prevSecondaryValues) {
        $document = $this->has($primaryKey) ? $this->p[$primaryKey] : null;
        foreach (array_keys($this->s) as $index) {
            $prevValue = $prevSecondaryValues[$index];
            $newValue = ArrUtils::get($document, $index);
            $this->updateSecondaryIndexValue($index, $primaryKey, $prevValue, $newValue);
        }
    }

    private function updateSecondaryIndexValue($index, $primaryKey, $prevValue, $newValue) {
        if ($prevValue != $newValue) {
            if (is_null($newValue)) {
                unset($this->s[$index][$prevValue]);
            } else {
                $this->s[$index][$newValue] = $primaryKey;
            }
        }
    }

}
